const { parseDuration, isLogoMode, calculateNextIndex } = require('../../js/goboard');

describe('parseDuration', () => {
    test('returns exact numerical float', () => {
        expect(parseDuration("15.5", 10)).toBe(15.5);
    });

    test('returns exact integer float', () => {
        expect(parseDuration("20", 10)).toBe(20);
    });

    test('returns fallback for empty string', () => {
        expect(parseDuration("", 10)).toBe(10);
    });

    test('returns fallback for null/undefined', () => {
        expect(parseDuration(null, 15)).toBe(15);
        expect(parseDuration(undefined, 10)).toBe(10);
    });

    test('returns fallback for text strings', () => {
        expect(parseDuration("abcd", 12)).toBe(12);
    });

    test('returns fallback for negative values', () => {
        expect(parseDuration("-5", 10)).toBe(10);
    });

    test('returns fallback for pure zero', () => {
        expect(parseDuration("0", 10)).toBe(10);
    });
});

describe('isLogoMode', () => {
    test('detects exact logo tag', () => {
        expect(isLogoMode("<LOGO>")).toBe(true);
    });

    test('detects tag with trailing/leading spaces', () => {
        expect(isLogoMode("  <LOGO>  ")).toBe(true);
    });

    test('rejects case mismatches (if intended strict, otherwise fix logic)', () => {
        expect(isLogoMode("<logo>")).toBe(false);
    });

    test('rejects normal text strings', () => {
        expect(isLogoMode("Welcome to the pool")).toBe(false);
    });

    test('rejects strings containing logo but not exact', () => {
        expect(isLogoMode("This <LOGO> is here")).toBe(false);
    });

    test('rejects null or undefined', () => {
        expect(isLogoMode(null)).toBe(false);
        expect(isLogoMode("")).toBe(false);
    });
});

describe('calculateNextIndex', () => {
    test('increments correctly in bounds', () => {
        expect(calculateNextIndex(0, 3)).toBe(1);
        expect(calculateNextIndex(1, 3)).toBe(2);
    });

    test('wraps back to zero when reaching end of array', () => {
        expect(calculateNextIndex(2, 3)).toBe(0);
    });

    test('handles empty arrays safely by staying zero', () => {
        expect(calculateNextIndex(0, 0)).toBe(0);
    });
});
