<?php
// ============================================================================
// Bootstrap
// ============================================================================
require_once get_template_directory() . '/inc/template-tags.php';
// Theme cache helpers
if (file_exists(get_template_directory() . '/inc/cache.php')) {
  require_once get_template_directory() . '/inc/cache.php';
}
// Theme admin settings (p5m settings helper + page)
if (file_exists(get_template_directory() . '/inc/admin-settings.php')) {
  require_once get_template_directory() . '/inc/admin-settings.php';
}
// Theme header settings (p5m header customization)
if (file_exists(get_template_directory() . '/inc/admin-header-settings.php')) {
  require_once get_template_directory() . '/inc/admin-header-settings.php';
}
// Theme footer settings (p5m footer customization)
if (file_exists(get_template_directory() . '/inc/admin-footer-settings.php')) {
  require_once get_template_directory() . '/inc/admin-footer-settings.php';
}


// ============================================================================
// Theme Setup (soporte core, menús, tamaños, editor)
// ============================================================================
add_action('after_setup_theme', function () {

  // Core
  add_theme_support('title-tag');
  add_theme_support('post-thumbnails');
  add_theme_support('menus');

  // Load translations from /languages
  load_theme_textdomain('p5marketing', get_template_directory() . '/languages');

  // Gutenberg + editor
  add_theme_support('editor-styles');
  add_editor_style('dist/tailwind.css');   // Tailwind en el editor
  add_theme_support('wp-block-styles');    // estilos base de bloques
  add_theme_support('align-wide');         // alignwide/alignfull
  add_theme_support('responsive-embeds');  // iframes fluidos

  // Menús
  register_nav_menus([
    'primary' => __('Primary Menu', 'p5marketing'),
    'footer'  => __('Footer Menu', 'p5marketing'),
    'social'  => __('Social Menu', 'p5marketing'),
  ]);

  // Tamaños útiles de imagen (ajusta a tu layout)
  add_image_size('content-lg', 1280);
  add_image_size('content-md', 1024);
  add_image_size('content-sm', 768);

  // Widget areas (sidebars)
  register_sidebar([
    'name'          => __('Post/Page Sidebar', 'p5marketing'),
    'id'            => 'p5m_post_sidebar',
    'description'   => __('Sidebar para posts y páginas con layout sidebar', 'p5marketing'),
    'before_widget' => '<div id="%1$s" class="widget %2$s mb-6">',
    'after_widget'  => '</div>',
    'before_title'  => '<h3 class="widget-title font-bold text-lg mb-4">',
    'after_title'   => '</h3>',
  ]);
});

// (Beaver Builder optimizations removed per request)


// ============================================================================
// Metabox: Layout selector (nativo, sin ACF) — para múltiples post types
// ============================================================================
// Define qué post types soportan los metaboxes (pages, posts y todos los CPTs públicos)
$p5m_layout_post_types = get_post_types(['public' => true], 'names'); // array de strings
// Excluir adjuntos si aparecen
$p5m_layout_post_types = array_values(array_diff($p5m_layout_post_types, ['attachment']));
// Permite que plugins/tema hijo ajusten la lista
$p5m_layout_post_types = apply_filters('p5m_layout_post_types', $p5m_layout_post_types);

// Callback para renderizar el metabox de layout
$p5m_layout_metabox_cb = function ($post) {
  $layout = get_post_meta($post->ID, 'p5m_layout', true) ?: 'normal';
  wp_nonce_field('p5m_layout_nonce', 'p5m_layout_nonce');
  
  $layouts = [
    'normal' => __('Normal (Centered, maximum width)', 'p5marketing'),
    'fullwidth' => __('Full-width (100% width)', 'p5marketing'),
    'sidebar-right' => __('With sidebar on the right', 'p5marketing'),
    'sidebar-left' => __('With sidebar on the left', 'p5marketing'),
  ];
  ?>
  <div style="display: flex; flex-direction: column; gap: 12px;">
    <?php foreach ($layouts as $value => $label): ?>
      <label style="display: flex; align-items: center; gap: 8px;">
        <input type="radio" name="p5m_layout" value="<?php echo esc_attr($value); ?>" 
               <?php checked($layout, $value); ?> />
        <span><?php echo esc_html($label); ?></span>
      </label>
    <?php endforeach; ?>
  </div>
  <p style="margin-top: 12px; font-size: 12px; color: #666;">
    <?php _e('Choose how you want the content to be displayed on this page.', 'p5marketing'); ?>
  </p>
  <?php
  // Content width selector (integrado aquí para reducir metaboxes)
  $content_width = get_post_meta($post->ID, 'p5m_content_width', true) ?: '1152';
  $widths = [
    'full'  => __('Full (100% screen width)', 'p5marketing'),
    '1024'  => '1024px',
    '1152'  => '1152px',
    '1280'  => '1280px',
    '1440'  => '1440px',
    '1600'  => '1600px',
    '1728'  => '1728px',
    '1920'  => '1920px',
  ];
  echo '<hr style="margin:16px 0;border:0;border-top:1px solid #ddd" />';
  echo '<strong style="display:block;margin-bottom:6px">' . esc_html__('Ancho del Contenido', 'p5marketing') . '</strong>';
  echo '<div style="display:flex;flex-direction:column;gap:6px">';
  foreach ($widths as $val => $label) {
    echo '<label style="display:flex;align-items:center;gap:6px">';
    echo '<input type="radio" name="p5m_content_width" value="' . esc_attr($val) . '" ' . checked($content_width, $val, false) . ' />';
    echo '<span>' . esc_html($label) . '</span>';
    echo '</label>';
  }
  echo '</div>';
};

