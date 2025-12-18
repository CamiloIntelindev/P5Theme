<?php
/**
 * P5 Header Settings
 * Custom header configuration (logo, menu, CTA)
 *
 * @package P5Marketing
 */

if (!defined('ABSPATH')) exit;

/* Enqueue admin scripts only on our header settings page */
add_action('admin_enqueue_scripts', function ($hook) {
  if ($hook !== 'appearance_page_p5m-header-settings') return;
  
  // WP media (uploader)
  wp_enqueue_media();

  // Color picker for color fields
  wp_enqueue_style('wp-color-picker');
  wp_enqueue_script('wp-color-picker');
  wp_add_inline_script('wp-color-picker', "jQuery(function($){ $('.p5m-color-field').wpColorPicker(); });", 'after');
  
  $js = get_template_directory() . '/assets/js/p5m-admin.js';
  if (file_exists($js)) {
    wp_enqueue_script('p5m-admin', get_template_directory_uri() . '/assets/js/p5m-admin.js', ['jquery'], filemtime($js), true);
  }
}, 20);

/* Add menu page under Appearance */
add_action('admin_menu', function() {
  add_theme_page(
    __('P5 Header Settings', 'p5marketing'),
    __('P5 Header', 'p5marketing'),
    'manage_options',
    'p5m-header-settings',
    'p5m_header_settings_page_html'
  );
});

