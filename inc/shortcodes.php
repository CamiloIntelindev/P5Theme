<?php
/**
 * Custom Shortcodes
 * Shortcodes personalizados del tema para usar en footer, widgets y contenido
 *
 * @package P5Marketing
 */

if (!defined('ABSPATH')) exit;

/**
 * Ejemplo: Año actual
 * Uso: [current_year]
 */
add_shortcode('current_year', function() {
  return date('Y');
});

/**
 * Ejemplo: Nombre del sitio
 * Uso: [site_name]
 */
add_shortcode('site_name', function() {
  return get_bloginfo('name');
});

/**
 * Ejemplo: Email de contacto del tema
 * Uso: [contact_email]
 */
add_shortcode('contact_email', function() {
  $email = p5m_get_setting('contact_email', get_bloginfo('admin_email'));
  return '<a href="mailto:' . esc_attr($email) . '">' . esc_html($email) . '</a>';
});

/**
 * Ejemplo: Logo del sitio
 * Uso: [site_logo]
 * Parámetros opcionales: width="120px" class="mi-clase"
 */
add_shortcode('site_logo', function($atts) {
  $atts = shortcode_atts([
    'width' => 'auto',
    'class' => 'site-logo',
  ], $atts);
  
  $logo = p5m_get_setting('logo', get_template_directory_uri() . '/assets/img/logo-fallback.svg');
  $alt = get_bloginfo('name');
  
  $style = $atts['width'] !== 'auto' ? ' style="width:' . esc_attr($atts['width']) . '"' : '';
  
  return '<img src="' . esc_url($logo) . '" alt="' . esc_attr($alt) . '" class="' . esc_attr($atts['class']) . '"' . $style . ' />';
});

/**
 * Ejemplo: Menú de navegación
 * Uso: [nav_menu location="footer-menu"]
 * Parámetros: location (requerido), class (opcional)
 */
add_shortcode('nav_menu', function($atts) {
  $atts = shortcode_atts([
    'location' => 'primary',
    'class' => 'footer-nav-menu',
  ], $atts);
  
  if (!has_nav_menu($atts['location'])) {
    return '<!-- Menú no asignado: ' . esc_html($atts['location']) . ' -->';
  }
  
  ob_start();
  wp_nav_menu([
    'theme_location' => $atts['location'],
    'container' => 'nav',
    'container_class' => $atts['class'],
    'menu_class' => 'menu-list',
    'fallback_cb' => false,
  ]);
  return ob_get_clean();
});

/**
 * Ejemplo: Redes sociales
 * Uso: [social_links]
 * Nota: Requiere que definas las URLs en P5 Settings o ACF
 */
add_shortcode('social_links', function($atts) {
  $atts = shortcode_atts([
    'class' => 'social-links flex gap-4',
  ], $atts);
  
  // Ejemplo básico - puedes ampliarlo según tus necesidades
  $socials = [
    'facebook' => p5m_get_setting('facebook_url', ''),
    'twitter' => p5m_get_setting('twitter_url', ''),
    'instagram' => p5m_get_setting('instagram_url', ''),
    'linkedin' => p5m_get_setting('linkedin_url', ''),
  ];
  
  $html = '<div class="' . esc_attr($atts['class']) . '">';
  
  foreach ($socials as $network => $url) {
    if (!empty($url)) {
      $html .= '<a href="' . esc_url($url) . '" target="_blank" rel="noopener noreferrer" class="social-link social-' . esc_attr($network) . '">';
      $html .= '<span class="sr-only">' . ucfirst($network) . '</span>';
      // Aquí podrías agregar iconos SVG o clases de Font Awesome
      $html .= '<i class="icon-' . esc_attr($network) . '"></i>';
      $html .= '</a>';
    }
  }
  
  $html .= '</div>';
  
  return $html;
});

/**
 * Ejemplo: Botón CTA personalizado
 * Uso: [cta_button text="Contactar" url="/contact" class="btn-primary"]
 */
add_shortcode('cta_button', function($atts) {
  $atts = shortcode_atts([
    'text' => 'Click Here',
    'url' => '#',
    'class' => 'cta-button',
    'target' => '_self',
  ], $atts);
  
  return '<a href="' . esc_url($atts['url']) . '" class="' . esc_attr($atts['class']) . '" target="' . esc_attr($atts['target']) . '">' . esc_html($atts['text']) . '</a>';
});

/**
 * Ejemplo: Widget de idiomas (si usas WPML o Polylang)
 * Uso: [language_switcher]
 */
add_shortcode('language_switcher', function() {
  // WPML
  if (function_exists('icl_get_languages')) {
    $languages = icl_get_languages('skip_missing=0');
    if (empty($languages)) return '';
    
    $html = '<ul class="language-switcher">';
    foreach ($languages as $lang) {
      $active = $lang['active'] ? ' class="active"' : '';
      $html .= '<li' . $active . '><a href="' . esc_url($lang['url']) . '">' . esc_html($lang['native_name']) . '</a></li>';
    }
    $html .= '</ul>';
    
    return $html;
  }
  
  // Polylang
  if (function_exists('pll_the_languages')) {
    ob_start();
    pll_the_languages(['show_flags' => 0, 'show_names' => 1]);
    return ob_get_clean();
  }
  
  return '';
});

