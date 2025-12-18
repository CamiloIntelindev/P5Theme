<?php
// inc/admin-settings.php
if (!defined('ABSPATH')) exit;

/* Enqueue admin scripts only on our settings page */
add_action('admin_enqueue_scripts', function ($hook) {
  // The hook for a theme page added via add_theme_page with slug 'p5m-settings' is 'appearance_page_p5m-settings'
  if ($hook !== 'appearance_page_p5m-settings') return;

  // WP media (uploader)
  wp_enqueue_media();

  $js = get_template_directory() . '/assets/js/p5m-admin.js';
  if (file_exists($js)) {
    wp_enqueue_script('p5m-admin', get_template_directory_uri() . '/assets/js/p5m-admin.js', ['jquery'], filemtime($js), true);
  }
}, 20);

add_action('admin_menu', function() {
  // Page under "Appearance"
  add_theme_page(
    __('P5 Marketing Settings', 'p5marketing'),
    __('P5 Settings', 'p5marketing'),
    'manage_options',
    'p5m-settings',
    'p5m_settings_page_html'
  );
});

// Handle cache flush action from admin
add_action('admin_post_p5m_flush_cache', function(){
  if (!current_user_can('manage_options')) wp_die('Unauthorized');
  check_admin_referer('p5m_flush_cache_nonce');
  if (function_exists('p5m_cache_flush')) {
    $count = p5m_cache_flush();
    $url = add_query_arg([
      'page' => 'p5m-settings',
      'p5m_cache_flushed' => $count,
    ], admin_url('themes.php'));
    wp_safe_redirect($url);
    exit;
  }
});

