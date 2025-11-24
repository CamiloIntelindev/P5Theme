<?php
/**
 * P5 Footer Settings
 * Configuración personalizada del footer con 9 campos HTML en grilla 3×3
 *
 * @package P5Marketing
 */

if (!defined('ABSPATH')) exit;

/* Add menu page under Appearance */
add_action('admin_menu', function() {
  add_theme_page(
    __('P5 Footer Settings', 'p5marketing'),
    __('P5 Footer', 'p5marketing'),
    'manage_options',
    'p5m-footer-settings',
    'p5m_footer_settings_page_html'
  );
});

/* Register settings */
add_action('admin_init', function() {
  register_setting('p5m_footer_settings_group', 'p5m_footer_settings', 'p5m_footer_settings_sanitize');
  
  // No usamos secciones tradicionales; renderizamos la grilla manualmente en la página
  // para que coincida exactamente con la vista previa

  // Sección de estilos de color (renderizada normalmente)
  add_settings_section(
    'p5m_footer_style_section',
    __('Estilos de color del Footer', 'p5marketing'),
    function() {
      echo '<p>' . __('Define los colores del fondo, texto y enlaces del footer. Deja en blanco para usar los valores por defecto del tema.', 'p5marketing') . '</p>';
    },
    'p5m-footer-settings'
  );

  add_settings_field(
    'p5m_footer_background_color',
    __('Color de fondo', 'p5marketing'),
    'p5m_field_footer_color_cb',
    'p5m-footer-settings',
    'p5m_footer_style_section',
    ['key' => 'background_color', 'placeholder' => '#f8fafc']
  );

  add_settings_field(
    'p5m_footer_text_color',
    __('Color de texto', 'p5marketing'),
    'p5m_field_footer_color_cb',
    'p5m-footer-settings',
    'p5m_footer_style_section',
    ['key' => 'text_color', 'placeholder' => '#374151']
  );

  add_settings_field(
    'p5m_footer_link_color',
    __('Color de enlaces', 'p5marketing'),
    'p5m_field_footer_color_cb',
    'p5m-footer-settings',
    'p5m_footer_style_section',
    ['key' => 'link_color', 'placeholder' => '#2563eb']
  );

  add_settings_field(
    'p5m_footer_link_hover_color',
    __('Color de enlaces (hover)', 'p5marketing'),
    'p5m_field_footer_color_cb',
    'p5m-footer-settings',
    'p5m_footer_style_section',
    ['key' => 'link_hover_color', 'placeholder' => '#1e40af']
  );
});

/* Field Callback for each block */
function p5m_field_footer_block_cb($args) {
  $opts = get_option('p5m_footer_settings', []);
  $key = $args['key'];
  $val = isset($opts[$key]) ? $opts[$key] : '';
  
  echo '<textarea id="p5m_footer_' . esc_attr($key) . '" name="p5m_footer_settings[' . esc_attr($key) . ']" rows="6" cols="50" class="large-text code" spellcheck="false">';
  echo esc_textarea($val);
  echo '</textarea>';
  echo '<p class="description">' . __('HTML permitido. Puedes incluir widgets, menús, texto, enlaces, etc.', 'p5marketing') . '</p>';
}

/* Sanitization */
function p5m_footer_settings_sanitize($input) {
  $out = [];
  
  // Sanitizar cada uno de los 9 campos permitiendo HTML básico
  for ($row = 1; $row <= 3; $row++) {
    for ($col = 1; $col <= 3; $col++) {
      $key = "col{$col}_row{$row}";
      if (isset($input[$key])) {
        // wp_kses_post permite HTML seguro de WordPress
        $out[$key] = wp_kses_post($input[$key]);
      }
    }
  }

  // Colores del footer
  $color_keys = ['background_color', 'text_color', 'link_color', 'link_hover_color'];
  foreach ($color_keys as $ck) {
    if (isset($input[$ck]) && $input[$ck] !== '') {
      $out[$ck] = sanitize_hex_color($input[$ck]);
    }
  }
  
  return $out;
}

/* Admin assets: color picker */
add_action('admin_enqueue_scripts', function($hook) {
  // Page hook for Appearance > P5 Footer
  if ($hook !== 'appearance_page_p5m-footer-settings') return;
  wp_enqueue_style('wp-color-picker');
  wp_enqueue_script('wp-color-picker');
  // Init color pickers
  $init_js = "jQuery(function($){ $('.p5m-color-field').wpColorPicker(); });";
  wp_add_inline_script('wp-color-picker', $init_js, 'after');
});

