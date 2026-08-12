<?php
if (!defined('ABSPATH')) exit;

/**
 * Spielend Entdecken – Theme Options (tab-based admin page).
 * Speichert unter den spielend_* Keys, die das Plugin (spielend-essentials)
 * und das Theme tatsächlich lesen (get_theme_mod → get_option Fallback).
 */

function se_theme_options_page() {
    add_menu_page(
        'Spielend Entdecken',
        'Spielend Entdecken',
        'manage_options',
        'spielend-theme-options',
        'se_render_options_page',
        'dashicons-toys',
        58
    );
    add_submenu_page(
        'spielend-theme-options',
        'Theme Optionen',
        'Theme Optionen',
        'manage_options',
        'spielend-theme-options'
    );
}
add_action('admin_menu', 'se_theme_options_page');

function se_theme_options_tabs() {
    $tabs = array(
        'general'   => array('label' => 'Allgemein', 'icon' => 'dashicons-admin-home'),
        'hero'      => array('label' => 'Startseite / Hero', 'icon' => 'dashicons-format-video'),
        'contact'   => array('label' => 'Kontakt', 'icon' => 'dashicons-phone'),
        'social'    => array('label' => 'Social Media', 'icon' => 'dashicons-share'),
        'newsletter' => array('label' => 'Newsletter', 'icon' => 'dashicons-email'),
        'shop'      => array('label' => 'Shop & Versand', 'icon' => 'dashicons-cart'),
    );
    return $tabs;
}