// Callback para renderizar el metabox de ocultar título
$p5m_hide_title_metabox_cb = function ($post) {
  $hide_title = get_post_meta($post->ID, 'p5m_hide_title', true);
  wp_nonce_field('p5m_hide_title_nonce', 'p5m_hide_title_nonce');
  ?>
  <div style="display: flex; flex-direction: column; gap: 12px;">
    <label style="display: flex; align-items: center; gap: 8px;">
      <input type="checkbox" name="p5m_hide_title" value="1" <?php checked($hide_title, '1'); ?> />
      <span><?php esc_html_e('Ocultar el título de esta página', 'p5marketing'); ?></span>
    </label>
  </div>
  <p style="margin-top: 12px; font-size: 12px; color: #666;">
    <?php _e('Útil si quieres usar un título personalizado con bloques o page builder.', 'p5marketing'); ?>
  </p>
  <?php
};

// Callback para renderizar el metabox de mostrar imagen destacada
$p5m_show_featured_metabox_cb = function ($post) {
  $show_featured = get_post_meta($post->ID, 'p5m_show_featured', true);
  wp_nonce_field('p5m_show_featured_nonce', 'p5m_show_featured_nonce');
  ?>
  <div style="display: flex; flex-direction: column; gap: 12px;">
    <label style="display: flex; align-items: center; gap: 8px;">
      <input type="checkbox" name="p5m_show_featured" value="1" <?php checked($show_featured, '1'); ?> />
      <span><?php esc_html_e('Mostrar imagen destacada en la página', 'p5marketing'); ?></span>
    </label>
  </div>
  <p style="margin-top: 12px; font-size: 12px; color: #666;">
    <?php _e('Activa para mostrar la imagen destacada al inicio del contenido.', 'p5marketing'); ?>
  </p>
  <?php
};

// Registrar metabox para cada post type
add_action('add_meta_boxes', function () use ($p5m_layout_post_types, $p5m_layout_metabox_cb, $p5m_hide_title_metabox_cb, $p5m_show_featured_metabox_cb) {
  foreach ($p5m_layout_post_types as $post_type) {
    // Featured image visibility (arriba)
    add_meta_box(
      'p5m_show_featured_meta',
      __('Featured Image Options', 'p5marketing'),
      $p5m_show_featured_metabox_cb,
      $post_type,
      'side',
      'high'
    );

    // Hide title metabox (arriba)
    add_meta_box(
      'p5m_hide_title_meta',
      __('Title Options', 'p5marketing'),
      $p5m_hide_title_metabox_cb,
      $post_type,
      'side',
      'high'
    );

    // Layout metabox (debajo)
    add_meta_box(
      'p5m_layout_meta',
      __('Layout', 'p5marketing'),
      $p5m_layout_metabox_cb,
      $post_type,
      'side',
      'low'
    );
  }
});

