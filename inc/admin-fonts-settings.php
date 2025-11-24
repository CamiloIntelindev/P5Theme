<?php
/**
 * P5 Custom Fonts Settings
 * Gestión de fuentes personalizadas con upload, familia, peso, estilo
 *
 * @package P5Marketing
 */

if (!defined('ABSPATH')) exit;

/* Add menu page under Appearance */
add_action('admin_menu', function() {
  add_theme_page(
    __('P5 Fuentes', 'p5marketing'),
    __('P5 Fuentes', 'p5marketing'),
    'manage_options',
    'p5m-fonts-settings',
    'p5m_fonts_settings_page_html'
  );
});

/* Enqueue admin scripts */
add_action('admin_enqueue_scripts', function ($hook) {
  if ($hook !== 'appearance_page_p5m-fonts-settings') return;
  wp_enqueue_media();
  wp_enqueue_style('p5m-fonts-admin', get_template_directory_uri() . '/inc/admin-fonts.css', [], '1.0');
}, 20);

/* Register settings */
add_action('admin_init', function() {
  register_setting('p5m_fonts_group', 'p5m_custom_fonts', 'p5m_fonts_sanitize');
});

/* Sanitization */
function p5m_fonts_sanitize($input) {
  if (!is_array($input)) return [];
  $out = [];
  
  foreach ($input as $key => $font) {
    if (!is_array($font)) continue;
    
    $sanitized = [];
    $sanitized['name'] = isset($font['name']) ? sanitize_text_field($font['name']) : '';
    $sanitized['family'] = isset($font['family']) ? sanitize_text_field($font['family']) : '';
    $sanitized['weight'] = isset($font['weight']) ? sanitize_text_field($font['weight']) : '400';
    $sanitized['style'] = isset($font['style']) ? sanitize_text_field($font['style']) : 'normal';
    $sanitized['woff2'] = isset($font['woff2']) ? esc_url_raw($font['woff2']) : '';
    $sanitized['woff'] = isset($font['woff']) ? esc_url_raw($font['woff']) : '';
    $sanitized['ttf'] = isset($font['ttf']) ? esc_url_raw($font['ttf']) : '';
    $sanitized['otf'] = isset($font['otf']) ? esc_url_raw($font['otf']) : '';
    
    // Solo guardar si tiene al menos nombre y un archivo
    if (!empty($sanitized['name']) && (!empty($sanitized['woff2']) || !empty($sanitized['woff']) || !empty($sanitized['ttf']) || !empty($sanitized['otf']))) {
      $out[$key] = $sanitized;
    }
  }
  
  return $out;
}

