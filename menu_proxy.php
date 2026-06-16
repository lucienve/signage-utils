<?php
declare(strict_types=1);

require_once 'signage_config.php';
require_once 'proxy_helper.php';

// ==========================================
// CONFIGURATION
// ==========================================
$api_key = GOOGLE_API_KEY;
$spreadsheet_id = GOOGLE_SHEET_ID; // Signage Config Sheet ID
$range = 'Menu!A2:D'; // Tab Name + Columns
$cache_file = 'menu_cache.json';
$cache_time = 900; // 15 Minutes

clear_cache_if_requested($cache_file);
// ==========================================

$url = "https://sheets.googleapis.com/v4/spreadsheets/" . $spreadsheet_id . "/values/" . $range . "?key=" . $api_key;

fetch_with_cache($url, $cache_file, $cache_time);
?>