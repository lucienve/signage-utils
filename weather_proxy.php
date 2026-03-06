<?php
require_once 'signage_config.php';
require_once 'proxy_helper.php';

// ==========================================
// CONFIGURATION
// ==========================================
$station_id = WEATHERFLOW_STATION_ID;
$token = WEATHERFLOW_TOKEN;
$cache_file = 'tempest_cache.json';
$cache_time = 600; // 10 minutes
// ==========================================

$url = "https://swd.weatherflow.com/swd/rest/observations/station/{$station_id}?token={$token}";

fetch_with_cache($url, $cache_file, $cache_time);
?>