/* Register settings */
add_action('admin_init', function() {
  register_setting('p5m_header_settings_group', 'p5m_header_settings', 'p5m_header_settings_sanitize');
  
  add_settings_section(
    'p5m_header_main_section',
    __('Header configuration', 'p5marketing'),
    function() {
      echo '<p>' . __('Customize the header logo, menu, and CTA button.', 'p5marketing') . '</p>';
    },
    'p5m-header-settings'
  );
  
  add_settings_field('p5m_header_logo', __('Header logo', 'p5marketing'), 'p5m_field_header_logo_cb', 'p5m-header-settings', 'p5m_header_main_section');
  add_settings_field('p5m_header_menu', __('Primary menu', 'p5marketing'), 'p5m_field_header_menu_cb', 'p5m-header-settings', 'p5m_header_main_section');
  add_settings_field('p5m_header_menu_display', __('Display menu as', 'p5marketing'), 'p5m_field_header_menu_display_cb', 'p5m-header-settings', 'p5m_header_main_section');
  add_settings_field('p5m_header_layout_order', __('Element order', 'p5marketing'), 'p5m_field_header_layout_order_cb', 'p5m-header-settings', 'p5m_header_main_section');
  add_settings_field('p5m_header_position', __('Header position', 'p5marketing'), 'p5m_field_header_position_cb', 'p5m-header-settings', 'p5m_header_main_section');
  add_settings_field('p5m_header_logo_width', __('Logo width', 'p5marketing'), 'p5m_field_header_logo_width_cb', 'p5m-header-settings', 'p5m_header_main_section');
  add_settings_field('p5m_header_min_height', __('Minimum header height', 'p5marketing'), 'p5m_field_header_min_height_cb', 'p5m-header-settings', 'p5m_header_main_section');
  
  // CTA section
  add_settings_section(
    'p5m_header_cta_section',
    __('CTA button', 'p5marketing'),
    function(){
      echo '<p>' . esc_html__('Configure the header Call-to-Action button.', 'p5marketing') . '</p>';
    },
    'p5m-header-settings'
  );
  
  add_settings_field('p5m_header_cta_enable', __('Show CTA', 'p5marketing'), 'p5m_field_header_cta_enable_cb', 'p5m-header-settings', 'p5m_header_cta_section');
  add_settings_field('p5m_header_cta_text', __('CTA button text', 'p5marketing'), 'p5m_field_header_cta_text_cb', 'p5m-header-settings', 'p5m_header_cta_section');
  add_settings_field('p5m_header_cta_url', __('CTA button URL', 'p5marketing'), 'p5m_field_header_cta_url_cb', 'p5m-header-settings', 'p5m_header_cta_section');
  add_settings_field('p5m_header_cta_bg_color', __('Background color', 'p5marketing'), 'p5m_field_header_color_cb', 'p5m-header-settings', 'p5m_header_cta_section', ['key' => 'cta_bg_color', 'placeholder' => '#4f46e5']);
  add_settings_field('p5m_header_cta_text_color', __('Text color', 'p5marketing'), 'p5m_field_header_color_cb', 'p5m-header-settings', 'p5m_header_cta_section', ['key' => 'cta_text_color', 'placeholder' => '#ffffff']);
  add_settings_field('p5m_header_cta_padding', __('Padding', 'p5marketing'), 'p5m_field_header_cta_padding_cb', 'p5m-header-settings', 'p5m_header_cta_section');
  add_settings_field('p5m_header_cta_border', __('Border', 'p5marketing'), 'p5m_field_header_cta_border_cb', 'p5m-header-settings', 'p5m_header_cta_section');
  add_settings_field('p5m_header_cta_border_radius', __('Border Radius', 'p5marketing'), 'p5m_field_header_cta_border_radius_cb', 'p5m-header-settings', 'p5m_header_cta_section');
  add_settings_field('p5m_header_cta_hover_bg', __('Hover - background color', 'p5marketing'), 'p5m_field_header_color_cb', 'p5m-header-settings', 'p5m_header_cta_section', ['key' => 'cta_hover_bg_color', 'placeholder' => '#4338ca']);
  add_settings_field('p5m_header_cta_hover_text', __('Hover - text color', 'p5marketing'), 'p5m_field_header_color_cb', 'p5m-header-settings', 'p5m_header_cta_section', ['key' => 'cta_hover_text_color', 'placeholder' => '#ffffff']);

  // Styles and behavior section
  add_settings_section(
    'p5m_header_style_section',
    __('Styles and behavior', 'p5marketing'),
    function(){
      echo '<p>' . esc_html__('Control the bottom border, background (solid or gradient), and behavior on scroll.', 'p5marketing') . '</p>';
    },
    'p5m-header-settings'
  );

  add_settings_field('p5m_header_border_bottom', __('Bottom border', 'p5marketing'), 'p5m_field_header_border_cb', 'p5m-header-settings', 'p5m_header_style_section');
  add_settings_field('p5m_header_background_mode', __('Header background', 'p5marketing'), 'p5m_field_header_background_mode_cb', 'p5m-header-settings', 'p5m_header_style_section');
  add_settings_field('p5m_header_bg_color', __('Solid color', 'p5marketing'), 'p5m_field_header_color_cb', 'p5m-header-settings', 'p5m_header_style_section', ['key' => 'bg_color', 'placeholder' => '#ffffff']);
  add_settings_field('p5m_header_bg_opacity', __('Solid color opacity (0–1)', 'p5marketing'), 'p5m_field_header_opacity_cb', 'p5m-header-settings', 'p5m_header_style_section', ['key' => 'bg_opacity']);
  add_settings_field('p5m_header_gradient_start', __('Gradient start color', 'p5marketing'), 'p5m_field_header_color_cb', 'p5m-header-settings', 'p5m_header_style_section', ['key' => 'gradient_start', 'placeholder' => '#ffffff']);
  add_settings_field('p5m_header_gradient_start_opacity', __('Gradient start opacity (0–1)', 'p5marketing'), 'p5m_field_header_opacity_cb', 'p5m-header-settings', 'p5m_header_style_section', ['key' => 'gradient_start_opacity']);
  add_settings_field('p5m_header_gradient_end', __('Gradient end color', 'p5marketing'), 'p5m_field_header_color_cb', 'p5m-header-settings', 'p5m_header_style_section', ['key' => 'gradient_end', 'placeholder' => '#f3f4f6']);
  add_settings_field('p5m_header_gradient_end_opacity', __('Gradient end opacity (0–1)', 'p5marketing'), 'p5m_field_header_opacity_cb', 'p5m-header-settings', 'p5m_header_style_section', ['key' => 'gradient_end_opacity']);
  add_settings_field('p5m_header_gradient_direction', __('Gradient direction', 'p5marketing'), 'p5m_field_header_gradient_direction_cb', 'p5m-header-settings', 'p5m_header_style_section');

  // Scroll threshold + colors on scroll
  add_settings_field('p5m_header_link_color', __('Navigation link color', 'p5marketing'), 'p5m_field_header_color_cb', 'p5m-header-settings', 'p5m_header_style_section', ['key' => 'link_color', 'placeholder' => '#000000']);

  add_settings_field('p5m_header_scroll_threshold', __('Scroll threshold (px)', 'p5marketing'), 'p5m_field_header_scroll_threshold_cb', 'p5m-header-settings', 'p5m_header_style_section');
  add_settings_field('p5m_header_scrolled_bg', __('Background when scrolling', 'p5marketing'), 'p5m_field_header_color_cb', 'p5m-header-settings', 'p5m_header_style_section', ['key' => 'scrolled_bg_color', 'placeholder' => '#ffffff']);
  add_settings_field('p5m_header_scrolled_bg_opacity', __('Background when scrolling opacity (0–1)', 'p5marketing'), 'p5m_field_header_opacity_cb', 'p5m-header-settings', 'p5m_header_style_section', ['key' => 'scrolled_bg_opacity']);
  add_settings_field('p5m_header_scrolled_link', __('Links when scrolling', 'p5marketing'), 'p5m_field_header_color_cb', 'p5m-header-settings', 'p5m_header_style_section', ['key' => 'scrolled_link_color', 'placeholder' => '#111827']);
  add_settings_field('p5m_header_scrolled_cta_bg', __('CTA background when scrolling', 'p5marketing'), 'p5m_field_header_color_cb', 'p5m-header-settings', 'p5m_header_style_section', ['key' => 'scrolled_cta_bg', 'placeholder' => '#4f46e5']);
  add_settings_field('p5m_header_scrolled_cta_text', __('CTA text when scrolling', 'p5marketing'), 'p5m_field_header_color_cb', 'p5m-header-settings', 'p5m_header_style_section', ['key' => 'scrolled_cta_text', 'placeholder' => '#ffffff']);
  
  // Custom grids section (rendered with a custom table)
  add_settings_section(
    'p5m_header_grids_section',
    __('Custom grids', 'p5marketing'),
    'p5m_header_grids_section_cb',
    'p5m-header-settings'
  );
});

