<?php
/**
 * P5 Marketing Theme Functions
 * 
 * Bootstrap principal del tema. Carga módulos organizados por funcionalidad.
 *
 * @package P5Marketing
 * @version 1.0.0
 */

if (!defined('ABSPATH')) exit;

// ============================================================================
// Core Modules
// ============================================================================

// Template tags and helpers
require_once get_template_directory() . '/inc/template-tags.php';

// Cache helpers
if (file_exists(get_template_directory() . '/inc/cache.php')) {
  require_once get_template_directory() . '/inc/cache.php';
}

// Theme setup (theme support, menus, sidebars, image sizes)
require_once get_template_directory() . '/inc/theme-setup.php';

// Metaboxes (layout, title, featured image)
require_once get_template_directory() . '/inc/metaboxes.php';

// Assets management (CSS/JS enqueue, preload, defer)
require_once get_template_directory() . '/inc/assets.php';

// Performance optimizations (head cleanup, cache headers, lazy loading)
require_once get_template_directory() . '/inc/performance.php';

// SEO & Analytics (meta tags, schema, GTM/GA4, custom scripts)
require_once get_template_directory() . '/inc/seo-analytics.php';

// Content filters (navigation, menus, AJAX)
require_once get_template_directory() . '/inc/content-filters.php';

// Multilanguage system (ES/EN translations without WPML)
require_once get_template_directory() . '/inc/multilang.php';

// ============================================================================
// Admin Settings Pages
// ============================================================================

// Theme admin settings (p5m settings helper + page)
if (file_exists(get_template_directory() . '/inc/admin-settings.php')) {
  require_once get_template_directory() . '/inc/admin-settings.php';
}

// Theme header settings (p5m header customization)
if (file_exists(get_template_directory() . '/inc/admin-header-settings.php')) {
  require_once get_template_directory() . '/inc/admin-header-settings.php';
}

// Theme footer settings (p5m footer customization)
if (file_exists(get_template_directory() . '/inc/admin-footer-settings.php')) {
  require_once get_template_directory() . '/inc/admin-footer-settings.php';
}

// Theme fonts settings (p5m custom fonts)
if (file_exists(get_template_directory() . '/inc/admin-fonts-settings.php')) {
  require_once get_template_directory() . '/inc/admin-fonts-settings.php';
}

// Custom shortcodes (for footer, widgets, and content)
if (file_exists(get_template_directory() . '/inc/shortcodes.php')) {
  require_once get_template_directory() . '/inc/shortcodes.php';
}

// Shortcodes help page
if (file_exists(get_template_directory() . '/inc/shortcodes-help.php')) {
  require_once get_template_directory() . '/inc/shortcodes-help.php';
}


// Permitir subida de SVG
function allow_svg_upload($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    return $mimes;
}
add_filter('upload_mimes', 'allow_svg_upload');

// Evitar error al ver la imagen SVG en la librería
function fix_svg_display() {
    echo '<style>
        .attachment-266x266, 
        .thumbnail img[src$=".svg"] {
            width: 100% !important;
            height: auto !important;
        }
    </style>';
}
add_action('admin_head', 'fix_svg_display');

// Validación del tipo MIME (opcional, pero más seguro)
function svg_mime_type_check($data, $file, $filename, $mimes) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);

    if ($ext === 'svg') {
        $data['ext'] = 'svg';
        $data['type'] = 'image/svg+xml';
    }

    return $data;
}
add_filter('wp_check_filetype_and_ext', 'svg_mime_type_check', 10, 4);



// Hotjar configuration
function hotjar() {
    $hotjar_id = get_option('sanasana_hotjar_id'); // Get ID from options
    $hotjar_sv = 6;
    $hotjar_script = <<<EOT
<script>
    (function(h,o,t,j,a,r){
        h.hj=h.hj||function(){(h.hj.q=h.hj.q||[]).push(arguments)};
        h._hjSettings={hjid:{$hotjar_id},hjsv:{$hotjar_sv}};
        a=o.getElementsByTagName('head')[0];
        r=o.createElement('script');r.async=1;
        r.src="https://static.hotjar.com/c/hotjar-{$hotjar_id}.js?sv={$hotjar_sv}";
        a.appendChild(r);
    })(window,document,'https://static.hotjar.com/c/hotjar-','.js?sv=');
</script>
EOT;

    echo $hotjar_script;
}
add_action('wp_head', 'hotjar');

// Add Hotjar Settings Menu
function add_hotjar_admin_menu() {
    add_submenu_page(
        'options-general.php', // Parent menu (Settings)
        __('Hotjar Settings', 'sanasana'),
        __('Hotjar', 'sanasana'),
        'manage_options',
        'hotjar-settings',
        'render_hotjar_settings_page'
    );
}
add_action('admin_menu', 'add_hotjar_admin_menu');

