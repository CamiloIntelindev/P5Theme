<?php
/**
 * Frontend Redirection Admin Settings
 */
if (!defined('ABSPATH')) exit;

function add_frontend_redirection_admin_menu() {
    add_submenu_page(
        'options-general.php',
        __('Frontend Redirection Settings', 'sanasana'),
        __('Frontend Redirection', 'sanasana'),
        'manage_options',
        'frontend-redirection-settings',
        'render_frontend_redirection_settings_page'
    );
}
add_action('admin_menu', 'add_frontend_redirection_admin_menu');

function register_frontend_redirection_settings() {
    register_setting('frontend_redirection_settings', 'sanasana_frontend_base_url');
    register_setting('frontend_redirection_settings', 'sanasana_plan_id_parameter');
    register_setting('frontend_redirection_settings', 'sanasana_affiliation_path');
    register_setting('frontend_redirection_settings', 'sanasana_login_path');

    add_settings_section('frontend_redirection_main_section', __('Frontend Redirection Configuration', 'sanasana'), 'render_frontend_redirection_section_info', 'frontend-redirection-settings');
    add_settings_field('sanasana_frontend_base_url', __('Frontend Base URL', 'sanasana'), 'render_frontend_base_url_field', 'frontend-redirection-settings', 'frontend_redirection_main_section');
    add_settings_field('sanasana_affiliation_path', __('Affiliation Path', 'sanasana'), 'render_affiliation_path_field', 'frontend-redirection-settings', 'frontend_redirection_main_section');
    add_settings_field('sanasana_plan_id_parameter', __('Plan ID Parameter', 'sanasana'), 'render_plan_id_parameter_field', 'frontend-redirection-settings', 'frontend_redirection_main_section');
    add_settings_field('sanasana_login_path', __('Login Path', 'sanasana'), 'render_login_path_field', 'frontend-redirection-settings', 'frontend_redirection_main_section');
}
add_action('admin_init', 'register_frontend_redirection_settings');

function render_frontend_redirection_settings_page() {
    if (!current_user_can('manage_options')) return;
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form action="options.php" method="post">
            <?php settings_fields('frontend_redirection_settings'); do_settings_sections('frontend-redirection-settings'); submit_button(); ?>
        </form>
    </div>
    <?php
}

function render_frontend_redirection_section_info() { echo '<p>' . __('Configure your Frontend Redirection settings below:', 'sanasana') . '</p>'; }

function render_frontend_base_url_field() { $value = get_option('sanasana_frontend_base_url', ''); ?>
    <input type="url" name="sanasana_frontend_base_url" value="<?php echo esc_attr($value); ?>" class="regular-text" placeholder="https://example.com">
    <p class="description"><?php _e('Enter the base URL for your frontend application.', 'sanasana'); ?></p>
<?php }

function render_plan_id_parameter_field() { $value = get_option('sanasana_plan_id_parameter', ''); ?>
    <input type="text" name="sanasana_plan_id_parameter" value="<?php echo esc_attr($value); ?>" class="regular-text" placeholder="plan_id">
    <p class="description"><?php _e('Enter the parameter name used for plan identification.', 'sanasana'); ?></p>
<?php }

function render_affiliation_path_field() { $value = get_option('sanasana_affiliation_path', ''); ?>
    <input type="text" name="sanasana_affiliation_path" value="<?php echo esc_attr($value); ?>" class="regular-text" placeholder="/affiliate">
    <p class="description"><?php _e('Enter the path used for affiliation redirection.', 'sanasana'); ?></p>
<?php }

function render_login_path_field() { $value = get_option('sanasana_login_path', ''); ?>
    <input type="text" name="sanasana_login_path" value="<?php echo esc_attr($value); ?>" class="regular-text" placeholder="/login">
    <p class="description"><?php _e('Enter the path used for login redirection.', 'sanasana'); ?></p>
<?php }