/* Field Callbacks */
function p5m_field_header_logo_cb() {
  $opts = get_option('p5m_header_settings', []);
  $val = esc_attr($opts['logo'] ?? '');
  $id  = intval($opts['logo_id'] ?? 0);
  
  echo '<div style="display:flex;align-items:center;gap:12px">';
  echo '<input id="p5m_header_logo" name="p5m_header_settings[logo]" type="text" value="'. $val .'" class="regular-text" placeholder="' . esc_attr(get_template_directory_uri() . '/assets/img/logo-fallback.svg') . '" />';
  echo '<input id="p5m_header_logo_id" name="p5m_header_settings[logo_id]" type="hidden" value="'. $id .'" />';
  echo '<button class="button p5m-media-upload" data-target="#p5m_header_logo" data-target-id="#p5m_header_logo_id">' . esc_html__('Select image', 'p5marketing') . '</button>';
  echo '<button class="button p5m-media-remove" type="button">' . esc_html__('Remove', 'p5marketing') . '</button>';
  if ($val) echo '<img id="p5m_header_logo_preview" src="'. esc_url($val) .'" alt="preview" style="max-height:48px;display:block;border-radius:4px;" />';
  else echo '<img id="p5m_header_logo_preview" src="" alt="preview" style="max-height:48px;display:none;border-radius:4px;" />';
  echo '</div>';
  echo '<p class="description">' . __('Header logo URL. If empty, the site default logo is used.', 'p5marketing') . '</p>';
}

function p5m_field_header_menu_cb() {
  $opts = get_option('p5m_header_settings', []);
  $selected = $opts['menu'] ?? 'primary';
  $menus = wp_get_nav_menus();
  
  echo '<select id="p5m_header_menu" name="p5m_header_settings[menu]" class="regular-text">';
  echo '<option value="primary" ' . selected($selected, 'primary', false) . '>' . esc_html__('Primary Menu (default)', 'p5marketing') . '</option>';
  
  foreach ($menus as $menu) {
    echo '<option value="' . esc_attr($menu->slug) . '" ' . selected($selected, $menu->slug, false) . '>';
    echo esc_html($menu->name);
    echo '</option>';
  }
  echo '</select>';
  echo '<p class="description">' . __('Select which menu appears in the header.', 'p5marketing') . '</p>';

  // Visual validation: location vs slug + assignment details
  $registered_locations = array_keys(get_registered_nav_menus());
  $is_location = in_array($selected, $registered_locations, true);
  echo '<div class="notice inline" style="margin-top:8px; padding:10px; background:#f8fafc; border:1px solid #e5e7eb; border-radius:4px;">';
  if ($is_location) {
    echo '<p style="margin:0 0 6px 0;"><strong>' . esc_html__('Type:', 'p5marketing') . '</strong> ' . esc_html__('Theme location', 'p5marketing') . ' <code>' . esc_html($selected) . '</code></p>';
    $locations = get_nav_menu_locations();
    $menu_id = isset($locations[$selected]) ? intval($locations[$selected]) : 0;
    if ($menu_id) {
      $menu_obj = wp_get_nav_menu_object($menu_id);
      $items = wp_get_nav_menu_items($menu_id);
      $count = is_array($items) ? count($items) : 0;
      echo '<p style="margin:0;"><strong>' . esc_html__('Assigned to:', 'p5marketing') . '</strong> ' . esc_html($menu_obj ? $menu_obj->name : '') . ' (' . sprintf(esc_html__('%d items', 'p5marketing'), $count) . ')</p>';
    } else {
      echo '<p style="margin:0;">' . esc_html__('This location does not have a menu assigned yet.', 'p5marketing') . ' <a href="' . esc_url(admin_url('nav-menus.php')) . '" target="_blank">' . esc_html__('Assign menu →', 'p5marketing') . '</a></p>';
    }
  } else {
    echo '<p style="margin:0 0 6px 0;"><strong>' . esc_html__('Type:', 'p5marketing') . '</strong> ' . esc_html__('Menu by slug', 'p5marketing') . ' <code>' . esc_html($selected) . '</code></p>';
    $menu_obj = get_term_by('slug', $selected, 'nav_menu');
    if ($menu_obj && !is_wp_error($menu_obj)) {
      $items = wp_get_nav_menu_items($menu_obj->term_id);
      $count = is_array($items) ? count($items) : 0;
      echo '<p style="margin:0;"><strong>' . esc_html__('Label:', 'p5marketing') . '</strong> ' . esc_html($menu_obj->name) . ' (' . sprintf(esc_html__('%d items', 'p5marketing'), $count) . ')</p>';
    } else {
      echo '<p style="margin:0;">' . esc_html__('No menu found with this slug.', 'p5marketing') . ' <a href="' . esc_url(admin_url('nav-menus.php')) . '" target="_blank">' . esc_html__('Create/edit menus →', 'p5marketing') . '</a></p>';
    }
  }
  echo '</div>';
}

