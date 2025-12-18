<?php
/**
 * Contact Form Admin Settings
 */
if (!defined('ABSPATH')) exit;

function add_contact_form_admin_menu() {
    add_submenu_page(
        'options-general.php',
        __('Contact Form Settings', 'sanasana'),
        __('Contact Form', 'sanasana'),
        'manage_options',
        'contact-form-settings',
        'render_settings_page'
    );
}
add_action('admin_menu', 'add_contact_form_admin_menu');

function register_contact_form_settings() {
    register_setting('contact_form_settings', 'sanasana_recaptcha_site_key');
    register_setting('contact_form_settings', 'sanasana_api_base_url');
    register_setting('contact_form_settings', 'sanasana_api_contact_form_path');

    add_settings_section('contact_form_main_section', __('Contact Form Configuration', 'sanasana'), 'render_section_info', 'contact-form-settings');
    add_settings_field('sanasana_recaptcha_site_key', __('reCAPTCHA Site Key', 'sanasana'), 'render_recaptcha_field', 'contact-form-settings', 'contact_form_main_section');
    add_settings_field('sanasana_api_base_url', __('API Base URL', 'sanasana'), 'render_api_url_field', 'contact-form-settings', 'contact_form_main_section');
    add_settings_field('sanasana_api_contact_form_path', __('API Contact Form Path', 'sanasana'), 'render_api_contact_form_path_field', 'contact-form-settings', 'contact_form_main_section');
}
add_action('admin_init', 'register_contact_form_settings');

function render_settings_page() { if (!current_user_can('manage_options')) return; ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php settings_fields('contact_form_settings'); do_settings_sections('contact-form-settings'); submit_button(); ?>
        </form>
    </div>
<?php }

function render_section_info() { echo '<p>' . __('Configure your contact form settings below:', 'sanasana') . '</p>'; }

function render_recaptcha_field() { $value = get_option('sanasana_recaptcha_site_key'); ?>
    <input type="text" name="sanasana_recaptcha_site_key" value="<?php echo esc_attr($value); ?>" class="regular-text" placeholder="reCAPTCHA public key">
    <p class="description"><?php _e('Enter your reCAPTCHA site key here.', 'sanasana'); ?></p>
<?php }

function render_api_url_field() { $value = get_option('sanasana_api_base_url'); ?>
    <input type="url" name="sanasana_api_base_url" value="<?php echo esc_attr($value); ?>" class="regular-text" placeholder="API base URL">
    <p class="description"><?php _e('Enter your API base URL here.', 'sanasana'); ?></p>
<?php }

function render_api_contact_form_path_field() { $value = get_option('sanasana_api_contact_form_path'); ?>
    <input type="text" name="sanasana_api_contact_form_path" value="<?php echo esc_attr($value); ?>" class="regular-text" placeholder="API contact form path">
    <p class="description"><?php _e('Enter your API contact form path here.', 'sanasana'); ?></p>
<?php }