// Guardar metabox para cada post type
add_action('save_post', function ($post_id) {
  // Save layout
  if (isset($_POST['p5m_layout_nonce']) && wp_verify_nonce($_POST['p5m_layout_nonce'], 'p5m_layout_nonce')) {
    if (!defined('DOING_AUTOSAVE') || !DOING_AUTOSAVE) {
      if (current_user_can('edit_post', $post_id)) {
        $value = isset($_POST['p5m_layout']) ? sanitize_text_field($_POST['p5m_layout']) : 'normal';
        // Validate against allowed layouts
        $valid_layouts = ['normal', 'fullwidth', 'sidebar-left', 'sidebar-right'];
        $value = in_array($value, $valid_layouts, true) ? $value : 'normal';
        update_post_meta($post_id, 'p5m_layout', $value);
      }
    }
  }
  
  // Save hide title
  if (isset($_POST['p5m_hide_title_nonce']) && wp_verify_nonce($_POST['p5m_hide_title_nonce'], 'p5m_hide_title_nonce')) {
    if (!defined('DOING_AUTOSAVE') || !DOING_AUTOSAVE) {
      if (current_user_can('edit_post', $post_id)) {
        $hide_title = isset($_POST['p5m_hide_title']) ? '1' : '0';
        update_post_meta($post_id, 'p5m_hide_title', $hide_title);
      }
    }
  }
  // Save content width
  if (isset($_POST['p5m_content_width'])) {
    $val = sanitize_text_field($_POST['p5m_content_width']);
    $allowed = ['full','1024','1152','1280','1440','1600','1728','1920'];
    if (!in_array($val, $allowed, true)) $val = '1152';
    update_post_meta($post_id, 'p5m_content_width', $val);
  }

  // Save featured image visibility
  if (isset($_POST['p5m_show_featured_nonce']) && wp_verify_nonce($_POST['p5m_show_featured_nonce'], 'p5m_show_featured_nonce')) {
    if (!defined('DOING_AUTOSAVE') || !DOING_AUTOSAVE) {
      if (current_user_can('edit_post', $post_id)) {
        $show_featured = isset($_POST['p5m_show_featured']) ? '1' : '0';
        update_post_meta($post_id, 'p5m_show_featured', $show_featured);
      }
    }
  }
}, 10, 1);

// Body class front-end para ancho contenido
add_filter('body_class', function($classes){
  if (is_singular()) {
    $val = get_post_meta(get_the_ID(), 'p5m_content_width', true);
    if ($val) $classes[] = 'p5m-width-' . sanitize_html_class($val);
  }
  return $classes;
});

// Editor (backend) añade clase para simular el mismo ancho
add_filter('admin_body_class', function($cls){
  $screen = get_current_screen();
  if ($screen && $screen->base === 'post' && isset($_GET['post'])) {
    $post_id = intval($_GET['post']);
    $val = get_post_meta($post_id, 'p5m_content_width', true);
    if ($val) $cls .= ' p5m-width-' . sanitize_html_class($val);
  }
  return $cls;
});

// (CSS dinámico front removido: ahora usamos clases estáticas en style.css para anchos)

// CSS en editor Gutenberg
add_action('admin_head', function(){
  $screen = get_current_screen();
  if (!$screen || $screen->base !== 'post') return;
  $post_id = isset($_GET['post']) ? intval($_GET['post']) : 0;
  if (!$post_id) return;
  $val = get_post_meta($post_id, 'p5m_content_width', true) ?: '1152';
  echo '<style id="p5m-editor-width">';
  if ($val === 'full') {
    echo '.edit-post-visual-editor__content-area{max-width:100%!important;}';
  } else {
    $px = intval($val);
    echo '.edit-post-visual-editor__content-area{max-width:' . $px . 'px;margin:0 auto;}';
  }
  // Preview helper classes (mobile/tablet toggles)
  echo 'body.p5m-preview-mobile .edit-post-visual-editor__content-area{width:375px;max-width:375px;}';
  echo 'body.p5m-preview-tablet .edit-post-visual-editor__content-area{width:768px;max-width:768px;}';
  echo '</style>';
  // Enhance preview mode detection: listen for WP preview mode changes
  echo '<script id="p5m-preview-controls">(function(){
    function setPreview(mode){
      document.body.classList.remove("p5m-preview-mobile","p5m-preview-tablet");
      if(mode==="mobile")document.body.classList.add("p5m-preview-mobile");
      else if(mode==="tablet")document.body.classList.add("p5m-preview-tablet");
    }
    document.addEventListener("DOMContentLoaded",function(){
      var toolbar=document.querySelector(".edit-post-header__settings");
      if(!toolbar)return;
      var wrap=document.createElement("div");
      wrap.className="p5m-preview-buttons";
      wrap.style.display="flex";
      wrap.style.gap="4px";
      wrap.innerHTML="<button type=button class=p5m-prev-btn data-mode=mobile style=padding:4px 8px;font-size:12px>Mobile</button><button type=button class=p5m-prev-btn data-mode=tablet style=padding:4px 8px;font-size:12px>Tablet</button><button type=button class=p5m-prev-btn data-mode=reset style=padding:4px 8px;font-size:12px>Desktop</button>";
      toolbar.appendChild(wrap);
      wrap.addEventListener("click",function(e){
        if(!e.target.matches(".p5m-prev-btn"))return;
        var m=e.target.getAttribute("data-mode");
        setPreview(m);
      });
      // Nota: los iconos nativos de vista previa pueden variar por versión.
      // Dejamos solo los botones personalizados (Mobile/Tablet/Desktop) para evitar errores.
    });
  })();</script>';
});


// ============================================================================
// Imágenes: sizes/srcset + atributos globales
// ============================================================================
add_filter('wp_calculate_image_sizes', function ($sizes, $size) {
  $container = 'min(100vw, 72rem)'; // ≈ 1152px
  return "(max-width: 768px) 100vw, {$container}";
}, 10, 2);