function p5m_field_header_menu_display_cb() {
  $opts = get_option('p5m_header_settings', []);
  $val = $opts['menu_display'] ?? 'full';
  
  echo '<select id="p5m_header_menu_display" name="p5m_header_settings[menu_display]" class="regular-text">';
  echo '<option value="full" ' . selected($val, 'full', false) . '>' . esc_html__('Full menu (links visible)', 'p5marketing') . '</option>';
  echo '<option value="hamburger" ' . selected($val, 'hamburger', false) . '>' . esc_html__('Hamburger (menu icon)', 'p5marketing') . '</option>';
  echo '</select>';
  echo '<p class="description">' . __('Choose whether to show the full menu or the hamburger icon on desktop. Mobile is always hamburger.', 'p5marketing') . '</p>';
}

function p5m_field_header_layout_order_cb() {
  $opts = get_option('p5m_header_settings', []);
  $val = $opts['layout_order'] ?? 'logo-nav-cta';
  
  echo '<select id="p5m_header_layout_order" name="p5m_header_settings[layout_order]" class="regular-text">';
  echo '<option value="logo-nav-cta" ' . selected($val, 'logo-nav-cta', false) . '>' . esc_html__('Logo - Nav - CTA', 'p5marketing') . '</option>';
  echo '<option value="logo-cta-nav" ' . selected($val, 'logo-cta-nav', false) . '>' . esc_html__('Logo - CTA - Nav', 'p5marketing') . '</option>';
  echo '<option value="nav-logo-cta" ' . selected($val, 'nav-logo-cta', false) . '>' . esc_html__('Nav - Logo - CTA', 'p5marketing') . '</option>';
  echo '<option value="cta-logo-nav" ' . selected($val, 'cta-logo-nav', false) . '>' . esc_html__('CTA - Logo - Nav', 'p5marketing') . '</option>';
  echo '<option value="nav-cta-logo" ' . selected($val, 'nav-cta-logo', false) . '>' . esc_html__('Nav - CTA - Logo', 'p5marketing') . '</option>';
  echo '<option value="cta-nav-logo" ' . selected($val, 'cta-nav-logo', false) . '>' . esc_html__('CTA - Nav - Logo', 'p5marketing') . '</option>';
  echo '</select>';
  echo '<p class="description">' . __('Choose the element order in the header.', 'p5marketing') . '</p>';
}

function p5m_field_header_position_cb() {
  $opts = get_option('p5m_header_settings', []);
  $val = $opts['position'] ?? 'sticky';
  
  echo '<select id="p5m_header_position" name="p5m_header_settings[position]" class="regular-text">';
  echo '<option value="sticky" ' . selected($val, 'sticky', false) . '>' . esc_html__('Sticky (stays on top)', 'p5marketing') . '</option>';
  echo '<option value="static" ' . selected($val, 'static', false) . '>' . esc_html__('Scroll (moves with content)', 'p5marketing') . '</option>';
  echo '<option value="fixed" ' . selected($val, 'fixed', false) . '>' . esc_html__('Fixed (always visible)', 'p5marketing') . '</option>';
  echo '</select>';
  echo '<p class="description">' . __('Choose whether the header stays sticky, scrolls with content, or remains fixed.', 'p5marketing') . '</p>';
}

function p5m_field_header_cta_enable_cb() {
  $opts = get_option('p5m_header_settings', []);
  $checked = isset($opts['cta_enable']) ? intval($opts['cta_enable']) : 1;
  echo '<label><input type="checkbox" id="p5m_header_cta_enable" name="p5m_header_settings[cta_enable]" value="1" ' . checked($checked, 1, false) . ' /> ';
  echo esc_html__('Show CTA button in the header', 'p5marketing') . '</label>';
  echo '<p class="description">' . __('Uncheck to hide the CTA entirely and allow the nav to take its space.', 'p5marketing') . '</p>';
}

function p5m_field_header_cta_text_cb() {
  $opts = get_option('p5m_header_settings', []);
  $val = esc_attr($opts['cta_text'] ?? '');
  echo '<input id="p5m_header_cta_text" name="p5m_header_settings[cta_text]" type="text" value="'. $val .'" class="regular-text" placeholder="Book Your Private 30-Minute Webinar" />';
  echo '<p class="description">' . __('Primary header CTA button text.', 'p5marketing') . '</p>';
}

function p5m_field_header_cta_url_cb() {
  $opts = get_option('p5m_header_settings', []);
  $val = esc_attr($opts['cta_url'] ?? '');
  echo '<input id="p5m_header_cta_url" name="p5m_header_settings[cta_url]" type="url" value="'. $val .'" class="regular-text" placeholder="/contact" />';
  echo '<p class="description">' . __('CTA button URL. Can be relative (/contact) or absolute (https://example.com).', 'p5marketing') . '</p>';
}

function p5m_field_header_cta_padding_cb() {
  $opts = get_option('p5m_header_settings', []);
  $val = esc_attr($opts['cta_padding'] ?? '');
  echo '<input id="p5m_header_cta_padding" name="p5m_header_settings[cta_padding]" type="text" value="'. $val .'" class="regular-text" placeholder="1rem 1.5rem" />';
  echo '<p class="description">' . __('Button padding in CSS format (e.g., "1rem 1.5rem" or "12px 24px"). Leave empty to use the default.', 'p5marketing') . '</p>';
}

