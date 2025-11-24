<?php
/**
 * P5M Multilanguage System
 * Sistema ligero de traducciones ES/EN sin dependencias externas
 * 
 * @package P5Marketing
 * @version 1.0.0
 */

if (!defined('ABSPATH')) exit;

// ============================================================================
// Configuration
// ============================================================================

define('P5M_DEFAULT_LANG', 'es');
define('P5M_AVAILABLE_LANGS', ['es', 'en']);

// ============================================================================
// Core Functions
// ============================================================================

/**
 * Detectar idioma actual
 * Prioridad: 1) URL, 2) Cookie, 3) Default
 */
function p5m_get_current_language() {
  static $lang = null;
  
  if ($lang !== null) return $lang;
  
  // 1. Detectar por URL (/en/...)
  $request_uri = $_SERVER['REQUEST_URI'] ?? '';
  if (preg_match('#^/en(/|$)#', $request_uri)) {
    $lang = 'en';
    return $lang;
  }
  
  // 2. Detectar por cookie
  if (isset($_COOKIE['p5m_lang']) && in_array($_COOKIE['p5m_lang'], P5M_AVAILABLE_LANGS, true)) {
    $lang = $_COOKIE['p5m_lang'];
    return $lang;
  }
  
  // 3. Default
  $lang = P5M_DEFAULT_LANG;
  return $lang;
}

/**
 * Obtener ID del post traducido
 * 
 * @param int $post_id ID del post actual
 * @param string $target_lang Idioma objetivo (es/en)
 * @return int|false ID del post traducido o false
 */
function p5m_get_translated_post_id($post_id, $target_lang) {
  if (!$post_id || !in_array($target_lang, P5M_AVAILABLE_LANGS, true)) {
    return false;
  }
  
  // Cache key
  $cache_key = "p5m_trans_{$post_id}_{$target_lang}";
  $translated_id = wp_cache_get($cache_key, 'p5m_multilang');
  
  if ($translated_id !== false) {
    return $translated_id;
  }
  
  // Buscar en post meta
  $meta_key = "_translation_{$target_lang}";
  $translated_id = get_post_meta($post_id, $meta_key, true);
  
  if ($translated_id && get_post_status($translated_id) === 'publish') {
    wp_cache_set($cache_key, $translated_id, 'p5m_multilang', 3600);
    return (int) $translated_id;
  }
  
  wp_cache_set($cache_key, 0, 'p5m_multilang', 3600);
  return false;
}

/**
 * Obtener URL del post traducido
 * 
 * @param int $post_id ID del post actual
 * @param string $target_lang Idioma objetivo
 * @return string|false URL traducida o false
 */
function p5m_get_translated_url($post_id, $target_lang) {
  $translated_id = p5m_get_translated_post_id($post_id, $target_lang);
  
  if (!$translated_id) {
    return false;
  }
  
  $url = get_permalink($translated_id);
  
  // Agregar prefijo /en/ si es necesario
  if ($target_lang === 'en' && strpos($url, '/en/') === false) {
    $url = str_replace(home_url('/'), home_url('/en/'), $url);
  }
  
  return $url;
}

/**
 * Traducir string del tema
 * 
 * @param string $key Clave del string
 * @param string|null $lang Idioma (null = actual)
 * @return string String traducido
 */
function p5m_t($key, $lang = null) {
  if ($lang === null) {
    $lang = p5m_get_current_language();
  }
  
  static $translations = null;
  
  if ($translations === null) {
    $json_path = get_template_directory() . '/languages/translations.json';
    if (file_exists($json_path)) {
      $json = file_get_contents($json_path);
      $translations = json_decode($json, true);
    } else {
      $translations = [];
    }
  }
  
  return $translations[$key][$lang] ?? $key;
}

/**
 * Alias corto para traducción
 */
function __t($key, $lang = null) {
  return p5m_t($key, $lang);
}

// ============================================================================
// URL Rewriting
// ============================================================================

/**
 * Agregar rewrite rules para /en/*
 */
