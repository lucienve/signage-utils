# Signage Utils Manifest

This document outlines the architecture, code structure, requirements, and assumptions for the `signage-utils` project. It serves as a reference for future development and maintenance.

## Overview
`signage-utils` is a collection of web-based digital signage dashboards designed to be displayed on large screens (e.g., TVs or monitors in a common area like a lodge or lounge). The project consists of frontend HTML/JS/CSS files that render the data and middleware PHP proxies that securely fetch, cache, and serve data from various external APIs.

The system relies on a "hub and spoke" model where the displays (clients) make requests to the local PHP proxies, which in turn communicate with external services like Google APIs, WeatherFlow, and PurpleAir.

## Architecture & Code Structure

### Coding Conventions
*   **PHP Typing**: All new and modified PHP files must declare strict types (`declare(strict_types=1);` at the top). Functions must use native parameter type hints (e.g. `string $url`) and include standard PHPDoc blocks for documentation.

### 1. Events Calendar (`events.html` & `calendar_proxy.php`)
*   **Purpose**: Displays the next 3 upcoming events.
*   **Frontend**: `events.html` requests data on load. It formats the date, time, and location (cleaning up verbose location strings).
*   **Backend**: `calendar_proxy.php` fetches events from a public Google Calendar.
*   **Caching**: 1-hour cache (`calendar_cache.json`).

### 2. Garbage Day Schedule (`garbage_day.html` & `garbage_day_proxy.php`)
*   **Purpose**: Displays the next garbage pickup day. Emphasizes delays due to standard holidays or manual alerts.
*   **Frontend**: `garbage_day.html` features a massive status display. It shows an orange alert box if a delay is active.
*   **Backend**: `garbage_day_proxy.php` automatically calculates the next Monday. It applies logic for standard US holidays (New Year's, Memorial Day, July 4th, Labor Day, Christmas) to push the schedule to Tuesday. It also checks a Google Sheet for manual overrides.
*   **Data Source**: Automatic calculation + Google Sheets API (Tab: `Garbage`, Range: `A2:B2`).

### 3. Digital Menu Board (`menu.html` & `menu_proxy.php`)
*   **Purpose**: A 2-column digital menu board for a restaurant.
*   **Frontend**: `menu.html` dynamically groups items into categories like Lunch, Soups, Appetizers, and Entrees. If no data is available for a column, it dynamically collapses.
*   **Backend**: `menu_proxy.php` fetches menu data from a Google Sheet.
*   **Data Source**: Google Sheets API (Tab: `Menu`, Range: `A2:D`).
*   **Caching**: 15-minute cache (`menu_cache.json`).

### 4. Weather Boards (`weather.html`, `weather_live.html`, proxies)
*   **Purpose**: Provides two different weather views (a detailed multi-view board and a highly visible "billboard" live view).
*   **`weather_live.html`**:
    *   Acts as a "Billboard" view with massive typography.
    *   Calculates dynamic "Feels Like" metrics (switches between Wind Chill and Heat Index depending on the temperature).
    *   Combines local weather station data and AQI data.
*   **`weather.html`**:
    *   Contains three distinct views controllable via URL parameters (`?view=current`, `?view=forecast`, `?view=alerts`).
    *   Fetches data directly from the National Weather Service (NWS) API natively in the browser (no proxy required).
*   **Proxies**: All external API calls use a shared `proxy_helper.php` library that standardizes API requests, caching mechanisms, and error routing.
    *   `weather_proxy.php`: Proxies a local WeatherFlow Tempest station (Station ID: `68285`). Caches for 10 minutes (`tempest_cache.json`).
    *   `purpleair_proxy.php`: Proxies a PurpleAir sensor (Sensor Index: `182513`). Caches for 5 minutes (`purpleair_cache.json`). The frontend calculates the official AQI value from raw PM2.5 readings.
    *   `calendar_proxy.php`: Proxies events from the public Google Calendar.
    *   `menu_proxy.php`: Proxies menu items from a designated Google Sheet.
    *   `garbage_day_proxy.php`: Performs standard holiday calculations and proxies Google Sheets for overrides, processing and returning the combined data payload for the frontend.
## Requirements

1.  **Environment**: 
    *   The scripts are hosted on **https://www.goldkeyestates.org**.
    *   A web server (Apache/Nginx) configured to run **PHP 8.1 (`ea-php81`)**.
    *   PHP `cURL` extension must be enabled for proxy scripts to function.
2.  **File Permissions**: 
    *   The web server must have write permissions to the deployment directory to create and update the local JSON cache files (e.g., `calendar_cache.json`, `menu_cache.json`, etc.).
3.  **Displays**:
    *   The digital signage software used is **PiSignage** (www.pisignage.com).
    *   Displays have a fixed resolution of **1920x1080 pixels** (16:9 aspect ratio).
4.  **Connectivity**:
    *   Outbound internet access is required for the PHP proxies to reach Google, PurpleAir, and WeatherFlow.

## Assumptions & Technical Debt

*   **Hardcoded Secrets**: API keys (Google API, PurpleAir, WeatherFlow Token) are hardcoded directly into the PHP files. In a production environment, these should ideally be moved to a `.env` file or server environment variables.
*   **Hardcoded Locations**: The NWS API calls in `weather.html` are hardcoded to Milford, PA coordinates (`41.323, -74.802`).
*   **Resiliency**: 
    *   The PHP proxies rely on an aggressively structured `stale-while-revalidate` fallback. If an API goes down or hits a rate limit, the proxies will serve the last known cached file and return a `500` status code inside the proxy logic (but a 200 HTTP code with stale data is preferred where handled).
*   **Google Sheets Format**: The menu and garbage proxies assume exact row/column structures in the target Google Sheet. If column layouts are changed by users, the dashboards will break.
*   **Cross-Origin Requests (CORS)**: Proxies are used predominantly to bypass CORS restrictions that would otherwise occur if the Javascript queried Google/WeatherFlow directly without proper OAuth scopes or origins configured.