/* Settings Page HTML */
function p5m_fonts_settings_page_html() {
  if (!current_user_can('manage_options')) return;
  
  $fonts = get_option('p5m_custom_fonts', []);
  ?>
  <div class="wrap">
    <h1><?php esc_html_e('P5 Fuentes Personalizadas', 'p5marketing'); ?></h1>
    <p><?php esc_html_e('Sube archivos de fuentes (.woff2, .woff) y configura familia, peso y estilo. Las fuentes estarán disponibles en Gutenberg.', 'p5marketing'); ?></p>
    
    <?php settings_errors('p5m_fonts_group'); ?>
    
    <form action="options.php" method="post" id="p5m-fonts-form">
      <?php settings_fields('p5m_fonts_group'); ?>
      
      <div id="p5m-fonts-list">
        <?php
        if (!empty($fonts)) {
          foreach ($fonts as $index => $font) {
            p5m_render_font_row($index, $font);
          }
        } else {
          p5m_render_font_row(0, []);
        }
        ?>
      </div>
      
      <p>
        <button type="button" class="button" id="p5m-add-font"><?php esc_html_e('+ Añadir fuente', 'p5marketing'); ?></button>
      </p>
      
      <?php submit_button(__('Guardar fuentes', 'p5marketing')); ?>
    </form>
  </div>
  
  <script>
  (function($){
    var fontIndex = <?php echo !empty($fonts) ? max(array_keys($fonts)) + 1 : 1; ?>;
    
    // Add new font row
    $('#p5m-add-font').on('click', function(){
      var template = `<?php echo addslashes(p5m_get_font_row_template('INDEX_PLACEHOLDER')); ?>`;
      var html = template.replace(/INDEX_PLACEHOLDER/g, fontIndex);
      $('#p5m-fonts-list').append(html);
      fontIndex++;
    });
    
    // Remove font row
    $(document).on('click', '.p5m-remove-font', function(){
      if (confirm('¿Eliminar esta fuente?')) {
        $(this).closest('.p5m-font-row').remove();
      }
    });
    
    // Media uploader for WOFF2
    $(document).on('click', '.p5m-upload-woff2', function(e){
      e.preventDefault();
      var btn = $(this);
      var row = btn.closest('.p5m-font-row');
      var input = row.find('.p5m-woff2-url');
      
      var frame = wp.media({
        title: 'Seleccionar archivo WOFF2',
        library: { type: ['font/woff2', 'application/font-woff2', 'application/octet-stream'] },
        button: { text: 'Usar este archivo' },
        multiple: false
      });
      
      frame.on('select', function(){
        var attachment = frame.state().get('selection').first().toJSON();
        input.val(attachment.url);
      });
      
      frame.open();
    });
    
    // Media uploader for WOFF
    $(document).on('click', '.p5m-upload-woff', function(e){
      e.preventDefault();
      var btn = $(this);
      var row = btn.closest('.p5m-font-row');
      var input = row.find('.p5m-woff-url');
      
      var frame = wp.media({
        title: 'Seleccionar archivo WOFF',
        library: { type: ['font/woff', 'application/font-woff', 'application/octet-stream'] },
        button: { text: 'Usar este archivo' },
        multiple: false
      });
      
      frame.on('select', function(){
        var attachment = frame.state().get('selection').first().toJSON();
        input.val(attachment.url);
      });
      
      frame.open();
    });
    
    // Media uploader for TTF
    $(document).on('click', '.p5m-upload-ttf', function(e){
      e.preventDefault();
      var btn = $(this);
      var row = btn.closest('.p5m-font-row');
      var input = row.find('.p5m-ttf-url');
      
      var frame = wp.media({
        title: 'Seleccionar archivo TTF',
        library: { type: ['font/ttf', 'application/x-font-ttf', 'application/octet-stream'] },
        button: { text: 'Usar este archivo' },
        multiple: false
      });
      
      frame.on('select', function(){
        var attachment = frame.state().get('selection').first().toJSON();
        input.val(attachment.url);
      });
      
      frame.open();
    });
    
    // Media uploader for OTF
    $(document).on('click', '.p5m-upload-otf', function(e){
      e.preventDefault();
      var btn = $(this);
      var row = btn.closest('.p5m-font-row');
      var input = row.find('.p5m-otf-url');
      
      var frame = wp.media({
        title: 'Seleccionar archivo OTF',
        library: { type: ['font/otf', 'application/vnd.ms-opentype', 'application/octet-stream'] },
        button: { text: 'Usar este archivo' },
        multiple: false
      });
      
      frame.on('select', function(){
        var attachment = frame.state().get('selection').first().toJSON();
        input.val(attachment.url);
      });
      
      frame.open();
    });
    
    // Preview update
    $(document).on('input change', '.p5m-font-family, .p5m-font-weight, .p5m-font-style, .p5m-woff2-url, .p5m-woff-url, .p5m-ttf-url, .p5m-otf-url', function(){
      var row = $(this).closest('.p5m-font-row');
      var preview = row.find('.p5m-font-preview');
      var family = row.find('.p5m-font-family').val() || 'sans-serif';
      var weight = row.find('.p5m-font-weight').val() || '400';
      var style = row.find('.p5m-font-style').val() || 'normal';
      var woff2 = row.find('.p5m-woff2-url').val();
      var woff = row.find('.p5m-woff-url').val();
      var ttf = row.find('.p5m-ttf-url').val();
      var otf = row.find('.p5m-otf-url').val();
      
      // Generate @font-face if file exists
      if (woff2 || woff || ttf || otf) {
        var fontFaceId = 'p5m-preview-' + row.data('index');
        $('#' + fontFaceId).remove();
        
        var src = [];
        if (woff2) src.push("url('" + woff2 + "') format('woff2')");
        if (woff) src.push("url('" + woff + "') format('woff')");
        if (ttf) src.push("url('" + ttf + "') format('truetype')");
        if (otf) src.push("url('" + otf + "') format('opentype')");
        
        var css = '@font-face { font-family: "' + family + '"; src: ' + src.join(', ') + '; font-weight: ' + weight + '; font-style: ' + style + '; font-display: swap; }';
        $('<style id="' + fontFaceId + '">' + css + '</style>').appendTo('head');
      }
      
      preview.css({
        'font-family': '"' + family + '", sans-serif',
        'font-weight': weight,
        'font-style': style
      });
    });
  })(jQuery);
  </script>
  
  <style>
  .p5m-font-row {
    background: #fff;
    border: 1px solid #ddd;
    padding: 20px;
    margin-bottom: 20px;
    border-radius: 4px;
    position: relative;
  }
  .p5m-font-row-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 16px;
    padding-bottom: 12px;
    border-bottom: 1px solid #f0f0f0;
  }
  .p5m-font-row-header h3 {
    margin: 0;
    font-size: 14px;
    font-weight: 600;
  }
  .p5m-font-fields {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
    margin-bottom: 16px;
  }
  .p5m-font-field {
    display: flex;
    flex-direction: column;
  }
  .p5m-font-field label {
    font-weight: 600;
    margin-bottom: 4px;
    font-size: 13px;
  }
  .p5m-font-field input,
  .p5m-font-field select {
    padding: 6px 8px;
  }
  .p5m-font-files {
    margin-bottom: 16px;
  }
  .p5m-font-file-row {
    display: flex;
    gap: 8px;
    align-items: center;
    margin-bottom: 8px;
  }
  .p5m-font-file-row input {
    flex: 1;
  }
  .p5m-font-preview {
    background: #f9f9f9;
    border: 1px solid #e0e0e0;
    padding: 20px;
    text-align: center;
    font-size: 24px;
    border-radius: 4px;
  }
  .p5m-remove-font {
    color: #d63638;
  }
  .p5m-remove-font:hover {
    color: #a00;
  }
  </style>
  <?php
}

