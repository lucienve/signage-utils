<?php
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

// ==========================================
// 1. AUTOMATIC CALCULATION (The Default)
// ==========================================

// Calculate "Next Monday"
$today = time();
// If today is Monday, we assume pickup hasn't happened yet until proven otherwise
if (date('N', $today) == 1) {
    $calc_timestamp = $today;
}
else {
    $calc_timestamp = strtotime('next monday');
}

$year = date('Y', $calc_timestamp);
$calc_date_str = date('Y-m-d', $calc_timestamp);

// Define Holiday Mondays (Dates that push pickup to Tuesday)
$holiday_mondays = [];
// Fixed Dates (only if on Monday)
if (date('N', strtotime("$year-01-01")) == 1)
    $holiday_mondays[] = "$year-01-01"; // New Years
if (date('N', strtotime("$year-07-04")) == 1)
    $holiday_mondays[] = "$year-07-04"; // July 4th
if (date('N', strtotime("$year-12-25")) == 1)
    $holiday_mondays[] = "$year-12-25"; // Christmas
// Floating Dates (Always Monday)
$holiday_mondays[] = date('Y-m-d', strtotime("last monday of may $year")); // Memorial Day
$holiday_mondays[] = date('Y-m-d', strtotime("first monday of september $year")); // Labor Day

// Determine Standard Pickup
$final_date = $calc_timestamp;
$is_delayed = false;
$reason = "";

if (in_array($calc_date_str, $holiday_mondays)) {
    $is_delayed = true;
    $final_date = strtotime('+1 day', $calc_timestamp); // Move to Tuesday

    // Set Reason
    if (strpos($calc_date_str, "-01-01"))
        $reason = "New Year's Day";
    elseif (strpos($calc_date_str, "-07-04"))
        $reason = "July 4th";
    elseif (strpos($calc_date_str, "-12-25"))
        $reason = "Christmas";
    elseif (date('n', $calc_timestamp) == 5)
        $reason = "Memorial Day";
    else
        $reason = "Labor Day";
}

// ==========================================
// 2. CHECK FOR MANUAL OVERRIDE (Google Sheet)
// ==========================================

// Fetch just the first row of data (A2:B2) from the 'Garbage' tab
$sheet_url = "https://sheets.googleapis.com/v4/spreadsheets/" . $GOOGLE_SHEET_ID . "/values/Garbage!A2:B2?key=" . $GOOGLE_API_KEY;

// Use our helper to fetch the raw data and return it as a string
$sheet_data = fetch_with_cache($sheet_url, $cache_file, $cache_time, [], true);

if ($sheet_data) {
    $json = json_decode($sheet_data, true);

    // Check if there is data in the cell
    if (!empty($json['values'][0][0])) {
        $override_str = $json['values'][0][0]; // Date (e.g., "2026-02-03")
        $override_reason = $json['values'][0][1] ?? "Service Alert";

        // Safety Check: Is this a valid date format?
        $override_ts = strtotime($override_str);

        if ($override_ts) {
            // LOGIC: Only use the override if it is Today or in the Future.
            $start_of_today = strtotime("today midnight");

            if ($override_ts >= $start_of_today) {
                $final_date = $override_ts;
                $is_delayed = true; // Manual overrides are always "delays/alerts"
                $reason = $override_reason;
            }
        }
    }
}

// ==========================================
// 3. OUTPUT
// ==========================================
echo json_encode([
    "pickup_date" => date('l, F j', $final_date), // "Tuesday, Feb 3"
    "day_name" => date('l', $final_date), // "Tuesday"
    "is_delayed" => $is_delayed,
    "reason" => $reason,
    "instructions" => "Please have cans out by 6:00 AM."
]);
?>