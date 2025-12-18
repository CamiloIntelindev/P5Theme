<?php
/**
 * P5 Footer Settings
 * Custom footer configuration with 9 HTML fields in a 3×3 grid
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
  
  // We do not use traditional sections; the grid renders manually to match the preview.

  // Color styles section (rendered normally)
  add_settings_section(
    'p5m_footer_style_section',
    __('Footer color styles', 'p5marketing'),
    function() {
      echo '<p>' . __('Set background, text, and link colors for the footer. Leave empty to use theme defaults.', 'p5marketing') . '</p>';
    },
    'p5m-footer-settings'
  );

  add_settings_field(
    'p5m_footer_background_color',
    __('Background color', 'p5marketing'),
    'p5m_field_footer_color_cb',
    'p5m-footer-settings',
    'p5m_footer_style_section',
    ['key' => 'background_color', 'placeholder' => '#f8fafc']
  );

  add_settings_field(
    'p5m_footer_text_color',
    __('Text color', 'p5marketing'),
    'p5m_field_footer_color_cb',
    'p5m-footer-settings',
    'p5m_footer_style_section',
    ['key' => 'text_color', 'placeholder' => '#374151']
  );

  add_settings_field(
    'p5m_footer_link_color',
    __('Link color', 'p5marketing'),
    'p5m_field_footer_color_cb',
    'p5m-footer-settings',
    'p5m_footer_style_section',
    ['key' => 'link_color', 'placeholder' => '#2563eb']
  );

  add_settings_field(
    'p5m_footer_link_hover_color',
    __('Link hover color', 'p5marketing'),
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
  echo '<p class="description">' . __('HTML allowed. You can include widgets, menus, text, links, and more.', 'p5marketing') . '</p>';
}

/* Sanitization */
function p5m_footer_settings_sanitize($input) {
  $out = [];
  
  // Sanitize each of the 9 fields allowing safe HTML
  for ($row = 1; $row <= 3; $row++) {
    for ($col = 1; $col <= 3; $col++) {
      $key = "col{$col}_row{$row}";
      if (isset($input[$key])) {
        // wp_kses_post allows WordPress-safe HTML
        $out[$key] = wp_kses_post($input[$key]);
      }
    }
  }

  // Footer colors
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
  wp_enqueue_script('jquery');
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
  // Defaults for preview when fields are empty
  $default_bg   = '#f8fafc';
  $default_text = '#374151';
  $default_link = '#2563eb';
  $default_hover= '#1e40af';
  $bg_color     = !empty($opts['background_color']) ? $opts['background_color'] : $default_bg;
  $text_color   = !empty($opts['text_color']) ? $opts['text_color'] : $default_text;
  $link_color   = !empty($opts['link_color']) ? $opts['link_color'] : $default_link;
  $hover_color  = !empty($opts['link_hover_color']) ? $opts['link_hover_color'] : $default_hover;
  ?>
  <div class="wrap">
    <h1><?php esc_html_e('P5 Footer Settings', 'p5marketing'); ?></h1>
    <p><?php esc_html_e('Configure footer styles and content in one place.', 'p5marketing'); ?></p>
    
    <?php settings_errors('p5m_footer_settings_group'); ?>
    
    <form action="options.php" method="post">
      <?php settings_fields('p5m_footer_settings_group'); ?>
      
      <h2 class="nav-tab-wrapper" style="margin-top:16px;">
        <a href="#" class="nav-tab nav-tab-active" data-p5m-tab="styles"><?php esc_html_e('Footer color styles', 'p5marketing'); ?></a>
        <a href="#" class="nav-tab" data-p5m-tab="grid"><?php esc_html_e('Grids and live preview', 'p5marketing'); ?></a>
      </h2>

      <div class="p5m-tabs">
        <!-- Color styles -->
        <div class="p5m-tab-content" data-p5m-tab="styles">
          <h2><?php esc_html_e('Footer color styles', 'p5marketing'); ?></h2>
          <p><?php esc_html_e('Set footer background, text, and link colors. Leave empty to use defaults.', 'p5marketing'); ?></p>
          <p class="description" style="margin-top:6px;">
            <?php esc_html_e('Note: the live preview is on the “Grids and live preview” tab.', 'p5marketing'); ?>
          </p>
          <table class="form-table" role="presentation">
            <tbody>
              <?php do_settings_fields('p5m-footer-settings', 'p5m_footer_style_section'); ?>
            </tbody>
          </table>
        </div>

        <!-- Grids + Preview -->
        <div class="p5m-tab-content" data-p5m-tab="grid" style="display:none;">
          <h2><?php esc_html_e('Footer content (3×3 grid)', 'p5marketing'); ?></h2>
          <p><?php esc_html_e('Each cell accepts HTML and shortcodes. Edit directly in the grid to see how blocks are organized.', 'p5marketing'); ?></p>
          <p class="description" style="background: #fff3cd; padding: 10px; border-left: 4px solid #ffc107; margin-bottom: 20px;">
            <strong>💡 Tip:</strong> <?php esc_html_e('You can use shortcodes for dynamic content.', 'p5marketing'); ?>
            <code>[current_year]</code>, <code>[site_name]</code>, <code>[nav_menu location="footer-menu"]</code>, <code>[contact_email]</code>
            <br>
            <a href="<?php echo esc_url(admin_url('themes.php?page=p5m-shortcodes-help')); ?>" style="margin-top: 5px; display: inline-block;"><?php esc_html_e('View the full list of available shortcodes →', 'p5marketing'); ?></a>
          </p>

          <style id="p5m-footer-preview-style">
            .p5m-footer-preview { background-color: <?php echo esc_attr($bg_color); ?>; color: <?php echo esc_attr($text_color); ?>; }
            .p5m-footer-preview a { color: <?php echo esc_attr($link_color); ?>; text-decoration: none; }
            .p5m-footer-preview a:hover { color: <?php echo esc_attr($hover_color); ?>; }
            .p5m-footer-preview .preview-inner { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; padding: 16px; border: 1px solid #e5e7eb; border-radius: 6px; }
            .p5m-footer-preview .preview-box { padding: 12px; background: rgba(255,255,255,0.5); border-radius: 4px; }
            .p5m-footer-preview h4 { margin: 0 0 8px; font-size: 13px; }
            .p5m-footer-preview p { margin: 0 0 6px; font-size: 12px; }
          </style>
          <h2 style="margin-top:0;"><?php esc_html_e('Style preview', 'p5marketing'); ?></h2>
          <p><?php esc_html_e('This preview reflects the colors configured on the Styles tab.', 'p5marketing'); ?></p>
          <div id="p5m-footer-preview" class="p5m-footer-preview" aria-live="polite">
            <div class="preview-inner">
              <div class="preview-box">
                <h4><?php esc_html_e('Column 1', 'p5marketing'); ?></h4>
                <p><?php esc_html_e('Sample footer text.', 'p5marketing'); ?></p>
                <p><a href="#" class="preview-link"><?php esc_html_e('Sample link', 'p5marketing'); ?></a></p>
              </div>
              <div class="preview-box">
                <h4><?php esc_html_e('Column 2', 'p5marketing'); ?></h4>
                <p><?php esc_html_e('You can place lists, menus, and more.', 'p5marketing'); ?></p>
                <p><a href="#" class="preview-link"><?php esc_html_e('Another link', 'p5marketing'); ?></a></p>
              </div>
              <div class="preview-box">
                <h4><?php esc_html_e('Column 3', 'p5marketing'); ?></h4>
                <p><?php esc_html_e('Contact information or social links.', 'p5marketing'); ?></p>
                <p><a href="#" class="preview-link"><?php esc_html_e('Follow us', 'p5marketing'); ?></a></p>
              </div>
            </div>
          </div>

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
                  <?php printf(__('Column %d - Row %d', 'p5marketing'), $col, $row); ?>
                </label>
                <textarea 
                  name="p5m_footer_settings[<?php echo esc_attr($key); ?>]" 
                  rows="8" 
                  style="width: 100%; font-family: monospace; font-size: 12px; padding: 8px; border: 1px solid #ccc; border-radius: 3px;"
                  spellcheck="false"
                ><?php echo esc_textarea($val); ?></textarea>
                <p class="description" style="margin-top: 6px; font-size: 11px; color: #666;">
                  <?php esc_html_e('HTML and shortcodes allowed', 'p5marketing'); ?>
                </p>
              </td>
              <?php endfor; ?>
            </tr>
            <?php endfor; ?>
          </table>

          <hr style="margin: 24px 0;" />
          <h2><?php esc_html_e('Structure preview', 'p5marketing'); ?></h2>
          <p><?php esc_html_e('Blocks render on the frontend in this order:', 'p5marketing'); ?></p>
          <table style="width:100%; border-collapse: collapse; margin-top: 12px;">
            <tr>
              <td style="border: 1px solid #ddd; padding: 12px; width: 33.33%; text-align: center; background: #f9f9f9;"><strong>Column 1 - Row 1</strong></td>
              <td style="border: 1px solid #ddd; padding: 12px; width: 33.33%; text-align: center; background: #f9f9f9;"><strong>Column 2 - Row 1</strong></td>
              <td style="border: 1px solid #ddd; padding: 12px; width: 33.33%; text-align: center; background: #f9f9f9;"><strong>Column 3 - Row 1</strong></td>
            </tr>
            <tr>
              <td style="border: 1px solid #ddd; padding: 12px; width: 33.33%; text-align: center;"><strong>Column 1 - Row 2</strong></td>
              <td style="border: 1px solid #ddd; padding: 12px; width: 33.33%; text-align: center;"><strong>Column 2 - Row 2</strong></td>
              <td style="border: 1px solid #ddd; padding: 12px; width: 33.33%; text-align: center;"><strong>Column 3 - Row 2</strong></td>
            </tr>
            <tr>
              <td style="border: 1px solid #ddd; padding: 12px; width: 33.33%; text-align: center; background: #f9f9f9;"><strong>Column 1 - Row 3</strong></td>
              <td style="border: 1px solid #ddd; padding: 12px; width: 33.33%; text-align: center; background: #f9f9f9;"><strong>Column 2 - Row 3</strong></td>
              <td style="border: 1px solid #ddd; padding: 12px; width: 33.33%; text-align: center; background: #f9f9f9;"><strong>Column 3 - Row 3</strong></td>
            </tr>
          </table>
        </div>
      </div>

      <?php submit_button(); ?>
    </form>
  </div>
  <?php
}

/* Tabs behavior */
add_action('admin_print_footer_scripts', function(){
  $screen = get_current_screen();
  if (!$screen || $screen->id !== 'appearance_page_p5m-footer-settings') return;
  $opts = get_option('p5m_footer_settings', []);
  $default_bg   = '#f8fafc';
  $default_text = '#374151';
  $default_link = '#2563eb';
  $default_hover= '#1e40af';
  $bg_color     = !empty($opts['background_color']) ? $opts['background_color'] : $default_bg;
  $text_color   = !empty($opts['text_color']) ? $opts['text_color'] : $default_text;
  $link_color   = !empty($opts['link_color']) ? $opts['link_color'] : $default_link;
  $hover_color  = !empty($opts['link_hover_color']) ? $opts['link_hover_color'] : $default_hover;
  ?>
  <script>
  (function($){
    $(function(){
      var $tabs = $('.nav-tab-wrapper .nav-tab');
      var $contents = $('.p5m-tab-content');

      function showTab(tab){
        $tabs.removeClass('nav-tab-active');
        $tabs.filter('[data-p5m-tab="' + tab + '"]').addClass('nav-tab-active');
        $contents.hide();
        $contents.filter('[data-p5m-tab="' + tab + '"]').show();
      }

      $tabs.on('click', function(e){
        e.preventDefault();
        showTab($(this).data('p5m-tab'));
      });

      // Initial state
      var initial = $tabs.filter('.nav-tab-active').data('p5m-tab') || 'styles';
      showTab(initial);

      // Live preview updates from color fields
      function readOrPlaceholder($el, placeholder) {
        var v = ($el.val() || '').trim();
        return v !== '' ? v : placeholder;
      }
      function updateFooterPreview(){
        var bg   = readOrPlaceholder($('input[name="p5m_footer_settings[background_color]"]'), '<?php echo esc_js($bg_color); ?>');
        var text = readOrPlaceholder($('input[name="p5m_footer_settings[text_color]"]'), '<?php echo esc_js($text_color); ?>');
        var link = readOrPlaceholder($('input[name="p5m_footer_settings[link_color]"]'), '<?php echo esc_js($link_color); ?>');
        var hover= readOrPlaceholder($('input[name="p5m_footer_settings[link_hover_color]"]'), '<?php echo esc_js($hover_color); ?>');

        $('#p5m-footer-preview').css({ backgroundColor: bg, color: text });
        $('#p5m-footer-preview a').css({ color: link });
        $('#p5m-footer-preview-style').text(
          '.p5m-footer-preview { background-color: ' + bg + '; color: ' + text + '; }' +
          ' .p5m-footer-preview a { color: ' + link + '; text-decoration: none; }' +
          ' .p5m-footer-preview a:hover { color: ' + hover + '; }'
        );
      }
      $(document).on('input change', 'input.p5m-color-field', updateFooterPreview);
      updateFooterPreview();
    });
  })(jQuery);
  </script>
  <?php
});

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
  echo '<p class="description">' . __('Use HEX format (for example, #111827). Leave empty to use the default style.', 'p5marketing') . '</p>';
}