add_filter('wp_get_attachment_image_attributes', function ($attr, $attachment, $size) {
  $attr['loading']  = $attr['loading'] ?? 'lazy';
  $attr['decoding'] = 'async';

  // Primera imagen “importante” en singular → prioridad alta
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
// Limpieza de front (ruido del head)
// ============================================================================
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


// ============================================================================
// ACF JSON (export/import automático en /acf-json)
// ============================================================================
add_filter('acf/settings/save_json', fn() => get_stylesheet_directory() . '/acf-json');
add_filter('acf/settings/load_json', function ($paths) {
  $paths[] = get_stylesheet_directory() . '/acf-json';
  return $paths;
});


// ============================================================================
// Assets (Tailwind, style.css, Alpine, header.js) + Optimización CSS/JS
// ============================================================================
add_action('wp_enqueue_scripts', function () {
  if (is_admin()) return;

  // Tailwind — NO BLOQUEANTE (preload + async)
  $tw_path = get_template_directory() . '/dist/tailwind.css';
  if (file_exists($tw_path)) {
    wp_enqueue_style('p5marketing-tailwind', get_template_directory_uri() . '/dist/tailwind.css', [], filemtime($tw_path));
  }

  // style.css del theme — NO BLOQUEANTE
  wp_enqueue_style('p5-style', get_template_directory_uri() . '/style.css', [], filemtime(get_template_directory() . '/style.css'));


  // Alpine desde CDN (defer para no bloquear)
  wp_enqueue_script('alpine', 'https://cdn.jsdelivr.net/npm/alpinejs@3.14.0/dist/cdn.min.js', [], null, true);
  wp_script_add_data('alpine', 'defer', true);

  // JS del header (defer)
  $hdr_js = get_template_directory() . '/assets/js/header.js';
  if (file_exists($hdr_js)) {
    wp_enqueue_script('p5m-header', get_template_directory_uri() . '/assets/js/header.js', [], filemtime($hdr_js), true);
    wp_script_add_data('p5m-header', 'defer', true);
  }
}, 100);

// 🔧 Convierte TODOS los CSS en preload + async (no bloqueante)
add_filter('style_loader_tag', function ($html, $handle) {
  $non_critical = ['p5marketing-tailwind', 'p5-style']; // CSS que no es crítico
  // También tratar como no-bloqueante cualquier hoja de estilos de Google Fonts
  if (in_array($handle, $non_critical, true) || strpos($html, 'fonts.googleapis.com') !== false) {
    $pre = str_replace(
      "rel='stylesheet'",
      "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"",
      $html
    );
    return $pre . "<noscript>{$html}</noscript>";
  }
  return $html;
}, 10, 2);

// x-cloak para Alpine (prevenir flicker)
add_action('wp_head', fn() => print('<style>[x-cloak]{display:none!important}</style>' . PHP_EOL), 1);

// Forzar display=swap en Google Fonts para evitar FOIT
add_filter('style_loader_src', function ($src, $handle) {
  if (strpos($src, 'fonts.googleapis.com') !== false && strpos($src, 'display=') === false) {
    $src .= (strpos($src, '?') === false ? '?' : '&') . 'display=swap';
  }
  return $src;
}, 10, 2);

// Defer JS de jsDelivr globalmente (excluye jQuery para compatibilidad)
add_filter('script_loader_tag', function ($tag, $handle, $src) {
  if (is_admin() || empty($src)) return $tag;
  if ($handle === 'jquery' || $handle === 'jquery-core') return $tag;
  if (strpos($src, 'cdn.jsdelivr.net') !== false) {
    if (strpos($tag, ' defer') === false) {
      $tag = str_replace(' src', ' defer src', $tag);
    }
  }
  return $tag;
}, 10, 3);

// ----------------------------------------------------------------------------
// Light HTML caching headers (only for non-logged users)
// This improves Lighthouse "efficient cache policy" for HTML responses.
// Note: static assets (images/css/js) require server config (.htaccess/NGINX)
// ----------------------------------------------------------------------------
add_action('send_headers', function () {
  if (is_admin() || is_user_logged_in()) return;
  // Avoid caching WP previews or search with params that shouldn't cache
  if (isset($_GET['preview']) || is_search()) return;
  $max_age = is_404() ? 60 : 300; // 1 min for 404, 5 min for normal pages
  // Clean any conflicting headers first
  if (!headers_sent()) {
    header_remove('Cache-Control');
    header_remove('Pragma');
    header('Cache-Control: public, max-age=' . intval($max_age) . ', stale-while-revalidate=30, stale-if-error=86400');
  }
}, 0);


// ============================================================================
// SEO/Meta desde ACF Options (Site Settings)
// ============================================================================
add_action('wp_head', function () {
  if (is_admin()) return;

  // DNS prefetch y preconnect para CDNs externos
  echo '<link rel="dns-prefetch" href="//cdn.jsdelivr.net">' . PHP_EOL;
  echo '<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>' . PHP_EOL;
  // Google Fonts: preconnect/dns-prefetch para reducir latencia
  echo '<link rel="dns-prefetch" href="//fonts.googleapis.com">' . PHP_EOL;
  echo '<link rel="dns-prefetch" href="//fonts.gstatic.com">' . PHP_EOL;
  echo '<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>' . PHP_EOL;
  echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . PHP_EOL;

  // Helper: safely get ACF field if ACF is active
  $get_acf_field = function ($key) {
    return function_exists('get_field') ? get_field($key, 'option') : null;
  };

  // Prefer theme settings (p5m_settings) but fallback to previous ACF options
  $force_noindex_raw = p5m_get_setting('force_noindex', $get_acf_field('force_noindex'));
  $force_noindex = (bool) intval($force_noindex_raw);

  $canonical_raw = p5m_get_setting('canonical_domain', $get_acf_field('canonical_domain'));
  $canonical = $canonical_raw ? rtrim((string)$canonical_raw, '/') : '';

  $gsc = trim((string)p5m_get_setting('gsc_verification', $get_acf_field('gsc_verification')));
  $bing = trim((string)p5m_get_setting('bing_verification', $get_acf_field('bing_verification')));

  $manifest_raw = p5m_get_setting('manifest_url', $get_acf_field('manifest_url'));
  $manifest = $manifest_raw ? esc_url($manifest_raw) : '';

  $theme_color_raw = p5m_get_setting('theme_color', $get_acf_field('theme_color'));
  $theme_color = $theme_color_raw ? sanitize_text_field($theme_color_raw) : '';

  if ($force_noindex) echo '<meta name="robots" content="noindex,nofollow" />' . PHP_EOL;

  if ($canonical && !is_404()) {
    $url = esc_url($canonical . $_SERVER['REQUEST_URI']);
    echo '<link rel="canonical" href="' . $url . '" />' . PHP_EOL;
  }

// ---------------------------------------------------------------------------
// Silence a known WP 6.7+ notice caused by Beaver Builder loading translations
// too early: "_load_textdomain_just_in_time was called incorrectly ... fl-builder"
// We only suppress this specific case to avoid masking other useful notices.
// ---------------------------------------------------------------------------
add_filter('doing_it_wrong_trigger_error', function ($trigger, $function, $message, $version) {
  if ($function === '_load_textdomain_just_in_time' && strpos($message, 'fl-builder') !== false) {
    return false; // Don’t trigger the notice for this known plugin behavior
  }
  return $trigger;
}, 10, 4);

  if ($gsc)      echo '<meta name="google-site-verification" content="' . esc_attr($gsc) . '">' . PHP_EOL;
  if ($bing)     echo '<meta name="msvalidate.01" content="' . esc_attr($bing) . '">' . PHP_EOL;
  if ($manifest) echo '<link rel="manifest" href="' . esc_url($manifest) . '">' . PHP_EOL;
  if ($theme_color) echo '<meta name="theme-color" content="' . esc_attr($theme_color) . '">' . PHP_EOL;

  // Preconnect (string multilinea o repeater)
  $hosts = p5m_get_setting('preconnect_hosts', $get_acf_field('preconnect_hosts'));
  if (!empty($hosts)) {
    if (is_string($hosts)) $hosts = preg_split('/[\r\n,]+/', $hosts);
    if (is_array($hosts)) {
      foreach ($hosts as $h) {
        $host = trim((string)($h['text'] ?? $h));
        if ($host) {
          // sanitize and only echo if valid-looking URL
          $safe = esc_url_raw($host);
          if ($safe) echo '<link rel="preconnect" href="' . esc_url($safe) . '" crossorigin />' . PHP_EOL;
        }
      }
    }
  }
}, 5);


// ============================================================================
// ============================================================================
// Schema.org Structured Data (JSON-LD)
// ============================================================================
add_action('wp_head', function () {
  if (is_admin()) return;
  
  // Organization schema (en todas las páginas)
  if (function_exists('p5m_the_organization_schema')) {
    p5m_the_organization_schema();
  }
  
  // Article/BlogPosting schema (solo en posts)
  if (function_exists('p5m_the_article_schema')) {
    p5m_the_article_schema();
  }
  
  // WebPage schema (pages/singulars que no sean posts)
  if (function_exists('p5m_the_webpage_schema')) {
    p5m_the_webpage_schema();
  }
}, 10);


// ============================================================================
// Analytics & Tags (GTM/GA4 diferidos según ACF)
// ============================================================================
add_action('wp_head', function () {
  if (is_admin()) return;

  // Helper: safely get ACF field if ACF is active
  $get_acf_field = function ($key) {
    return function_exists('get_field') ? get_field($key, 'option') : null;
  };

  $gtm = trim((string)p5m_get_setting('gtm_container_id', $get_acf_field('gtm_container_id')));
  $gtm = $gtm ?: '';
  $ga4 = trim((string)p5m_get_setting('ga4_measurement_id', $get_acf_field('ga4_measurement_id')));
  $ga4 = $ga4 ?: '';

  // a) GTM diferido (si hay contenedor)
  if ($gtm) : ?>
    <script>
    (function(w,d,s,l,i){
      w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});
      function loadGTM(){ if(d.getElementById('gtm-script'))return;
        var f=d.getElementsByTagName(s)[0], j=d.createElement(s);
        j.async=true; j.id='gtm-script'; j.src='https://www.googletagmanager.com/gtm.js?id='+i;
        f.parentNode.insertBefore(j,f);
      }
      function init(){loadGTM(); cleanup();}
      function cleanup(){ d.removeEventListener('scroll',init); d.removeEventListener('mousemove',init); d.removeEventListener('touchstart',init); }
      setTimeout(init,3000);
      d.addEventListener('scroll',init,{once:true});
      d.addEventListener('mousemove',init,{once:true});
      d.addEventListener('touchstart',init,{once:true});
    })(window,document,'script','dataLayer','<?php echo esc_js($gtm); ?>');
    </script>
  <?php
  // b) Sin GTM: GA4 directo diferido (si hay ID)
  elseif ($ga4) : ?>
    <script>
    (function(w,d,s,i){
      function loadGA(){ if(d.getElementById('ga4-script'))return;
        var f=d.getElementsByTagName(s)[0], j=d.createElement(s);
        j.async=true; j.id='ga4-script'; j.src='https://www.googletagmanager.com/gtag/js?id='+i;
        f.parentNode.insertBefore(j,f);
        w.dataLayer=w.dataLayer||[]; w.gtag=function(){dataLayer.push(arguments);}
        gtag('js', new Date()); gtag('config', i);
      }
      function init(){loadGA(); cleanup();}
      function cleanup(){ d.removeEventListener('scroll',init); d.removeEventListener('mousemove',init); d.removeEventListener('touchstart',init); }
      setTimeout(init,3000);
      d.addEventListener('scroll',init,{once:true});
      d.addEventListener('mousemove',init,{once:true});
      d.addEventListener('touchstart',init,{once:true});
    })(window,document,'script','<?php echo esc_js($ga4); ?>');
    </script>
  <?php endif;
}, 5);

