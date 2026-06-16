<?php
declare(strict_types=1);

require_once 'signage_config.php';
require_once 'proxy_helper.php';

// ==========================================
// CONFIGURATION
// ==========================================
$cache_file = 'nws_forecast_cache.json';
$cache_time = 3600; // 1 hour cache
// ==========================================

// Milford, PA (Gold Key) resolved forecast URL
$url = "https://api.weather.gov/gridpoints/BGM/109,27/forecast";

fetch_with_cache($url, $cache_file, $cache_time);
?>
