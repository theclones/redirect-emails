<?php

// Global variables for campaign and email IDs
$GLOBALS['campaign_id'] = null;
$GLOBALS['email_id'] = null;
$GLOBALS['campaign_data'] = null;
$GLOBALS['email_data'] = null;

class CampaignHelper
{
    private $redis;
    private $variants = [];
    private $cache_ttl = 300; // 5 minutes cache for campaigns
    private $email_dashboard_url;

    public function __construct()
    {
        $this->initRedis();
        $this->loadVariants();
        $this->email_dashboard_url = $this->getEmailDashboardUrl();
        $this->initializeGlobalIds();
    }

    private function initRedis()
    {
        try {
            $this->redis = new Redis();

            // Get Redis URL from environment
            $redisUrl = getenv('REDIS_URL');
            if (empty($redisUrl)) {
                if (isset($_ENV['REDIS_URL'])) {
                    $redisUrl = $_ENV['REDIS_URL'];
                } elseif (isset($_SERVER['REDIS_URL'])) {
                    $redisUrl = $_SERVER['REDIS_URL'];
                }
            }

            if ($redisUrl) {
                // Parse Redis URL: redis://[username[:password]]@host:port[/database]
                $parsedUrl = parse_url($redisUrl);

                $host = $parsedUrl['host'] ?? 'localhost';
                $port = $parsedUrl['port'] ?? 6379;
                $password = $parsedUrl['pass'] ?? null;
                $database = isset($parsedUrl['path']) ? ltrim($parsedUrl['path'], '/') : 0;

                if (!$this->redis->connect($host, $port, 2.5)) {
                    error_log("Failed to connect to Redis at {$host}:{$port}");
                    $this->redis = null;
                    return;
                }

                // Authenticate if password is provided
                if ($password && !$this->redis->auth($password)) {
                    error_log("Redis authentication failed");
                    $this->redis = null;
                    return;
                }

                // Select database if specified
                if ($database && !$this->redis->select($database)) {
                    error_log("Failed to select Redis database {$database}");
                }

            } else {
                // Fallback to old method for backward compatibility
                $host = $_ENV['REDIS_HOST'] ?? 'redis';
                $port = $_ENV['REDIS_PORT'] ?? 6379;

                if (!$this->redis->connect($host, $port, 2.5)) {
                    error_log("Failed to connect to Redis at {$host}:{$port}");
                    $this->redis = null;
                }
            }
        } catch (Exception $e) {
            error_log("Redis connection error: " . $e->getMessage());
            $this->redis = null;
        }
    }

    private function loadVariants()
    {
        $variants_dir = __DIR__ . '/variants';
        if (!is_dir($variants_dir)) {
            return;
        }

        $cache_key = 'campaign:variants:list';

        // Try to get from cache first
        if ($this->redis && $cached = $this->redis->get($cache_key)) {
            $this->variants = json_decode($cached, true);
            return;
        }

        // Load from filesystem
        $this->variants = [];
        $dirs = scandir($variants_dir);
        foreach ($dirs as $dir) {
            if ($dir !== '.' && $dir !== '..' && is_dir($variants_dir . '/' . $dir)) {
                $this->variants[] = $dir;
            }
        }

        // Cache the variants list
        if ($this->redis && !empty($this->variants)) {
            $this->redis->setex($cache_key, 3600, json_encode($this->variants)); // 1 hour cache for variants
        }
    }

    private function getEmailDashboardUrl()
    {
        $url = getenv("EMAIL_DASHBOARD_URL");
        if (empty($url)) {
            if (isset($_ENV["EMAIL_DASHBOARD_URL"])) {
                $url = $_ENV["EMAIL_DASHBOARD_URL"];
            } elseif (isset($_SERVER["EMAIL_DASHBOARD_URL"])) {
                $url = $_SERVER["EMAIL_DASHBOARD_URL"];
            } else {
                $url = "http://email-dashboard-api"; // Default fallback
            }
        }
        return rtrim($url, '/');
    }