/**
 * AGREGA TUS PROPIOS SHORTCODES AQUÍ
 * 
 * Ejemplo de estructura:
 * 
 * add_shortcode('mi_shortcode', function($atts, $content = '') {
 *   $atts = shortcode_atts([
 *     'parametro1' => 'valor_default',
 *     'parametro2' => 'otro_default',
 *   ], $atts);
 *   
 *   // Tu lógica aquí
 *   $output = '<div class="mi-elemento">';
 *   $output .= esc_html($atts['parametro1']);
 *   $output .= '</div>';
 *   
 *   return $output;
 * });
 */

add_shortcode( 'footer_tiny_tex', function (){
    $footer_tiny_tex = '<div class="footer-tiny-text">'.__('En alianza con:', 'Sanasana footer').'</div>';
    return $footer_tiny_tex;
});

add_shortcode( 'footer_social', function(){
  $social_footer = '<ul class="footer-social-links flex gap-4">';
    $social_footer .= '<li><a href="https://www.facebook.com/sanasanacostarica" target="_blank" rel="noopener noreferrer" class="social-link social-facebook"><img loading="lazy" decoding="async" class="" src="https://sanasanastoragews.blob.core.windows.net/blobwsdev/2025/02/SANASANA-Iconosfacebook.webp" alt="'.__("Sanasana - Salud como nunca antes", "Sanasana footer").'" itemprop="image"  height="30" width="30" title="SANASANA-Iconosfacebook"></a></li>';
    $social_footer .= '<li><a href="https://www.linkedin.com/company/sanasanacostarica/" target="_blank" rel="noopener noreferrer" class="social-link social-linkedin"><img loading="lazy" decoding="async" class="" src="https://sanasanastoragews.blob.core.windows.net/blobwsdev/2025/02/SANASANA-Iconolinked.webp" alt="'.__("Sanasana - Salud como nunca antes", "Sanasana footer").'" itemprop="image"  height="30" width="30" title="SANASANA-Iconolinked"></a></li>';
    $social_footer .= '<li><a href="https://www.instagram.com/sanasanacostarica/" target="_blank" rel="noopener noreferrer" class="social-link social-instagram"><img loading="lazy" decoding="async" class="" src="https://sanasanastoragews.blob.core.windows.net/blobwsdev/2025/02/SANASANA-Iconoinsta.webp" alt="'.__("Sanasana - Salud como nunca antes", "Sanasana footer").'" itemprop="image" height="30" width="30" title="SANASANA-Iconoinsta"></a></li>';    
    $social_footer .= '<li><a href="https://www.youtube.com/@SanaSanaCR" target="_blank" rel="noopener noreferrer" class="social-link social-youtube"><img loading="lazy" decoding="async" class="" src="https://sanasanastoragews.blob.core.windows.net/blobwsdev/2025/02/SANASANA-Iconoyoutube.webp" alt="'.__("Sanasana - Salud como nunca antes", "Sanasana footer").'" itemprop="image" height="30" width="30" title="SANASANA-Iconoyoutube"></a></li>';   
    $social_footer .= '<li><a href="https://wa.me/50672627262" target="_blank" rel="noopener" itemprop="url"><img loading="lazy" decoding="async" class="" src="https://sanasanastoragews.blob.core.windows.net/blobwsdev/2025/02/SANASANA-Iconowhastapp.webp" alt="'.__("Sanasana - Salud como nunca antes", "Sanasana footer").'" itemprop="image" height="30" width="30" title="SANASANA-Iconowhastapp"></a></li>';  
  $social_footer .= '</ul>';
  return $social_footer;  
} );

add_shortcode( 'footer_images', function(){
  $footer_images = '<div class="footer-images flex gap-8">';
    $footer_images .= '<a href="https://sanasana.com/" target="_self" itemprop="url"><img loading="lazy" decoding="async" class="" src="https://sanasana.com/wp-content/uploads/2025/02/logo_black_1x.webp" alt="'.__("Cuidarte hoy es estar bien mañana Para una vida | SANA SANA", "Sanasana footer").'" itemprop="image" height="34" width="189" title="logo_black_1x"></a>';
    $footer_images .= '<a href="https://hospitalcima.com/es/" target="_self" itemprop="url"><img loading="lazy" decoding="async" width="113" height="29" class="" src="https://sanasana.com/wp-content/uploads/2025/02/logo_cima_1x.webp" alt="'.__("Cuidarte hoy es estar bien mañana Para una vida | SANA SANA", "Sanasana footer").'" itemprop="image" title="logo_cima_1x"></a>';
  $footer_images .= '</div>';
  return $footer_images;  
} );

add_shortcode( 'footer_menu', function(){
  $footer_menu = '
  <ul id="menu-footer-menu">
    <li><a href="https://sanasana.com/terminos-de-uso/"><span class="menu-item-text">Términos de uso</span></a></li>
    <li><a href="https://sanasana.com/politicas-de-privacidad/"><span class="menu-item-text">Política de Privacidad</span></a></li>
    <li><a href="#"><span class="menu-item-text">© SANA SANA todos los derechos reservados</span></a></li>
  </ul>';
  return $footer_menu;
});

/**
 * Language Switcher
 * Uso: [lang_switcher]
 * Parámetros: show_flags="1" show_names="1" class="custom-class"
 */
add_shortcode('lang_switcher', function($atts) {
  $atts = shortcode_atts([
    'show_flags' => '0',
    'show_names' => '1',
    'class' => 'p5m-lang-switcher',
  ], $atts);
  
  return p5m_language_switcher([
    'show_flags' => $atts['show_flags'] === '1',
    'show_names' => $atts['show_names'] === '1',
    'class' => $atts['class'],
  ]);
});
