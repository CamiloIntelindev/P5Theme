# P5Marketing Theme

[![Build Tailwind CSS](https://github.com/CamiloIntelindev/P5Theme/actions/workflows/build-css.yml/badge.svg)](https://github.com/CamiloIntelindev/P5Theme/actions/workflows/build-css.yml)

A lightweight, Tailwind-based WordPress theme focused on performance and modern UX.

## Quick Start

1. Place the theme in `wp-content/themes/p5marketing`.
2. Run `npm install` (if you need to modify CSS/JS).
3. Build styles with `npm run build`.
4. Activate the theme in Appearance → Themes.

### CSS build via CI

- The GitHub Actions workflow builds Tailwind on pushes/PRs that touch templates, Tailwind config, JS, or CSS.
- When `dist/tailwind.css` changes, the bot commits it with `[skip ci]` to avoid loops.
- Editors who don’t run Node locally can rely on the workflow: push changes (or merge PRs) and pull the updated `dist/tailwind.css` after the check completes.

## Theme Settings

Settings are available under Appearance → P5 Settings and include:

- Logo (ID + URL)
- Contact email
- Google Tag Manager ID
- GA4 Measurement ID
- Preconnect hosts

Use the `p5m_get_setting($key, $default = null)` helper to read them in templates.

## Development

- Source CSS: `src/css/tailwind.css`
- Compiled CSS: `dist/tailwind.css`
- Admin JS: `assets/js/p5m-admin.js`
- Header JS: `assets/js/header.js`

Build:

```bash
npm install
npm run build
```

### Modular Architecture

See `docs/modular-architecture.md` for module responsibilities and load order. The theme keeps `functions.php` as a thin bootstrap that requires `inc/*` modules grouped by concern.

### Runtime Verification

See `docs/runtime-checklist.md` for quick steps to validate analytics injection, sitemap filters, performance guards, and UI behaviors.

### Scan for Duplicate Hooks

Use the helper script to scan for common hooks/injections and ensure they live in the right module:

```bash
bash scripts/scan-theme-hooks.sh
```

### Contributing

See `docs/contributing.md` for naming conventions, hook priorities, enqueue patterns, i18n/escaping rules, and module guidelines.

## Templates

- `404.php`, `search.php`, `archive.php`, `page.php`, `singular.php`
- `inc/admin-settings.php` — Theme options
- `inc/template-tags.php` — Helper functions (logo, layout, breadcrumbs, schema, helpers)

## Internationalization

Translations live in `languages/`. Load the `.pot` into Poedit or use WP-CLI to generate `.po`/`.mo` files.

## Useful Hooks & Filters

- `p5m_layout_post_types` — Filter which post types get the layout metabox

## Contributing

- Keep Tailwind classes consistent
- Add translations for any new strings