// ============================================================================
// Custom Scripts (Header/Body/Footer) - Diferidos
// ============================================================================
add_action('wp_head', function () {
  if (is_admin()) return;
  $scripts = trim((string)p5m_get_setting('header_scripts', ''));
  if (empty($scripts)) return;
  ?>
  <script id="p5m-header-custom-loader">
  (function(d){
    var executed = false;
    function loadHeaderScripts(){
      if(executed) return;
      executed = true;
      var container = d.createElement('div');
      container.innerHTML = <?php echo wp_json_encode($scripts); ?>;
      var scripts = container.querySelectorAll('script');
      scripts.forEach(function(s){
        var newScript = d.createElement('script');
        if(s.src) newScript.src = s.src;
        else newScript.textContent = s.textContent;
        Array.from(s.attributes).forEach(function(attr){
          if(attr.name !== 'src') newScript.setAttribute(attr.name, attr.value);
        });
        d.head.appendChild(newScript);
      });
      cleanup();
    }
    function cleanup(){ 
      d.removeEventListener('scroll',loadHeaderScripts); 
      d.removeEventListener('mousemove',loadHeaderScripts); 
      d.removeEventListener('touchstart',loadHeaderScripts); 
    }
    setTimeout(loadHeaderScripts, 3000);
    d.addEventListener('scroll', loadHeaderScripts, {once:true});
    d.addEventListener('mousemove', loadHeaderScripts, {once:true});
    d.addEventListener('touchstart', loadHeaderScripts, {once:true});
  })(document);
  </script>
  <?php
}, 99);

