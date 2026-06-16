<?php
declare(strict_types=1);

require_once 'signage_config.php';
require_once 'proxy_helper.php';

// ==========================================
// CONFIGURATION
// ==========================================
$cache_file = 'nws_alerts_cache.json';
$cache_time = 300; // 5 minutes cache
// ==========================================

clear_cache_if_requested($cache_file);

// Milford, PA (Gold Key) resolved alerts URL
$url = "https://api.weather.gov/alerts/active?point=41.323,-74.802";

fetch_with_cache($url, $cache_file, $cache_time);
?>