/* Render a single font row */
function p5m_render_font_row($index, $font = []) {
  $name = isset($font['name']) ? esc_attr($font['name']) : '';
  $family = isset($font['family']) ? esc_attr($font['family']) : '';
  $weight = isset($font['weight']) ? esc_attr($font['weight']) : '400';
  $style = isset($font['style']) ? esc_attr($font['style']) : 'normal';
  $woff2 = isset($font['woff2']) ? esc_url($font['woff2']) : '';
  $woff = isset($font['woff']) ? esc_url($font['woff']) : '';
  
  $display_name = !empty($name) ? $name : __('Nueva fuente', 'p5marketing');
  ?>
  <div class="p5m-font-row" data-index="<?php echo esc_attr($index); ?>">
    <div class="p5m-font-row-header">
      <h3><?php echo esc_html($display_name); ?></h3>
      <button type="button" class="button-link p5m-remove-font"><?php esc_html_e('Eliminar', 'p5marketing'); ?></button>
    </div>
    
    <div class="p5m-font-fields">
      <div class="p5m-font-field">
        <label><?php esc_html_e('Nombre identificador', 'p5marketing'); ?></label>
        <input type="text" name="p5m_custom_fonts[<?php echo $index; ?>][name]" value="<?php echo $name; ?>" placeholder="Ej: Inter Bold" class="regular-text" />
      </div>
      
      <div class="p5m-font-field">
        <label><?php esc_html_e('Familia (font-family)', 'p5marketing'); ?></label>
        <input type="text" name="p5m_custom_fonts[<?php echo $index; ?>][family]" value="<?php echo $family; ?>" placeholder="Ej: Inter" class="regular-text p5m-font-family" />
      </div>
      
      <div class="p5m-font-field">
        <label><?php esc_html_e('Peso (font-weight)', 'p5marketing'); ?></label>
        <select name="p5m_custom_fonts[<?php echo $index; ?>][weight]" class="p5m-font-weight">
          <option value="100" <?php selected($weight, '100'); ?>>100 - Thin</option>
          <option value="200" <?php selected($weight, '200'); ?>>200 - Extra Light</option>
          <option value="300" <?php selected($weight, '300'); ?>>300 - Light</option>
          <option value="400" <?php selected($weight, '400'); ?>>400 - Regular</option>
          <option value="500" <?php selected($weight, '500'); ?>>500 - Medium</option>
          <option value="600" <?php selected($weight, '600'); ?>>600 - Semi Bold</option>
          <option value="700" <?php selected($weight, '700'); ?>>700 - Bold</option>
          <option value="800" <?php selected($weight, '800'); ?>>800 - Extra Bold</option>
          <option value="900" <?php selected($weight, '900'); ?>>900 - Black</option>
        </select>
      </div>
      
      <div class="p5m-font-field">
        <label><?php esc_html_e('Estilo (font-style)', 'p5marketing'); ?></label>
        <select name="p5m_custom_fonts[<?php echo $index; ?>][style]" class="p5m-font-style">
          <option value="normal" <?php selected($style, 'normal'); ?>>Normal</option>
          <option value="italic" <?php selected($style, 'italic'); ?>>Italic</option>
        </select>
      </div>
    </div>
    
    <div class="p5m-font-files">
      <label style="font-weight:600; margin-bottom:8px; display:block;"><?php esc_html_e('Archivos de fuente', 'p5marketing'); ?></label>
      
      <div class="p5m-font-file-row">
        <input type="text" name="p5m_custom_fonts[<?php echo $index; ?>][woff2]" value="<?php echo $woff2; ?>" placeholder="URL del archivo .woff2" class="regular-text p5m-woff2-url" />
        <button type="button" class="button p5m-upload-woff2"><?php esc_html_e('Subir WOFF2', 'p5marketing'); ?></button>
      </div>
      
      <div class="p5m-font-file-row">
        <input type="text" name="p5m_custom_fonts[<?php echo $index; ?>][woff]" value="<?php echo $woff; ?>" placeholder="URL del archivo .woff (fallback)" class="regular-text p5m-woff-url" />
        <button type="button" class="button p5m-upload-woff"><?php esc_html_e('Subir WOFF', 'p5marketing'); ?></button>
      </div>
      
      <div class="p5m-font-file-row">
        <input type="text" name="p5m_custom_fonts[<?php echo $index; ?>][ttf]" value="<?php echo isset($font['ttf']) ? esc_attr($font['ttf']) : ''; ?>" placeholder="URL del archivo .ttf" class="regular-text p5m-ttf-url" />
        <button type="button" class="button p5m-upload-ttf"><?php esc_html_e('Subir TTF', 'p5marketing'); ?></button>
      </div>
      
      <div class="p5m-font-file-row">
        <input type="text" name="p5m_custom_fonts[<?php echo $index; ?>][otf]" value="<?php echo isset($font['otf']) ? esc_attr($font['otf']) : ''; ?>" placeholder="URL del archivo .otf" class="regular-text p5m-otf-url" />
        <button type="button" class="button p5m-upload-otf"><?php esc_html_e('Subir OTF', 'p5marketing'); ?></button>
      </div>
      
      <p class="description"><?php esc_html_e('Sube al menos WOFF2 (formato moderno) y opcionalmente WOFF, TTF u OTF como fallback.', 'p5marketing'); ?></p>
    </div>
    
    <div class="p5m-font-preview">
      <?php esc_html_e('The quick brown fox jumps over the lazy dog', 'p5marketing'); ?>
    </div>
  </div>
  <?php
}

