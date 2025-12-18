<?php
/**
 * Hotjar Admin Settings
 */
if (!defined('ABSPATH')) exit;

// Hotjar script injection (uses option sanasana_hotjar_id)
function hotjar() {
    $hotjar_id = get_option('sanasana_hotjar_id');
    $hotjar_sv = 6;
    if (empty($hotjar_id)) return;
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

// Admin menu entry
function add_hotjar_admin_menu() {
    add_submenu_page(
        'options-general.php',
        __('Hotjar Settings', 'sanasana'),
        __('Hotjar', 'sanasana'),
        'manage_options',
        'hotjar-settings',
        'render_hotjar_settings_page'
    );
}
add_action('admin_menu', 'add_hotjar_admin_menu');

// Register settings
function register_hotjar_settings() {
    register_setting('hotjar_settings', 'sanasana_hotjar_id');
    add_settings_section('hotjar_main_section', __('Hotjar Configuration', 'sanasana'), 'render_hotjar_section_info', 'hotjar-settings');
    add_settings_field('sanasana_hotjar_id', __('Hotjar ID', 'sanasana'), 'render_hotjar_id_field', 'hotjar-settings', 'hotjar_main_section');
}
add_action('admin_init', 'register_hotjar_settings');

function render_hotjar_settings_page() {
    if (!current_user_can('manage_options')) return;
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php settings_fields('hotjar_settings'); do_settings_sections('hotjar-settings'); submit_button(); ?>
        </form>
    </div>
    <?php
}

function render_hotjar_section_info() { echo '<p>' . __('Configure your Hotjar settings below:', 'sanasana') . '</p>'; }

function render_hotjar_id_field() {
    $value = get_option('sanasana_hotjar_id');
    ?>
    <input type="text" name="sanasana_hotjar_id" value="<?php echo esc_attr($value); ?>" class="regular-text" placeholder="Hotjar ID">
    <p class="description"><?php _e('Enter your Hotjar ID here.', 'sanasana'); ?></p>
    <?php
}
