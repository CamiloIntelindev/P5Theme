<?php
/**
 * SEO & Analytics
 * Meta tags, Schema.org, GTM/GA4, custom scripts
 *
 * @package P5Marketing
 */

if (!defined('ABSPATH')) exit;

// Helper ACF fallback
function p5m_acf_or_setting($key, $default = null) {
  $get_acf = function_exists('get_field') ? get_field($key, 'option') : null;
  return p5m_get_setting($key, $get_acf) ?? $default;
}

// SEO/Meta
add_action('wp_head', function () {
  if (is_admin()) return;

  $force_noindex = (bool) intval(p5m_acf_or_setting('force_noindex'));
  $canonical = rtrim((string)p5m_acf_or_setting('canonical_domain'), '/');
  $gsc = trim((string)p5m_acf_or_setting('gsc_verification'));
  $bing = trim((string)p5m_acf_or_setting('bing_verification'));
  $manifest = esc_url(p5m_acf_or_setting('manifest_url'));
  $theme_color = sanitize_text_field(p5m_acf_or_setting('theme_color'));

  if ($force_noindex) echo '<meta name="robots" content="noindex,nofollow" />' . PHP_EOL;
  
  if ($canonical && !is_404()) {
    $url = esc_url($canonical . $_SERVER['REQUEST_URI']);
    echo '<link rel="canonical" href="' . $url . '" />' . PHP_EOL;
  }

  if ($gsc)      echo '<meta name="google-site-verification" content="' . esc_attr($gsc) . '">' . PHP_EOL;
  if ($bing)     echo '<meta name="msvalidate.01" content="' . esc_attr($bing) . '">' . PHP_EOL;
  if ($manifest) echo '<link rel="manifest" href="' . esc_url($manifest) . '">' . PHP_EOL;
  if ($theme_color) echo '<meta name="theme-color" content="' . esc_attr($theme_color) . '">' . PHP_EOL;
}, 5);

// Schema.org Structured Data (JSON-LD)
add_action('wp_head', function () {
  if (is_admin()) return;
  
  if (function_exists('p5m_the_organization_schema')) {
    p5m_the_organization_schema();
  }
  
  if (function_exists('p5m_the_article_schema')) {
    p5m_the_article_schema();
  }
  
  if (function_exists('p5m_the_webpage_schema')) {
    p5m_the_webpage_schema();
  }
}, 10);

// GTM/GA4 diferidos
add_action('wp_head', function () {
  if (is_admin()) return;

  // Preferimos ajustes del tema (P5 Settings); si están vacíos,
  // hacemos fallback a las opciones históricas del child theme (sanasana_*).
  $gtm = trim((string)p5m_acf_or_setting('gtm_container_id'));
  if (!$gtm) {
    $gtm = trim((string) get_option('sanasana_gtm_id'));
  }
  $ga4 = trim((string)p5m_acf_or_setting('ga4_measurement_id'));
  if (!$ga4) {
    $ga4 = trim((string) get_option('sanasana_ga_id'));
  }

  // GTM diferido
  if ($gtm) : ?>
    <script>
    (function(w,d,s,l,i){
      w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});
      function loadGTM(){ if(d.getElementById('gtm-script'))return;
        var f=d.getElementsByTagName(s)[0], j=d.createElement(s);
        j.async=true; j.id='gtm-script'; j.src='https://www.googletagmanager.com/gtm.js?id='+i;
        f.parentNode.insertBefore(j,f);
      }
      function init(){loadGTM(); cleanup();}
      function cleanup(){ d.removeEventListener('scroll',init); d.removeEventListener('mousemove',init); d.removeEventListener('touchstart',init); }
      setTimeout(init,3000);
      d.addEventListener('scroll',init,{once:true});
      d.addEventListener('mousemove',init,{once:true});
      d.addEventListener('touchstart',init,{once:true});
    })(window,document,'script','dataLayer','<?php echo esc_js($gtm); ?>');
    </script>
  <?php
  // GA4 directo diferido (sin GTM)
  elseif ($ga4) : ?>
    <script>
    (function(w,d,s,i){
      function loadGA(){ if(d.getElementById('ga4-script'))return;
        var f=d.getElementsByTagName(s)[0], j=d.createElement(s);
        j.async=true; j.id='ga4-script'; j.src='https://www.googletagmanager.com/gtag/js?id='+i;
        f.parentNode.insertBefore(j,f);
        w.dataLayer=w.dataLayer||[]; w.gtag=function(){dataLayer.push(arguments);}
        gtag('js', new Date()); gtag('config', i);
      }
      function init(){loadGA(); cleanup();}
      function cleanup(){ d.removeEventListener('scroll',init); d.removeEventListener('mousemove',init); d.removeEventListener('touchstart',init); }
      setTimeout(init,3000);
      d.addEventListener('scroll',init,{once:true});
      d.addEventListener('mousemove',init,{once:true});
      d.addEventListener('touchstart',init,{once:true});
    })(window,document,'script','<?php echo esc_js($ga4); ?>');
    </script>
  <?php endif;
}, 5);

