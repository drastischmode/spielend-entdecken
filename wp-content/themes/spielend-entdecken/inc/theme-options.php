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
        'spielend_brand_claim'    => array('label' => 'Claim / Kurzbeschreibung', 'tab' => 'general', 'type' => 'textarea', 'default' => 'Hochwertiges Spielzeug für neugierige Kinder – sicher, nachhaltig, kreativ. Seit 1902.', 'desc' => 'Wird u. a. in der Fußzeile und als SEO-Beschreibung verwendet.'),
        'spielend_opening_hours'  => array('label' => 'Öffnungszeiten', 'tab' => 'general', 'type' => 'textarea', 'default' => "Mo–Fr: 10:00–13:00 & 14:00–18:00\nSa: 10:00–14:00", 'desc' => 'Eine Zeile pro Tag/Zeitfenster.'),
        'spielend_footer_year'    => array('label' => 'Footer Jahreszahl', 'tab' => 'general', 'type' => 'text', 'default' => '', 'desc' => 'Leer lassen = aktuelles Jahr wird automatisch angezeigt.'),

        // Hero
        'spielend_hero_title'     => array('label' => 'Hero Titel', 'tab' => 'hero', 'type' => 'text', 'default' => 'Entdecke die Welt des Spielens', 'desc' => 'Die große Überschrift oben auf der Startseite.'),
        'spielend_hero_subtitle'  => array('label' => 'Hero Untertitel', 'tab' => 'hero', 'type' => 'textarea', 'default' => 'Hochwertiges Spielzeug für neugierige Kinder – sicher, nachhaltig, kreativ', 'desc' => 'Kurzer Text unter dem Titel.'),
        'spielend_hero_cta_text'  => array('label' => 'Haupt-Button Text', 'tab' => 'hero', 'type' => 'text', 'default' => 'Jetzt stöbern', 'desc' => 'Beschriftung des orangen Buttons.'),
        'spielend_hero_cta_url'   => array('label' => 'Haupt-Button Link', 'tab' => 'hero', 'type' => 'url', 'default' => '/shop', 'desc' => 'Ziel des Haupt-Buttons, z. B. /shop oder /produkt-kategorie/holzspielzeug/'),
        'spielend_hero_cta2_text' => array('label' => 'Zweiter Button Text', 'tab' => 'hero', 'type' => 'text', 'default' => '', 'desc' => 'Optional – leer lassen, um den zweiten Button auszublenden.'),
        'spielend_hero_cta2_url'  => array('label' => 'Zweiter Button Link', 'tab' => 'hero', 'type' => 'url', 'default' => '', 'desc' => 'Ziel des zweiten (umrandeten) Buttons.'),
        'spielend_hero_video_url' => array('label' => 'Hero Video URL', 'tab' => 'hero', 'type' => 'url', 'default' => '', 'desc' => 'MP4-Link zum Video im Hintergrund. Leer = Standard-Video wird verwendet.'),
        'spielend_hero_poster'    => array('label' => 'Hero Poster-Bild', 'tab' => 'hero', 'type' => 'url', 'default' => '', 'desc' => 'Fallback-Bild (URL), falls das Video nicht lädt.'),
        'spielend_hero_badges'    => array('label' => 'Trust-Badges unter dem Hero', 'tab' => 'hero', 'type' => 'textarea', 'default' => "Seit 1902||Gratis Versand ab 50 €|/versand||Über 130 Produkte||", 'desc' => 'Eine pro Zeile: Text|Link. Link ist optional. Verwende || zwischen Einträgen.'),
        // Hinweis-Box für Hero
        'spielend_hero_info'      => array('label' => '', 'tab' => 'hero', 'type' => 'note', 'default' => '', 'desc' => 'Tipp: Lege Videos am besten in Medien → Bibliothek hoch und kopiere die URL hierher. So wird kein externer Dienst benötigt.'),

        // Kontakt (Plugin nutzt exakt diese Keys)
        'spielend_contact_phone'   => array('label' => 'Telefon', 'tab' => 'contact', 'type' => 'text', 'default' => '+49 (0)2151 - 970267'),
        'spielend_contact_email'   => array('label' => 'E-Mail', 'tab' => 'contact', 'type' => 'text', 'default' => 'info@spielend-entdecken.de'),
        'spielend_contact_address' => array('label' => 'Adresse', 'tab' => 'contact', 'type' => 'textarea', 'default' => "Hochstraße 57\n47918 Tönisvorst"),
        'spielend_contact_whatsapp' => array('label' => 'WhatsApp Nummer', 'tab' => 'contact', 'type' => 'text', 'default' => '0152 - 22 41 47 91', 'desc' => 'Mit Ländervorwahl, ohne Sonderzeichen ist am sichersten (z. B. 4915222414791).'),

        // Social (Plugin liest spielend_social_{key})
        'spielend_social_facebook'  => array('label' => 'Facebook URL', 'tab' => 'social', 'type' => 'url', 'default' => '', 'desc' => 'Leer lassen = Icon wird ausgeblendet.'),
        'spielend_social_instagram' => array('label' => 'Instagram URL', 'tab' => 'social', 'type' => 'url', 'default' => '', 'desc' => 'Leer lassen = Icon wird ausgeblendet.'),
        'spielend_social_tiktok'    => array('label' => 'TikTok URL', 'tab' => 'social', 'type' => 'url', 'default' => '', 'desc' => 'Leer lassen = Icon wird ausgeblendet.'),
        'spielend_social_pinterest' => array('label' => 'Pinterest URL', 'tab' => 'social', 'type' => 'url', 'default' => '', 'desc' => 'Leer lassen = Icon wird ausgeblendet.'),
        'spielend_social_youtube'   => array('label' => 'YouTube URL', 'tab' => 'social', 'type' => 'url', 'default' => '', 'desc' => 'Leer lassen = Icon wird ausgeblendet.'),
        'spielend_social_x'         => array('label' => 'X / Twitter URL', 'tab' => 'social', 'type' => 'url', 'default' => '', 'desc' => 'Leer lassen = Icon wird ausgeblendet.'),

        // Newsletter
        'spielend_newsletter_service' => array('label' => 'Service', 'tab' => 'newsletter', 'type' => 'text', 'default' => '', 'desc' => 'mailchimp oder brevo. Leer = einfaches internes Formular ohne API.'),
        'spielend_newsletter_api_key' => array('label' => 'API Key', 'tab' => 'newsletter', 'type' => 'text', 'default' => '', 'desc' => 'Nur nötig, wenn ein Service oben angegeben ist.'),
        'spielend_newsletter_discount' => array('label' => 'Willkommens-Rabatt (%)', 'tab' => 'newsletter', 'type' => 'text', 'default' => '10', 'desc' => 'Rabatt in Prozent, den Newsletter-Abonnenten erhalten.'),

        // Shop
        'spielend_shipping_info'      => array('label' => 'Versandinformationen', 'tab' => 'shop', 'type' => 'textarea', 'default' => 'Versandkosten: 4,90 € – ab 50 € Bestellwert versandkostenfrei.'),
        'spielend_free_shipping_min'  => array('label' => 'Versandkostenfrei ab (€)', 'tab' => 'shop', 'type' => 'text', 'default' => '50', 'desc' => 'Bestellwert, ab dem der Versand kostenlos ist.'),
        'spielend_shipping_cost'      => array('label' => 'Versandkosten (€)', 'tab' => 'shop', 'type' => 'text', 'default' => '4.90', 'desc' => 'Standard-Versandkosten in Euro (z. B. 4.90).'),
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
        <span class="dashicons dashicons-toys" style="font-size:28px;width:28px;height:28px;color:#CC4D00;"></span>
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

        <?php if ('hero' === $active_tab) : ?>
            <div class="se-admin-note">
                <strong>So sieht dein Startbereich aus:</strong> Titel → Untertitel → Buttons → Vertrauens-Punkte.
                Alle Felder unten sind optional – leere Felder werden einfach ausgeblendet.
            </div>
        <?php elseif ('general' === $active_tab) : ?>
            <div class="se-admin-note">
                <strong>Allgemeine Angaben</strong> – werden vor allem im Footer und für SEO verwendet.
            </div>
        <?php elseif ('contact' === $active_tab) : ?>
            <div class="se-admin-note">
                <strong>Kontaktdaten</strong> – erscheinen auf der Kontaktseite und im Footer. So erreichen Kunden dich.
            </div>
        <?php elseif ('social' === $active_tab) : ?>
            <div class="se-admin-note">
                <strong>Social Media</strong> – Leere Felder werden automatisch ausgeblendet. Trage nur die Profile ein, die du wirklich nutzt.
            </div>
        <?php elseif ('newsletter' === $active_tab) : ?>
            <div class="se-admin-note">
                <strong>Newsletter</strong> – Für einen einfachen Start reicht das interne Formular (Service leer lassen).
            </div>
        <?php elseif ('shop' === $active_tab) : ?>
            <div class="se-admin-note">
                <strong>Shop &amp; Versand</strong> – Zeigt Versand-Infos auf der Website. Die tatsächlichen Versandkosten legst du unter WooCommerce → Einstellungen fest.
            </div>
        <?php endif; ?>

        <?php foreach (se_theme_option_field_defs() as $key => $def) : ?>
            <?php if ($def['tab'] !== $active_tab) continue; ?>
            <?php se_render_field($key, $def); ?>
        <?php endforeach; ?>

        <?php submit_button('Änderungen speichern'); ?>
    </form>

    <div class="se-admin-preview" id="se-admin-preview"></div>

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
    $desc = isset($def['desc']) ? $def['desc'] : '';
    ?>
