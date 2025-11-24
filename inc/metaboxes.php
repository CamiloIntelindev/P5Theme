<?php
/**
 * Metaboxes
 * Layout, Title Options, Featured Image Options para posts, páginas y CPTs
 *
 * @package P5Marketing
 */

if (!defined('ABSPATH')) exit;

// Define qué post types soportan los metaboxes (pages, posts y todos los CPTs públicos)
$p5m_layout_post_types = get_post_types(['public' => true], 'names');
$p5m_layout_post_types = array_values(array_diff($p5m_layout_post_types, ['attachment']));
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
  // Enhance preview mode detection
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
    });
  })();</script>';
});

// Helper para saber si mostrar imagen destacada
function p5m_show_featured() {
  $meta = get_post_meta(get_the_ID(), 'p5m_show_featured', true);
  if ($meta === '1') return true;
  if ($meta === '0') return false;
  // Fallback al ajuste global
  $global = function_exists('p5m_get_setting') ? intval(p5m_get_setting('featured_default', 0)) : 0;
  return $global === 1;
}