// GTM Noscript
add_action('wp_body_open', function () {
  if (is_admin()) return;
  if ($gtm = trim((string)p5m_acf_or_setting('gtm_container_id'))) {
    echo '<noscript><iframe src="https://www.googletagmanager.com/ns.html?id='.esc_attr($gtm).'" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>' . PHP_EOL;
  }
}, 5);

// Custom Scripts (Header/Body/Footer) - Diferidos
add_action('wp_head', function () {
  if (is_admin()) return;
  $scripts = trim((string)p5m_get_setting('header_scripts', ''));
  if (empty($scripts)) return;
  ?>
  <script id="p5m-header-custom-loader">
  (function(d){
    var executed = false;
    function loadHeaderScripts(){
      if(executed) return;
      executed = true;
      var container = d.createElement('div');
      container.innerHTML = <?php echo wp_json_encode($scripts); ?>;
      var scripts = container.querySelectorAll('script');
      scripts.forEach(function(s){
        var newScript = d.createElement('script');
        if(s.src) newScript.src = s.src;
        else newScript.textContent = s.textContent;
        Array.from(s.attributes).forEach(function(attr){
          if(attr.name !== 'src') newScript.setAttribute(attr.name, attr.value);
        });
        d.head.appendChild(newScript);
      });
      cleanup();
    }
    function cleanup(){ 
      d.removeEventListener('scroll',loadHeaderScripts); 
      d.removeEventListener('mousemove',loadHeaderScripts); 
      d.removeEventListener('touchstart',loadHeaderScripts); 
    }
    setTimeout(loadHeaderScripts, 3000);
    d.addEventListener('scroll', loadHeaderScripts, {once:true});
    d.addEventListener('mousemove', loadHeaderScripts, {once:true});
    d.addEventListener('touchstart', loadHeaderScripts, {once:true});
  })(document);
  </script>
  <?php
}, 99);

add_action('wp_body_open', function () {
  if (is_admin()) return;
  $scripts = trim((string)p5m_get_setting('body_scripts', ''));
  if (empty($scripts)) return;
  ?>
  <script id="p5m-body-custom-loader">
  (function(d){
    var executed = false;
    function loadBodyScripts(){
      if(executed) return;
      executed = true;
      var container = d.createElement('div');
      container.innerHTML = <?php echo wp_json_encode($scripts); ?>;
      var scripts = container.querySelectorAll('script');
      scripts.forEach(function(s){
        var newScript = d.createElement('script');
        if(s.src) newScript.src = s.src;
        else newScript.textContent = s.textContent;
        Array.from(s.attributes).forEach(function(attr){
          if(attr.name !== 'src') newScript.setAttribute(attr.name, attr.value);
        });
        d.body.appendChild(newScript);
      });
      cleanup();
    }
    function cleanup(){ 
      d.removeEventListener('scroll',loadBodyScripts); 
      d.removeEventListener('mousemove',loadBodyScripts); 
      d.removeEventListener('touchstart',loadBodyScripts); 
    }
    setTimeout(loadBodyScripts, 3000);
    d.addEventListener('scroll', loadBodyScripts, {once:true});
    d.addEventListener('mousemove', loadBodyScripts, {once:true});
    d.addEventListener('touchstart', loadBodyScripts, {once:true});
  })(document);
  </script>
  <?php
}, 99);

add_action('wp_footer', function () {
  if (is_admin()) return;
  $scripts = trim((string)p5m_get_setting('footer_scripts', ''));
  if (empty($scripts)) return;
  ?>
  <script id="p5m-footer-custom-loader">
  (function(d){
    var executed = false;
    function loadFooterScripts(){
      if(executed) return;
      executed = true;
      var container = d.createElement('div');
      container.innerHTML = <?php echo wp_json_encode($scripts); ?>;
      var scripts = container.querySelectorAll('script');
      scripts.forEach(function(s){
        var newScript = d.createElement('script');
        if(s.src) newScript.src = s.src;
        else newScript.textContent = s.textContent;
        Array.from(s.attributes).forEach(function(attr){
          if(attr.name !== 'src') newScript.setAttribute(attr.name, attr.value);
        });
        d.body.appendChild(newScript);
      });
      cleanup();
    }
    function cleanup(){ 
      d.removeEventListener('scroll',loadFooterScripts); 
      d.removeEventListener('mousemove',loadFooterScripts); 
      d.removeEventListener('touchstart',loadFooterScripts); 
    }
    setTimeout(loadFooterScripts, 3000);
    d.addEventListener('scroll', loadFooterScripts, {once:true});
    d.addEventListener('mousemove', loadFooterScripts, {once:true});
    d.addEventListener('touchstart', loadFooterScripts, {once:true});
  })(document);
  </script>
  <?php
}, 99);

// Immediate Scripts (no delay)
add_action('wp_footer', function () {
  if (is_admin()) return;
  $scripts = trim((string)p5m_get_setting('immediate_footer', ''));
  if (empty($scripts)) return;
  echo PHP_EOL . '<!-- P5M Immediate Footer Scripts -->' . PHP_EOL;
  echo $scripts . PHP_EOL;
}, 999);
