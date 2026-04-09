<?php
declare(strict_types=1);

// garbage_day_proxy.php
header('Content-Type: application/json');
require_once 'signage_config.php';
require_once 'proxy_helper.php';

// ==========================================
// CONFIGURATION
// ==========================================
$GOOGLE_API_KEY = GOOGLE_API_KEY;
$GOOGLE_SHEET_ID = GOOGLE_SHEET_ID;
$cache_file = 'garbage_cache.json'; // Added cache file for consistency
$cache_time = 3600; // 1 Hour

// If clear cache is requested, delete the existing cache file
if (isset($_GET['clear'])) {
    if (!isset($_GET['token']) || $_GET['token'] !== CACHE_CLEAR_TOKEN) {
        http_response_code(403);
        die(json_encode(["error" => "Invalid or missing cache clear token"]));
    }
    if (file_exists($cache_file)) {
        unlink($cache_file);
    }
}

// ==========================================
// 1. FETCH OVERRIDE DATA (Google Sheet)
// ==========================================

// Fetch just the first row of data (A2:B2) from the 'Garbage' tab
$sheet_url = "https://sheets.googleapis.com/v4/spreadsheets/" . $GOOGLE_SHEET_ID . "/values/Garbage!A2:B2?key=" . $GOOGLE_API_KEY;

// Use our helper to fetch the raw data and return it as a string
$sheet_data = fetch_with_cache($sheet_url, $cache_file, $cache_time, [], true);

// ==========================================
// 2. CALCULATE AND OUTPUT
// ==========================================

require_once 'GarbageDayCalculator.php';

$calculator = new GarbageDayCalculator();
$result = $calculator->calculate(time(), $sheet_data);

echo json_encode($result);
?>