function p5m_field_header_cta_border_cb() {
  $opts = get_option('p5m_header_settings', []);
  $val = esc_attr($opts['cta_border'] ?? '');
  echo '<input id="p5m_header_cta_border" name="p5m_header_settings[cta_border]" type="text" value="'. $val .'" class="regular-text" placeholder="1px solid #4f46e5" />';
  echo '<p class="description">' . __('Button border in CSS format (e.g., "1px solid #000" or "2px dashed red"). Leave empty for no border.', 'p5marketing') . '</p>';
}

function p5m_field_header_cta_border_radius_cb() {
  $opts = get_option('p5m_header_settings', []);
  $val = esc_attr($opts['cta_border_radius'] ?? '');
  echo '<input id="p5m_header_cta_border_radius" name="p5m_header_settings[cta_border_radius]" type="text" value="'. $val .'" class="regular-text" placeholder="0.375rem" />';
  echo '<p class="description">' . __('Border radius in CSS format (e.g., "8px", "0.5rem", "50%"). Leave empty to use the default.', 'p5marketing') . '</p>';
}

// Field: bottom border
function p5m_field_header_border_cb() {
  $opts = get_option('p5m_header_settings', []);
  $checked = !empty($opts['border_bottom']) ? 'checked' : '';
  echo '<label><input type="checkbox" name="p5m_header_settings[border_bottom]" value="1" ' . $checked . ' /> ' . esc_html__('Show bottom border', 'p5marketing') . '</label>';
}

// Field: background mode
function p5m_field_header_background_mode_cb() {
  $opts = get_option('p5m_header_settings', []);
  $val = $opts['background_mode'] ?? 'solid';
  echo '<select name="p5m_header_settings[background_mode]" class="regular-text">';
  echo '<option value="solid" ' . selected($val, 'solid', false) . '>' . esc_html__('Solid', 'p5marketing') . '</option>';
  echo '<option value="gradient" ' . selected($val, 'gradient', false) . '>' . esc_html__('Gradient', 'p5marketing') . '</option>';
  echo '</select>';
  echo '<p class="description">' . __('Choose between a solid color or a gradient for the header background.', 'p5marketing') . '</p>';
}

// Generic color field
function p5m_field_header_color_cb($args) {
  $key = $args['key'] ?? '';
  $placeholder = $args['placeholder'] ?? '#ffffff';
  $opts = get_option('p5m_header_settings', []);
  $val = esc_attr($opts[$key] ?? '');
  echo '<input type="text" class="p5m-color-field" name="p5m_header_settings[' . esc_attr($key) . ']" value="' . $val . '" placeholder="' . esc_attr($placeholder) . '" />';
}

// Gradient direction
function p5m_field_header_gradient_direction_cb() {
  $opts = get_option('p5m_header_settings', []);
  $val = $opts['gradient_direction'] ?? 'horizontal';
  echo '<select name="p5m_header_settings[gradient_direction]" class="regular-text">';
  echo '<option value="horizontal" ' . selected($val, 'horizontal', false) . '>' . esc_html__('Horizontal', 'p5marketing') . '</option>';
  echo '<option value="vertical" ' . selected($val, 'vertical', false) . '>' . esc_html__('Vertical', 'p5marketing') . '</option>';
  echo '</select>';
}

// Scroll threshold
function p5m_field_header_scroll_threshold_cb() {
  $opts = get_option('p5m_header_settings', []);
  $val = isset($opts['scroll_threshold']) ? intval($opts['scroll_threshold']) : 0;
  echo '<input type="number" min="0" step="10" name="p5m_header_settings[scroll_threshold]" value="' . esc_attr($val) . '" class="small-text" />';
  echo '<p class="description">' . __('Pixel height after which the header style changes. Use 0 to disable the change.', 'p5marketing') . '</p>';
}

// Generic opacity field (0–1)
function p5m_field_header_opacity_cb($args) {
  $key = $args['key'] ?? '';
  $opts = get_option('p5m_header_settings', []);
  $val = isset($opts[$key]) ? $opts[$key] : '';
  echo '<input type="number" min="0" max="1" step="0.05" name="p5m_header_settings[' . esc_attr($key) . ']" value="' . esc_attr($val) . '" class="small-text" placeholder="1" />';
}

