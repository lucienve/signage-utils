<?php
declare(strict_types=1);

/**
 * proxy_helper.php
 * 
 * Shared helper function for proxying external APIs with caching.
 * Ensures consistent error handling, header management, and caching logic
 * across all digital signage endpoints.
 */

/**
 * Fetches data from an API and caches it.
 *
 * @param string $url The API URL to fetch.
 * @param string $cache_file Path to the cache file.
 * @param int $cache_time Cache validity time in seconds.
 * @param array<string> $headers Optional HTTP headers for cURL.
 * @param bool $return_only If true, returns the data instead of echoing it.
 * @return string|bool Returns string data, boolean false on error/failure. Note: may exit directly.
 */
function fetch_with_cache(string $url, string $cache_file, int $cache_time, array $headers = [], bool $return_only = false): string|bool
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
    return true;
}

/**
 * Safely clears the cache file if a 'clear' query parameter is present.
 *
 * @param string $cache_file Path to the cache file.
 * @return void
 */
function clear_cache_if_requested(string $cache_file): void
{
    if (isset($_GET['clear'])) {
        if (file_exists($cache_file)) {
            try {
                if (is_writable($cache_file)) {
                    unlink($cache_file);
                } else {
                    header('X-Cache-Error: Cache file is not writable');
                }
            } catch (\Throwable $e) {
                error_log("Failed to clear cache for $cache_file: " . $e->getMessage());
            }
        }
    }
}
?>