add_action('wp_body_open', function () {
  if (is_admin()) return;
  $scripts = trim((string)p5m_get_setting('body_scripts', ''));
  if (empty($scripts)) return;
  ?>
  <script id="p5m-body-custom-loader">
  (function(d){
    var executed = false;
    function loadBodyScripts(){
      if(executed) return;
      executed = true;
      var container = d.createElement('div');
      container.innerHTML = <?php echo wp_json_encode($scripts); ?>;
      var scripts = container.querySelectorAll('script');
      scripts.forEach(function(s){
        var newScript = d.createElement('script');
        if(s.src) newScript.src = s.src;
        else newScript.textContent = s.textContent;
        Array.from(s.attributes).forEach(function(attr){
          if(attr.name !== 'src') newScript.setAttribute(attr.name, attr.value);
        });
        d.body.appendChild(newScript);
      });
      cleanup();
    }
    function cleanup(){ 
      d.removeEventListener('scroll',loadBodyScripts); 
      d.removeEventListener('mousemove',loadBodyScripts); 
      d.removeEventListener('touchstart',loadBodyScripts); 
    }
    setTimeout(loadBodyScripts, 3000);
    d.addEventListener('scroll', loadBodyScripts, {once:true});
    d.addEventListener('mousemove', loadBodyScripts, {once:true});
    d.addEventListener('touchstart', loadBodyScripts, {once:true});
  })(document);
  </script>
  <?php
}, 99);