/* Sanitization */
function p5m_header_settings_sanitize($input) {
  $out = [];
  
  // Logo
  if (isset($input['logo'])) {
    $out['logo'] = esc_url_raw($input['logo']);
  }
  if (isset($input['logo_id'])) {
    $out['logo_id'] = intval($input['logo_id']);
    // If logo_id is present but logo URL is empty, derive URL from the attachment
    if (empty($out['logo']) && $out['logo_id']) {
      $url = wp_get_attachment_url($out['logo_id']);
      if ($url) $out['logo'] = esc_url_raw($url);
    }
  }
  
  // Menu
  if (isset($input['menu'])) {
    $out['menu'] = sanitize_text_field($input['menu']);
  }
  
  // Position
  if (isset($input['position'])) {
    $val = sanitize_text_field($input['position']);
    // Allow: sticky, static, fixed (keep compatibility with 'scroll')
    if ($val === 'scroll') { $val = 'static'; }
    $out['position'] = in_array($val, ['sticky', 'static', 'fixed']) ? $val : 'sticky';
  }
  
  // Border bottom
  $out['border_bottom'] = !empty($input['border_bottom']) ? 1 : 0;

  // Background
  if (isset($input['background_mode'])) {
    $mode = sanitize_text_field($input['background_mode']);
    $out['background_mode'] = in_array($mode, ['solid','gradient']) ? $mode : 'solid';
  }
  // Color sanitization: accept HEX or RGBA
  $color_keys = ["bg_color","gradient_start","gradient_end","link_color","scrolled_bg_color","scrolled_link_color","scrolled_cta_bg","scrolled_cta_text"];
  foreach ($color_keys as $ck) {
    if (isset($input[$ck]) && $input[$ck] !== "") {
      $val = trim($input[$ck]);
      // Check if RGBA format
      if (preg_match("/^rgba?\s*\(/i", $val)) {
        $out[$ck] = sanitize_text_field($val);
      } else {
        $out[$ck] = sanitize_hex_color($val);
      }
    }
  }
  if (isset($input['gradient_direction'])) {
    $dir = sanitize_text_field($input['gradient_direction']);
    $out['gradient_direction'] = in_array($dir, ['horizontal','vertical']) ? $dir : 'horizontal';
  }
  // Logo width (append px if only digits entered)
  if (isset($input['logo_width']) && $input['logo_width'] !== '') {
    $lw = trim($input['logo_width']);
    if (preg_match('/^\d+$/', $lw)) {
      $lw .= 'px';
    }
    $out['logo_width'] = sanitize_text_field($lw);
  }
  
  // Header min height
  if (isset($input["min_height"])) {
    $out["min_height"] = absint($input["min_height"]);
  }
  
  if (isset($input['scroll_threshold'])) {
    $out['scroll_threshold'] = absint($input['scroll_threshold']);
  }

  // Opacities 0–1
  $opacity_keys = ['bg_opacity','gradient_start_opacity','gradient_end_opacity','scrolled_bg_opacity'];
  foreach ($opacity_keys as $ok) {
    if (isset($input[$ok]) && $input[$ok] !== '') {
      $val = floatval($input[$ok]);
      if ($val < 0) $val = 0; if ($val > 1) $val = 1;
      $out[$ok] = $val;
    }
  }
  
  // CTA
  $out['cta_enable'] = !empty($input['cta_enable']) ? 1 : 0;
  
  if (isset($input['cta_text'])) {
    $out['cta_text'] = sanitize_text_field($input['cta_text']);
  }
  if (isset($input['cta_url'])) {
    $out['cta_url'] = esc_url_raw($input['cta_url']);
  }
  
  // CTA Colors (bg, text, hover)
  $cta_color_keys = ['cta_bg_color', 'cta_text_color', 'cta_hover_bg_color', 'cta_hover_text_color'];
  foreach ($cta_color_keys as $ck) {
    if (isset($input[$ck]) && $input[$ck] !== "") {
      $val = trim($input[$ck]);
      if (preg_match("/^rgba?\s*\(/i", $val)) {
        $out[$ck] = sanitize_text_field($val);
      } else {
        $out[$ck] = sanitize_hex_color($val);
      }
    }
  }
  
  // CTA Styling (padding, border, radius)
  if (isset($input['cta_padding']) && $input['cta_padding'] !== '') {
    $out['cta_padding'] = sanitize_text_field($input['cta_padding']);
  }
  if (isset($input['cta_border']) && $input['cta_border'] !== '') {
    $out['cta_border'] = sanitize_text_field($input['cta_border']);
  }
  if (isset($input['cta_border_radius']) && $input['cta_border_radius'] !== '') {
    $out['cta_border_radius'] = sanitize_text_field($input['cta_border_radius']);
  }
  
  // Menu display mode
  if (isset($input['menu_display'])) {
    $val = sanitize_text_field($input['menu_display']);
    $out['menu_display'] = in_array($val, ['full', 'hamburger']) ? $val : 'full';
  }
  
  // Layout order
  if (isset($input['layout_order'])) {
    $val = sanitize_text_field($input['layout_order']);
    $allowed = ['logo-nav-cta', 'logo-cta-nav', 'nav-logo-cta', 'cta-logo-nav', 'nav-cta-logo', 'cta-nav-logo'];
    $out['layout_order'] = in_array($val, $allowed) ? $val : 'logo-nav-cta';
  }
  
  // Custom grids (allow HTML and shortcodes)
  $grid_keys = ['top_col1', 'top_col2', 'top_col3', 'bottom_col1', 'bottom_col2', 'bottom_col3'];
  foreach ($grid_keys as $gk) {
    if (isset($input[$gk])) {
      $out[$gk] = wp_kses_post($input[$gk]);
    }
  }
  
  return $out;
}

