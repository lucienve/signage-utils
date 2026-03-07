<?php
declare(strict_types=1);

/**
 * GarbageDayCalculator.php
 * 
 * Extracts the business logic for calculating Gold Key garbage pickup days
 * into a testable class. Handles holiday offsets and manual sheet overrides.
 */
class GarbageDayCalculator
{
    /**
     * Calculates the pickup day based on the current timestamp, holiday rules, and sheet override data.
     *
     * @param int $current_timestamp The "current" time (usually time())
     * @param string|null $sheet_data The raw JSON string returned from the Google Sheet override cache, or null if fetch failed.
     * @return array Associative array containing the pickup date, name, delay status, reason, and instructions.
     */
    public function calculate(int $current_timestamp, ?string $sheet_data): array
    {
        // 1. Calculate "Next Monday"
        // If today is Monday, we assume pickup hasn't happened yet until proven otherwise
        if (date('N', $current_timestamp) == 1) {
            $calc_timestamp = $current_timestamp;
        }
        else {
            // Using a relative string based off the current timestamp provided
            $calc_timestamp = strtotime('next monday', $current_timestamp);
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
            if (strpos($calc_date_str, "-01-01") !== false)
                $reason = "New Year's Day";
            elseif (strpos($calc_date_str, "-07-04") !== false)
                $reason = "July 4th";
            elseif (strpos($calc_date_str, "-12-25") !== false)
                $reason = "Christmas";
            elseif (date('n', $calc_timestamp) == 5)
                $reason = "Memorial Day";
            else
                $reason = "Labor Day";
        }

        // 2. CHECK FOR MANUAL OVERRIDE (Google Sheet)
        if ($sheet_data) {
            $json = json_decode($sheet_data, true);

            // Check if there is data in the cell
            if (isset($json['values']) && !empty($json['values'][0][0])) {
                $override_str = $json['values'][0][0]; // Date (e.g., "2026-02-03")
                $override_reason = $json['values'][0][1] ?? "Service Alert";

                // Safety Check: Is this a valid date format?
                $override_ts = strtotime($override_str);

                if ($override_ts) {
                    // LOGIC: Only use the override if it is Today or in the Future.
                    // We base "today midnight" off the injected current_timestamp for testability
                    $start_of_today = strtotime(date('Y-m-d 00:00:00', $current_timestamp));

                    if ($override_ts >= $start_of_today) {
                        $final_date = $override_ts;
                        $is_delayed = true; // Manual overrides are always "delays/alerts"
                        $reason = $override_reason;
                    }
                }
            }
        }

        // 3. OUTPUT
        return [
            "pickup_date" => date('l, F j', $final_date), // "Tuesday, Feb 3"
            "day_name" => date('l', $final_date), // "Tuesday"
            "is_delayed" => $is_delayed,
            "reason" => $reason,
            "instructions" => "Please have cans out by 6:00 AM."
        ];
    }
}