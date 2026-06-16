/**
 * js/menu.js
 * 
 * Shared algorithmic functions for Gold Key Signage menu displays.
 * Extracted for unit testing and reusability.
 */

/**
 * Formats a raw price value to ensure it has a dollar sign if it is numeric.
 * @param {string|number|null} p The raw price input.
 * @returns {string} The formatted price.
 */
function cleanPrice(p) {
    if (!p) return "";
    p = p.toString().trim();
    if (!p.startsWith('$') && !isNaN(parseFloat(p))) return '$' + p;
    return p;
}

/**
 * Checks if the menu is expired relative to the current local time in Milford, PA.
 * @param {string|null} expirationDateString The expiration date string.
 * @returns {boolean} True if current time is past expiration, false otherwise.
 */
function isMenuExpired(expirationDateString) {
    if (!expirationDateString) return false;

    // YYYY-MM-DD strings are parsed as UTC. Instead, append an artificial time 
    // to force it to parse as the local Date to align with logic below. A space 
    // works better than T for handling standard M/D/YYYY formats.
    const parsedDate = new Date(`${expirationDateString} 23:59:59`);
    if (isNaN(parsedDate)) return false;

    // Obtain the current time correctly mapped to Milford, PA
    const nowInMilford = new Date(new Date().toLocaleString("en-US", { timeZone: "America/New_York" }));

    return nowInMilford > parsedDate;
}

/**
 * Categorizes raw menu rows from spreadsheet into distinct lunch, soup, salad, app, entree, and dessert groups.
 * @param {Array<Array<string>>} rows The raw rows from the Google Sheet.
 * @returns {Object} Categorized menu object containing items lists, expiration date, and status.
 */
function categorizeMenuItems(rows) {
    const categories = {
        lunch: [],
        soups: [],
        salads: [],
        apps: [],
        entrees: [],
        desserts: [],
        expirationDate: null,
        isExpired: false
    };

    rows.forEach(row => {
        // [0]Category, [1]Name, [2]Price, [3]Description
        const category = (row[0] || "").trim().toLowerCase();
        if (!row[1]) return;

        if (category === 'expiration') {
            categories.expirationDate = row[1];
            categories.isExpired = isMenuExpired(row[1]);
            return;
        }

        const item = {
            name: row[1],
            price: cleanPrice(row[2]),
            desc: row[3] || ""
        };

        if (category === 'lunch') categories.lunch.push(item);
        else if (category === 'soup') categories.soups.push(item);
        else if (category === 'salad') categories.salads.push(item);
        else if (category === 'appetizer') categories.apps.push(item);
        else if (category === 'entree') categories.entrees.push(item);
        else if (category === 'dessert') categories.desserts.push(item);
    });

    return categories;
}

// Export for Node.js (Jest) but don't break in browser
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        cleanPrice,
        isMenuExpired,
        categorizeMenuItems
    };
}
