# Contributing Guide

A concise set of conventions to keep the theme consistent, maintainable, and performant.

## Architecture & Modules
- See `docs/modular-architecture.md` for full module responsibilities and load order.
- Keep `functions.php` as a thin bootstrap that only requires `inc/*` modules.
- Add new features by concern:
  - Assets: `inc/assets.php`
  - Performance: `inc/performance.php`
  - SEO/Analytics: `inc/seo-analytics.php`
  - Content/UI filters: `inc/content-filters.php`
  - Admin settings pages: `inc/admin-<feature>-settings.php`

## Naming Conventions
- PHP function prefix: `p5m_` (e.g., `p5m_get_setting()`, `p5m_should_optimize()`).
- Files: lowercase, dashes (`admin-hotjar-settings.php`, `seo-analytics.php`).
- Callbacks: descriptive names (`p5m_enqueue_front_assets`, `p5m_adjust_rankmath_sitemap`).

## Hooks & Priorities
- Enqueue: `wp_enqueue_scripts` at priority 10 unless dependency order requires otherwise.
- Head injections (analytics): use early priority (1–5) to ensure tag presence and reduce FOUC.
- Content filters (like rel adjustments): use priority ~20 to run after shortcodes.
- Performance/asset guards: late priorities (99–999) for defer/delay/exclusions.
- Always document non-default priorities in a brief comment above the hook.

## Enqueue Patterns
- Use `wp_enqueue_script()`/`wp_enqueue_style()` with explicit dependencies.
- Version assets using the theme version or file mtime in development.
- Prefer footer scripts (`true` as last param) and let `inc/performance.php` manage async/defer.
- Avoid inline scripts in templates; inject via actions (e.g., `wp_head`) in the correct module.

## Settings & Options
- Read options via `p5m_get_setting($key, $default)` from `inc/admin-settings.php`.
- Sanitize when saving; escape when outputting (see Escaping below).
- Provide sensible fallbacks if settings are empty.

## Internationalization (i18n)
- Text domain: `p5marketing`.
- Use `__()`, `_e()`, `_x()`, and their `esc_*` variants for safe output (`esc_html__()`).
- Add new strings to the POT file and provide translations in `languages/`.

## Security & Escaping
- URLs: `esc_url()`; attributes: `esc_attr()`; HTML blocks: `wp_kses_post()`.
- Never print unsanitized user input. Escape at render time.
- When building HTML attributes dynamically, validate expected values before echoing.

## Performance Guidelines
- Gate heavy logic with `p5m_should_optimize()` where applicable.
- Avoid blocking CSS/JS; prefer async/defer and critical CSS where reasonable.
- Only load plugin assets on pages that need them (see `inc/performance.php`).

## CSS/Tailwind
- Prefer utility-first classes; keep style in `src/css/tailwind.css`.
- Rebuild via `npm run build` after changing Tailwind sources.
- Keep class naming consistent and avoid one-off inline styles unless necessary.

## Adding New Modules
1. Create `inc/<feature>.php` with focused responsibility.
2. Require it in `functions.php` under the appropriate section.
3. Add hooks and document priorities inline.
4. If admin UI is needed, create `inc/admin-<feature>-settings.php`.

## Debugging & Logs
- Use `p5m_debug($data)` for quick logging during development.
- Develop with `WP_DEBUG` enabled; watch error logs for asset pipeline and sitemap behavior.
- For image handling, ensure complete size arrays to avoid `image_downsize` notices.

## Code Style
- Follow WordPress PHP Coding Standards (brace style, spacing, naming).
- Keep functions small and single-purpose; refactor shared logic into helpers.

## Pull Request Checklist
- Module placement aligns with responsibilities.
- Hooks have appropriate priorities and are documented.
- Settings read via `p5m_get_setting()`; outputs are escaped.
- i18n strings use `p5marketing` domain.
- No duplicate injections (analytics, sitemap filters, Rocket exclusions).
- README and docs updated if behavior or usage changes.
