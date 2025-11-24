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

  // Get critical images list from settings
  $critical_images = p5m_get_setting('critical_images', '');
  $critical_urls = array_filter(array_map('trim', explode("\n", $critical_images)));
  
  // Check if current image URL is in critical list
  $image_url = wp_get_attachment_url($attachment->ID);
  $is_critical = false;
  
  foreach ($critical_urls as $critical_url) {
    if (strpos($image_url, $critical_url) !== false || strpos($critical_url, basename($image_url)) !== false) {
      $is_critical = true;
      break;
    }
  }
  
  // Critical images get high priority
  if ($is_critical) {
    $attr['fetchpriority'] = 'high';
    $attr['loading'] = 'eager';
  }
  // Primera imagen "importante" en singular → prioridad alta (fallback)
  elseif (is_singular() && in_the_loop() && in_array($size, ['full','content-lg','large'], true)) {
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

// ============================================================================
// Image Optimization: Compression, WebP conversion, and resizing
// ============================================================================

// Set JPEG/WebP compression quality from settings
add_filter('jpeg_quality', function($quality) {
  return intval(p5m_get_setting('image_quality', 82));
});

add_filter('wp_editor_set_quality', function($quality, $mime_type) {
  if ($mime_type === 'image/jpeg' || $mime_type === 'image/webp') {
    return intval(p5m_get_setting('image_quality', 82));
  }
  return $quality;
}, 10, 2);

// Automatically resize large images on upload
add_filter('wp_handle_upload_prefilter', function($file) {
  $max_width = intval(p5m_get_setting('max_image_width', 2560));
  
  if (!$max_width || $max_width < 1200) {
    return $file;
  }
  
  // Only process images
  if (strpos($file['type'], 'image/') !== 0) {
    return $file;
  }
  
  // Skip SVG and GIF
  if (in_array($file['type'], ['image/svg+xml', 'image/gif'])) {
    return $file;
  }
  
  // Get image dimensions
  $image_size = getimagesize($file['tmp_name']);
  if (!$image_size || $image_size[0] <= $max_width) {
    return $file; // Image is already small enough
  }
  
  // Load image for resizing
  $editor = wp_get_image_editor($file['tmp_name']);
  if (is_wp_error($editor)) {
    return $file;
  }
  
  // Calculate new dimensions maintaining aspect ratio
  $current_width = $image_size[0];
  $current_height = $image_size[1];
  $new_width = $max_width;
  $new_height = intval($current_height * ($max_width / $current_width));
  
  // Resize
  $editor->resize($new_width, $new_height, false);
  
  // Save back to temp file
  $saved = $editor->save($file['tmp_name']);
  if (is_wp_error($saved)) {
    return $file;
  }
  
  return $file;
});

// Generate WebP versions automatically
add_filter('wp_generate_attachment_metadata', function($metadata, $attachment_id) {
  if (!p5m_get_setting('enable_webp', 1)) {
    return $metadata;
  }
  
  // Get the uploaded file path
  $file_path = get_attached_file($attachment_id);
  
  if (!$file_path || !file_exists($file_path)) {
    return $metadata;
  }
  
  // Only process JPEG and PNG
  $mime_type = get_post_mime_type($attachment_id);
  if (!in_array($mime_type, ['image/jpeg', 'image/png'])) {
    return $metadata;
  }
  
  // Generate WebP for main image
  p5m_generate_webp_image($file_path);
  
  // Generate WebP for all sizes
  if (!empty($metadata['sizes']) && is_array($metadata['sizes'])) {
    $upload_dir = wp_upload_dir();
    $base_dir = dirname($file_path);
    
    foreach ($metadata['sizes'] as $size => $size_data) {
      if (!empty($size_data['file'])) {
        $size_path = $base_dir . '/' . $size_data['file'];
        if (file_exists($size_path)) {
          p5m_generate_webp_image($size_path);
        }
      }
    }
  }
  
  return $metadata;
}, 10, 2);

// Helper function to generate WebP
function p5m_generate_webp_image($file_path) {
  if (!function_exists('imagewebp')) {
    return false; // WebP not supported
  }
  
  $webp_path = preg_replace('/\.(jpe?g|png)$/i', '.webp', $file_path);
  
  // Skip if WebP already exists
  if (file_exists($webp_path)) {
    return true;
  }
  
  // Load image
  $image = wp_get_image_editor($file_path);
  if (is_wp_error($image)) {
    return false;
  }
  
  // Set quality
  $quality = intval(p5m_get_setting('image_quality', 82));
  $image->set_quality($quality);
  
  // Save as WebP
  $saved = $image->save($webp_path, 'image/webp');
  
  return !is_wp_error($saved);
}

// Serve WebP images when available (via picture element or content filter)
add_filter('the_content', 'p5m_replace_images_with_webp', 10);

function p5m_replace_images_with_webp($content) {
  if (!p5m_get_setting('enable_webp', 1)) {
    return $content;
  }
  
  // Match all img tags
  $pattern = '/<img([^>]+)src=["\']([^"\']+\.(jpe?g|png))["\']([^>]*)>/i';
  
  return preg_replace_callback($pattern, function($matches) {
    $img_tag = $matches[0];
    $before_src = $matches[1];
    $img_src = $matches[2];
    $after_src = $matches[4];
    
    // Check if WebP version exists
    $webp_src = preg_replace('/\.(jpe?g|png)$/i', '.webp', $img_src);
    
    // If it's a local URL, check if file exists
    if (strpos($img_src, home_url()) === 0 || strpos($img_src, '/') === 0) {
      $webp_path = str_replace(home_url(), ABSPATH, $webp_src);
      $webp_path = str_replace('//', '/', $webp_path);
      
      if (!file_exists($webp_path)) {
        return $img_tag; // WebP doesn't exist, return original
      }
    }
    
    // Build picture element with WebP and fallback
    $picture = '<picture>';
    $picture .= '<source type="image/webp" srcset="' . esc_url($webp_src) . '">';
    $picture .= $img_tag;
    $picture .= '</picture>';
    
    return $picture;
  }, $content);
}