    /**
     * Initialize global IDs and data from URL/parameters
     */
    private function initializeGlobalIds()
    {
        // Extract and set global campaign ID
        $GLOBALS['campaign_id'] = $this->extractCampaignId();

        // Extract and set global email ID
        $GLOBALS['email_id'] = $this->extractEmailId();

        // Load campaign data if campaign ID exists
        if ($GLOBALS['campaign_id']) {
            $GLOBALS['campaign_data'] = $this->getCampaignData($GLOBALS['campaign_id']);
        }

        // Load email data if email ID exists
        if ($GLOBALS['email_id']) {
            $GLOBALS['email_data'] = $this->getEmailData($GLOBALS['email_id']);
        }
    }

    /**
     * Extract campaign ID from URL path or GET parameter
     * Priority: cid-{ID} in path > ?cid={ID} parameter
     */
    public function extractCampaignId()
    {
        // Check path for cid-{ID} pattern first (highest priority)
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        if (preg_match('/cid-(\d+)/', $request_uri, $matches)) {
            return intval($matches[1]);
        }

        // Check GET parameter ?cid={ID}
        if (isset($_GET['cid']) && is_numeric($_GET['cid'])) {
            return intval($_GET['cid']);
        }

        // Fallback to default from environment
        $defaultCampaignId = getenv("DEFAULT_CAMPAIGN_ID");
        if (empty($defaultCampaignId)) {
            if (isset($_ENV["DEFAULT_CAMPAIGN_ID"])) {
                $defaultCampaignId = $_ENV["DEFAULT_CAMPAIGN_ID"];
            } elseif (isset($_SERVER["DEFAULT_CAMPAIGN_ID"])) {
                $defaultCampaignId = $_SERVER["DEFAULT_CAMPAIGN_ID"];
            }
        }

        return $defaultCampaignId ? intval($defaultCampaignId) : null;
    }

    /**
     * Extract email ID from URL path or GET parameter
     * Priority: eid-{ID} in path > ?eid={ID} parameter
     */
    public function extractEmailId()
    {
        // Check path for eid-{ID} pattern first (highest priority)
        $request_uri = $_SERVER['REQUEST_URI'] ?? '';
        if (preg_match('/eid-([a-zA-Z0-9@._-]+)/', $request_uri, $matches)) {
            return urldecode($matches[1]);
        }

        // Check GET parameter ?eid={ID}
        if (isset($_GET['eid'])) {
            return $_GET['eid'];
        }

        return null;
    }

    /**
     * Fetch campaign data with 5-minute Redis caching
     */
    public function getCampaignData($campaignId = null, $skipCache = false)
    {
        if (!$campaignId) {
            $campaignId = $this->extractCampaignId();
        }

        if (!$campaignId) {
            error_log("[CampaignHelper] No campaign ID found");
            return null;
        }

        $cache_key = "campaign:{$campaignId}";

        // Try Redis first unless cache is disabled
        if (!$skipCache && $this->redis) {
            $cached = $this->redis->get($cache_key);
            if ($cached) {
                $data = json_decode($cached, true);
                if ($data) {
                    return $data;
                }
            }
        }

        // Fetch from API
        $apiUrl = "{$this->email_dashboard_url}/api/public/campaigns/{$campaignId}";


        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $apiUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        if ($httpCode == 200 && !empty($response)) {
            $responseData = json_decode($response, true);
            if (isset($responseData['data'])) {
                $campaignData = $responseData['data'];

                // Store in Redis using emails-panel format
                if ($this->redis) {
                    // Store main campaign data
                    $this->redis->setex($cache_key, $this->cache_ttl, json_encode($campaignData));

                    // Store additional quick-access keys like emails-panel does
                    if (isset($campaignData['campaignType'])) {
                        $this->redis->setex("campaign:{$campaignId}:type", $this->cache_ttl, $campaignData['campaignType']);
                    }
                    if (isset($campaignData['landingPageStreamId'])) {
                        $this->redis->setex("campaign:{$campaignId}:landing_stream", $this->cache_ttl, $campaignData['landingPageStreamId']);
                    }
                    if (isset($campaignData['redirectStreamId'])) {
                        $this->redis->setex("campaign:{$campaignId}:redirect_stream", $this->cache_ttl, $campaignData['redirectStreamId']);
                    }
                }
                return $campaignData;
            }
        }

        error_log("[CampaignHelper] Failed to fetch campaign {$campaignId} - HTTP {$httpCode}, Error: {$error}");
        return null;
    }

