<?php
/**
 * P5 Marketing Theme Functions
 *
 * Bootstrap principal del tema. Carga módulos organizados por funcionalidad.
 *
 * @package P5Marketing
 */

if (!defined('ABSPATH')) exit;

// Global helpers (gates, debug)
require_once get_template_directory() . '/inc/globals.php';
require_once get_template_directory() . '/inc/theme-updates.php';

// Core modules
require_once get_template_directory() . '/inc/template-tags.php';
if (file_exists(get_template_directory() . '/inc/cache.php')) {
  require_once get_template_directory() . '/inc/cache.php';
}
require_once get_template_directory() . '/inc/theme-setup.php';
require_once get_template_directory() . '/inc/metaboxes.php';
require_once get_template_directory() . '/inc/assets.php';
require_once get_template_directory() . '/inc/performance.php';
require_once get_template_directory() . '/inc/seo-analytics.php';
require_once get_template_directory() . '/inc/content-filters.php';
require_once get_template_directory() . '/inc/multilang.php';

// Admin and editor modules
if (file_exists(get_template_directory() . '/inc/admin-settings.php')) {
  require_once get_template_directory() . '/inc/admin-settings.php';
}
if (file_exists(get_template_directory() . '/inc/admin-header-settings.php')) {
  require_once get_template_directory() . '/inc/admin-header-settings.php';
}
if (file_exists(get_template_directory() . '/inc/admin-footer-settings.php')) {
  require_once get_template_directory() . '/inc/admin-footer-settings.php';
}
if (file_exists(get_template_directory() . '/inc/admin-fonts-settings.php')) {
  require_once get_template_directory() . '/inc/admin-fonts-settings.php';
}
if (file_exists(get_template_directory() . '/inc/shortcodes.php')) {
  require_once get_template_directory() . '/inc/shortcodes.php';
}
if (file_exists(get_template_directory() . '/inc/shortcodes-help.php')) {
  require_once get_template_directory() . '/inc/shortcodes-help.php';
}
if (file_exists(get_template_directory() . '/inc/blocks-loader.php')) {
  require_once get_template_directory() . '/inc/blocks-loader.php';
}

// Custom admin modules
if (file_exists(get_template_directory() . '/inc/admin-hotjar-settings.php')) {
  require_once get_template_directory() . '/inc/admin-hotjar-settings.php';
}
if (file_exists(get_template_directory() . '/inc/admin-frontend-redirection.php')) {
  require_once get_template_directory() . '/inc/admin-frontend-redirection.php';
}
if (file_exists(get_template_directory() . '/inc/admin-contact-form-settings.php')) {
  require_once get_template_directory() . '/inc/admin-contact-form-settings.php';
}
if (file_exists(get_template_directory() . '/inc/admin-google-settings.php')) {
  require_once get_template_directory() . '/inc/admin-google-settings.php';
}
