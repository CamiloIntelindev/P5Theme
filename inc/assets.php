<?php
/**
 * Assets Management
 * Enqueue CSS/JS, optimizaciones de carga, preload/defer
 *
 * @package P5Marketing
 */

if (!defined('ABSPATH')) exit;

// Enqueue scripts and styles
add_action('wp_enqueue_scripts', function () {
  if (is_admin()) return;
  // Keep normal assets; no special changes for BB editor here

  // Tailwind — NO BLOQUEANTE (preload + async)
  if (is_admin()) return;
  $tw_path = get_template_directory() . '/dist/tailwind.css';
  if (file_exists($tw_path)) {
    wp_enqueue_style('p5marketing-tailwind', get_template_directory_uri() . '/dist/tailwind.css', [], filemtime($tw_path));
  }

  // style.css del theme — NO BLOQUEANTE
  wp_enqueue_style('p5-style', get_template_directory_uri() . '/style.css', [], filemtime(get_template_directory() . '/style.css'));

  // Alpine desde CDN (defer para no bloquear)
  wp_enqueue_script('alpine', 'https://cdn.jsdelivr.net/npm/alpinejs@3.14.0/dist/cdn.min.js', [], null, true);
  wp_script_add_data('alpine', 'defer', true);

  // JS del header (defer)
  $hdr_js = get_template_directory() . '/assets/js/header.js';
  if (file_exists($hdr_js)) {
    wp_enqueue_script('p5m-header', get_template_directory_uri() . '/assets/js/header.js', [], filemtime($hdr_js), true);
    wp_script_add_data('p5m-header', 'defer', true);
  }
}, 100);

// Convierte TODOS los CSS en preload + async (no bloqueante)
add_filter('style_loader_tag', function ($html, $handle) {
  if (function_exists('p5m_should_optimize') && !p5m_should_optimize()) return $html;
  $non_critical = ['p5marketing-tailwind', 'p5-style'];
  // También tratar como no-bloqueante cualquier hoja de estilos de Google Fonts
  if (in_array($handle, $non_critical, true) || strpos($html, 'fonts.googleapis.com') !== false) {
    $pre = str_replace(
      "rel='stylesheet'",
      "rel='preload' as='style' onload=\"this.onload=null;this.rel='stylesheet'\"",
      $html
    );
    return $pre . "<noscript>{$html}</noscript>";
  }
  return $html;
}, 10, 2);

// x-cloak para Alpine (prevenir flicker)
add_action('wp_head', fn() => print('<style>[x-cloak]{display:none!important}</style>' . PHP_EOL), 1);

