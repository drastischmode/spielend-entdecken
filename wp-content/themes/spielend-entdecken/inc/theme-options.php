<?php
if (!defined('ABSPATH')) exit;

function se_theme_options_page() {
    add_options_page(
        __('Theme Options', 'spielend-entdecken'),
        __('Theme Options', 'spielend-entdecken'),
        'manage_options',
        'spielend-theme-options',
        'se_render_options_page'
    );
}
add_action('admin_menu', 'se_theme_options_page');

function se_register_settings() {
    $fields = [
        'se_social_facebook', 'se_social_instagram', 'se_social_tiktok',
        'se_social_pinterest', 'se_social_youtube',
        'se_contact_email', 'se_contact_phone', 'se_contact_address',
        'se_newsletter_api_key', 'se_newsletter_service',
        'se_accent_color', 'se_logo_url',
        'se_shipping_info'
    ];
    foreach ($fields as $field) {
        register_setting('se_theme_options', $field, 'sanitize_text_field');
    }
}
add_action('admin_init', 'se_register_settings');

function se_render_options_page() {
?>
<div class="wrap">
    <h1><?php _e('Theme Options', 'spielend-entdecken'); ?></h1>
    <form method="post" action="options.php">
        <?php settings_fields('se_theme_options'); ?>

        <h2><?php _e('Social Media', 'spielend-entdecken'); ?></h2>
        <table class="form-table">
            <?php se_render_field('se_social_facebook', 'Facebook URL'); ?>
            <?php se_render_field('se_social_instagram', 'Instagram URL'); ?>
            <?php se_render_field('se_social_tiktok', 'TikTok URL'); ?>
            <?php se_render_field('se_social_pinterest', 'Pinterest URL'); ?>
            <?php se_render_field('se_social_youtube', 'YouTube URL'); ?>
        </table>

        <h2><?php _e('Contact', 'spielend-entdecken'); ?></h2>
        <table class="form-table">
            <?php se_render_field('se_contact_email', 'Email'); ?>
            <?php se_render_field('se_contact_phone', 'Telefon'); ?>
            <?php se_render_field('se_contact_address', 'Adresse', true); ?>
        </table>

        <h2><?php _e('Newsletter', 'spielend-entdecken'); ?></h2>
        <table class="form-table">
            <?php se_render_field('se_newsletter_service', 'Service (mailchimp/brevo)'); ?>
            <?php se_render_field('se_newsletter_api_key', 'API Key'); ?>
        </table>

        <h2><?php _e('Design', 'spielend-entdecken'); ?></h2>
        <table class="form-table">
            <?php se_render_field('se_accent_color', 'Akzentfarbe (Hex)', false, 'color'); ?>
            <?php se_render_field('se_logo_url', 'Logo URL', false, 'url'); ?>
        </table>

        <h2><?php _e('Shop', 'spielend-entdecken'); ?></h2>
        <table class="form-table">
            <?php se_render_field('se_shipping_info', 'Versandinformationen', true); ?>
        </table>

        <?php submit_button(); ?>
    </form>
</div>
<?php
}

function se_render_field($option, $label, $textarea = false, $type = 'text') {
    $value = get_option($option, '');
?>
<tr>
    <th scope="row"><label for="<?php echo $option; ?>"><?php echo $label; ?></label></th>
    <td>
        <?php if ($textarea): ?>
            <textarea name="<?php echo $option; ?>" id="<?php echo $option; ?>" class="regular-text" rows="3"><?php echo esc_textarea($value); ?></textarea>
        <?php else: ?>
            <input type="<?php echo $type; ?>" name="<?php echo $option; ?>" id="<?php echo $option; ?>" value="<?php echo esc_attr($value); ?>" class="regular-text" />
        <?php endif; ?>
    </td>
</tr>
<?php
}

function se_theme_option($key, $default = '') {
    return get_option($key, $default);
}
