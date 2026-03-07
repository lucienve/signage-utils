const { toF, toMph, toInches, calculateAQI, calc } = require('../../js/weather');

describe('Weather Utility Functions', () => {

    test('toF converts Celsius to Fahrenheit', () => {
        expect(toF(0)).toBe(32);
        expect(toF(100)).toBe(212);
        expect(toF(-40)).toBe(-40);
        expect(Math.round(toF(23.5))).toBe(74); // 74.3
    });

    test('toMph converts m/s to mph', () => {
        expect(toMph(10)).toBe("22");
        expect(toMph(0)).toBe("0");
        expect(toMph(5.5)).toBe("12");
    });

    test('toInches converts mm to inches', () => {
        expect(toInches(25.4)).toBe("1.00");
        expect(toInches(0)).toBe("0.00");
        expect(toInches(10)).toBe("0.39");
    });

});

describe('AQI Calculations', () => {

    test('calculateAQI handles undefined/null', () => {
        expect(calculateAQI(undefined)).toBe("--");
        expect(calculateAQI(null)).toBe("--");
    });

    test('calculateAQI calculates AQI bands correctly based on PM2.5', () => {
        // Good
        expect(calculateAQI(5.0)).toBe(21); // (50/12)*5 = 20.8 -> 21
        expect(calculateAQI(0)).toBe(0);
        expect(calculateAQI(12.0)).toBe(50);

        // Moderate
        expect(calculateAQI(12.1)).toBe(51);
        expect(calculateAQI(35.4)).toBe(100);

        // Unhealthy for Sensitive
        expect(calculateAQI(35.5)).toBe(101);
        expect(calculateAQI(55.4)).toBe(150);

        // Unhealthy
        expect(calculateAQI(55.5)).toBe(151);
        expect(calculateAQI(150.4)).toBe(200);

        // Very Unhealthy
        expect(calculateAQI(150.5)).toBe(201);
        expect(calculateAQI(250.4)).toBe(300);

        // Hazardous
        expect(calculateAQI(250.5)).toBe(301);
        expect(calculateAQI(350.4)).toBe(400);
        expect(calculateAQI(350.5)).toBe(401);
        expect(calculateAQI(500.0)).toBe(500);
    });

    test('calc interpolates boundaries properly', () => {
        expect(calc(12.0, 50, 0, 12.0, 0)).toBe(50);
        expect(calc(35.4, 100, 51, 35.4, 12.1)).toBe(100);
    });

});