<?php if ('note' === $type) : ?>
    <div class="se-admin-note">
        <span class="dashicons dashicons-lightbulb" aria-hidden="true"></span>
        <p><?php echo esc_html($desc); ?></p>
    </div>
<?php else : ?>
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
        <?php if ($desc) : ?>
            <p class="description"><?php echo esc_html($desc); ?></p>
        <?php endif; ?>
    </div>
</div>
<?php endif; ?>
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
    .se-admin-wrap{max-width:1100px}
    .se-admin-tabs{margin-bottom:20px}
    .se-admin-tabs .nav-tab{display:inline-flex;align-items:center;gap:4px;font-weight:600}
    .se-admin-form{background:#fff;border:1px solid #e0e0e0;border-radius:12px;padding:24px 28px;max-width:760px;box-shadow:0 1px 4px rgba(0,0,0,.04)}
    .se-admin-field{display:flex;flex-wrap:wrap;gap:8px 16px;padding:14px 0;border-bottom:1px solid #f0f0f0}
    .se-admin-field:last-child{border-bottom:none}
    .se-admin-field-label{flex:0 0 260px;font-weight:600;padding-top:6px;line-height:1.4}
    .se-admin-field-input{flex:1 1 400px}
    .se-admin-field-input input[type="text"],.se-admin-field-input input[type="url"],.se-admin-field-input textarea{max-width:100%;border-radius:6px;border-color:#d0d0d0}
    .se-admin-field-input .description{color:#6b7280;font-style:normal;margin-top:6px;line-height:1.5}
    .se-admin-note{background:#fff8f0;border:1px solid #ffe0c2;border-left:4px solid #CC4D00;border-radius:8px;padding:14px 18px;margin:0 0 20px;color:#4a4a4a;line-height:1.6}
    .se-admin-note strong{color:#292524}
    .se-admin-note .dashicons{color:#CC4D00;margin-right:4px;vertical-align:-3px}
    .se-admin-preview{max-width:760px;margin-top:20px;background:#faf9f6;border:1px dashed #c9c9c9;border-radius:12px;padding:20px}
    @media(max-width:782px){
      .se-admin-field-label{flex:0 0 100%}
      .se-admin-field-input{flex:1 1 100%}
    }
    </style>';
}
add_action('admin_head', 'se_admin_page_styles');