add_action('init', function() {
  // Páginas: /en/page-slug/
  add_rewrite_rule(
    '^en/([^/]+)/?$',
    'index.php?pagename=$matches[1]&lang=en',
    'top'
  );
  
  // Posts: /en/2024/11/post-slug/
  add_rewrite_rule(
    '^en/([0-9]{4})/([0-9]{2})/([^/]+)/?$',
    'index.php?year=$matches[1]&monthnum=$matches[2]&name=$matches[3]&lang=en',
    'top'
  );
  
  // Archives: /en/category/slug/
  add_rewrite_rule(
    '^en/category/([^/]+)/?$',
    'index.php?category_name=$matches[1]&lang=en',
    'top'
  );
  
  // Home EN: /en/
  add_rewrite_rule(
    '^en/?$',
    'index.php?lang=en',
    'top'
  );
}, 10);

/**
 * Registrar query var 'lang'
 */
add_filter('query_vars', function($vars) {
  $vars[] = 'lang';
  return $vars;
});

/**
 * Modificar permalink para agregar /en/ si es necesario
 */
add_filter('post_link', function($permalink, $post) {
  $lang = get_post_meta($post->ID, '_post_language', true);
  
  if ($lang === 'en' && strpos($permalink, '/en/') === false) {
    $permalink = str_replace(home_url('/'), home_url('/en/'), $permalink);
  }
  
  return $permalink;
}, 10, 2);

add_filter('page_link', function($permalink, $post_id) {
  $lang = get_post_meta($post_id, '_post_language', true);
  
  if ($lang === 'en' && strpos($permalink, '/en/') === false) {
    $permalink = str_replace(home_url('/'), home_url('/en/'), $permalink);
  }
  
  return $permalink;
}, 10, 2);

// ============================================================================
// Language Switcher
// ============================================================================

/**
 * Renderizar language switcher
 * 
 * @param array $args Argumentos opcionales
 * @return string HTML del switcher
 */
function p5m_language_switcher($args = []) {
  $defaults = [
    'show_flags' => false,
    'show_names' => true,
    'class' => 'p5m-lang-switcher'
  ];
  
  $args = wp_parse_args($args, $defaults);
  $current_lang = p5m_get_current_language();
  $post_id = get_queried_object_id();
  
  $languages = [
    'es' => ['name' => 'Español', 'flag' => '🇪🇸'],
    'en' => ['name' => 'English', 'flag' => '🇺🇸']
  ];
  
  ob_start();
  ?>
  <div class="<?php echo esc_attr($args['class']); ?>">
    <?php foreach ($languages as $code => $data): ?>
      <?php
      $url = '';
      $is_active = ($code === $current_lang);
      
      if ($is_active) {
        $url = '#';
      } elseif ($post_id) {
        $translated_url = p5m_get_translated_url($post_id, $code);
        $url = $translated_url ?: home_url($code === 'en' ? '/en/' : '/');
      } else {
        $url = home_url($code === 'en' ? '/en/' : '/');
      }
      ?>
      <a href="<?php echo esc_url($url); ?>" 
         class="lang-item <?php echo $is_active ? 'active' : ''; ?>"
         data-lang="<?php echo esc_attr($code); ?>"
         <?php if (!$is_active): ?>onclick="document.cookie='p5m_lang=<?php echo $code; ?>;path=/;max-age=31536000'"<?php endif; ?>>
        <?php if ($args['show_flags']): ?>
          <span class="flag"><?php echo $data['flag']; ?></span>
        <?php endif; ?>
        <?php if ($args['show_names']): ?>
          <span class="name"><?php echo esc_html($data['name']); ?></span>
        <?php endif; ?>
      </a>
    <?php endforeach; ?>
  </div>
  <?php
  return ob_get_clean();
}

// ============================================================================
// Admin UI - Metabox para vincular traducciones
// ============================================================================

/**
 * Agregar metabox en el editor
 */
add_action('add_meta_boxes', function() {
  $post_types = ['post', 'page'];
  
  foreach ($post_types as $type) {
    add_meta_box(
      'p5m_translation_meta',
      '🌐 Traducciones',
      'p5m_render_translation_metabox',
      $type,
      'side',
      'high'
    );
  }
});

/**
 * Renderizar metabox
 */
