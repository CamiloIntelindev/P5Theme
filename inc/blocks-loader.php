<?php
/**
 * Custom Gutenberg Blocks Loader
 * Carga todos los bloques personalizados desde la carpeta /blocks
 *
 * @package P5Marketing
 */

if (!defined('ABSPATH')) exit;

/**
 * Load all custom blocks
 */
function p5m_load_custom_blocks() {
  $blocks_dir = get_template_directory() . '/blocks/';
  
  // Lista de bloques a cargar
  $blocks = [
    'posts-grid.php',
    // Aquí agregaremos más bloques en el futuro
    // 'testimonials.php',
    // 'cta-banner.php',
    // 'services-grid.php',
  ];
  
  foreach ($blocks as $block) {
    $file = $blocks_dir . $block;
    if (file_exists($file)) {
      require_once $file;
    }
  }
}
add_action('init', 'p5m_load_custom_blocks');

/**
 * Register all custom blocks
 */
function p5m_register_all_blocks() {
  if (!function_exists('register_block_type')) {
    return;
  }
  
  // Registrar cada bloque
  p5m_register_posts_grid_block();
  
  // Aquí agregaremos el registro de más bloques
  // p5m_register_testimonials_block();
  // p5m_register_cta_banner_block();
}
add_action('init', 'p5m_register_all_blocks');

/**
 * Enqueue block editor assets (JavaScript para el editor)
 */
function p5m_enqueue_block_editor_assets() {
  $blocks_js = get_template_directory() . '/assets/js/blocks-editor.js';
  
  if (file_exists($blocks_js)) {
    wp_enqueue_script(
      'p5m-blocks-editor',
      get_template_directory_uri() . '/assets/js/blocks-editor.js',
      ['wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-i18n'],
      filemtime($blocks_js),
      true
    );
    
    // Pasar data a JavaScript
    wp_localize_script('p5m-blocks-editor', 'p5mBlocksData', [
      'postTypes' => p5m_get_available_post_types(),
      'imageSizes' => p5m_get_available_image_sizes(),
    ]);
  }
}
add_action('enqueue_block_editor_assets', 'p5m_enqueue_block_editor_assets');

/**
 * Get available post types for block settings
 */
function p5m_get_available_post_types() {
  $post_types = get_post_types(['public' => true], 'objects');
  $options = [];
  
  foreach ($post_types as $post_type) {
    if ($post_type->name !== 'attachment') {
      $options[] = [
        'label' => $post_type->label,
        'value' => $post_type->name,
      ];
    }
  }
  
  return $options;
}

/**
 * Get available image sizes for block settings
 */
function p5m_get_available_image_sizes() {
  $sizes = get_intermediate_image_sizes();
  $options = [];
  
  foreach ($sizes as $size) {
    $options[] = [
      'label' => ucwords(str_replace(['-', '_'], ' ', $size)),
      'value' => $size,
    ];
  }
  
  // Add 'full' size
  $options[] = [
    'label' => __('Full Size', 'p5marketing'),
    'value' => 'full',
  ];
  
  return $options;
}

/**
 * Add custom block category
 */
function p5m_block_categories($categories) {
  return array_merge(
    $categories,
    [
      [
        'slug' => 'p5m-blocks',
        'title' => __('P5 Marketing Blocks', 'p5marketing'),
        'icon' => 'grid-view',
      ],
    ]
  );
}
add_filter('block_categories_all', 'p5m_block_categories', 10, 1);