add_action('admin_init', function() {
    register_setting('p5m_settings_group', 'p5m_settings', 'p5m_settings_sanitize');

    add_settings_section('p5m_main_section', __('Global settings', 'p5marketing'), function(){ echo '<p>' . esc_html__('Global theme configuration', 'p5marketing') . '</p>'; }, 'p5m-settings');

    add_settings_field('p5m_logo', __('Logo del sitio', 'p5marketing'), 'p5m_field_logo_cb', 'p5m-settings', 'p5m_main_section');
    add_settings_field('p5m_contact_email', __('Email de contacto', 'p5marketing'), 'p5m_field_contact_email_cb', 'p5m-settings', 'p5m_main_section');
    add_settings_field('p5m_theme_color', __('Theme color (hex)', 'p5marketing'), 'p5m_field_theme_color_cb', 'p5m-settings', 'p5m_main_section');
  add_settings_field('p5m_force_noindex', __('Forzar noindex', 'p5marketing'), 'p5m_field_force_noindex_cb', 'p5m-settings', 'p5m_main_section');
  add_settings_field('p5m_canonical_domain', __('Canonical domain (no trailing /)', 'p5marketing'), 'p5m_field_canonical_domain_cb', 'p5m-settings', 'p5m_main_section');
  add_settings_field('p5m_gsc_verification', __('Google Search Console verification', 'p5marketing'), 'p5m_field_gsc_verification_cb', 'p5m-settings', 'p5m_main_section');
  add_settings_field('p5m_bing_verification', __('Bing verification', 'p5marketing'), 'p5m_field_bing_verification_cb', 'p5m-settings', 'p5m_main_section');
  add_settings_field('p5m_manifest_url', __('Manifest URL', 'p5marketing'), 'p5m_field_manifest_url_cb', 'p5m-settings', 'p5m_main_section');
  add_settings_field('p5m_preconnect_hosts', __('Preconnect hosts (one per line or comma-separated)', 'p5marketing'), 'p5m_field_preconnect_hosts_cb', 'p5m-settings', 'p5m_main_section');
  add_settings_field('p5m_gtm_container_id', __('GTM Container ID', 'p5marketing'), 'p5m_field_gtm_container_id_cb', 'p5m-settings', 'p5m_main_section');
  add_settings_field('p5m_ga4_measurement_id', __('GA4 Measurement ID', 'p5marketing'), 'p5m_field_ga4_measurement_id_cb', 'p5m-settings', 'p5m_main_section');

  // Content behavior
  add_settings_field('p5m_featured_default', __('Mostrar imagen destacada por defecto (singular)', 'p5marketing'), 'p5m_field_featured_default_cb', 'p5m-settings', 'p5m_main_section');
  add_settings_field('p5m_archive_thumbs', __('Mostrar miniaturas en listados/archivos', 'p5marketing'), 'p5m_field_archive_thumbs_cb', 'p5m-settings', 'p5m_main_section');
  add_settings_field('p5m_enable_breadcrumbs', __('Habilitar breadcrumbs', 'p5marketing'), 'p5m_field_enable_breadcrumbs_cb', 'p5m-settings', 'p5m_main_section');

  // Custom Scripts Section
  add_settings_section('p5m_scripts_section', __('Custom Scripts (Deferred)', 'p5marketing'), function(){ 
    echo '<p>' . __('Custom scripts that load in a deferred way to avoid impacting performance. All load after 3s or user interaction (scroll/click/touch).', 'p5marketing') . '</p>'; 
  }, 'p5m-settings');
  
  add_settings_field('p5m_header_scripts', __('Header Scripts', 'p5marketing'), 'p5m_field_header_scripts_cb', 'p5m-settings', 'p5m_scripts_section');
  add_settings_field('p5m_body_scripts', __('Body Scripts (after <body>)', 'p5marketing'), 'p5m_field_body_scripts_cb', 'p5m-settings', 'p5m_scripts_section');
  add_settings_field('p5m_footer_scripts', __('Footer Scripts', 'p5marketing'), 'p5m_field_footer_scripts_cb', 'p5m-settings', 'p5m_scripts_section');

  // Immediate Scripts Section (no delay)
  add_settings_section('p5m_immediate_section', __('Immediate Scripts (No deferring)', 'p5marketing'), function(){ 
    echo '<p>' . __('Scripts that load immediately (useful for cookie banners, urgent notices, etc). <strong>Use sparingly</strong> to avoid hurting performance.', 'p5marketing') . '</p>'; 
  }, 'p5m-settings');
  
  add_settings_field('p5m_immediate_footer', __('Footer Scripts (Inmediatos)', 'p5marketing'), 'p5m_field_immediate_footer_cb', 'p5m-settings', 'p5m_immediate_section');

  // Image Optimization Section
  add_settings_section('p5m_image_optimization_section', __('Image Optimization', 'p5marketing'), function(){ 
    echo '<p>' . __('Configure how images are optimized to improve LCP and load times. PageSpeed Insights often recommends specific images to optimize.', 'p5marketing') . '</p>'; 
  }, 'p5m-settings');
  
  add_settings_field('p5m_image_quality', __('JPEG/WebP compression quality (%)', 'p5marketing'), 'p5m_field_image_quality_cb', 'p5m-settings', 'p5m_image_optimization_section');
  add_settings_field('p5m_enable_webp', __('Convert to WebP automatically', 'p5marketing'), 'p5m_field_enable_webp_cb', 'p5m-settings', 'p5m_image_optimization_section');
  add_settings_field('p5m_critical_images', __('Critical image URLs (eager loading)', 'p5marketing'), 'p5m_field_critical_images_cb', 'p5m-settings', 'p5m_image_optimization_section');
  add_settings_field('p5m_optimize_images_list', __('Specific URLs to optimize (from PageSpeed)', 'p5marketing'), 'p5m_field_optimize_images_list_cb', 'p5m-settings', 'p5m_image_optimization_section');
  add_settings_field('p5m_max_image_width', __('Maximum image width (px)', 'p5marketing'), 'p5m_field_max_image_width_cb', 'p5m-settings', 'p5m_image_optimization_section');
});

