<?php
require_once 'signage_config.php';
require_once 'proxy_helper.php';

// ==========================================
// CONFIGURATION
// ==========================================
$api_key = GOOGLE_API_KEY;
$calendar_id = GOOGLE_CALENDAR_ID; // Gold Key Public Calendar
$cache_file = 'calendar_cache.json';
$cache_time = 3600; // 1 Hour Cache
// ==========================================

$timeMin = date('c'); // Now
$timeMax = date('c', strtotime('+7 days')); // Next 7 days

// 'singleEvents=true' expands recurring events into individual instances
$url = "https://www.googleapis.com/calendar/v3/calendars/" . urlencode($calendar_id) . "/events?"
    . "key={$api_key}"
    . "&timeMin=" . urlencode($timeMin)
    . "&timeMax=" . urlencode($timeMax)
    . "&singleEvents=true"
    . "&orderBy=startTime";

fetch_with_cache($url, $cache_file, $cache_time);
?>