function p5m_render_translation_metabox($post) {
  wp_nonce_field('p5m_translation_meta', 'p5m_translation_nonce');
  
  $post_lang = get_post_meta($post->ID, '_post_language', true) ?: 'es';
  $trans_es = get_post_meta($post->ID, '_translation_es', true);
  $trans_en = get_post_meta($post->ID, '_translation_en', true);
  
  ?>
  <div class="p5m-translation-metabox">
    <p>
      <label><strong>Idioma de este post:</strong></label><br>
      <select name="p5m_post_language" style="width:100%">
        <option value="es" <?php selected($post_lang, 'es'); ?>>🇪🇸 Español</option>
        <option value="en" <?php selected($post_lang, 'en'); ?>>🇺🇸 English</option>
      </select>
    </p>
    
    <hr>
    
    <p>
      <label><strong>Versión en Español (ID):</strong></label><br>
      <input type="number" name="p5m_translation_es" value="<?php echo esc_attr($trans_es); ?>" style="width:100%" placeholder="ID del post en español">
    </p>
    
    <p>
      <label><strong>Versión en English (ID):</strong></label><br>
      <input type="number" name="p5m_translation_en" value="<?php echo esc_attr($trans_en); ?>" style="width:100%" placeholder="ID del post en inglés">
    </p>
    
    <p class="description">
      💡 Ingresa el ID del post en el otro idioma. Deja vacío si no hay traducción.
    </p>
  </div>
  <style>
  .p5m-translation-metabox input[type="number"],
  .p5m-translation-metabox select {
    margin-top: 5px;
  }
  </style>
  <?php
}

/**
 * Guardar metabox
 */
add_action('save_post', function($post_id) {
  // Verificaciones
  if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
  if (!isset($_POST['p5m_translation_nonce']) || !wp_verify_nonce($_POST['p5m_translation_nonce'], 'p5m_translation_meta')) return;
  if (!current_user_can('edit_post', $post_id)) return;
  
  // Guardar idioma del post
  if (isset($_POST['p5m_post_language'])) {
    update_post_meta($post_id, '_post_language', sanitize_text_field($_POST['p5m_post_language']));
  }
  
  // Guardar IDs de traducciones
  if (isset($_POST['p5m_translation_es'])) {
    $trans_es = intval($_POST['p5m_translation_es']);
    update_post_meta($post_id, '_translation_es', $trans_es > 0 ? $trans_es : '');
  }
  
  if (isset($_POST['p5m_translation_en'])) {
    $trans_en = intval($_POST['p5m_translation_en']);
    update_post_meta($post_id, '_translation_en', $trans_en > 0 ? $trans_en : '');
  }
  
  // Limpiar cache
  wp_cache_delete("p5m_trans_{$post_id}_es", 'p5m_multilang');
  wp_cache_delete("p5m_trans_{$post_id}_en", 'p5m_multilang');
}, 10, 1);

// ============================================================================
// Helper: Mostrar idioma actual en admin bar
// ============================================================================

add_action('admin_bar_menu', function($wp_admin_bar) {
  if (is_admin()) return;
  
  $lang = p5m_get_current_language();
  $lang_name = $lang === 'en' ? 'English' : 'Español';
  
  $wp_admin_bar->add_node([
    'id' => 'p5m-current-lang',
    'title' => '🌐 ' . $lang_name,
    'href' => '#',
  ]);
}, 999);

// ============================================================================
// Auto-switch Navigation Menus based on Language
// ============================================================================

/**
 * Filtrar ubicación del menú según idioma actual
 * Si estás en inglés y existe primary_en, usa ese. Si no, usa primary.
 */

// Solo aplicar el filtro en el frontend, nunca en el admin
if (!is_admin()) {
  add_filter('theme_mod_nav_menu_locations', function($locations) {
    $lang = p5m_get_current_language();
    // Solo actuar si estamos en inglés
    if ($lang !== 'en') {
      return $locations;
    }
    // Si hay menú en inglés configurado, usarlo
    if (!empty($locations['primary_en'])) {
      $locations['primary'] = $locations['primary_en'];
    }
    if (!empty($locations['footer_en'])) {
      $locations['footer'] = $locations['footer_en'];
    }
    return $locations;
  }, 10, 1);
}

