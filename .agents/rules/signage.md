---
trigger: always_on
description: Project specific guidelines regarding signage-utils
---

# Signage Utils Project Rules

These rules provide specific context for working on the `signage-utils` codebase. Please follow these conventions in addition to the core and language-specific rules.

## Tech Stack & Architecture
- **Environment:** PHP 8.1 (`ea-php81`), HTML, CSS, Vanilla JS. Tests run via PHPUnit and Jest.
- **Display Target:** PiSignage displays at a fixed 1920x1080 resolution (16:9). UI elements should be massive and high-contrast, optimized for "billboard" viewing from a distance.
- **Architecture:** Hub-and-spoke. Frontend HTML requests local PHP proxies. Proxies fetch and cache data from external APIs (Google Sheets/Calendar, WeatherFlow, PurpleAir).
- **Caching:** Proxies employ aggressive `stale-while-revalidate` JSON caching (e.g., `*_cache.json`) to handle external API rate limits or downtime. Shared proxy logic lives in `proxy_helper.php`.

## Coding Conventions Specific to Signage
- **PHP:** Use `declare(strict_types=1);` in all new/modified files. Use native parameter hints and PHPDoc blocks.
- **JS & Tests:** Write testable logic. Extract complex inline scripts to `.js` files (like `js/goboard.js`) and inline styles to `.css` files.
- **Separation of Concerns:** `tests/php/` uses PHPUnit, `tests/js/` uses Jest.
- **Deployment:** Handled via GitHub Actions (`ftp-deploy.yml`) on `main` push. Testing/dev files must remain excluded via `.ftpignore`.