function se_theme_option_field_defs() {
    return array(
        // Allgemein
        'spielend_brand_claim'    => array('label' => 'Claim / Kurzbeschreibung', 'tab' => 'general', 'type' => 'textarea', 'default' => 'Hochwertiges Spielzeug für neugierige Kinder – sicher, nachhaltig, kreativ. Seit 1902.'),
        'spielend_opening_hours'  => array('label' => 'Öffnungszeiten', 'tab' => 'general', 'type' => 'textarea', 'default' => "Mo–Fr: 10:00–13:00 & 14:00–18:00\nSa: 10:00–14:00"),
        'spielend_footer_year'    => array('label' => 'Footer Jahreszahl (leer = aktuelles Jahr)', 'tab' => 'general', 'type' => 'text', 'default' => ''),

        // Hero
        'spielend_hero_title'     => array('label' => 'Hero Titel', 'tab' => 'hero', 'type' => 'text', 'default' => 'Entdecke die Welt des Spielens'),
        'spielend_hero_subtitle'  => array('label' => 'Hero Untertitel', 'tab' => 'hero', 'type' => 'textarea', 'default' => 'Hochwertiges Spielzeug für neugierige Kinder – sicher, nachhaltig, kreativ'),
        'spielend_hero_cta_text'  => array('label' => 'Hero Button Text', 'tab' => 'hero', 'type' => 'text', 'default' => 'Jetzt stöbern'),
        'spielend_hero_cta_url'   => array('label' => 'Hero Button URL', 'tab' => 'hero', 'type' => 'url', 'default' => '/shop'),
        'spielend_hero_cta2_text' => array('label' => 'Hero Button 2 Text (optional)', 'tab' => 'hero', 'type' => 'text', 'default' => ''),
        'spielend_hero_cta2_url'  => array('label' => 'Hero Button 2 URL', 'tab' => 'hero', 'type' => 'url', 'default' => ''),
        'spielend_hero_video_url' => array('label' => 'Hero Video URL (MP4)', 'tab' => 'hero', 'type' => 'url', 'default' => ''),
        'spielend_hero_poster'    => array('label' => 'Hero Poster-Bild URL (Fallback)', 'tab' => 'hero', 'type' => 'url', 'default' => ''),
        'spielend_hero_badges'    => array('label' => 'Trust-Badges (eine pro Zeile: Text|Link)', 'tab' => 'hero', 'type' => 'textarea', 'default' => "Seit 1902||Gratis Versand ab 50 €|/versand||Über 130 Produkte||" ),

        // Kontakt (Plugin nutzt exakt diese Keys)
        'spielend_contact_phone'   => array('label' => 'Telefon', 'tab' => 'contact', 'type' => 'text', 'default' => '+49 (0)2151 - 970267'),
        'spielend_contact_email'   => array('label' => 'E-Mail', 'tab' => 'contact', 'type' => 'text', 'default' => 'info@spielend-entdecken.de'),
        'spielend_contact_address' => array('label' => 'Adresse', 'tab' => 'contact', 'type' => 'textarea', 'default' => "Hochstraße 57\n47918 Tönisvorst"),
        'spielend_contact_whatsapp' => array('label' => 'WhatsApp Nummer', 'tab' => 'contact', 'type' => 'text', 'default' => '0152 - 22 41 47 91'),

        // Social (Plugin liest spielend_social_{key})
        'spielend_social_facebook'  => array('label' => 'Facebook URL', 'tab' => 'social', 'type' => 'url', 'default' => ''),
        'spielend_social_instagram' => array('label' => 'Instagram URL', 'tab' => 'social', 'type' => 'url', 'default' => ''),
        'spielend_social_tiktok'    => array('label' => 'TikTok URL', 'tab' => 'social', 'type' => 'url', 'default' => ''),
        'spielend_social_pinterest' => array('label' => 'Pinterest URL', 'tab' => 'social', 'type' => 'url', 'default' => ''),
        'spielend_social_youtube'   => array('label' => 'YouTube URL', 'tab' => 'social', 'type' => 'url', 'default' => ''),
        'spielend_social_x'         => array('label' => 'X / Twitter URL', 'tab' => 'social', 'type' => 'url', 'default' => ''),

        // Newsletter
        'spielend_newsletter_service' => array('label' => 'Service (mailchimp / brevo / leer = WP-intern)', 'tab' => 'newsletter', 'type' => 'text', 'default' => ''),
        'spielend_newsletter_api_key' => array('label' => 'API Key', 'tab' => 'newsletter', 'type' => 'text', 'default' => ''),
        'spielend_newsletter_discount' => array('label' => 'Rabatt in %', 'tab' => 'newsletter', 'type' => 'text', 'default' => '10'),

        // Shop
        'spielend_shipping_info'      => array('label' => 'Versandinformationen', 'tab' => 'shop', 'type' => 'textarea', 'default' => 'Versandkosten: 4,90 € – ab 50 € Bestellwert versandkostenfrei.'),
        'spielend_free_shipping_min'  => array('label' => 'Versandkostenfrei ab (€)', 'tab' => 'shop', 'type' => 'text', 'default' => '50'),
        'spielend_shipping_cost'      => array('label' => 'Versandkosten (€)', 'tab' => 'shop', 'type' => 'text', 'default' => '4.90'),
    );
}

function se_register_settings() {
    foreach (array_keys(se_theme_option_field_defs()) as $field) {
        register_setting('spielend_theme_options', $field, 'sanitize_text_field');
    }
}
add_action('admin_init', 'se_register_settings');