/* Callbacks for fields */
function p5m_field_logo_cb() {
    $opts = get_option('p5m_settings', []);
    $val = esc_attr($opts['logo'] ?? '');
  $id  = intval($opts['logo_id'] ?? 0);
  echo '<div style="display:flex;align-items:center;gap:12px">';
  echo '<input id="p5m_logo" name="p5m_settings[logo]" type="text" value="'. $val .'" class="regular-text" />';
  echo '<input id="p5m_logo_id" name="p5m_settings[logo_id]" type="hidden" value="'. $id .'" />';
  echo '<button class="button p5m-media-upload" data-target="#p5m_logo">Seleccionar imagen</button>';
  echo '<button class="button p5m-media-remove" type="button">Eliminar</button>';
  if ($val) echo '<img id="p5m_logo_preview" src="'. esc_url($val) .'" alt="preview" style="max-height:48px;display:block;border-radius:4px;" />';
  else echo '<img id="p5m_logo_preview" src="" alt="preview" style="max-height:48px;display:none;border-radius:4px;" />';
  echo '</div>';
  echo '<p class="description">URL del logo (puedes usar el selector de medios).</p>';
}

function p5m_field_contact_email_cb() {
    $opts = get_option('p5m_settings', []);
    $val = esc_attr($opts['contact_email'] ?? '');
    echo '<input id="p5m_contact_email" name="p5m_settings[contact_email]" type="email" value="'. $val .'" class="regular-text" />';
}

function p5m_field_theme_color_cb() {
    $opts = get_option('p5m_settings', []);
    $val = esc_attr($opts['theme_color'] ?? '');
    echo '<input id="p5m_theme_color" name="p5m_settings[theme_color]" type="text" value="'. $val .'" placeholder="#1E40AF" />';
}

function p5m_field_force_noindex_cb() {
  $opts = get_option('p5m_settings', []);
  $val = !empty($opts['force_noindex']);
  echo '<label><input name="p5m_settings[force_noindex]" type="checkbox" value="1" '. checked(1, $val, false) .' /> ' . __('Activar noindex (staging/preview)', 'p5marketing') . '</label>';
}

function p5m_field_canonical_domain_cb() {
  $opts = get_option('p5m_settings', []);
  $val = esc_attr($opts['canonical_domain'] ?? '');
  echo '<input id="p5m_canonical_domain" name="p5m_settings[canonical_domain]" type="text" value="'. $val .'" class="regular-text" />';
}

function p5m_field_gsc_verification_cb() {
  $opts = get_option('p5m_settings', []);
  $val = esc_attr($opts['gsc_verification'] ?? '');
  echo '<input id="p5m_gsc_verification" name="p5m_settings[gsc_verification]" type="text" value="'. $val .'" class="regular-text" />';
}

function p5m_field_bing_verification_cb() {
  $opts = get_option('p5m_settings', []);
  $val = esc_attr($opts['bing_verification'] ?? '');
  echo '<input id="p5m_bing_verification" name="p5m_settings[bing_verification]" type="text" value="'. $val .'" class="regular-text" />';
}

function p5m_field_manifest_url_cb() {
  $opts = get_option('p5m_settings', []);
  $val = esc_attr($opts['manifest_url'] ?? '');
  echo '<input id="p5m_manifest_url" name="p5m_settings[manifest_url]" type="url" value="'. $val .'" class="regular-text" />';
}

function p5m_field_preconnect_hosts_cb() {
  $opts = get_option('p5m_settings', []);
  $val = esc_textarea(is_array($opts['preconnect_hosts'] ?? '') ? implode("\n", $opts['preconnect_hosts']) : ($opts['preconnect_hosts'] ?? ''));
  echo '<textarea id="p5m_preconnect_hosts" name="p5m_settings[preconnect_hosts]" rows="4" cols="50" class="large-text">'. $val .'</textarea>';
  echo '<p class="description">' . __('Enter hosts separated by new lines or commas. Example: https://example.com', 'p5marketing') . '</p>';
}

function p5m_field_gtm_container_id_cb() {
  $opts = get_option('p5m_settings', []);
  $val = esc_attr($opts['gtm_container_id'] ?? '');
  echo '<input id="p5m_gtm_container_id" name="p5m_settings[gtm_container_id]" type="text" value="'. $val .'" class="regular-text" />';
}

function p5m_field_ga4_measurement_id_cb() {
  $opts = get_option('p5m_settings', []);
  $val = esc_attr($opts['ga4_measurement_id'] ?? '');
  echo '<input id="p5m_ga4_measurement_id" name="p5m_settings[ga4_measurement_id]" type="text" value="'. $val .'" class="regular-text" />';
}

