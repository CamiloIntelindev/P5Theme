# Runtime Verification Checklist

Quick steps to confirm the theme works as expected after changes.

## Core
- Site loads without PHP notices/warnings in `WP_DEBUG` mode.
- Navigation, templates, and language switch behave normally.

## Analytics
- GTM: a single `googletagmanager.com/gtm.js` request is present; no duplicates.
- GA4: a single `googletagmanager.com/gtag/js` request is present.
- Ahrefs: `analytics.ahrefs.com/analytics.js` appears only once and only on the front-end.

## Sitemap (RankMath)
- `/sitemap_index.xml` loads.
- Excluded slugs (`faq-tab`, `resena`, `tabs`, `promocion`) are not listed.
- Portal URLs (`portal.sanasana.com/login`, `/affiliate`) are excluded.
- Caching is disabled in development to reflect changes immediately.

## Performance
- Beaver Builder and Sanasana assets load only where needed.
- WP Rocket exclusions apply to builder scripts; no delayed execution breakage.
- No blocking CSS/JS warnings beyond expected.

## Front-end UI
- SVG uploads preview correctly in Media Library.
- "Nosotros" modal opens and sizes responsive video properly.
- Portal links in content have `rel="nofollow noopener noreferrer"`.

## Admin
- Theme settings pages render: Header, Footer, Fonts, Hotjar, Google, Redirection, Contact Form.
- Settings read via `p5m_get_setting()` reflect in front-end behaviors.