add_action('wp_footer', function () {
  if (is_admin()) return;
  $scripts = trim((string)p5m_get_setting('footer_scripts', ''));
  if (empty($scripts)) return;
  ?>
  <script id="p5m-footer-custom-loader">
  (function(d){
    var executed = false;
    function loadFooterScripts(){
      if(executed) return;
      executed = true;
      var container = d.createElement('div');
      container.innerHTML = <?php echo wp_json_encode($scripts); ?>;
      var scripts = container.querySelectorAll('script');
      scripts.forEach(function(s){
        var newScript = d.createElement('script');
        if(s.src) newScript.src = s.src;
        else newScript.textContent = s.textContent;
        Array.from(s.attributes).forEach(function(attr){
          if(attr.name !== 'src') newScript.setAttribute(attr.name, attr.value);
        });
        d.body.appendChild(newScript);
      });
      cleanup();
    }
    function cleanup(){ 
      d.removeEventListener('scroll',loadFooterScripts); 
      d.removeEventListener('mousemove',loadFooterScripts); 
      d.removeEventListener('touchstart',loadFooterScripts); 
    }
    setTimeout(loadFooterScripts, 3000);
    d.addEventListener('scroll', loadFooterScripts, {once:true});
    d.addEventListener('mousemove', loadFooterScripts, {once:true});
    d.addEventListener('touchstart', loadFooterScripts, {once:true});
  })(document);
  </script>
  <?php
}, 99);

// ============================================================================
// Immediate Scripts (no delay - for cookie banners, critical notices)
// ============================================================================
add_action('wp_footer', function () {
  if (is_admin()) return;
  $scripts = trim((string)p5m_get_setting('immediate_footer', ''));
  if (empty($scripts)) return;
  echo PHP_EOL . '<!-- P5M Immediate Footer Scripts -->' . PHP_EOL;
  echo $scripts . PHP_EOL;
}, 999); // High priority to load last but immediately

// Noscript GTM
add_action('wp_body_open', function () {
  if (is_admin()) return;
  $get_acf_field = function ($key) {
    return function_exists('get_field') ? get_field($key, 'option') : null;
  };
  if ($gtm = trim((string)p5m_get_setting('gtm_container_id', $get_acf_field('gtm_container_id')))) {
    echo '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id='.esc_attr($gtm).'" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>' . PHP_EOL;
  }
}, 5);


// ============================================================================
// Menús: clases y compatibilidad con Navigation block + Tailwind
// ============================================================================
add_filter('nav_menu_link_attributes', function ($atts, $item, $args) {
  if (!isset($args->theme_location)) return $atts;

  // Estilo base por menú
  if ($args->theme_location === 'primary') {
    $base = 'wp-block-navigation-item__content inline-flex items-center gap-1 py-2 text-gray-700 hover:text-gray-900 transition';
    // Agregar padding izquierdo si es submenu
    if (isset($item->menu_item_parent) && $item->menu_item_parent > 0) {
      $base .= ' pl-4';
    }
    $atts['class'] = isset($atts['class']) ? "{$atts['class']} {$base}" : $base;
  } elseif ($args->theme_location === 'social') {
    $base = 'inline-flex items-center text-gray-500 hover:text-gray-800 transition';
    $atts['class'] = isset($atts['class']) ? "{$atts['class']} {$base}" : $base;
  }

  // Accesibilidad si tiene hijos
  if (in_array('menu-item-has-children', $item->classes ?? [], true)) {
    $atts['aria-haspopup'] = 'true';
    $atts['aria-expanded'] = 'false';
  }
  return $atts;
}, 10, 3);

add_filter('nav_menu_css_class', function ($classes, $item, $args) {
  if (!isset($args->theme_location)) return $classes;

  if ($args->theme_location === 'primary') {
    $classes[] = 'list-none wp-block-navigation-item';
    if (in_array('menu-item-has-children', $classes, true)) {
      $classes[] = 'has-child relative group';
    }
  } elseif ($args->theme_location === 'social') {
    $classes[] = 'list-none';
  }
  return $classes;
}, 10, 3);