function p5m_field_featured_default_cb() {
  $opts = get_option('p5m_settings', []);
  $val = !empty($opts['featured_default']);
  echo '<label><input name="p5m_settings[featured_default]" type="checkbox" value="1" ' . checked(1, $val, false) . ' /> ' . __('If enabled, pages/posts will show the default featured image (unless disabled in the metabox).', 'p5marketing') . '</label>';
}

function p5m_field_archive_thumbs_cb() {
  $opts = get_option('p5m_settings', []);
  $val = array_key_exists('archive_thumbs', $opts) ? (bool)$opts['archive_thumbs'] : true; // default true
  echo '<label><input name="p5m_settings[archive_thumbs]" type="checkbox" value="1" ' . checked(1, $val, false) . ' /> ' . __('Show thumbnails in archives/categories', 'p5marketing') . '</label>';
}

function p5m_field_enable_breadcrumbs_cb() {
  $opts = get_option('p5m_settings', []);
  $val = array_key_exists('enable_breadcrumbs', $opts) ? (bool)$opts['enable_breadcrumbs'] : true; // default true
  echo '<label><input name="p5m_settings[enable_breadcrumbs]" type="checkbox" value="1" ' . checked(1, $val, false) . ' /> ' . __('Show breadcrumbs on pages and posts', 'p5marketing') . '</label>';
}

function p5m_field_header_scripts_cb() {
  $opts = get_option('p5m_settings', []);
  $val = esc_textarea($opts['header_scripts'] ?? '');
  echo '<textarea id="p5m_header_scripts" name="p5m_settings[header_scripts]" rows="8" cols="50" class="large-text code" spellcheck="false">'. $val .'</textarea>';
  echo '<p class="description">' . __('Scripts inserted in the &lt;head&gt; in a deferred way. Example: &lt;script src="..."&gt;&lt;/script&gt; or inline code.', 'p5marketing') . '</p>';
}

function p5m_field_body_scripts_cb() {
  $opts = get_option('p5m_settings', []);
  $val = esc_textarea($opts['body_scripts'] ?? '');
  echo '<textarea id="p5m_body_scripts" name="p5m_settings[body_scripts]" rows="8" cols="50" class="large-text code" spellcheck="false">'. $val .'</textarea>';
  echo '<p class="description">' . __('Scripts inserted after the &lt;body&gt; tag in a deferred way.', 'p5marketing') . '</p>';
}

function p5m_field_footer_scripts_cb() {
  $opts = get_option('p5m_settings', []);
  $val = esc_textarea($opts['footer_scripts'] ?? '');
  echo '<textarea id="p5m_footer_scripts" name="p5m_settings[footer_scripts]" rows="8" cols="50" class="large-text code" spellcheck="false">'. $val .'</textarea>';
  echo '<p class="description">' . __('Scripts inserted before closing &lt;/body&gt; in a deferred way.', 'p5marketing') . '</p>';
}

function p5m_field_immediate_footer_cb() {
  $opts = get_option('p5m_settings', []);
  $val = esc_textarea($opts['immediate_footer'] ?? '');
  echo '<textarea id="p5m_immediate_footer" name="p5m_settings[immediate_footer]" rows="8" cols="50" class="large-text code" spellcheck="false">'. $val .'</textarea>';
  echo '<p class="description">' . __('Scripts that load <strong>immediately</strong> before &lt;/body&gt;. Use for cookie banners or critical scripts. <strong>Not deferred.</strong>', 'p5marketing') . '</p>';
}

function p5m_field_image_quality_cb() {
  $opts = get_option('p5m_settings', []);
  $val = intval($opts['image_quality'] ?? 82);
  echo '<input id="p5m_image_quality" name="p5m_settings[image_quality]" type="number" min="60" max="100" value="'. $val .'" class="small-text" />';
  echo '<p class="description">' . __('Compression quality for JPEG/WebP (60-100). Default: 82. Lower = smaller files.', 'p5marketing') . '</p>';
}