/* Get template for JS */
function p5m_get_font_row_template($index) {
  ob_start();
  p5m_render_font_row($index, []);
  return ob_get_clean();
}

/* Generate @font-face CSS in frontend */
add_action('wp_head', function() {
  $fonts = get_option('p5m_custom_fonts', []);
  if (empty($fonts)) return;
  
  echo "\n<style id=\"p5m-custom-fonts\">\n";
  foreach ($fonts as $font) {
    if (empty($font['family'])) continue;
    if (empty($font['woff2']) && empty($font['woff']) && empty($font['ttf']) && empty($font['otf'])) continue;
    
    $src = [];
    if (!empty($font['woff2'])) {
      $src[] = "url('" . esc_url($font['woff2']) . "') format('woff2')";
    }
    if (!empty($font['woff'])) {
      $src[] = "url('" . esc_url($font['woff']) . "') format('woff')";
    }
    if (!empty($font['ttf'])) {
      $src[] = "url('" . esc_url($font['ttf']) . "') format('truetype')";
    }
    if (!empty($font['otf'])) {
      $src[] = "url('" . esc_url($font['otf']) . "') format('opentype')";
    }
    
    echo "@font-face {\n";
    echo "  font-family: '" . esc_attr($font['family']) . "';\n";
    echo "  src: " . implode(",\n       ", $src) . ";\n";
    echo "  font-weight: " . esc_attr($font['weight']) . ";\n";
    echo "  font-style: " . esc_attr($font['style']) . ";\n";
    echo "  font-display: swap;\n";
    echo "}\n\n";
  }
  echo "</style>\n";
}, 5);

/* Register fonts in Gutenberg */
add_filter('block_editor_settings_all', function($settings) {
  $fonts = get_option('p5m_custom_fonts', []);
  if (empty($fonts)) return $settings;
  
  $font_families = [];
  foreach ($fonts as $font) {
    if (empty($font['family'])) continue;
    
    // Agrupar por familia
    if (!isset($font_families[$font['family']])) {
      $font_families[$font['family']] = [
        'name' => $font['family'],
        'slug' => sanitize_title($font['family']),
        'fontFamily' => '"' . $font['family'] . '", sans-serif'
      ];
    }
  }
  
  if (!isset($settings['__experimentalFeatures']['typography']['fontFamilies']['theme'])) {
    $settings['__experimentalFeatures']['typography']['fontFamilies']['theme'] = [];
  }
  
  foreach ($font_families as $family) {
    $settings['__experimentalFeatures']['typography']['fontFamilies']['theme'][] = $family;
  }
  
  return $settings;
}, 10);