/* Settings Page HTML */
function p5m_header_settings_page_html() {
  if (!current_user_can('manage_options')) return;
  ?>
  <div class="wrap">
    <h1><?php esc_html_e('P5 Header Settings', 'p5marketing'); ?></h1>
    <p><?php esc_html_e('Configure the header logo, menu, and CTA for your site.', 'p5marketing'); ?></p>
    
    <?php settings_errors('p5m_header_settings_group'); ?>
    
    <form action="options.php" method="post">
      <?php
        settings_fields('p5m_header_settings_group');
        ?>
        <h2 class="nav-tab-wrapper" style="margin-top:16px;">
          <a href="#" class="nav-tab nav-tab-active" data-p5m-tab="general"><?php esc_html_e('General', 'p5marketing'); ?></a>
          <a href="#" class="nav-tab" data-p5m-tab="cta"><?php esc_html_e('CTA', 'p5marketing'); ?></a>
          <a href="#" class="nav-tab" data-p5m-tab="style"><?php esc_html_e('Styles', 'p5marketing'); ?></a>
          <a href="#" class="nav-tab" data-p5m-tab="grids"><?php esc_html_e('Grids', 'p5marketing'); ?></a>
        </h2>

        <div class="p5m-tabs">
          <!-- General -->
          <div class="p5m-tab-content" data-p5m-tab="general">
            <h2><?php esc_html_e('Header configuration', 'p5marketing'); ?></h2>
            <p><?php esc_html_e('Customize the logo, menu, and element order.', 'p5marketing'); ?></p>
            <table class="form-table" role="presentation">
              <tbody>
                <?php do_settings_fields('p5m-header-settings', 'p5m_header_main_section'); ?>
              </tbody>
            </table>
          </div>

          <!-- CTA -->
          <div class="p5m-tab-content" data-p5m-tab="cta" style="display:none;">
            <h2><?php esc_html_e('CTA button', 'p5marketing'); ?></h2>
            <p><?php esc_html_e('Configure the header Call-to-Action button.', 'p5marketing'); ?></p>
            <table class="form-table" role="presentation">
              <tbody>
                <?php do_settings_fields('p5m-header-settings', 'p5m_header_cta_section'); ?>
              </tbody>
            </table>
          </div>

          <!-- Styles and behavior -->
          <div class="p5m-tab-content" data-p5m-tab="style" style="display:none;">
            <h2><?php esc_html_e('Styles and behavior', 'p5marketing'); ?></h2>
            <p><?php esc_html_e('Control the bottom border, background, and behavior on scroll.', 'p5marketing'); ?></p>
            <table class="form-table" role="presentation">
              <tbody>
                <?php do_settings_fields('p5m-header-settings', 'p5m_header_style_section'); ?>
              </tbody>
            </table>
          </div>

          <!-- Custom grids -->
          <div class="p5m-tab-content" data-p5m-tab="grids" style="display:none;">
            <h2><?php esc_html_e('Custom grids', 'p5marketing'); ?></h2>
            <?php p5m_header_grids_section_cb(); ?>
          </div>
        </div>

        <?php submit_button(); ?>
      ?>
    </form>
  </div>
  
  <script>
  (function($){
    // Media uploader (reuses the logic from the other settings page)
    $(document).on('click', '.p5m-media-upload', function(e){
      e.preventDefault();
      var btn = $(this);
      var target = $(btn.data('target'));
      var targetId = $(btn.data('target-id'));
      
      var frame = wp.media({
        title: 'Select an image',
        library: { type: 'image' },
        button: { text: 'Use this image' },
        multiple: false
      });
      
      frame.on('select', function(){
        var attachment = frame.state().get('selection').first().toJSON();
        target.val(attachment.url);
        if (targetId.length) {
          targetId.val(attachment.id);
        }
        // Preview
        var preview = target.attr('id') + '_preview';
        $('#' + preview).attr('src', attachment.url).show();
      });
      
      frame.open();
    });
    
    // Remove media
    $(document).on('click', '.p5m-media-remove', function(e){
      e.preventDefault();
      var btn = $(this);
      var input = btn.siblings('input[type="text"]').first();
      var inputId = btn.siblings('input[type="hidden"]').first();
      var preview = btn.siblings('img').first();
      
      input.val('');
      inputId.val('');
      preview.hide();
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

function p5m_field_header_grid_cb($args) {
  $opts = get_option('p5m_header_settings', []);
  $key = $args['key'] ?? '';
  $val = $opts[$key] ?? '';
  
  echo '<textarea id="p5m_header_' . esc_attr($key) . '" name="p5m_header_settings[' . esc_attr($key) . ']" rows="4" class="large-text code">';
  echo esc_textarea($val);
  echo '</textarea>';
  echo '<p class="description">' . __('You can use HTML or shortcodes, or leave it empty. do_shortcode() is supported.', 'p5marketing') . '</p>';
}

// Callback to render the grids section as a table
function p5m_header_grids_section_cb() {
  $opts = get_option('p5m_header_settings', []);
  ?>
  <p><?php esc_html_e('Add custom content (shortcodes, banners, buttons) above and below the main header.', 'p5marketing'); ?></p>
  <p class="description" style="background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin-bottom: 20px;">
    <strong>💡 Tip:</strong> <?php esc_html_e('You can use shortcodes for dynamic content. Examples:', 'p5marketing'); ?> 
    <code>[current_year]</code>, <code>[site_name]</code>, <code>[nav_menu location="menu"]</code>
    <br>
    <a href="<?php echo esc_url(admin_url('themes.php?page=p5m-shortcodes-help')); ?>" style="margin-top: 5px; display: inline-block;"><?php esc_html_e('View the full list of available shortcodes →', 'p5marketing'); ?></a>
  </p>
  
  <h3 style="margin-top: 24px; margin-bottom: 12px;">📍 <?php esc_html_e('Top grid (before the header)', 'p5marketing'); ?></h3>
  <table class="p5m-header-grid-top" style="width:100%; border-collapse: collapse; margin: 20px 0;">
    <tr>
      <?php for ($col = 1; $col <= 3; $col++): 
        $key = "top_col{$col}";
        $val = isset($opts[$key]) ? $opts[$key] : '';
      ?>
      <td style="border: 1px solid #ddd; padding: 12px; width: 33.33%; vertical-align: top; background: #f9f9f9;">
        <label style="display: block; font-weight: 600; margin-bottom: 8px;">
          <?php printf(__('Column %d', 'p5marketing'), $col); ?>
        </label>
        <textarea 
          name="p5m_header_settings[<?php echo esc_attr($key); ?>]" 
          rows="6" 
          style="width: 100%; font-family: monospace; font-size: 12px; padding: 8px; border: 1px solid #ccc; border-radius: 3px;"
          spellcheck="false"
        ><?php echo esc_textarea($val); ?></textarea>
        <p class="description" style="margin-top: 6px; font-size: 11px; color: #666;">
          <?php esc_html_e('HTML and shortcodes allowed', 'p5marketing'); ?>
        </p>
      </td>
      <?php endfor; ?>
    </tr>
  </table>
  
  <h3 style="margin-top: 32px; margin-bottom: 12px;">📍 <?php esc_html_e('Bottom grid (after the header)', 'p5marketing'); ?></h3>
  <table class="p5m-header-grid-bottom" style="width:100%; border-collapse: collapse; margin: 20px 0;">
    <tr>
      <?php for ($col = 1; $col <= 3; $col++): 
        $key = "bottom_col{$col}";
        $val = isset($opts[$key]) ? $opts[$key] : '';
      ?>
      <td style="border: 1px solid #ddd; padding: 12px; width: 33.33%; vertical-align: top; background: #fff;">
        <label style="display: block; font-weight: 600; margin-bottom: 8px;">
          <?php printf(__('Column %d', 'p5marketing'), $col); ?>
        </label>
        <textarea 
          name="p5m_header_settings[<?php echo esc_attr($key); ?>]" 
          rows="6" 
          style="width: 100%; font-family: monospace; font-size: 12px; padding: 8px; border: 1px solid #ccc; border-radius: 3px;"
          spellcheck="false"
        ><?php echo esc_textarea($val); ?></textarea>
        <p class="description" style="margin-top: 6px; font-size: 11px; color: #666;">
          <?php esc_html_e('HTML and shortcodes allowed', 'p5marketing'); ?>
        </p>
      </td>
      <?php endfor; ?>
    </tr>
  </table>
  
  <div style="background: #e7f3ff; padding: 12px; border-left: 4px solid #2196F3; margin-top: 20px;">
    <strong>ℹ️ Structure:</strong>
    <ul style="margin: 8px 0 0 20px;">
      <li><strong>Top grid:</strong> Appears BEFORE the header (useful for announcements or promo banners)</li>
      <li><strong>Bottom grid:</strong> Appears AFTER the header (useful for secondary menus or breadcrumbs)</li>
      <li>Each grid has <strong>3 columns</strong> on desktop and collapses to 1 column on mobile</li>
      <li>Grids only render if they have content (0 height by default)</li>
    </ul>
  </div>
  <?php
}

/* Helper to read header settings easily */
function p5m_get_header_setting($key, $default = null) {
  $opts = get_option('p5m_header_settings', []);
  if (is_array($opts) && array_key_exists($key, $opts)) {
    return $opts[$key];
  }
  return $default;
}

// Field: logo width
function p5m_field_header_logo_width_cb() {
  $opts = get_option('p5m_header_settings', []);
  $val = isset($opts['logo_width']) ? esc_attr($opts['logo_width']) : '';
  echo '<input type="text" name="p5m_header_settings[logo_width]" value="' . $val . '" class="small-text" placeholder="auto" />';
  echo '<p class="description">' . __('Logo width (e.g., 180px, 12rem, 15%, auto). If you enter only numbers (e.g., 180) "px" will be added automatically. Leave empty for fixed height (h-10).', 'p5marketing') . '</p>';
}

// Field: minimum header height
function p5m_field_header_min_height_cb() {
  $opts = get_option('p5m_header_settings', []);
  $val = isset($opts['min_height']) ? intval($opts['min_height']) : 0;
  echo '<input type="number" min="0" step="1" name="p5m_header_settings[min_height]" value="' . esc_attr($val) . '" class="small-text" placeholder="0" />';
  echo '<span> px</span>';
  echo '<p class="description">' . __('Minimum header height in pixels. 0 = automatic height based on content.', 'p5marketing') . '</p>';
}