// Register Hotjar Settings
function register_hotjar_settings() {
    register_setting('hotjar_settings', 'sanasana_hotjar_id');

    add_settings_section(
        'hotjar_main_section',
        __('Hotjar Configuration', 'sanasana'),
        'render_hotjar_section_info',
        'hotjar-settings'
    );

    add_settings_field(
        'sanasana_hotjar_id',
        __('Hotjar ID', 'sanasana'),
        'render_hotjar_id_field',
        'hotjar-settings',
        'hotjar_main_section'
    );
}
add_action('admin_init', 'register_hotjar_settings');

// Render Hotjar Settings Page
function render_hotjar_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('hotjar_settings');
            do_settings_sections('hotjar-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Render Hotjar Section Info
function render_hotjar_section_info() {
    echo '<p>' . __('Configure your Hotjar settings below:', 'sanasana') . '</p>';
}

// Render Hotjar ID Field
function render_hotjar_id_field() {
    $value = get_option('sanasana_hotjar_id');
    ?>
    <input type="text" 
           name="sanasana_hotjar_id" 
           value="<?php echo esc_attr($value); ?>" 
           class="regular-text"
           placeholder="Hotjar ID">
    <p class="description">
        <?php _e('Enter your Hotjar ID here.', 'sanasana'); ?>
    </p>
    <?php
}
// End hotjar configuration

// ============================================================================
// Sanasana Plugin Asset Optimization
// ============================================================================
// Conditionally dequeue heavy Sanasana plugin CSS/JS if no related shortcodes
// appear in the current singular content. This avoids loading ~ multiple HTTP
// requests site‑wide when not needed.
// Shortcodes detected: tabs, evaluation-tabs, questionnaire, programs, faq, resenas.
// Extend list as plugin adds more.
function p5m_conditionally_optimize_sanasana_assets() {
    if (is_admin()) return; // Front-end only

    // Collect post content for shortcode scanning (singular); for archives keep everything for safety.
    $content = '';
    $is_singular = is_singular();
    if ($is_singular) {
        $post = get_post();
        if ($post) { $content = $post->post_content; }
    }

    // Map shortcode tags to logical asset groups.
    $shortcode_groups = [
        // Pricing tables / programs (accordion heavy)
        'price_table' => 'programs',
        'price_table_cards' => 'programs',
        'price_table_cards_nosotros' => 'programs',
        'price_table_details' => 'programs',
        'get_program_details' => 'programs',
        'get_render_program_ahorros' => 'programs',
        'get_price_table_compare' => 'programs',
        'compare_programs' => 'programs',
        'compare_programs_singular' => 'programs',
        'toggle_button' => 'programs',
        // Tabs
        'tabs' => 'tabs',
        'evaluation-tabs' => 'tabs',
        // FAQ
        'faq_tabs' => 'faq',
        // Questionnaire
        'questionnaire_render' => 'questionnaire',
        'cuestionario' => 'questionnaire',
        // Forms / buttons (need form styles & interaction libs)
        'contact_form' => 'forms',
        'learn_more_form' => 'forms',
        'ingresa_button' => 'forms',
        'afiliate_home_hero_buttons' => 'forms',
        'conoce_mas_button' => 'forms',
        'affiliate_button_single_redirection' => 'forms',
        'affiliate_button_plan_details_top' => 'forms',
        'affiliate_button_footer' => 'forms',
        'schedule_button_single_redirection' => 'forms',
        // Resenas (reviews)
        'resenas_frontend' => 'resenas',
        // SEO helper shortcodes generally lightweight -> no group (site-name, video-testimonio) intentionally excluded.
    ];

    // Asset handles grouped.
    $assets = [
        'programs' => [
            'styles' => ['pricetable-styles-css'],
            'scripts' => ['scripts-price']
        ],
        'tabs' => [
            'styles' => ['tabstable-styles-css'],
            'scripts' => ['scripts-tabs','scripts-tabs-horizontal','scripts-tabs-vertical']
        ],
        'faq' => [
            'styles' => ['tabstable-styles-css'], // reuse tabs styles if FAQ uses tab infra
            'scripts' => ['scripts-tabs']
        ],
        'questionnaire' => [
            'styles' => ['questionnaire-styles-css'],
            'scripts' => [] // questionnaire script commented out in plugin
        ],
        'forms' => [
            'styles' => ['form-styles-css','sweetalert2','notyf-css','intl-tel-input'],
            'scripts' => ['sweetalert2','notyf-js','intl-tel-input','script-general','google-recaptcha']
        ],
        'resenas' => [
            'styles' => [],
            'scripts' => []
        ],
    ];

    // Always allow jQuery core.
    $always_allow_scripts = ['jquery','jquery-core','jquery-migrate'];

    // Determine required groups for this request.
    $required_groups = [];
    if ($is_singular && $content) {
        foreach ($shortcode_groups as $tag => $group) {
            if (strpos($content, '['.$tag) !== false && has_shortcode($content, $tag)) {
                $required_groups[$group] = true;
            }
        }
    }

    // If NO shortcode groups needed on this singular page, we can dequeue ALL plugin assets (original behavior) but keep forms libs if a contact template maybe? We'll conservatively only keep nothing.
    $plugin_handles_styles = [
        'pricetable-styles-css','tabstable-styles-css','questionnaire-styles-css','form-styles-css','bootstrap-css','sweetalert2','notyf-css','intl-tel-input'
    ];
    $plugin_handles_scripts = [
        'scripts-price','scripts-tabs','scripts-tabs-horizontal','scripts-tabs-vertical','script-general','bootstrap-js','sweetalert2','notyf-js','intl-tel-input','google-recaptcha'
    ];

    if (empty($required_groups)) {
        foreach ($plugin_handles_styles as $h) { wp_dequeue_style($h); wp_deregister_style($h); }
        foreach ($plugin_handles_scripts as $h) { if (!in_array($h,$always_allow_scripts,true)) { wp_dequeue_script($h); wp_deregister_script($h); } }
        return; // done
    }

    // Build whitelist of handles actually required.
    $needed_styles = [];
    $needed_scripts = [];
    foreach (array_keys($required_groups) as $group) {
        if (isset($assets[$group])) {
            $needed_styles = array_merge($needed_styles, $assets[$group]['styles']);
            $needed_scripts = array_merge($needed_scripts, $assets[$group]['scripts']);
        }
    }

    // OPTIONAL: If any group requires Bootstrap (e.g., legacy markup), you can add condition; for now only load bootstrap if tabs/programs present.
    $needs_bootstrap = isset($required_groups['programs']) || isset($required_groups['tabs']);
    if ($needs_bootstrap) {
        $needed_styles[] = 'bootstrap-css';
        $needed_scripts[] = 'bootstrap-js';
    }

    // If forms present, ensure reCAPTCHA loads after interaction (convert to async+defer if present).
    if (in_array('google-recaptcha', $needed_scripts, true)) {
        add_filter('script_loader_tag', function($tag,$handle,$src){
            if ($handle==='google-recaptcha') {
                if (strpos($tag,'async')===false) $tag = str_replace('<script ','<script async ',$tag);
                if (strpos($tag,'defer')===false) $tag = str_replace('<script ','<script defer ',$tag);
            }
            return $tag;
        },10,3);
    }

    // Dequeue any plugin handle not in whitelist.
    foreach ($plugin_handles_styles as $h) {
        if (!in_array($h, $needed_styles, true)) { wp_dequeue_style($h); wp_deregister_style($h); }
    }
    foreach ($plugin_handles_scripts as $h) {
        if (!in_array($h, $needed_scripts, true) && !in_array($h,$always_allow_scripts,true)) { wp_dequeue_script($h); wp_deregister_script($h); }
    }
}
add_action('wp_enqueue_scripts', 'p5m_conditionally_optimize_sanasana_assets', 999);

// ============================================================================
// Beaver Builder Asset Optimization
// ============================================================================
// Conditionally dequeue Beaver Builder frontend assets on pages where BB is
// not used. Detects via FLBuilderModel::is_builder_enabled() or post meta.
// Core assets: fl-builder-layout CSS/JS (generated per-page), global base CSS/JS,
// Font Awesome 5, Foundation Icons, jQuery plugins, animations, slideshow libs.
function p5m_conditionally_optimize_bb_assets() {
  if (is_admin()) return; // Front-end only

  // Check if Beaver Builder plugin is active
  if (!class_exists('FLBuilderModel')) return;

  $is_bb_page = false;
  $post_id = get_the_ID();
  
  // Detect BB-enabled posts
  if ($post_id) {
    if (method_exists('FLBuilderModel', 'is_builder_enabled')) {
      $is_bb_page = FLBuilderModel::is_builder_enabled($post_id);
    } else {
      // Fallback: check post meta
      $is_bb_page = get_post_meta($post_id, '_fl_builder_enabled', true) == '1';
    }
  }

  // If not a BB page, dequeue heavy BB assets
  if (!$is_bb_page) {
    // Core BB layout styles/scripts (auto-generated per-page)
    $bb_layout_handles_styles = [
      'fl-builder-layout-' . $post_id, // Dynamic layout CSS
      'fl-builder-layout-bundle-' . $post_id, // Global + layout bundle
    ];
    $bb_layout_handles_scripts = [
      'fl-builder-layout-' . $post_id,
    ];

    // BB global base assets (loaded on every BB page)
    $bb_global_styles = [
      'font-awesome-5',      // FA5 all.css
      'foundation-icons',    // Foundation icon set
      'fl-slideshow',        // Slideshow CSS
      'fl-builder-layout-bundle', // When no post-specific
    ];
    $bb_global_scripts = [
      'jquery-waypoints',    // Animations / waypoints
      'imagesloaded',        // Image lazy loading helper
      'fl-slideshow',        // Slideshow JS
      'yui3',                // Legacy slideshow dependency
      'youtube-player',      // Video module
      'vimeo-player',        // Video module
    ];

    // Dequeue post-specific handles
    foreach ($bb_layout_handles_styles as $h) {
      wp_dequeue_style($h);
      wp_deregister_style($h);
    }
    foreach ($bb_layout_handles_scripts as $h) {
      wp_dequeue_script($h);
      wp_deregister_script($h);
    }

    // Dequeue global BB handles
    foreach ($bb_global_styles as $h) {
      wp_dequeue_style($h);
      wp_deregister_style($h);
    }
    foreach ($bb_global_scripts as $h) {
      wp_dequeue_script($h);
      wp_deregister_script($h);
    }
  }
}
add_action('wp_enqueue_scripts', 'p5m_conditionally_optimize_bb_assets', 999);

function add_custom_breakpoint(){
  ?>
<script>
  jQuery(document).ready(function() {
    function checkWidth() {
      if (jQuery(window).width() < 1228) {
        jQuery('body').addClass('ast-header-break-point');
        jQuery('body').removeClass('ast-desktop');
        jQuery('#ast-desktop-header').css('display', 'none');
        //console.log(jQuery(window).width());
      } else {
        jQuery('body').removeClass('ast-header-break-point');
        jQuery('body').addClass('ast-desktop');
        jQuery('#ast-desktop-header').css('display', 'block');
      }
    }

    // Verificamos el ancho al cargar la página
    checkWidth();

    // Verificamos el ancho cada vez que se redimensiona la ventana
    jQuery(window).resize(checkWidth);
  });


</script>

<?php
}
// Desactivado: era específico de Astra y ya no aplica en este tema.
// add_action('wp_head', 'add_custom_breakpoint');

//Allow svg
function add_file_types_to_uploads($file_types){
$new_filetypes = array();
$new_filetypes['svg'] = 'image/svg+xml';
$file_types = array_merge($file_types, $new_filetypes );
return $file_types;
}
add_filter('upload_mimes', 'add_file_types_to_uploads');

// Add Frontend Redirection Settings Menu
function add_frontend_redirection_admin_menu() {
    add_submenu_page(
        'options-general.php', // Parent menu (Settings)
        __('Frontend Redirection Settings', 'sanasana'),
        __('Frontend Redirection', 'sanasana'),
        'manage_options',
        'frontend-redirection-settings',
        'render_frontend_redirection_settings_page'
    );
}
add_action('admin_menu', 'add_frontend_redirection_admin_menu');

// Register Frontend Redirection Settings
function register_frontend_redirection_settings() {
    register_setting('frontend_redirection_settings', 'sanasana_frontend_base_url');
    register_setting('frontend_redirection_settings', 'sanasana_plan_id_parameter');
    register_setting('frontend_redirection_settings', 'sanasana_affiliation_path');
    register_setting('frontend_redirection_settings', 'sanasana_login_path');

    add_settings_section(
        'frontend_redirection_main_section',
        __('Frontend Redirection Configuration', 'sanasana'),
        'render_frontend_redirection_section_info',
        'frontend-redirection-settings'
    );

    add_settings_field(
        'sanasana_frontend_base_url',
        __('Frontend Base URL', 'sanasana'),
        'render_frontend_base_url_field',
        'frontend-redirection-settings',
        'frontend_redirection_main_section'
    );

    add_settings_field(
        'sanasana_affiliation_path',
        __('Affiliation Path', 'sanasana'),
        'render_affiliation_path_field',
        'frontend-redirection-settings',
        'frontend_redirection_main_section'
    );
	
    add_settings_field(
        'sanasana_plan_id_parameter',
        __('Plan ID Parameter', 'sanasana'),
        'render_plan_id_parameter_field',
        'frontend-redirection-settings',
        'frontend_redirection_main_section'
    );
	
  add_settings_field(
        'sanasana_login_path',
        __('Login Path', 'sanasana'),
        'render_login_path_field',
        'frontend-redirection-settings',
        'frontend_redirection_main_section'
    );	
}
add_action('admin_init', 'register_frontend_redirection_settings');

// Render Frontend Redirection Settings Page
function render_frontend_redirection_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('frontend_redirection_settings');
            do_settings_sections('frontend-redirection-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Render Frontend Redirection Section Info
function render_frontend_redirection_section_info() {
    echo '<p>' . __('Configure your Frontend Redirection settings below:', 'sanasana') . '</p>';
}

// Render Frontend Base URL Field
function render_frontend_base_url_field() {
    $value = get_option('sanasana_frontend_base_url', '');
    ?>
    <input type="url" 
           name="sanasana_frontend_base_url" 
           value="<?php echo esc_attr($value); ?>" 
           class="regular-text"
           placeholder="https://example.com">
    <p class="description">
        <?php _e('Enter the base URL for your frontend application.', 'sanasana'); ?>
    </p>
    <?php
}

// Render Plan ID Parameter Field
function render_plan_id_parameter_field() {
    $value = get_option('sanasana_plan_id_parameter', '');
    ?>
    <input type="text" 
           name="sanasana_plan_id_parameter" 
           value="<?php echo esc_attr($value); ?>" 
           class="regular-text"
           placeholder="plan_id">
    <p class="description">
        <?php _e('Enter the parameter name used for plan identification.', 'sanasana'); ?>
    </p>
    <?php
}

// Render Affiliation Path Field
function render_affiliation_path_field() {
    $value = get_option('sanasana_affiliation_path', '');
    ?>
    <input type="text" 
           name="sanasana_affiliation_path" 
           value="<?php echo esc_attr($value); ?>" 
           class="regular-text"
           placeholder="/affiliate">
    <p class="description">
        <?php _e('Enter the path used for affiliation redirection.', 'sanasana'); ?>
    </p>
    <?php
}

// Render Login Path Field  
function render_login_path_field() {
    $value = get_option('sanasana_login_path', '');
    ?>
    <input type="text" 
           name="sanasana_login_path" 
           value="<?php echo esc_attr($value); ?>"  
           class="regular-text"
           placeholder="/login">
    <p class="description">
        <?php _e('Enter the path used for login redirection.', 'sanasana'); ?>
    </p>
    <?php
}


//Add Contact Form Settings Menu
function add_contact_form_admin_menu()
{
    add_submenu_page(
        'options-general.php', // Parent menu (Settings)
        __('Contact Form Settings', 'sanasana'),
        __('Contact Form', 'sanasana'),
        'manage_options',
        'contact-form-settings',
        'render_settings_page',
    );
}
add_action('admin_menu', 'add_contact_form_admin_menu');


//Register Contact Form Settings
function register_contact_form_settings()
{
    register_setting('contact_form_settings', 'sanasana_recaptcha_site_key');
    register_setting('contact_form_settings', 'sanasana_api_base_url');
    register_setting('contact_form_settings', 'sanasana_api_contact_form_path');

    add_settings_section(
        'contact_form_main_section',
        __('Contact Form Configuration', 'sanasana'),
        'render_section_info',
        'contact-form-settings'
    );

    add_settings_field(
        'sanasana_recaptcha_site_key',
        __('reCAPTCHA Site Key', 'sanasana'),
        'render_recaptcha_field',
        'contact-form-settings',
        'contact_form_main_section'
    );

    add_settings_field(
        'sanasana_api_base_url',
        __('API Base URL', 'sanasana'),
    'render_api_url_field',
        'contact-form-settings',
        'contact_form_main_section'
    );

    add_settings_field(
        'sanasana_api_contact_form_path',
        __('API Contact Form Path', 'sanasana'),
        'render_api_contact_form_path_field',
        'contact-form-settings',
        'contact_form_main_section'
    );
}
add_action('admin_init', 'register_contact_form_settings');


function render_settings_page()
{
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('contact_form_settings');
            do_settings_sections('contact-form-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

function render_section_info()
{
    echo '<p>' . __('Configure your contact form settings below:', 'sanasana') . '</p>';
}

function render_recaptcha_field()
{
    $value = get_option('sanasana_recaptcha_site_key');
    ?>
    <input type="text" 
           name="sanasana_recaptcha_site_key" 
           value="<?php echo esc_attr($value); ?>" 
           class="regular-text"
           placeholder="reCAPTCHA public key">
    <p class="description">
        <?php _e('Enter your reCAPTCHA site key here.', 'sanasana'); ?>
    </p>
    <?php
}

function render_api_url_field()
{
    $value = get_option('sanasana_api_base_url');
    ?>
    <input type="url" 
           name="sanasana_api_base_url" 
           value="<?php echo esc_attr($value); ?>" 
           class="regular-text"
           placeholder="API base URL">
    <p class="description">
        <?php _e('Enter your API base URL here.', 'sanasana'); ?>
    </p>
    <?php
}

function render_api_contact_form_path_field()
{
    $value = get_option('sanasana_api_contact_form_path');
    ?>
    <input type="text" 
           name="sanasana_api_contact_form_path" 
           value="<?php echo esc_attr($value); ?>" 
           class="regular-text"
           placeholder="API contact form path">
    <p class="description">
        <?php _e('Enter your API contact form path here.', 'sanasana'); ?>
    </p>
    <?php
}


// Add Google Settings Menu
function add_google_admin_menu() {
    add_submenu_page(
        'options-general.php', // Parent menu (Settings)
        __('Google Settings', 'sanasana'),
        __('Google', 'sanasana'),
        'manage_options',
        'google-settings',
        'render_google_settings_page'
    );
}
add_action('admin_menu', 'add_google_admin_menu');

// Register Google Settings
function register_google_settings() {
    register_setting('google_settings', 'sanasana_gtm_id');
    register_setting('google_settings', 'sanasana_ga_id');

    add_settings_section(
        'google_main_section',
        __('Google Configuration', 'sanasana'),
        'render_google_section_info',
        'google-settings'
    );

    add_settings_field(
        'sanasana_gtm_id',
        __('Google Tag Manager ID', 'sanasana'),
        'render_gtm_id_field',
        'google-settings',
        'google_main_section'
    );
	
  add_settings_field(
        'sanasana_ga_id',
        __('Google Analytics ID', 'sanasana'),
        'render_ga_id_field',
        'google-settings',
        'google_main_section'
    );	
}
add_action('admin_init', 'register_google_settings');

// Render Google Settings Page
function render_google_settings_page() {
    if (!current_user_can('manage_options')) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('google_settings');
            do_settings_sections('google-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Render Google Section Info
function render_google_section_info() {
    echo '<p>' . __('Configure your Google settings below:', 'sanasana') . '</p>';
}

// Render GTM ID Field
function render_gtm_id_field() {
    $value = get_option('sanasana_gtm_id');
    ?>
    <input type="text" 
           name="sanasana_gtm_id" 
           value="<?php echo esc_attr($value); ?>" 
           class="regular-text"
           placeholder="GTM-XXXXXX">
    <p class="description">
        <?php _e('Enter your Google Tag Manager ID (e.g., GTM-XXXXXX).', 'sanasana'); ?>
    </p>
    <?php
}

// Render GA ID Field
function render_ga_id_field() {
    $value = get_option('sanasana_ga_id');
    ?>
    <input type="text" 
           name="sanasana_ga_id" 
           value="<?php echo esc_attr($value); ?>" 
           class="regular-text"
           placeholder="UA-XXXXXXXXX-X or G-XXXXXXXXXX">
    <p class="description">
        <?php _e('Enter your Google Analytics ID (e.g., UA-XXXXXXXXX-X or G-XXXXXXXXXX).', 'sanasana'); ?>
    </p>
    <?php
}
/*
function add_gtm_to_header() {
    ?>
    <!-- Google Tag Manager -->
    <script>
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id=' + i + dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','<?php echo get_option('sanasana_gtm_id'); ?>'); 
    </script>
    <!-- End Google Tag Manager -->
    <?php
}*/
function add_gtm_to_header() {
    $gtm_id = get_option('sanasana_gtm_id');
    if (empty($gtm_id)) {
        return; // No imprime nada si no hay ID
    }
    ?>
    <!-- Google Tag Manager -->
    <script>
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        'https://www.googletagmanager.com/gtm.js?id=' + i + dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','<?php echo esc_js($gtm_id); ?>'); 
    </script>
    <!-- End Google Tag Manager -->
    <?php
}

/*
function add_gtm_to_body() {
    ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript><iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo get_option('sanasana_gtm_id'); ?>"
    height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
}
*/
function add_gtm_to_body() {
    $gtm_id = get_option('sanasana_gtm_id');
    if (empty($gtm_id)) {
        return; // No imprime nada si no hay ID
    }
    ?>
    <!-- Google Tag Manager (noscript) -->
    <noscript>
        <iframe src="https://www.googletagmanager.com/ns.html?id=<?php echo esc_attr($gtm_id); ?>"
        height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
    <!-- End Google Tag Manager (noscript) -->
    <?php
}


function add_google_analytics() {
    ?>
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo get_option('sanasana_ga_id'); ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?php echo get_option('sanasana_ga_id'); ?>'); 
    </script>
    <?php
}
/*
// Add GTM to header and body only if domain is sanasana.com
if (strtolower($_SERVER['HTTP_HOST']) === 'sanasana.com') {
    add_action('wp_head', 'add_gtm_to_header');
    add_action('wp_head', 'add_google_analytics');
    add_action('wp_body_open', 'add_gtm_to_body');
}*/
// Deshabilitamos la inyección directa de GTM/GA desde aquí para evitar duplicados.
// p5marketing/inc/seo-analytics.php se encarga de insertar GTM/GA4 y ahora
// incluye fallback a las opciones "sanasana_*" si los ajustes del tema están vacíos.
// Mantener comentado para referencia histórica.
// if (isset($_SERVER['HTTP_HOST']) && preg_match('/(^|\.)sanasana\.com$/', $_SERVER['HTTP_HOST'])) {
//     add_action('wp_head', 'add_gtm_to_header');
//     add_action('wp_head', 'add_google_analytics');
//     add_action('wp_body_open', 'add_gtm_to_body');
// }

//Test image error
// Log de ayuda para detectar llamadas incompletas a image_downsize SOLO en desarrollo
if (defined('WP_DEBUG') && WP_DEBUG) {
  add_filter('image_downsize', function($out, $id, $size) {
      if (is_array($size) && (!isset($size['width']) || !isset($size['height']))) {
          error_log('⚠️ image_downsize called with incomplete size at:');
          error_log(print_r(debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10), true));
      }
      return $out;
  }, 10, 3);
}

add_action('wp_head', function () {
    if (is_admin()) return; // no en admin

    // Host canónico del sitio
    $host = preg_replace('/^www\./', '', parse_url(home_url(), PHP_URL_HOST));

    // Elige clave según dominio
    $key = ($host === 'sanasana.com')
        ? 'mToHRUoAWeqVSKj443GOTQ'      // prod
        : 'hGdhcZnN5sydTuwCud0QXA';    // dev/staging

    echo '<script src="https://analytics.ahrefs.com/analytics.js" data-key="' . esc_attr($key) . '" async></script>';
}, 1);


//Ajustes del sitemap Rankmath 

add_filter( 'rank_math/sitemap/entry', function( $url, $type, $object ) {
    // URL exacta a excluir
    $exclude_url = home_url('/programa/');

    if ( $url === $exclude_url ) {
        return false;
    }

    return $url;
}, 10, 3 );

//rankmath adjust
// functions.php (child)
add_filter('rank_math/sitemap/entry', function($url, $type, $object){
    if (empty($url)) return $url;

    $p    = wp_parse_url($url);
    $path = isset($p['path']) ? rtrim($p['path'], '/') : '';
    $host = isset($p['host']) ? strtolower($p['host']) : '';

    if ($path === '') return $url;

    // 1) Slugs locales a excluir (con o sin /en/)
    $slugs = ['faq-tab', 'resena', 'tabs', 'promocion'];
    foreach ($slugs as $slug) {
        // Coincide /slug o /en/slug (con o sin barra final original)
        if (preg_match('#^/(?:en/)?' . preg_quote($slug, '#') . '$#i', $path)) {
            return ''; // excluir del sitemap
        }
    }

    // 2) URLs del portal a excluir (aplican aunque vengan con query ?lang=... o programId=...)
    if ($host === 'portal.sanasana.com') {
        if (preg_match('#^/(login|affiliate)$#i', $path)) {
            return '';
        }
        // Si algún día aparecen subrutas (p.ej. /affiliate/xyz), usa esta línea en vez de la anterior:
        // if (preg_match('#^/(login|affiliate)(/|$)#i', $path)) return '';
    }

    return $url;
}, 10, 3);

// (Opcional en pruebas) desactivar cache del sitemap para ver cambios al instante:
add_filter('rank_math/sitemap/enable_caching', '__return_false');

/**
 * Forzar nofollow en enlaces que apunten a portal.sanasana.com
 */
add_filter('the_content', function ($content) {
    // Expresión regular para detectar enlaces a portal.sanasana.com
    $pattern = '/<a\s+([^>]*href=["\']https?:\/\/portal\.sanasana\.com[^"\']*["\'][^>]*)>/i';

    // Reemplazar para incluir rel="nofollow noopener noreferrer"
    $replacement = '<a $1 rel="nofollow noopener noreferrer">';

    return preg_replace($pattern, $replacement, $content);
}, 20);


add_filter('rocket_delay_js_exclusions', function($patterns){
  $patterns[] = 'fl-builder';            // cualquier fl-builder*.js
  $patterns[] = 'beaver-builder';        // por si acaso
  return $patterns;
});
add_filter('rocket_exclude_defer_js', function($excluded){
  $excluded[] = 'fl-builder';
  return $excluded;
});

add_action('wp_head', 'nosotros_modal');
function nosotros_modal() {
  echo '
  <style>
    .testimonio-home,
    .nosotros-conoce-mas{
      cursor: pointer;
    }
  </style>
  <script>
  jQuery(document).ready(function () {

    function setIframeSize(iframe) {
      var viewportWidth = jQuery(document).width(); // ancho de la ventana
      var maxWidth = 966; // ancho máximo permitido
      var width = Math.min(viewportWidth * 0.9, maxWidth); // 90% del ancho o máximo
      var height = width * (9 / 16); // relación 16:9
      iframe.attr("width", width);
      iframe.attr("height", height);
    }

    jQuery(".nosotros-conoce-mas").on("click", function (e) {
      e.preventDefault();

      // Elimina el modal previo si ya existe
      jQuery("#video-nosotros-modal").remove();

      // Inserta el modal dinámicamente en el body
      jQuery(".site-main").append(`
        <div id="video-nosotros-modal" class="video-testimonio-container-desktop" style="
          position: fixed;
          top: 0; left: 0;
          width: 100%;
          height: 100%;
          background: rgba(0,0,0,0.5);
          display: flex;
          justify-content: center;
          align-items: center;
          /*z-index: 9999;*/
          flex-direction: column;
        ">
          <span class="close-video" style="
            position: absolute;
            right: 15%;
            top: 15%;
            background: #ffffff;
            width: 40px;
            height: 40px;
            border-radius: 100px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;

          ">&times;</span>
          <iframe src="" frameborder="0" allow="autoplay; fullscreen"></iframe>
        </div>
      `);

      var iframe = jQuery("#video-nosotros-modal").find("iframe");
      var video_url = "https://www.youtube.com/embed/mYaq94wrTiw"; // versión embed
      var separador = video_url.includes("?") ? "&" : "?";

      // Ajusta el tamaño antes de reproducir
      setIframeSize(iframe);

      // Muestra el modal y reproduce el video
      jQuery("#video-nosotros-modal").fadeIn(200);
      iframe.attr("src", video_url + separador + "autoplay=1&rel=0");
    });
		
    /////
    jQuery(".testimonio-home").on("click", function (e) {
      e.preventDefault();

      // Elimina el modal previo si ya existe
      jQuery("#video-nosotros-modal").remove();

      // Inserta el modal dinámicamente en el body
      jQuery(".site-main").append(`
        <div id="video-nosotros-modal" class="video-testimonio-container-desktop" style="
          position: fixed;
          top: 0; left: 0;
          width: 100%;
          height: 100%;
          background: rgba(0,0,0,0.5);
          display: flex;
          justify-content: center;
          align-items: center;
          /*z-index: 9999;*/
          flex-direction: column;
        ">
          <span class="close-video" style="
            position: absolute;
            right: 15%;
            top: 15%;
            background: #ffffff;
            width: 40px;
            height: 40px;
            border-radius: 100px;
            display: flex;
            justify-content: center;
            align-items: center;
            font-size: 18px;

          ">&times;</span>
          <iframe src="" frameborder="0" allow="autoplay; fullscreen"></iframe>
        </div>
      `);

      var iframe = jQuery("#video-nosotros-modal").find("iframe");
      var video_url = "https://www.youtube.com/embed/o56eDhrDTN8?si=bwkEeKP2nZz44aDl"; // versión embed
      var separador = video_url.includes("?") ? "&" : "?";

      // Ajusta el tamaño antes de reproducir
      setIframeSize(iframe);

      // Muestra el modal y reproduce el video
      jQuery("#video-nosotros-modal").fadeIn(200);
      iframe.attr("src", video_url + separador + "autoplay=1&rel=0");
    });

    // Cerrar el video
    jQuery(document).on("click", ".close-video", function () {
      jQuery("#video-nosotros-modal").fadeOut(200, function() {
        jQuery(this).remove(); // elimina completamente el modal
      });
    });

    // Reajustar tamaño si cambia el viewport
    jQuery(window).on("resize", function () {
      var iframe = jQuery("#video-nosotros-modal").find("iframe");
      if (iframe.length) {
        setIframeSize(iframe);
      }
    });
  });
  </script>
  <style>
    .nosotros-conoce-mas{
      cursor: pointer;
    }
  </style>
  ';
}
