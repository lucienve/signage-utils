/**
 * js/weather.js
 * 
 * Shared algorithmic functions for Gold Key Signage weather displays.
 * Extracted for unit testing and reusability.
 */

/**
 * Converts Celsius to Fahrenheit.
 * @param {number} c Temperature in Celsius.
 * @returns {number} Temperature in Fahrenheit.
 */
const toF = (c) => (c * 9 / 5) + 32;

/**
 * Converts meters per second to miles per hour (rounded to integer).
 * @param {number} ms Speed in meters per second.
 * @returns {string} Speed in miles per hour.
 */
const toMph = (ms) => (ms * 2.23694).toFixed(0);

/**
 * Converts millimeters to inches (formatted to two decimal places).
 * @param {number} mm Length in millimeters.
 * @returns {string} Length in inches.
 */
const toInches = (mm) => (mm * 0.0393701).toFixed(2);

/**
 * Calculates the US EPA Air Quality Index (AQI) based on PM2.5 concentration.
 * @param {number|null|undefined} pm The PM2.5 concentration (ug/m3).
 * @returns {number|string} The calculated AQI value, or "--" if invalid.
 */
function calculateAQI(pm) {
    if (pm === undefined || pm === null) return "--";
    if (pm >= 350.5) return calc(pm, 500, 401, 500, 350.5);
    if (pm >= 250.5) return calc(pm, 400, 301, 350.4, 250.5);
    if (pm >= 150.5) return calc(pm, 300, 201, 250.4, 150.5);
    if (pm >= 55.5) return calc(pm, 200, 151, 150.4, 55.5);
    if (pm >= 35.5) return calc(pm, 150, 101, 55.4, 35.5);
    if (pm >= 12.1) return calc(pm, 100, 51, 35.4, 12.1);
    if (pm >= 0) return calc(pm, 50, 0, 12.0, 0);
    return 0;
}

/**
 * EPA AQI linear interpolation formula.
 * @param {number} Cp Observed concentration.
 * @param {number} Ih AQI value corresponding to BPh.
 * @param {number} Il AQI value corresponding to BPl.
 * @param {number} BPh Breakpoint greater than or equal to Cp.
 * @param {number} BPl Breakpoint less than or equal to Cp.
 * @returns {number} The interpolated AQI.
 */
function calc(Cp, Ih, Il, BPh, BPl) {
    return Math.round(((Ih - Il) / (BPh - BPl)) * (Cp - BPl) + Il);
}

// Export for Node.js (Jest) but don't break in browser
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        toF,
        toMph,
        toInches,
        calculateAQI,
        calc
    };
}
