<?php
/**
 * Theme Setup
 * Configuración básica del tema: theme support, menús, sidebars, tamaños de imagen
 *
 * @package P5Marketing
 */

if (!defined('ABSPATH')) exit;

add_action('after_setup_theme', function () {
  // Core WordPress features
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

  // Menús (con soporte para múltiples idiomas)
  register_nav_menus([
    'primary'    => __('Primary Menu (Español)', 'p5marketing'),
    'primary_en' => __('Primary Menu (English)', 'p5marketing'),
    'footer'     => __('Footer Menu (Español)', 'p5marketing'),
    'footer_en'  => __('Footer Menu (English)', 'p5marketing'),
    'social'     => __('Social Menu', 'p5marketing'),
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

// ACF JSON (export/import automático en /acf-json)
add_filter('acf/settings/save_json', fn() => get_stylesheet_directory() . '/acf-json');
add_filter('acf/settings/load_json', function ($paths) {
  $paths[] = get_stylesheet_directory() . '/acf-json';
  return $paths;
});

// Gutenberg: permitir todos los bloques
add_filter('allowed_block_types_all', function($allowed, $context){
  return true;
}, 10, 2);

// Silence WP 6.7+ notice caused by Beaver Builder loading translations too early
add_filter('doing_it_wrong_trigger_error', function ($trigger, $function, $message, $version) {
  if ($function === '_load_textdomain_just_in_time' && strpos($message, 'fl-builder') !== false) {
    return false;
  }
  return $trigger;
}, 10, 4);

// Comments Control
add_filter('comments_open', '__return_false', 9999);
add_filter('pings_open', '__return_false', 9999);
add_filter('comment_form_defaults', function ($defaults) {
  $defaults['comment_notes_before'] = '';
  return $defaults;
});