function p5m_field_enable_webp_cb() {
  $opts = get_option('p5m_settings', []);
  $checked = isset($opts['enable_webp']) ? intval($opts['enable_webp']) : 1;
  echo '<label><input type="checkbox" id="p5m_enable_webp" name="p5m_settings[enable_webp]" value="1" ' . checked($checked, 1, false) . ' /> ';
  echo __('Generate WebP versions automatically on upload (reduces size 25-35%)', 'p5marketing') . '</label>';
}

function p5m_field_critical_images_cb() {
  $opts = get_option('p5m_settings', []);
  $val = esc_textarea($opts['critical_images'] ?? '');
  echo '<textarea id="p5m_critical_images" name="p5m_settings[critical_images]" rows="6" cols="50" class="large-text code" spellcheck="false" placeholder="https://ejemplo.com/wp-content/uploads/2024/hero-image.jpg&#10;/wp-content/uploads/logo.png">'. $val .'</textarea>';
  echo '<p class="description">' . __('Critical image URLs (one per line). These load with loading="eager" and fetchpriority="high" to improve LCP. Examples: hero images, primary logos.', 'p5marketing') . '</p>';
}

function p5m_field_optimize_images_list_cb() {
  $opts = get_option('p5m_settings', []);
  $val = esc_textarea($opts['optimize_images_list'] ?? '');
  echo '<textarea id="p5m_optimize_images_list" name="p5m_settings[optimize_images_list]" rows="10" cols="50" class="large-text code" spellcheck="false" placeholder="https://ejemplo.com/wp-content/uploads/2024/imagen-pesada.jpg&#10;/wp-content/uploads/banner.png">'. $val .'</textarea>';
  echo '<p class="description">' . __('Paste URLs that PageSpeed Insights recommends optimizing (one per line). The theme will try to compress and convert them to WebP automatically.', 'p5marketing') . '</p>';
}

function p5m_field_max_image_width_cb() {
  $opts = get_option('p5m_settings', []);
  $val = intval($opts['max_image_width'] ?? 2560);
  echo '<input id="p5m_max_image_width" name="p5m_settings[max_image_width]" type="number" min="1200" max="4000" step="100" value="'. $val .'" class="small-text" />';
  echo '<p class="description">' . __('Maximum width in pixels for uploaded images. Larger images will be resized automatically. Default: 2560px.', 'p5marketing') . '</p>';
}

