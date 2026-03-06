<?php
/**
 * proxy_helper.php
 * 
 * Shared helper function for proxying external APIs with caching.
 * Ensures consistent error handling, header management, and caching logic
 * across all digital signage endpoints.
 */

function fetch_with_cache($url, $cache_file, $cache_time, $headers = [], $return_only = false)
{
    // 1. Serve Cache if fresh
    if (file_exists($cache_file) && (time() - filemtime($cache_file) < $cache_time)) {
        if ($return_only)
            return file_get_contents($cache_file);
        header('Content-Type: application/json');
        header('X-Source: Cache');
        readfile($cache_file);
        exit;
    }

    // 2. Fetch using cURL
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Common fixes for shared hosting
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_USERAGENT, 'GoldKeySignage/1.0');
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    if (!empty($headers)) {
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    }

    $data = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);

    // 3. Error Handling
    if ($http_code != 200 || $data === false) {
        // If live fetch fails, try to serve OLD cache
        if (file_exists($cache_file)) {
            if ($return_only)
                return file_get_contents($cache_file);
            header('Content-Type: application/json');
            header('X-Source: Stale-Cache');
            readfile($cache_file);
            exit;
        }

        // If no cache exists, show the error
        if ($return_only)
            return false;
        http_response_code(500);
        $error_msg = $data === false ? "cURL Error: $curl_error" : "API returned status $http_code";
        die(json_encode(["error" => $error_msg, "details" => $data]));
    }

    // 4. Save to Cache and Serve
    $write_result = file_put_contents($cache_file, $data);

    if ($write_result === false && !$return_only) {
        // If we can't save the cache (permissions issue), notify via header
        header('X-Cache-Error: Write Permission Denied');
    }

    if ($return_only)
        return $data;

    header('Content-Type: application/json');
    header('X-Source: Live API');
    echo $data;
}
?>