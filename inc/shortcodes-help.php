<?php
/**
 * Shortcodes Help Page
 * Documentación de shortcodes disponibles en el tema
 *
 * @package P5Marketing
 */

if (!defined('ABSPATH')) exit;

// Add help page under Appearance
add_action('admin_menu', function() {
  add_theme_page(
    __('Shortcodes Disponibles', 'p5marketing'),
    __('Shortcodes', 'p5marketing'),
    'manage_options',
    'p5m-shortcodes-help',
    'p5m_shortcodes_help_page_html'
  );
});

function p5m_shortcodes_help_page_html() {
  if (!current_user_can('manage_options')) return;
  ?>
  <div class="wrap">
    <h1><?php esc_html_e('Shortcodes Disponibles', 'p5marketing'); ?></h1>
    <p><?php esc_html_e('Usa estos shortcodes en el footer, widgets, páginas y posts para insertar contenido dinámico.', 'p5marketing'); ?></p>
    
    <div class="card" style="max-width: none; margin-top: 20px;">
      <h2 class="title"><?php esc_html_e('Shortcodes Básicos', 'p5marketing'); ?></h2>
      
      <table class="widefat striped" style="margin-top: 15px;">
        <thead>
          <tr>
            <th style="width: 30%;"><?php esc_html_e('Shortcode', 'p5marketing'); ?></th>
            <th style="width: 40%;"><?php esc_html_e('Descripción', 'p5marketing'); ?></th>
            <th style="width: 30%;"><?php esc_html_e('Ejemplo de Uso', 'p5marketing'); ?></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><code>[current_year]</code></td>
            <td>Muestra el año actual (útil para copyright)</td>
            <td><code>© [current_year] Mi Empresa</code></td>
          </tr>
          <tr>
            <td><code>[site_name]</code></td>
            <td>Nombre del sitio (configurado en Ajustes Generales)</td>
            <td><code>Bienvenido a [site_name]</code></td>
          </tr>
          <tr>
            <td><code>[contact_email]</code></td>
            <td>Email de contacto (del tema o admin) con enlace</td>
            <td><code>Escríbenos: [contact_email]</code></td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <div class="card" style="max-width: none; margin-top: 20px;">
      <h2 class="title"><?php esc_html_e('Shortcodes con Parámetros', 'p5marketing'); ?></h2>
      
      <table class="widefat striped" style="margin-top: 15px;">
        <thead>
          <tr>
            <th style="width: 30%;"><?php esc_html_e('Shortcode', 'p5marketing'); ?></th>
            <th style="width: 40%;"><?php esc_html_e('Parámetros', 'p5marketing'); ?></th>
            <th style="width: 30%;"><?php esc_html_e('Ejemplo', 'p5marketing'); ?></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><code>[site_logo]</code></td>
            <td>
              <strong>width</strong>: ancho del logo (default: auto)<br>
              <strong>class</strong>: clase CSS (default: site-logo)
            </td>
            <td><code>[site_logo width="150px" class="footer-logo"]</code></td>
          </tr>
          <tr>
            <td><code>[nav_menu]</code></td>
            <td>
              <strong>location</strong>: ubicación del menú (requerido)<br>
              <strong>class</strong>: clase CSS del contenedor
            </td>
            <td><code>[nav_menu location="footer-menu" class="menu-footer"]</code></td>
          </tr>
          <tr>
            <td><code>[cta_button]</code></td>
            <td>
              <strong>text</strong>: texto del botón<br>
              <strong>url</strong>: enlace de destino<br>
              <strong>class</strong>: clase CSS<br>
              <strong>target</strong>: _self o _blank
            </td>
            <td><code>[cta_button text="Contactar" url="/contact" class="btn-primary"]</code></td>
          </tr>
          <tr>
            <td><code>[social_links]</code></td>
            <td>
              <strong>class</strong>: clase CSS del contenedor
            </td>
            <td><code>[social_links class="social-icons flex gap-3"]</code></td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <div class="card" style="max-width: none; margin-top: 20px;">
      <h2 class="title"><?php esc_html_e('Shortcodes Especiales', 'p5marketing'); ?></h2>
      
      <table class="widefat striped" style="margin-top: 15px;">
        <thead>
          <tr>
            <th style="width: 30%;"><?php esc_html_e('Shortcode', 'p5marketing'); ?></th>
            <th style="width: 70%;"><?php esc_html_e('Descripción', 'p5marketing'); ?></th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><code>[language_switcher]</code></td>
            <td>Selector de idiomas (requiere WPML o Polylang instalado y configurado)</td>
          </tr>
        </tbody>
      </table>
    </div>
    
    <div class="card" style="max-width: none; margin-top: 20px; background: #f0f6fc; border-left: 4px solid #0073aa;">
      <h2 class="title"><?php esc_html_e('💡 Cómo Crear tus Propios Shortcodes', 'p5marketing'); ?></h2>
      <p><?php esc_html_e('Puedes agregar shortcodes personalizados editando el archivo:', 'p5marketing'); ?></p>
      <p><code style="background: white; padding: 8px; display: block; margin: 10px 0;">/wp-content/themes/p5marketing/inc/shortcodes.php</code></p>
      
      <p><strong><?php esc_html_e('Ejemplo de shortcode personalizado:', 'p5marketing'); ?></strong></p>
      <pre style="background: white; padding: 15px; overflow-x: auto; border: 1px solid #ddd; margin-top: 10px;">
add_shortcode('mi_shortcode', function($atts) {
  $atts = shortcode_atts([
    'texto' => 'Valor por defecto',
  ], $atts);
  
  return '&lt;div class="mi-contenedor"&gt;' . esc_html($atts['texto']) . '&lt;/div&gt;';
});</pre>
      
      <p><strong><?php esc_html_e('Luego úsalo así:', 'p5marketing'); ?></strong></p>
      <p><code>[mi_shortcode texto="Mi contenido personalizado"]</code></p>
    </div>
    
    <div style="margin-top: 30px; padding: 15px; background: #fff3cd; border-left: 4px solid #ffc107;">
      <h3 style="margin-top: 0;">⚠️ <?php esc_html_e('Importante', 'p5marketing'); ?></h3>
      <ul style="margin: 0;">
        <li><?php esc_html_e('Los shortcodes se procesan automáticamente en el footer, widgets y contenido de páginas/posts.', 'p5marketing'); ?></li>
        <li><?php esc_html_e('Si un shortcode no funciona, verifica que esté escrito correctamente (sin espacios extra).', 'p5marketing'); ?></li>
        <li><?php esc_html_e('Los parámetros son opcionales; cada shortcode tiene valores por defecto.', 'p5marketing'); ?></li>
      </ul>
    </div>
    
    <p style="margin-top: 30px;">
      <a href="<?php echo esc_url(admin_url('themes.php?page=p5m-footer-settings')); ?>" class="button button-primary">
        ← <?php esc_html_e('Volver a Ajustes del Footer', 'p5marketing'); ?>
      </a>
    </p>
  </div>
  <?php
}