function se_render_options_page() {
    $tabs = se_theme_options_tabs();
    $active_tab = isset($_GET['tab']) && isset($tabs[$_GET['tab']]) ? sanitize_key(wp_unslash($_GET['tab'])) : 'general';
    ?>
<div class="wrap se-admin-wrap">
    <h1 style="display:flex;align-items:center;gap:10px;">
        <span class="dashicons dashicons-toys" style="font-size:28px;width:28px;height:28px;color:#FF6B35;"></span>
        Spielend Entdecken – Theme Optionen
    </h1>

    <nav class="nav-tab-wrapper se-admin-tabs">
        <?php foreach ($tabs as $key => $tab) : ?>
            <a href="?page=spielend-theme-options&tab=<?php echo esc_attr($key); ?>"
               class="nav-tab <?php echo $active_tab === $key ? 'nav-tab-active' : ''; ?>">
                <span class="dashicons <?php echo esc_attr($tab['icon']); ?>" style="vertical-align:middle;margin-right:4px;"></span>
                <?php echo esc_html($tab['label']); ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <form method="post" action="options.php" class="se-admin-form">
        <?php settings_fields('spielend_theme_options'); ?>

        <?php foreach (se_theme_option_field_defs() as $key => $def) : ?>
            <?php if ($def['tab'] !== $active_tab) continue; ?>
            <?php se_render_field($key, $def); ?>
        <?php endforeach; ?>

        <?php if (in_array($active_tab, array('hero', 'social', 'contact'), true)) : ?>
            <p class="description" style="margin-top:-8px;margin-bottom:16px;">
                Diese Werte werden direkt auf der Website gerendert. Leere Social-URLs werden automatisch ausgeblendet.
            </p>
        <?php endif; ?>

        <?php submit_button('Änderungen speichern'); ?>
    </form>

    <hr>
    <h2>Status</h2>
    <table class="widefat striped" style="max-width:700px;">
        <tr><td><strong>Theme</strong></td><td><?php echo esc_html(wp_get_theme()->get('Name')); ?></td></tr>
        <tr><td><strong>WooCommerce</strong></td><td><?php echo class_exists('WooCommerce') ? esc_html(WC()->version) : 'nicht aktiv'; ?></td></tr>
        <tr><td><strong>Essentials-Plugin</strong></td><td><?php echo class_exists('Spielend_Essentials') ? 'aktiv' : 'nicht aktiv'; ?></td></tr>
        <tr><td><strong>Hero-Video</strong></td><td><?php $v = get_option('spielend_hero_video_url', ''); echo $v ? esc_html(basename($v)) : 'Standard (Uploads)'; ?></td></tr>
        <tr><td><strong>Produkte</strong></td><td><?php echo class_exists('WooCommerce') ? wp_count_posts('product')->publish : '0'; ?></td></tr>
    </table>
</div>
    <?php
}

function se_render_field($option, $def) {
    $value = get_option($option, '');
    if ('' === $value && isset($def['default'])) {
        $value = $def['default'];
    }
    $type = isset($def['type']) ? $def['type'] : 'text';
    ?>
<div class="se-admin-field">
    <div class="se-admin-field-label">
        <label for="<?php echo esc_attr($option); ?>"><?php echo esc_html($def['label']); ?></label>
    </div>
    <div class="se-admin-field-input">
        <?php if ('textarea' === $type) : ?>
            <textarea name="<?php echo esc_attr($option); ?>" id="<?php echo esc_attr($option); ?>" rows="3" class="large-text"><?php echo esc_textarea($value); ?></textarea>
        <?php else : ?>
            <input type="<?php echo esc_attr($type); ?>" name="<?php echo esc_attr($option); ?>" id="<?php echo esc_attr($option); ?>"
                   value="<?php echo esc_attr($value); ?>" class="regular-text" />
        <?php endif; ?>
    </div>
</div>
    <?php
}

/** Bequemer Helfer für das Theme / Frontend: spielend_opt() */
function spielend_opt($key, $default = '') {
    $value = get_theme_mod($key, '');
    if ('' === $value) {
        $value = get_option($key, '');
    }
    if ('' === $value && '' !== $default) {
        $value = $default;
    }
    return is_string($value) ? $value : '';
}

/** Inline-Admin-CSS nur auf der Options-Seite laden */function se_admin_page_styles() {
    $screen = get_current_screen();
    if (!$screen || 'toplevel_page_spielend-theme-options' !== $screen->id) {
        return;
    }
    echo '<style>
    .se-admin-wrap{max-width:960px}
    .se-admin-tabs{margin-bottom:20px}
    .se-admin-form{background:#fff;border:1px solid #e0e0e0;border-radius:8px;padding:24px 28px;max-width:760px}
    .se-admin-field{display:flex;flex-wrap:wrap;gap:8px 16px;padding:12px 0;border-bottom:1px solid #f0f0f0}
    .se-admin-field-label{flex:0 0 280px;font-weight:600;padding-top:6px}
    .se-admin-field-input{flex:1 1 380px}
    .se-admin-field-input input,.se-admin-field-input textarea{max-width:100%}
    .se-admin-field-input .description{color:#666}
    </style>';
}
add_action('admin_head', 'se_admin_page_styles');
