<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../../GarbageDayCalculator.php';

/**
 * Unit tests for the GarbageDayCalculator class.
 */
class GarbageDayCalculatorTest extends TestCase
{
    private GarbageDayCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new GarbageDayCalculator();
    }

    /**
     * Tests standard Monday pickup when no delays or overrides exist.
     */
    public function testStandardMondayPickup(): void
    {
        // "Current" time: A random Wednesday (Feb 4, 2026) -> Next Monday is Feb 9
        $currentTime = strtotime('2026-02-04 12:00:00');
        $result = $this->calculator->calculate($currentTime, null);

        $this->assertEquals("Monday, February 9", $result['pickup_date']);
        $this->assertEquals("Monday", $result['day_name']);
        $this->assertFalse($result['is_delayed']);
        $this->assertEquals("", $result['reason']);
    }

    /**
     * Tests that pickup date remains unchanged when run on Monday itself.
     */
    public function testPickupOnMondayStaysMonday(): void
    {
        // "Current" time: A Monday (Feb 9, 2026) -> Pickup is Today, Feb 9
        $currentTime = strtotime('2026-02-09 08:00:00');
        $result = $this->calculator->calculate($currentTime, null);

        $this->assertEquals("Monday, February 9", $result['pickup_date']);
        $this->assertFalse($result['is_delayed']);
    }

    /**
     * Tests that Memorial Day holiday shifts the pickup to Tuesday.
     */
    public function testHolidayPushToTuesday_MemorialDay(): void
    {
        // "Current" time: Thursday before Memorial Day 2026 (May 21, 2026)
        // Memorial day 2026 is Monday, May 25th.
        $currentTime = strtotime('2026-05-21 12:00:00');
        $result = $this->calculator->calculate($currentTime, null);

        // Expected pickup is Tuesday, May 26th
        $this->assertEquals("Tuesday, May 26", $result['pickup_date']);
        $this->assertEquals("Tuesday", $result['day_name']);
        $this->assertTrue($result['is_delayed']);
        $this->assertEquals("Memorial Day", $result['reason']);
    }

    /**
     * Tests that Labor Day holiday shifts the pickup to Tuesday.
     */
    public function testHolidayPushToTuesday_LaborDay(): void
    {
        // "Current" time: Friday before Labor Day 2026 (Sept 4, 2026)
        // Labor Day 2026 is Monday, Sept 7th.
        $currentTime = strtotime('2026-09-04 12:00:00');
        $result = $this->calculator->calculate($currentTime, null);

        // Expected pickup is Tuesday, Sept 8th
        $this->assertEquals("Tuesday, September 8", $result['pickup_date']);
        $this->assertTrue($result['is_delayed']);
        $this->assertEquals("Labor Day", $result['reason']);
    }

    /**
     * Tests manual override parsed from Google Sheet override data.
     */
    public function testManualOverrideFromGoogleSheet(): void
    {
        // "Current" time: Feb 4, 2026. Normal pickup would be Feb 9.
        $currentTime = strtotime('2026-02-04 12:00:00');

        // Provide mock sheet data for an override on Thursday, Feb 12
        $mockSheetData = json_encode([
            "values" => [
                ["2026-02-12", "Blizzard Delay"]
            ]
        ]);

        $result = $this->calculator->calculate($currentTime, $mockSheetData);

        $this->assertEquals("Thursday, February 12", $result['pickup_date']);
        $this->assertEquals("Thursday", $result['day_name']);
        $this->assertTrue($result['is_delayed']);
        $this->assertEquals("Blizzard Delay", $result['reason']);
    }

    /**
     * Tests that past manual overrides are ignored.
     */
    public function testPastManualOverrideIsIgnored(): void
    {
        // "Current" time: Feb 4, 2026.
        $currentTime = strtotime('2026-02-04 12:00:00');

        // Provide mock sheet data for an override in the PAST (Jan 1, 2026)
        $mockSheetData = json_encode([
            "values" => [
                ["2026-01-01", "Past Delay"]
            ]
        ]);

        $result = $this->calculator->calculate($currentTime, $mockSheetData);

        // Should ignore past override and calculate standard pickup for Feb 9
        $this->assertEquals("Monday, February 9", $result['pickup_date']);
        $this->assertFalse($result['is_delayed']);
    }

    /**
     * Tests that invalid dates in manual overrides are safely ignored.
     */
    public function testInvalidDateManualOverrideIsIgnored(): void
    {
        // "Current" time: Feb 4, 2026. Normal pickup would be Feb 9.
        $currentTime = strtotime('2026-02-04 12:00:00');

        // Provide mock sheet data with garbage text as date
        $mockSheetData = json_encode([
            "values" => [
                ["NOT_A_DATE", "Bad Data"]
            ]
        ]);

        $result = $this->calculator->calculate($currentTime, $mockSheetData);

        // Should ignore invalid date and calculate standard pickup for Feb 9
        $this->assertEquals("Monday, February 9", $result['pickup_date']);
        $this->assertFalse($result['is_delayed']);
    }

    /**
     * Tests that empty dates in manual overrides are safely ignored.
     */
    public function testEmptyDateManualOverrideIsIgnored(): void
    {
        // "Current" time: Feb 4, 2026. Normal pickup would be Feb 9.
        $currentTime = strtotime('2026-02-04 12:00:00');

        // Provide mock sheet data with empty string as date
        $mockSheetData = json_encode([
            "values" => [
                ["", "Empty Date"]
            ]
        ]);

        $result = $this->calculator->calculate($currentTime, $mockSheetData);

        // Should ignore empty date and calculate standard pickup for Feb 9
        $this->assertEquals("Monday, February 9", $result['pickup_date']);
        $this->assertFalse($result['is_delayed']);
    }

    /**
     * Tests that malformed JSON in sheet data is safely ignored.
     */
    public function testMalformedJsonManualOverrideIsIgnored(): void
    {
        // "Current" time: Feb 4, 2026. Normal pickup would be Feb 9.
        $currentTime = strtotime('2026-02-04 12:00:00');

        // Provide malformed JSON
        $mockSheetData = "{ invalid json }";

        $result = $this->calculator->calculate($currentTime, $mockSheetData);

        // Should ignore malformed JSON and calculate standard pickup for Feb 9
        $this->assertEquals("Monday, February 9", $result['pickup_date']);
        $this->assertFalse($result['is_delayed']);
    }
}