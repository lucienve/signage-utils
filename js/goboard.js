/**
 * js/goboard.js
 * Extracted logic for the GoBoard sequential signage display.
 */

/**
 * Parses the duration string from the spreadsheet.
 * @param {string|number|null} durationStr The value from Column D.
 * @param {number} fallback The default number of seconds to return if invalid.
 * @returns {number} The parsed duration in seconds.
 */
function parseDuration(durationStr, fallback = 10) {
    if (durationStr === null || durationStr === undefined || durationStr === '') {
        return fallback;
    }
    const parsed = parseFloat(durationStr);
    if (isNaN(parsed) || parsed <= 0) {
        return fallback;
    }
    return parsed;
}

/**
 * Determines if the current row explicitly calls for the Logo view.
 * @param {string|null} firstCell The value from Column A.
 * @returns {boolean} True if the cell equals exactly <LOGO> (ignoring trailing/leading spaces).
 */
function isLogoMode(firstCell) {
    if (!firstCell) return false;
    return String(firstCell).trim() === '<LOGO>';
}

/**
 * Safely calculates the next index in a display sequence, wrapping back to 0 at the end.
 * @param {number} currentIndex The currently active index.
 * @param {number} arrayLength The total length of the array.
 * @returns {number} The next index.
 */
function calculateNextIndex(currentIndex, arrayLength) {
    if (arrayLength === 0) return 0;
    return (currentIndex + 1) % arrayLength;
}

// Export for Node.js (Jest testing)
// In the browser, module doesn't natively exist like this without a bundler.
if (typeof module !== 'undefined' && typeof module.exports !== 'undefined') {
    module.exports = {
        parseDuration,
        isLogoMode,
        calculateNextIndex
    };
}
