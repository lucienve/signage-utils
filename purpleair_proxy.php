<?php
require_once 'signage_config.php';
require_once 'proxy_helper.php';

// ==========================================
// CONFIGURATION
// ==========================================
$sensor_index = PURPLEAIR_SENSOR_INDEX; // Gold Key Sensor
$api_key = PURPLEAIR_API_KEY;
$cache_file = 'purpleair_cache.json';
$cache_time = 300; // 5 minutes
// ==========================================

$url = "https://api.purpleair.com/v1/sensors/{$sensor_index}?fields=pm2.5_atm,humidity";
$headers = [
    "X-API-Key: {$api_key}"
];

fetch_with_cache($url, $cache_file, $cache_time, $headers);
?>