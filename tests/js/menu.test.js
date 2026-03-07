const { cleanPrice, isMenuExpired, categorizeMenuItems } = require('../../js/menu');

describe('Menu Utility Functions', () => {

    test('cleanPrice formats prices correctly', () => {
        expect(cleanPrice("5")).toBe("$5");
        expect(cleanPrice("5.99")).toBe("$5.99");
        expect(cleanPrice("$10")).toBe("$10"); // Keeps existing $
        expect(cleanPrice("Market Price")).toBe("Market Price"); // Doesn't force $ on strings
        expect(cleanPrice("")).toBe(""); // Handles empty
        expect(cleanPrice(null)).toBe("");
    });

    test('isMenuExpired identifies expired dates accurately', () => {
        // Set system time to Feb 5th 2026, 12:00 PM EST 
        jest.useFakeTimers();
        jest.setSystemTime(new Date('2026-02-05T12:00:00-05:00'));

        // Expiration is Feb 4th (Yesterday). Should be expired.
        expect(isMenuExpired("2026-02-04")).toBe(true);

        // Expiration is Feb 6th (Tomorrow). Should NOT be expired.
        expect(isMenuExpired("2026-02-06")).toBe(false);

        // Expiration is Feb 4th (Yesterday) but in short format. Should be expired.
        expect(isMenuExpired("2/4/2026")).toBe(true);

        // Expiration is Feb 6th (Tomorrow) but in short format. Should NOT be expired.
        expect(isMenuExpired("2/6/2026")).toBe(false);

        // Expiration is Feb 5th (Today). Should NOT be expired (expires at midnight).
        expect(isMenuExpired("2026-02-05")).toBe(false);

        // Handle invalid parsing
        expect(isMenuExpired("Invalid Date Format")).toBe(false);
        expect(isMenuExpired("")).toBe(false);

        jest.useRealTimers();
    });

    test('categorizeMenuItems groups row arrays properly', () => {
        const mockRows = [
            ["Expiration", "2026-10-24", "", ""],
            ["Lunch", "Burger", "10", "A tasty burger"],
            ["Soup", "Tomato", "5.50", ""],
            ["Salad", "Caesar", "8", "Romaine"],
            ["Entree", "Steak", "$25", "Medium Rare"],
            ["Dessert", "Cake", "6", ""],
            ["Unknown Category", "Ignores Me", "10", ""],
            ["", "", "", ""] // Empty row
        ];

        // Mock Date object inside categorizeMenuItems slightly trickier through global so we let standard runtime rule
        // Assuming current year is before 2026-10-24

        const result = categorizeMenuItems(mockRows);

        // Check categories
        expect(result.lunch.length).toBe(1);
        expect(result.lunch[0]).toEqual({ name: "Burger", price: "$10", desc: "A tasty burger" });

        expect(result.soups.length).toBe(1);
        expect(result.salads.length).toBe(1);
        expect(result.entrees.length).toBe(1);
        expect(result.entrees[0].price).toBe("$25"); // Dollar sign preserved

        expect(result.desserts.length).toBe(1);
        expect(result.apps.length).toBe(0);

        // Check meta
        expect(result.expirationDate).toBe("2026-10-24");
    });

});