    /**
     * Track campaign CLICK action
     */
    public function trackClickAction($campaignId, $email = null, $ipAddress = null, $userAgent = null)
    {
        if (!$campaignId) {
            return false;
        }

        $apiUrl = "{$this->email_dashboard_url}/api/public/campaigns/actions";

        $data = [
            'campaignId' => intval($campaignId),
            'actionType' => 'CLICKED',
            'ipAddress' => $ipAddress ?: $this->getClientIp(),
            'userAgent' => $userAgent ?: ($_SERVER['HTTP_USER_AGENT'] ?? '')
        ];

        if ($email) {
            $data['email'] = $email;
        }

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $apiUrl);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($curl, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 2);
        curl_setopt($curl, CURLOPT_TIMEOUT, 5);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode == 200 || $httpCode == 201) {
            return true;
        } else {
            error_log("[CampaignHelper] Failed to track CLICK action: HTTP {$httpCode}");
            return false;
        }
    }

    /**
     * Get client IP address
     */
    private function getClientIp()
    {
        $headers = [
            'HTTP_CF_CONNECTING_IP',
            'HTTP_TRUE_CLIENT_IP',
            'HTTP_FASTLY_CLIENT_IP',
            'HTTP_X_ENVOY_EXTERNAL_ADDRESS',
            'HTTP_X_CLIENT_IP',
            'HTTP_CLIENT_IP',
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'HTTP_REAL_IP',
            'HTTP_FORWARDED_FOR',
            'REMOTE_ADDR'
        ];

        foreach ($headers as $header) {
            if (isset($_SERVER[$header])) {
                $ip = $_SERVER[$header];
                if (is_string($ip)) {
                    // Handle comma-separated IPs (like X-Forwarded-For)
                    if (strpos($ip, ',') !== false) {
                        $ip = trim(explode(',', $ip)[0]);
                    }
                    if (filter_var($ip, FILTER_VALIDATE_IP)) {
                        return $ip;
                    }
                }
            }
        }

        return '127.0.0.1'; // Fallback
    }

    /**
     * Select random variant from available variants
     */
    public function selectRandomVariant()
    {
        if (empty($this->variants)) {
            return null;
        }

        return $this->variants[array_rand($this->variants)];
    }

    /**
     * Enhanced variant selection for user with better distribution
     */
    public function selectVariantForUser($userId)
    {
        if (empty($this->variants)) {
            return null;
        }

        // Use a combination of user ID and current time for better randomization
        $seed = crc32($userId . microtime(true));
        mt_srand($seed);

        // Get a random index
        $randomIndex = mt_rand(0, count($this->variants) - 1);

        return $this->variants[$randomIndex];
    }

    /**
     * Get or create user assignment for variant
     */
    public function getVariantForUser($userId)
    {
        if (!$userId || empty($this->variants)) {
            return null;
        }

        $cache_key = "user:variant:{$userId}";
        $clientIp = $this->getClientIp();

        // Check for force variant parameter (for testing)
        if (isset($_GET['force_variant']) && in_array($_GET['force_variant'], $this->variants)) {
            $forced_variant = $_GET['force_variant'];
            // Cache the forced assignment
            if ($this->redis) {
                $this->redis->setex($cache_key, 86400 * 30, $forced_variant);
            }
            error_log("[CampaignHelper] FORCED variant '{$forced_variant}' for IP {$clientIp} (userId: {$userId})");
            return $forced_variant;
        }

        // Check if user already has assigned variant
        if ($this->redis) {
            $cached_variant = $this->redis->get($cache_key);
            if ($cached_variant && in_array($cached_variant, $this->variants)) {
                error_log("[CampaignHelper] CACHED variant '{$cached_variant}' for IP {$clientIp} (userId: {$userId})");
                return $cached_variant;
            }
        }

        // Assign new variant using enhanced selection
        $selected_variant = $this->selectVariantForUser($userId);

        // Cache the assignment (long TTL since it shouldn't change)
        if ($this->redis && $selected_variant) {
            $this->redis->setex($cache_key, 86400 * 30, $selected_variant); // 30 days
        }

        error_log("[CampaignHelper] NEW variant '{$selected_variant}' assigned to IP {$clientIp} (userId: {$userId})");
        return $selected_variant;
    }

    /**
     * Create X-Accel-Redirect header for nginx internal redirect
     * Handles both index.php and index.html priority
     */
    public function createXAccelRedirect($variant, $requestedPath = null)
    {
        if (!$variant || !in_array($variant, $this->variants)) {
            return null;
        }

        $variantPath = "/var/www/html/variants/{$variant}";

        // Always redirect to the variant root index file, regardless of requested path
        // This ensures that paths like /something-eid-4099811 go to the variant's index page
        // Check for index.html first (since these seem to be static sites), then index.php
        if (file_exists($variantPath . '/index.html')) {
            return "/internal-variant/{$variant}/index.html";
        } elseif (file_exists($variantPath . '/index.php')) {
            return "/internal-variant/{$variant}/index.php";
        }

        // Default to index.html if neither exists
        return "/internal-variant/{$variant}/index.html";
    }

    /**
     * Generate unique user ID from IP and User Agent
     */
    public function generateUserId($ip = null, $userAgent = null)
    {
        $ip = $ip ?: $this->getClientIp();
        $userAgent = $userAgent ?: ($_SERVER['HTTP_USER_AGENT'] ?? '');

        $fingerprint = $ip . '|' . $userAgent . '|' . date('Y-m-d'); // Daily rotation
        return hash('sha256', $fingerprint);
    }

    /**
     * Set variant assignment cookie
     */
    public function setVariantCookie($variant, $domain = null)
    {
        if (!$variant || !in_array($variant, $this->variants)) {
            return false;
        }

        $cookie_options = [
            'expires' => time() + (86400 * 30), // 30 days
            'path' => '/',
            'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
            'httponly' => true,
            'samesite' => 'Lax'
        ];

        if ($domain) {
            $cookie_options['domain'] = $domain;
        }

        return setcookie('campaign_variant', $variant, $cookie_options);
    }

    /**
     * Store campaign data in Redis - exactly matches emails-panel RedisService format
     */
    public function storeCampaignData($campaignId, $campaignData, $ttl = null)
    {
        if (!$this->redis || !$campaignId) {
            return false;
        }

        $ttl = $ttl ?: $this->cache_ttl;

        // Store complete campaign data using emails-panel key format
        $key = "campaign:{$campaignId}";
        $result = $this->redis->setex($key, $ttl, json_encode($campaignData));

        // Store campaign type for quick access (emails-panel format)
        if (isset($campaignData['campaignType'])) {
            $this->redis->setex("campaign:{$campaignId}:type", $ttl, $campaignData['campaignType']);
        }

        // Store stream IDs for quick access (emails-panel format)
        if (isset($campaignData['landingPageStreamId'])) {
            $this->redis->setex("campaign:{$campaignId}:landing_stream", $ttl, $campaignData['landingPageStreamId']);
        }
        if (isset($campaignData['redirectStreamId'])) {
            $this->redis->setex("campaign:{$campaignId}:redirect_stream", $ttl, $campaignData['redirectStreamId']);
        }

        return $result;
    }

    /**
     * Get available variants
     */
    public function getVariants()
    {
        return $this->variants;
    }

    /**
     * Record analytics/metrics for variant assignments
     */
    public function recordVariantAssignment($userId, $variant, $campaignId = null)
    {
        if (!$this->redis) {
            return false;
        }

        $today = date('Y-m-d');
        $hour = date('Y-m-d:H');

        // Increment counters
        $this->redis->incr("stats:variants:assignments:{$today}");
        $this->redis->incr("stats:variant:{$variant}:{$today}");
        $this->redis->incr("stats:variants:hourly:{$hour}");

        if ($campaignId) {
            $this->redis->incr("stats:campaign:{$campaignId}:variants:{$today}");
        }

        // Set expiry on stats keys (keep for 30 days)
        $this->redis->expire("stats:variants:assignments:{$today}", 86400 * 30);
        $this->redis->expire("stats:variant:{$variant}:{$today}", 86400 * 30);
        $this->redis->expire("stats:variants:hourly:{$hour}", 86400 * 30);

        if ($campaignId) {
            $this->redis->expire("stats:campaign:{$campaignId}:variants:{$today}", 86400 * 30);
        }

        return true;
    }

    /**
     * Health check
     */
    public function isHealthy()
    {
        return [
            'redis_connected' => $this->redis && $this->redis->ping(),
            'variants_loaded' => count($this->variants),
            'variants' => $this->variants,
            'email_dashboard_url' => $this->email_dashboard_url
        ];
    }

    /**
     * Fetch email data with Redis caching - matches emails-panel format exactly
     */
    public function getEmailData($emailId = null, $skipCache = false)
    {
        if (!$emailId) {
            $emailId = $this->extractEmailId();
        }

        if (!$emailId) {
            return null;
        }

        // Check cache first by email ID
        if (!$skipCache && $this->redis) {
            $cached = $this->redis->get("email_id:{$emailId}");
            if ($cached) {
                $data = json_decode($cached, true);
                if ($data) {
                    return $data;
                }
            }
        }

        // Cache miss - fetch from API using numeric ID
        $apiUrl = "{$this->email_dashboard_url}/api/public/emails?id={$emailId}";

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $apiUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);

        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpCode == 200 && !empty($response)) {
            $responseData = json_decode($response, true);
            if (isset($responseData['data']) && isset($responseData['data']['email'])) {
                $emailData = $responseData['data'];
                $emailAddress = strtolower($emailData['email']);

                // Store in Redis in BOTH formats
                if ($this->redis) {
                    // Store by email ID (for redirect-emails lookup)
                    $this->redis->setex("email_id:{$emailId}", 3600, json_encode($emailData));

                    // Store by email address (emails-panel format)
                    $this->redis->setex("email:{$emailAddress}", 3600, json_encode($emailData));

                    // Store additional quick-access keys like emails-panel does
                    if (isset($emailData['provider'])) {
                        $this->redis->setex("email:{$emailAddress}:provider", 3600, $emailData['provider']['name']);
                    }
                    if (isset($emailData['country'])) {
                        $this->redis->setex("email:{$emailAddress}:country", 3600, $emailData['country']['code']);
                    }
                }

                return $emailData;
            }
        }

        error_log("[CampaignHelper] Failed to fetch email data for ID {$emailId}");
        return null;
    }

    /**
     * Store email data in Redis - exactly matches emails-panel RedisService format
     */
    public function storeEmailData($email, $emailData, $ttl = 3600) {
        if (!$this->redis || !$email) {
            return false;
        }

        $emailLower = strtolower($email);

        // Store complete email data (emails-panel format)
        $key = "email:{$emailLower}";
        $result = $this->redis->setex($key, $ttl, json_encode($emailData));

        // Store provider info for quick access (emails-panel format)
        if (isset($emailData['provider'])) {
            $this->redis->setex("email:{$emailLower}:provider", $ttl, $emailData['provider']['name']);
        }

        // Store country code for quick access (emails-panel format)
        if (isset($emailData['country'])) {
            $this->redis->setex("email:{$emailLower}:country", $ttl, $emailData['country']['code']);
        }

        return $result;
    }

    /**
     * Get email data by email address - exactly matches emails-panel RedisService format
     */
    public function getEmailDataByAddress($email) {
        if (!$this->redis || !$email) {
            return null;
        }

        $key = "email:" . strtolower($email);
        $cached = $this->redis->get($key);
        if ($cached) {
            return json_decode($cached, true);
        }
        return null;
    }


    /**
     * Get redirect stream ID from campaign data for Adspect
     */
    public function getRedirectStreamId()
    {
        $campaignData = $GLOBALS['campaign_data'];
        if ($campaignData && isset($campaignData['redirectStreamId'])) {
            return $campaignData['redirectStreamId'];
        }
        return null;
    }

    /**
     * Get landing page stream ID from campaign data for Adspect
     */
    public function getLandingPageStreamId()
    {
        $campaignData = $GLOBALS['campaign_data'];
        if ($campaignData && isset($campaignData['landingPageStreamId'])) {
            return $campaignData['landingPageStreamId'];
        }
        return null;
    }

    /**
     * ADSPECT FUNCTION: Redirect to campaign domain with cid and eid parameters only
     * This function is called from Adspect when redirect flow is needed
     */
    public function redirectToCampaignDomain()
    {
        // Use global variables
        $campaignId = $GLOBALS['campaign_id'];
        $emailId = $GLOBALS['email_id'];
        $campaignData = $GLOBALS['campaign_data'];
        $emailData = $GLOBALS['email_data'];

        if (!$campaignId) {
            error_log("[CampaignHelper] redirectToCampaignDomain: No campaign ID found");
            http_response_code(400);
            exit("Campaign ID required");
        }

        if (!$campaignData) {
            error_log("[CampaignHelper] redirectToCampaignDomain: Campaign {$campaignId} not found");
            http_response_code(404);
            exit("Campaign not found");
        }

        // Track CLICK action
        $email = $emailData ? $emailData['email'] : null;
        $this->trackClickAction($campaignId, $email);

        // Parse domains - they might be comma-separated or an array
        $domains = $campaignData['domains'] ?? null;
        if (!$domains) {
            error_log("[CampaignHelper] redirectToCampaignDomain: No domains found in campaign {$campaignId}");
            http_response_code(404);
            exit("No redirect domains configured");
        }

        if (is_string($domains)) {
            $domainList = array_map('trim', explode(',', $domains));
        } elseif (is_array($domains)) {
            $domainList = $domains;
        } else {
            $domainList = [];
        }

        // Filter out empty domains
        $domainList = array_filter($domainList, function ($domain) {
            return !empty($domain);
        });

        if (empty($domainList)) {
            error_log("[CampaignHelper] redirectToCampaignDomain: No valid domains in campaign {$campaignId}");
            http_response_code(404);
            exit("No valid redirect domains");
        }

        // Pick a random domain
        $randomDomain = $domainList[array_rand($domainList)];

        // Ensure the domain has a protocol
        if (!preg_match('/^https?:\/\//', $randomDomain)) {
            $randomDomain = 'https://' . $randomDomain;
        }

        // Build new query parameters - only cid and eid
        $queryParams = [];
        if ($campaignId) {
            $queryParams['cid'] = $campaignId;
        }
        if ($emailId) {
            $queryParams['eid'] = $emailId;
        }

        // Build final redirect URL
        $redirectUrl = $randomDomain;
        if (!empty($queryParams)) {
            $redirectUrl .= '?' . http_build_query($queryParams);
        }


        // Perform 302 redirect
        header("Location: {$redirectUrl}", true, 302);
        exit();
    }

    /**
     * ADSPECT FUNCTION: X-Accel-Redirect to random variant
     * This function is called from Adspect when landing page flow is needed
     */
    public function redirectToRandomVariant()
    {
        // Use global variables
        $campaignId = $GLOBALS['campaign_id'];
        $emailId = $GLOBALS['email_id'];
        $emailData = $GLOBALS['email_data'];

        // Track CLICK action if we have campaign data
        if ($campaignId) {
            $email = $emailData ? $emailData['email'] : null;
            $this->trackClickAction($campaignId, $email);
        }

        // Generate user ID for variant assignment
        $userId = $this->generateUserId();

        // Check if user already has variant assigned via cookie
        $assignedVariant = $_COOKIE['campaign_variant'] ?? null;

        if (!$assignedVariant || !in_array($assignedVariant, $this->variants)) {
            // Assign new variant
            $assignedVariant = $this->getVariantForUser($userId);

            if (!$assignedVariant) {
                error_log("[CampaignHelper] redirectToRandomVariant: No variants available");
                http_response_code(500);
                exit("No variants available");
            }

            // Set cookie for future requests
            $this->setVariantCookie($assignedVariant);

            // Record the assignment
            $this->recordVariantAssignment($userId, $assignedVariant, $campaignId);

        }

        // Create X-Accel-Redirect header
        $requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $xAccelRedirect = $this->createXAccelRedirect($assignedVariant, $requestPath);

        if ($xAccelRedirect) {
            header("X-Accel-Redirect: {$xAccelRedirect}");
            exit();
        } else {
            error_log("[CampaignHelper] redirectToRandomVariant: Failed to create X-Accel-Redirect");
            http_response_code(500);
            exit("Redirect failed");
        }
    }

    /**
     * Clear campaign cache (for debugging)
     */
    public function clearCampaignCache($campaignId = null)
    {
        if (!$this->redis) {
            return false;
        }

        if ($campaignId) {
            return $this->redis->del("campaign:data:{$campaignId}");
        }

        // Clear all campaign caches
        $pattern = "campaign:data:*";
        $keys = $this->redis->keys($pattern);
        if ($keys) {
            return $this->redis->del($keys);
        }

        return true;
    }
}