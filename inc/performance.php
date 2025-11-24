<?php
/**
 * Performance Optimizations
 * Limpieza de head, preconnect, cache headers, lazy loading
 *
 * @package P5Marketing
 */

if (!defined('ABSPATH')) exit;

// Limpieza de front (ruido del head)
add_action('init', function () {
  // Emojis
  remove_action('wp_head', 'print_emoji_detection_script', 7);
  remove_action('wp_print_styles', 'print_emoji_styles');

  // oEmbed discovery + host JS
  remove_action('wp_head', 'wp_oembed_add_discovery_links');
  remove_action('wp_head', 'wp_oembed_add_host_js');

  // Global styles/duotone
  remove_action('wp_enqueue_scripts', 'wp_enqueue_global_styles');
  remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
});

add_filter('xmlrpc_enabled', '__return_false');
remove_action('template_redirect', 'rest_output_link_header', 11);

// Light HTML caching headers (only for non-logged users)
add_action('send_headers', function () {
  if (is_admin() || is_user_logged_in()) return;
  if (isset($_GET['preview']) || is_search()) return;
  $max_age = is_404() ? 60 : 300;
  if (!headers_sent()) {
    header_remove('Cache-Control');
    header_remove('Pragma');
    header('Cache-Control: public, max-age=' . intval($max_age) . ', stale-while-revalidate=30, stale-if-error=86400');
  }
}, 0);

// DNS prefetch y preconnect
add_action('wp_head', function () {
  if (is_admin()) return;

  echo '<link rel="dns-prefetch" href="//cdn.jsdelivr.net">' . PHP_EOL;
  echo '<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>' . PHP_EOL;
  echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . PHP_EOL;
  echo '<link rel="dns-prefetch" href="//fonts.gstatic.com">' . PHP_EOL;
  echo '<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>' . PHP_EOL;
  echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . PHP_EOL;

  // Preconnect adicionales desde settings
  $get_acf_field = function ($key) {
    return function_exists('get_field') ? get_field($key, 'option') : null;
  };
  
  $hosts = p5m_get_setting('preconnect_hosts', $get_acf_field('preconnect_hosts'));
  if (!empty($hosts)) {
    if (is_string($hosts)) $hosts = preg_split('/[\r\n,]+/', $hosts);
    if (is_array($hosts)) {
      foreach ($hosts as $h) {
        $host = trim((string)($h['text'] ?? $h));
        if ($host) {
          $safe = esc_url_raw($host);
          if ($safe) echo '<link rel="preconnect" href="' . esc_url($safe) . '" crossorigin />' . PHP_EOL;
        }
      }
    }
  }
}, 5);

// Preload critical resources
add_action('wp_head', function() {
  if (is_admin()) return;
  
  // Preload Tailwind CSS (critical)
  $tw_uri = get_template_directory_uri() . '/dist/tailwind.css';
  $tw_path = get_template_directory() . '/dist/tailwind.css';
  if (file_exists($tw_path)) {
    $tw_ver = filemtime($tw_path);
    echo '<link rel="preload" href="' . esc_url($tw_uri) . '?ver=' . $tw_ver . '" as="style">' . PHP_EOL;
  }
  
  // Preload main stylesheet
  $style_uri = get_template_directory_uri() . '/style.css';
  $style_path = get_template_directory() . '/style.css';
  if (file_exists($style_path)) {
    $style_ver = filemtime($style_path);
    echo '<link rel="preload" href="' . esc_url($style_uri) . '?ver=' . $style_ver . '" as="style">' . PHP_EOL;
  }
}, 6);

// Image sizes/srcset + atributos globales
add_filter('wp_calculate_image_sizes', function ($sizes, $size) {
  $container = 'min(100vw, 72rem)'; // ≈ 1152px
  return "(max-width: 768px) 100vw, {$container}";
}, 10, 2);

add_filter('wp_get_attachment_image_attributes', function ($attr, $attachment, $size) {
  $attr['loading']  = $attr['loading'] ?? 'lazy';
  $attr['decoding'] = 'async';

  // Primera imagen "importante" en singular → prioridad alta
  if (is_singular() && in_the_loop() && in_array($size, ['full','content-lg','large'], true)) {
    static $first = true;
    if ($first) {
      $attr['fetchpriority'] = 'high';
      $attr['loading'] = 'eager';
      $first = false;
    }
  }
  return $attr;
}, 10, 3);

// ============================================================================
// Defer render-blocking resources from Astra/Coral theme
// ============================================================================

// Defer non-critical CSS (menu-item, cache layouts, tabs, price)
add_filter('style_loader_tag', function ($html, $handle) {
  // List of non-critical stylesheets to defer
  $defer_handles = [
    'menu-item-style',
    'cache-layout',
    'styles-tabs',
    'styles-price',
    'tabstable-styles-css',
    'astra-menu-animation',
    'astra-widgets',
  ];
  
  // Check if this handle should be deferred
  foreach ($defer_handles as $defer_handle) {
    if (strpos($handle, $defer_handle) !== false || $handle === $defer_handle) {
      // Convert to preload + async load
      $preload = str_replace(
        "rel='stylesheet'",
        "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"",
        $html
      );
      return $preload . "<noscript>{$html}</noscript>";
    }
  }
  
  return $html;
}, 20, 2);

// Defer jQuery Migrate (not critical for initial render)
add_filter('script_loader_tag', function ($tag, $handle, $src) {
  if (is_admin() || empty($src)) return $tag;
  
  // Remove defer from jQuery Migrate - just dequeue it instead
  // (Already dequeued above)
  
  // Defer non-critical scripts only
  $defer_scripts = [
    'astra-theme-js',
    'astra-menu-animation',
  ];
  
  if (in_array($handle, $defer_scripts, true)) {
    if (strpos($tag, ' defer') === false && strpos($tag, ' async') === false) {
      return str_replace(' src', ' defer src', $tag);
    }
  }
  
  return $tag;
}, 20, 3);

// Remove jQuery Migrate if not needed
add_filter('wp_default_scripts', function ($scripts) {
  if (!is_admin() && isset($scripts->registered['jquery'])) {
    $script = $scripts->registered['jquery'];
    if ($script->deps) {
      // Remove jquery-migrate dependency
      $script->deps = array_diff($script->deps, ['jquery-migrate']);
    }
  }
});

// Keep jQuery in header but optimize it
// Don't move to footer - some inline scripts depend on it
add_action('wp_enqueue_scripts', function() {
  if (!is_admin()) {
    // Dequeue jQuery Migrate completely
    wp_dequeue_script('jquery-migrate');
  }
}, 100);

// Optimize resource hints priority
add_filter('wp_resource_hints', function($urls, $relation_type) {
  if ($relation_type === 'dns-prefetch') {
    // Remove WordPress.org prefetch (not needed)
    $urls = array_diff($urls, ['//s.w.org']);
  }
  return $urls;
}, 10, 2);

