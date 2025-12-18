<?php
/**
 * Google (GTM/GA) Admin Settings
 */
if (!defined('ABSPATH')) exit;

function add_google_admin_menu() {
    add_submenu_page(
        'options-general.php',
        __('Google Settings', 'sanasana'),
        __('Google', 'sanasana'),
        'manage_options',
        'google-settings',
        'render_google_settings_page'
    );
}
add_action('admin_menu', 'add_google_admin_menu');

function register_google_settings() {
    register_setting('google_settings', 'sanasana_gtm_id');
    register_setting('google_settings', 'sanasana_ga_id');

    add_settings_section('google_main_section', __('Google Configuration', 'sanasana'), 'render_google_section_info', 'google-settings');
    add_settings_field('sanasana_gtm_id', __('Google Tag Manager ID', 'sanasana'), 'render_gtm_id_field', 'google-settings', 'google_main_section');
    add_settings_field('sanasana_ga_id', __('Google Analytics ID', 'sanasana'), 'render_ga_id_field', 'google-settings', 'google_main_section');
}
add_action('admin_init', 'register_google_settings');

function render_google_settings_page() { if (!current_user_can('manage_options')) return; ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php settings_fields('google_settings'); do_settings_sections('google-settings'); submit_button(); ?>
        </form>
    </div>
<?php }

function render_google_section_info() { echo '<p>' . __('Configure your Google settings below:', 'sanasana') . '</p>'; }

function render_gtm_id_field() { $value = get_option('sanasana_gtm_id'); ?>
    <input type="text" name="sanasana_gtm_id" value="<?php echo esc_attr($value); ?>" class="regular-text" placeholder="GTM-XXXXXX">
    <p class="description"><?php _e('Enter your Google Tag Manager ID (e.g., GTM-XXXXXX).', 'sanasana'); ?></p>
<?php }

function render_ga_id_field() { $value = get_option('sanasana_ga_id'); ?>
    <input type="text" name="sanasana_ga_id" value="<?php echo esc_attr($value); ?>" class="regular-text" placeholder="UA-XXXXXXXXX-X or G-XXXXXXXXXX">
    <p class="description"><?php _e('Enter your Google Analytics ID (e.g., UA-XXXXXXXXX-X or G-XXXXXXXXXX).', 'sanasana'); ?></p>
<?php }