/* Sanitization */
function p5m_settings_sanitize($input) {
    $opts = get_option('p5m_settings', []);
    $out = [];
    if (isset($input['logo'])) {
    $out['logo'] = esc_url_raw($input['logo']);
    }
  if (isset($input['logo_id'])) {
    $out['logo_id'] = intval($input['logo_id']);
    // If logo_id is present but logo URL is empty, try to derive URL from attachment
    if (empty($out['logo']) && $out['logo_id']) {
      $url = wp_get_attachment_url($out['logo_id']);
      if ($url) $out['logo'] = esc_url_raw($url);
    }
  }
    if (isset($input['contact_email'])) {
        $out['contact_email'] = sanitize_email($input['contact_email']);
    }
    if (isset($input['theme_color'])) {
        $out['theme_color'] = sanitize_text_field($input['theme_color']);
    }
  if (isset($input['force_noindex'])) {
    $out['force_noindex'] = $input['force_noindex'] ? 1 : 0;
  }
  if (!empty($input['canonical_domain'])) {
    $out['canonical_domain'] = untrailingslashit(sanitize_text_field($input['canonical_domain']));
  }
  if (!empty($input['gsc_verification'])) {
    $out['gsc_verification'] = sanitize_text_field($input['gsc_verification']);
  }
  if (!empty($input['bing_verification'])) {
    $out['bing_verification'] = sanitize_text_field($input['bing_verification']);
  }
  if (!empty($input['manifest_url'])) {
    $out['manifest_url'] = esc_url_raw($input['manifest_url']);
  }
  if (!empty($input['preconnect_hosts'])) {
    $raw = $input['preconnect_hosts'];
    if (is_array($raw)) $raw = implode(",", $raw);
    $parts = preg_split('/[\r\n,]+/', (string)$raw);
    $clean = [];
    foreach ($parts as $p) {
      $h = trim($p);
      if ($h) $clean[] = esc_url_raw($h);
    }
    $out['preconnect_hosts'] = $clean;
  }
  if (!empty($input['gtm_container_id'])) {
    $out['gtm_container_id'] = sanitize_text_field($input['gtm_container_id']);
  }
  if (!empty($input['ga4_measurement_id'])) {
    $out['ga4_measurement_id'] = sanitize_text_field($input['ga4_measurement_id']);
  }
  
  // Custom scripts - allow HTML/JS but sanitize minimally (wp_kses_post allows script tags)
  if (isset($input['header_scripts'])) {
    $out['header_scripts'] = trim($input['header_scripts']);
  }
  if (isset($input['body_scripts'])) {
    $out['body_scripts'] = trim($input['body_scripts']);
  }
  if (isset($input['footer_scripts'])) {
    $out['footer_scripts'] = trim($input['footer_scripts']);
  }
  if (isset($input['immediate_footer'])) {
    $out['immediate_footer'] = trim($input['immediate_footer']);
  }

  // Image optimization settings
  if (isset($input['image_quality'])) {
    $quality = intval($input['image_quality']);
    $out['image_quality'] = max(60, min(100, $quality)); // Clamp between 60-100
  }
  $out['enable_webp'] = !empty($input['enable_webp']) ? 1 : 0;
  
  if (isset($input['critical_images'])) {
    $out['critical_images'] = sanitize_textarea_field($input['critical_images']);
  }
  if (isset($input['optimize_images_list'])) {
    $out['optimize_images_list'] = sanitize_textarea_field($input['optimize_images_list']);
  }
  if (isset($input['max_image_width'])) {
    $width = intval($input['max_image_width']);
    $out['max_image_width'] = max(1200, min(4000, $width)); // Clamp between 1200-4000
  }

  // Content behavior booleans
  $out['featured_default'] = !empty($input['featured_default']) ? 1 : 0;
  $out['archive_thumbs'] = !empty($input['archive_thumbs']) ? 1 : 0;
  $out['enable_breadcrumbs'] = !empty($input['enable_breadcrumbs']) ? 1 : 0;

  return $out;
}

