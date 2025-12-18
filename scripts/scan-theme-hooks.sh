#!/usr/bin/env bash
set -euo pipefail

THEME_DIR="$(cd "$(dirname "$0")"/.. && pwd)"
cd "$THEME_DIR"

patterns=(
  "googletagmanager.com/gtm.js"
  "googletagmanager.com/gtag/js"
  "analytics.ahrefs.com/analytics.js"
  "rank_math/sitemap/entry"
  "rank_math/sitemap/enable_caching"
  "rocket_delay_js_exclusions"
  "rocket_exclude_defer_js"
  "nosotros_modal"
  "portal.sanasana.com"
)

printf "Scanning theme for common hooks and injections...\n\n"
for p in "${patterns[@]}"; do
  echo "=== Pattern: $p ==="
  # Search all PHP files under theme folder
  grep -RIn --include='*.php' "$p" . || echo "(none)"
  echo
done

cat <<EOF
Tips:
- Expect matches primarily under ./inc/seo-analytics.php, ./inc/performance.php, ./inc/assets.php, and ./inc/content-filters.php.
- If you see any matches in ./functions.php, consider moving them into the appropriate module.
EOF