/* Settings Page HTML */
function p5m_footer_settings_page_html() {
  if (!current_user_can('manage_options')) return;
  $opts = get_option('p5m_footer_settings', []);
  ?>
  <div class="wrap">
    <h1><?php esc_html_e('P5 Footer Settings', 'p5marketing'); ?></h1>
    <p><?php esc_html_e('Configura los 9 bloques del footer en una grilla de 3 columnas × 3 filas.', 'p5marketing'); ?></p>
    
    <?php settings_errors('p5m_footer_settings_group'); ?>
    
    <form action="options.php" method="post">
      <?php settings_fields('p5m_footer_settings_group'); ?>
      
      <h2><?php esc_html_e('Contenido del Footer (Grilla 3×3)', 'p5marketing'); ?></h2>
      <p><?php esc_html_e('Cada celda acepta HTML y shortcodes. Edita directamente en la grilla para ver cómo se organizan los bloques.', 'p5marketing'); ?></p>
      <p class="description" style="background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin-bottom: 20px;">
        <strong>💡 Tip:</strong> Puedes usar shortcodes para contenido dinámico. Ejemplos: 
        <code>[current_year]</code>, <code>[site_name]</code>, <code>[nav_menu location="footer-menu"]</code>, <code>[contact_email]</code>
        <br>
        <a href="<?php echo esc_url(admin_url('themes.php?page=p5m-shortcodes-help')); ?>" style="margin-top: 5px; display: inline-block;">Ver lista completa de shortcodes disponibles →</a>
      </p>
      
      <table class="p5m-footer-grid" style="width:100%; border-collapse: collapse; margin: 20px 0;">
        <?php for ($row = 1; $row <= 3; $row++): ?>
        <tr>
          <?php for ($col = 1; $col <= 3; $col++): 
            $key = "col{$col}_row{$row}";
            $val = isset($opts[$key]) ? $opts[$key] : '';
            $bg = ($row % 2 === 1) ? '#f9f9f9' : '#fff';
          ?>
          <td style="border: 1px solid #ddd; padding: 12px; width: 33.33%; vertical-align: top; background: <?php echo $bg; ?>;">
            <label style="display: block; font-weight: 600; margin-bottom: 8px;">
              <?php printf(__('Columna %d - Fila %d', 'p5marketing'), $col, $row); ?>
            </label>
            <textarea 
              name="p5m_footer_settings[<?php echo esc_attr($key); ?>]" 
              rows="8" 
              style="width: 100%; font-family: monospace; font-size: 12px; padding: 8px; border: 1px solid #ccc; border-radius: 3px;"
              spellcheck="false"
            ><?php echo esc_textarea($val); ?></textarea>
            <p class="description" style="margin-top: 6px; font-size: 11px; color: #666;">
              <?php esc_html_e('HTML y shortcodes permitidos', 'p5marketing'); ?>
            </p>
          </td>
          <?php endfor; ?>
        </tr>
        <?php endfor; ?>
      </table>
      
      <hr style="margin: 32px 0;" />
      
      <?php do_settings_sections('p5m-footer-settings'); ?>
      
      <?php submit_button(); ?>
    </form>
    
    <hr style="margin: 24px 0;" />
    <h2><?php esc_html_e('Vista previa de la estructura', 'p5marketing'); ?></h2>
    <p><?php esc_html_e('Los bloques se organizan así en el frontend:', 'p5marketing'); ?></p>
    <table style="width:100%; border-collapse: collapse; margin-top: 12px;">
      <tr>
        <td style="border: 1px solid #ddd; padding: 12px; width: 33.33%; text-align: center; background: #f9f9f9;">
          <strong>Columna 1 - Fila 1</strong>
        </td>
        <td style="border: 1px solid #ddd; padding: 12px; width: 33.33%; text-align: center; background: #f9f9f9;">
          <strong>Columna 2 - Fila 1</strong>
        </td>
        <td style="border: 1px solid #ddd; padding: 12px; width: 33.33%; text-align: center; background: #f9f9f9;">
          <strong>Columna 3 - Fila 1</strong>
        </td>
      </tr>
      <tr>
        <td style="border: 1px solid #ddd; padding: 12px; width: 33.33%; text-align: center;">
          <strong>Columna 1 - Fila 2</strong>
        </td>
        <td style="border: 1px solid #ddd; padding: 12px; width: 33.33%; text-align: center;">
          <strong>Columna 2 - Fila 2</strong>
        </td>
        <td style="border: 1px solid #ddd; padding: 12px; width: 33.33%; text-align: center;">
          <strong>Columna 3 - Fila 2</strong>
        </td>
      </tr>
      <tr>
        <td style="border: 1px solid #ddd; padding: 12px; width: 33.33%; text-align: center; background: #f9f9f9;">
          <strong>Columna 1 - Fila 3</strong>
        </td>
        <td style="border: 1px solid #ddd; padding: 12px; width: 33.33%; text-align: center; background: #f9f9f9;">
          <strong>Columna 2 - Fila 3</strong>
        </td>
        <td style="border: 1px solid #ddd; padding: 12px; width: 33.33%; text-align: center; background: #f9f9f9;">
          <strong>Columna 3 - Fila 3</strong>
        </td>
      </tr>
    </table>
  </div>
  <?php
}

/* Helper to read footer settings easily */
function p5m_get_footer_setting($key, $default = '') {
  $opts = get_option('p5m_footer_settings', []);
  if (is_array($opts) && array_key_exists($key, $opts)) {
    return $opts[$key];
  }
  return $default;
}

/* Color picker field callback */
function p5m_field_footer_color_cb($args) {
  $opts = get_option('p5m_footer_settings', []);
  $key = $args['key'];
  $val = isset($opts[$key]) ? $opts[$key] : '';
  $placeholder = isset($args['placeholder']) ? $args['placeholder'] : '#000000';
  echo '<input type="text" class="p5m-color-field" name="p5m_footer_settings[' . esc_attr($key) . ']" value="' . esc_attr($val) . '" placeholder="' . esc_attr($placeholder) . '" />';
  echo '<p class="description">' . __('Usa formato HEX (por ejemplo, #111827). Déjalo vacío para usar el estilo por defecto.', 'p5marketing') . '</p>';
}