/* Page HTML */
function p5m_settings_page_html() {
    if (!current_user_can('manage_options')) return;
    ?>
    <div class="wrap">
      <h1><?php esc_html_e('P5 Marketing Settings', 'p5marketing'); ?></h1>
      <?php if (isset($_GET['p5m_cache_flushed'])): ?>
        <div class="notice notice-success is-dismissible"><p>
          <?php printf(esc_html__('Cache flushed: %d item(s) removed.', 'p5marketing'), intval($_GET['p5m_cache_flushed'])); ?>
        </p></div>
      <?php endif; ?>
      <form action="options.php" method="post">
        <?php
          settings_fields('p5m_settings_group');
          // Tab navigation
          ?>
          <h2 class="nav-tab-wrapper" style="margin-top:16px;">
            <a href="#" class="nav-tab nav-tab-active" data-p5m-tab="general"><?php esc_html_e('General', 'p5marketing'); ?></a>
            <a href="#" class="nav-tab" data-p5m-tab="tools"><?php esc_html_e('Herramientas', 'p5marketing'); ?></a>
            <a href="#" class="nav-tab" data-p5m-tab="images"><?php esc_html_e('Images', 'p5marketing'); ?></a>
            <a href="#" class="nav-tab" data-p5m-tab="scripts"><?php esc_html_e('Scripts (Deferred)', 'p5marketing'); ?></a>
            <a href="#" class="nav-tab" data-p5m-tab="immediate"><?php esc_html_e('Immediate Scripts', 'p5marketing'); ?></a>
          </h2>

          <div class="p5m-tabs">
            <!-- General -->
            <div class="p5m-tab-content" data-p5m-tab="general">
              <h2><?php esc_html_e('Ajustes globales', 'p5marketing'); ?></h2>
              <p><?php esc_html_e('Global theme configuration', 'p5marketing'); ?></p>
              <table class="form-table" role="presentation">
                <tbody>
                  <?php do_settings_fields('p5m-settings', 'p5m_main_section'); ?>
                </tbody>
              </table>
            </div>

            <!-- Tools -->
            <div class="p5m-tab-content" data-p5m-tab="tools" style="display:none;">
              <h2><?php esc_html_e('Performance', 'p5marketing'); ?></h2>
              <p><?php esc_html_e('Use the button to clear the theme cache (breadcrumbs, schema, fragments). This does not affect server/CDN caches.', 'p5marketing'); ?></p>
              <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post" style="margin-top:12px;">
                <input type="hidden" name="action" value="p5m_flush_cache" />
                <?php wp_nonce_field('p5m_flush_cache_nonce'); ?>
                <?php submit_button(__('Flush Theme Cache', 'p5marketing'), 'secondary', 'submit', false); ?>
              </form>
            </div>

            <!-- Image optimization -->
            <div class="p5m-tab-content" data-p5m-tab="images" style="display:none;">
              <h2><?php esc_html_e('Image Optimization', 'p5marketing'); ?></h2>
              <p><?php esc_html_e('Configure how images are optimized to improve LCP and load times. PageSpeed Insights often recommends specific images.', 'p5marketing'); ?></p>
              <table class="form-table" role="presentation">
                <tbody>
                  <?php do_settings_fields('p5m-settings', 'p5m_image_optimization_section'); ?>
                </tbody>
              </table>
            </div>

            <!-- Deferred scripts -->
            <div class="p5m-tab-content" data-p5m-tab="scripts" style="display:none;">
              <h2><?php esc_html_e('Custom Scripts (Deferred)', 'p5marketing'); ?></h2>
              <p><?php esc_html_e('Custom scripts that load in a deferred way to avoid impacting performance. All load after 3s or user interaction (scroll/click/touch).', 'p5marketing'); ?></p>
              <table class="form-table" role="presentation">
                <tbody>
                  <?php do_settings_fields('p5m-settings', 'p5m_scripts_section'); ?>
                </tbody>
              </table>
            </div>

            <!-- Immediate scripts -->
            <div class="p5m-tab-content" data-p5m-tab="immediate" style="display:none;">
              <h2><?php esc_html_e('Immediate Scripts (Not deferred)', 'p5marketing'); ?></h2>
              <p><?php esc_html_e('Scripts that load immediately (useful for cookie banners, urgent notices, etc). Use sparingly to avoid performance hits.', 'p5marketing'); ?></p>
              <table class="form-table" role="presentation">
                <tbody>
                  <?php do_settings_fields('p5m-settings', 'p5m_immediate_section'); ?>
                </tbody>
              </table>
            </div>
          </div>

          <?php submit_button(); ?>
        ?>
      </form>
    </div>
    <script>
    (function($){
      // Media uploader quick helper (jQuery present in WP admin)
      $(document).on('click', '.p5m-media-upload', function(e){
        e.preventDefault();
        var target = $($(this).data('target'));
        var frame = wp.media({
          title: 'Selecciona una imagen',
          library: { type: 'image' },
          button: { text: 'Usar esta imagen' },
          multiple: false
        });
        frame.on('select', function(){
          var attachment = frame.state().get('selection').first().toJSON();
          target.val(attachment.url);
        });
        frame.open();
      });

      // Tabs toggle
      $(document).on('click', '.nav-tab-wrapper .nav-tab', function(e){
        e.preventDefault();
        var tab = $(this).data('p5m-tab');
        $('.nav-tab-wrapper .nav-tab').removeClass('nav-tab-active');
        $(this).addClass('nav-tab-active');
        $('.p5m-tab-content').hide();
        $('.p5m-tab-content[data-p5m-tab="' + tab + '"]').show();
      });
    })(jQuery);
    </script>
    <?php
}

/* Helper to read settings easily */
function p5m_get_setting($key, $default = null) {
    $opts = get_option('p5m_settings', []);
    if (is_array($opts) && array_key_exists($key, $opts)) {
        return $opts[$key];
    }
    return $default;
}