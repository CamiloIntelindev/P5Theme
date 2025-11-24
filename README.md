# P5Marketing Theme

A lightweight, Tailwind-based WordPress theme focused on performance and modern UX.

## Quick Start

1. Place the theme in `wp-content/themes/p5marketing`.
2. Run `npm install` (if you need to modify CSS/JS).
3. Build styles with `npm run build`.
4. Activate the theme in Appearance → Themes.

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

