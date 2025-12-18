# Modular Architecture

This theme organizes functionality into small, focused modules under `inc/`. The goal is clarity, testability, and easy future maintenance.

## Load Order (bootstrap)
Loaded by `functions.php`:

1. `inc/globals.php` — global gates and debug helpers
2. `inc/template-tags.php` — template helpers
3. `inc/cache.php` (if present) — cache utilities
4. `inc/theme-setup.php` — theme supports, menus, image sizes
5. `inc/metaboxes.php` — metabox registration
6. `inc/assets.php` — enqueue styles/scripts, small front-end injections
7. `inc/performance.php` — performance tweaks and asset optimization
8. `inc/seo-analytics.php` — SEO meta, GTM/GA, Ahrefs, sitemap filters
9. `inc/content-filters.php` — content/menu filters and AJAX helpers
10. `inc/multilang.php` — language helpers
11. Admin/editor modules — `inc/admin-*.php`, `inc/shortcodes*.php`, `inc/blocks-loader.php`

## Responsibilities
- `globals.php`: `p5m_is_bb_active`, `p5m_should_optimize`, `p5m_debug`
- `assets.php`: enqueue, SVG support, inline "nosotros" modal
- `performance.php`: head cleanup, defer, plugin asset guards, WP Rocket exclusions
- `seo-analytics.php`: GTM/GA deferred injection, Ahrefs analytics, RankMath sitemap filters
- `content-filters.php`: rel enforcement for external portal links, menu/content tweaks
- `admin-*`: settings pages (header/footer/fonts, Hotjar, Google IDs, redirection, contact form)

## Extension Guidelines
- Place new logic by concern (assets, performance, analytics, content).
- Keep `functions.php` as a thin loader only.
- Prefer `p5m_get_setting()` for options; avoid reading raw options directly.
- Avoid inline scripts in templates; inject via actions in the appropriate module.
- If adding a new admin page, use `inc/admin-<feature>-settings.php` and group related helpers there.

## Debugging Tips
- Use `p5m_debug($data)` to log context safely.
- Develop with `WP_DEBUG` on; check the log for asset pipeline and sitemap behaviors.
- When touching performance settings, validate front-end script loading order with browser DevTools.