add_filter('nav_menu_submenu_css_class', function ($classes, $args) {
  if (!isset($args->theme_location) || $args->theme_location !== 'primary') return $classes;
  $classes[] = 'wp-block-navigation__submenu-container';
  // Oculto por defecto, visible en hover con Tailwind/CSS
  // Eliminamos la separación (mt-0) para evitar que el submenu se cierre al moverse entre padre y submenu
  // Añadimos transición y control de opacidad para un comportamiento más suave
  $classes[] = 'hidden group-hover:block absolute left-0 top-full mt-0 min-w-[12rem] rounded-lg border bg-white shadow-lg z-50 transition-opacity duration-150 opacity-100';
  return $classes;
}, 10, 2);


// ============================================================================
// Gutenberg: permitir todos los bloques (ajústalo si quieres limitar)
// ============================================================================
add_filter('allowed_block_types_all', function($allowed, $context){
  return true;
}, 10, 2);

// ============================================================================
// AJAX: Load More Posts
// ============================================================================
add_action('wp_ajax_p5m_load_more_posts', 'p5m_load_more_posts_callback');
add_action('wp_ajax_nopriv_p5m_load_more_posts', 'p5m_load_more_posts_callback');

function p5m_load_more_posts_callback() {
  check_ajax_referer('p5m_load_more_nonce', 'nonce');

  $paged = intval($_POST['paged'] ?? 1);
  $query_args = isset($_POST['query_args']) ? (array)$_POST['query_args'] : [];

  // Default args
  $defaults = array(
    'posts_per_page' => 6,
    'orderby'        => 'date',
    'order'          => 'DESC',
    'post_status'    => 'publish',
  );

  $args = wp_parse_args($query_args, $defaults);
  $args['paged'] = $paged;

  $query = new WP_Query($args);

  if ($query->have_posts()) {
    ob_start();
    while ($query->have_posts()) {
      $query->the_post();
      ?>
      <article <?php post_class('bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow overflow-hidden flex flex-col'); ?>>
        <?php
        if (has_post_thumbnail()) {
          ?>
          <div class="h-48 overflow-hidden bg-gray-200">
            <?php
            the_post_thumbnail('medium', array(
              'class' => 'w-full h-full object-cover hover:scale-105 transition-transform duration-300',
              'alt'   => get_the_title(),
            ));
            ?>
          </div>
          <?php
        }
        ?>
        <div class="p-6 flex flex-col flex-1">
          <h3 class="text-lg font-semibold text-gray-900 mb-2 line-clamp-2">
            <a href="<?php the_permalink(); ?>" class="hover:text-blue-600 transition-colors">
              <?php the_title(); ?>
            </a>
          </h3>
          <time class="text-sm text-gray-500 mb-3">
            <?php echo esc_html(get_the_date('d F Y')); ?>
          </time>
          <p class="text-gray-600 text-sm line-clamp-3 mb-4">
            <?php echo esc_html(wp_trim_words(get_the_excerpt(), 20)); ?>
          </p>
          <a href="<?php the_permalink(); ?>" class="inline-block text-blue-600 hover:text-blue-700 font-medium text-sm mt-auto">
            <?php esc_html_e('Read more →', 'p5marketing'); ?>
          </a>
        </div>
      </article>
      <?php
    }
    $html = ob_get_clean();
    wp_reset_postdata();

    // Check if there are more pages
    $has_more = ($paged < $query->max_num_pages);

    wp_send_json_success(array(
      'html'     => $html,
      'has_more' => $has_more,
    ));
  } else {
    wp_send_json_error(array(
      'message' => __('No more posts found.', 'p5marketing'),
    ));
  }
}

// ============================================================================
// Comments Control
// ============================================================================

/**
 * Deshabilita comentarios globalmente en todos los posts/páginas
 * Descomenta la siguiente línea para activar:
 */
add_filter('comments_open', '__return_false', 9999);
add_filter('pings_open', '__return_false', 9999);

/**
 * Oculta el formulario de comentarios
 */
add_filter('comment_form_defaults', function ($defaults) {
  $defaults['comment_notes_before'] = '';
  return $defaults;
});

// Helper para saber si mostrar imagen destacada
function p5m_show_featured() {
  $meta = get_post_meta(get_the_ID(), 'p5m_show_featured', true);
  if ($meta === '1') return true;
  if ($meta === '0') return false;
  // Fallback al ajuste global: por defecto 0 (no mostrar) si no está configurado
  $global = function_exists('p5m_get_setting') ? intval(p5m_get_setting('featured_default', 0)) : 0;
  return $global === 1;
}