// Critical CSS inline (above-the-fold content)
add_action('wp_head', function() {
  if (is_admin()) return;
  ?>
  <style id="critical-css">
  /* Critical layout - prevent FOUC */
  *,::before,::after{box-sizing:border-box}
  body{margin:0;padding:0;font-family:-apple-system,BlinkMacSystemFont,"Segoe UI",Roboto,sans-serif;line-height:1.6;color:#1a202c;background:#fff}
  .site-header{position:relative;z-index:999;background:#fff}
  .site-content{min-height:50vh}
  .site-footer{margin-top:auto}
  .ast-builder-layout-element{visibility:visible}
  /* Prevent layout shift for menu */
  .main-header-bar{min-height:60px}
  .ast-container{max-width:1200px;margin:0 auto;padding:0 20px}
  /* Hero/above-fold skeleton */
  .wp-block-cover,.hero-section{min-height:400px;display:flex;align-items:center;justify-content:center}
  /* Hide animations until loaded */
  .animate-on-scroll{opacity:0;transition:opacity .3s ease-in}
  .loaded .animate-on-scroll{opacity:1}
  </style>
  <?php
}, 2);

// Forzar display=swap en Google Fonts para evitar FOIT
add_filter('style_loader_src', function ($src, $handle) {
  if (strpos($src, 'fonts.googleapis.com') !== false && strpos($src, 'display=') === false) {
    $src .= (strpos($src, '?') === false ? '?' : '&') . 'display=swap';
  }
  return $src;
}, 10, 2);

// Defer JS de jsDelivr globalmente (excluye jQuery para compatibilidad)
add_filter('script_loader_tag', function ($tag, $handle, $src) {
    if (is_admin() || empty($src) || (function_exists('p5m_should_optimize') && !p5m_should_optimize())) return $tag;
  if ($handle === 'jquery' || $handle === 'jquery-core') return $tag;
  if (strpos($src, 'cdn.jsdelivr.net') !== false) {
    if (strpos($tag, ' defer') === false) {
      $tag = str_replace(' src', ' defer src', $tag);
    }
  }
  return $tag;
}, 10, 3);

// ============================================================================
// SVG upload support + admin preview fix
// ============================================================================
add_filter('upload_mimes', function($mimes){
  $mimes['svg'] = 'image/svg+xml';
  return $mimes;
});

add_action('admin_head', function(){
  echo '<style>.attachment-266x266,.thumbnail img[src$=".svg"]{width:100%!important;height:auto!important}</style>';
});

add_filter('wp_check_filetype_and_ext', function($data, $file, $filename, $mimes){
  $ext = pathinfo($filename, PATHINFO_EXTENSION);
  if ($ext === 'svg') {
    $data['ext'] = 'svg';
    $data['type'] = 'image/svg+xml';
  }
  return $data;
}, 10, 4);

// ============================================================================
// Custom inline script: Nosotros modal
// ============================================================================
function nosotros_modal() {
  echo '
  <style>
    .testimonio-home,
    .nosotros-conoce-mas{cursor:pointer;}
  </style>
  <script>
  jQuery(document).ready(function () {
    function setIframeSize(iframe) {
      var viewportWidth = jQuery(document).width();
      var maxWidth = 966;
      var width = Math.min(viewportWidth * 0.9, maxWidth);
      var height = width * (9 / 16);
      iframe.attr("width", width);
      iframe.attr("height", height);
    }
    jQuery(".nosotros-conoce-mas").on("click", function (e) {
      e.preventDefault();
      jQuery("#video-nosotros-modal").remove();
      jQuery(".site-main").append(`
        <div id="video-nosotros-modal" class="video-testimonio-container-desktop" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:flex;justify-content:center;align-items:center;flex-direction:column;">
          <span class="close-video" style="position:absolute;right:15%;top:15%;background:#ffffff;width:40px;height:40px;border-radius:100px;display:flex;justify-content:center;align-items:center;font-size:18px;">&times;</span>
          <iframe src="" frameborder="0" allow="autoplay; fullscreen"></iframe>
        </div>
      `);
      var iframe = jQuery("#video-nosotros-modal").find("iframe");
      var video_url = "https://www.youtube.com/embed/mYaq94wrTiw";
      var separador = video_url.includes("?") ? "&" : "?";
      setIframeSize(iframe);
      jQuery("#video-nosotros-modal").fadeIn(200);
      iframe.attr("src", video_url + separador + "autoplay=1&rel=0");
    });
    jQuery(".testimonio-home").on("click", function (e) {
      e.preventDefault();
      jQuery("#video-nosotros-modal").remove();
      jQuery(".site-main").append(`
        <div id="video-nosotros-modal" class="video-testimonio-container-desktop" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.5);display:flex;justify-content:center;align-items:center;flex-direction:column;">
          <span class="close-video" style="position:absolute;right:15%;top:15%;background:#ffffff;width:40px;height:40px;border-radius:100px;display:flex;justify-content:center;align-items:center;font-size:18px;">&times;</span>
          <iframe src="" frameborder="0" allow="autoplay; fullscreen"></iframe>
        </div>
      `);
      var iframe = jQuery("#video-nosotros-modal").find("iframe");
      var video_url = "https://www.youtube.com/embed/o56eDhrDTN8?si=bwkEeKP2nZz44aDl";
      var separador = video_url.includes("?") ? "&" : "?";
      setIframeSize(iframe);
      jQuery("#video-nosotros-modal").fadeIn(200);
      iframe.attr("src", video_url + separador + "autoplay=1&rel=0");
    });
    jQuery(document).on("click", ".close-video", function () {
      jQuery("#video-nosotros-modal").fadeOut(200, function(){ jQuery(this).remove(); });
    });
    jQuery(window).on("resize", function () {
      var iframe = jQuery("#video-nosotros-modal").find("iframe");
      if (iframe.length) { setIframeSize(iframe); }
    });
  });
  </script>
  ';
}
add_action('wp_head', 'nosotros_modal');
