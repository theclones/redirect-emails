<?php
/**
 * Lightweight variant assignment for visitors without campaign context.
 * Picks a random variant, sets cookie, and X-Accel-Redirects to it.
 * Skips Adspect — no fingerprinting or RPC needed here.
 */
ini_set('display_errors', '0');
error_reporting(E_ALL);

require_once __DIR__ . '/campaign_helper.php';

$helper = new CampaignHelper();

// Check if cookie already exists (race condition guard)
$existingVariant = $_COOKIE['campaign_variant'] ?? null;
if ($existingVariant && in_array($existingVariant, $helper->getVariants())) {
    $variant = $existingVariant;
} else {
    $variant = $helper->selectRandomVariant();

    if (!$variant) {
        // Fallback to default if no variants available
        header("X-Accel-Redirect: /internal-variant/default/index.html");
        exit();
    }

    $helper->setVariantCookie($variant);
}

// Build the internal redirect path preserving the original request path
$requestPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$requestPath = $requestPath ?: '/';

// Map the request path into the variant's directory
$variantBasePath = "/var/www/html/variants/{$variant}";
$targetPath = "/internal-variant/{$variant}{$requestPath}";

// If requesting root or a directory, resolve to index file
if (substr($requestPath, -1) === '/' || $requestPath === '/') {
    if (file_exists($variantBasePath . $requestPath . 'index.html')) {
        $targetPath = "/internal-variant/{$variant}{$requestPath}index.html";
    } elseif (file_exists($variantBasePath . $requestPath . 'index.php')) {
        $targetPath = "/internal-variant/{$variant}{$requestPath}index.php";
    }
}

header("X-Accel-Redirect: {$targetPath}");
exit();
