<?php
declare(strict_types=1);

require_once 'signage_config.php';
require_once 'proxy_helper.php';

// ==========================================
// CONFIGURATION
// ==========================================
$api_key = GOOGLE_API_KEY;
$spreadsheet_id = GOOGLE_SHEET_ID;
$range = 'GoBoard!A2:D2'; // Tab Name + Columns
$cache_file = 'goboard_cache.json';
$cache_time = 900; // 15 Minutes

// If clear_cache is requested, delete the existing cache file
if (isset($_GET['clear_cache']) && $_GET['clear_cache'] == '1') {
    if (file_exists($cache_file)) {
        unlink($cache_file);
    }
}
// ==========================================

$url = "https://sheets.googleapis.com/v4/spreadsheets/" . $spreadsheet_id . "/values/" . $range . "?key=" . $api_key;

fetch_with_cache($url, $cache_file, $cache_time);
?>