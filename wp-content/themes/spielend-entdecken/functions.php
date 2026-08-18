<?php
if (!defined('ABSPATH')) exit;

define('SE_THEME_VERSION', '2.1.0');

function se_setup() {
    add_theme_support('block-template-parts');
    add_theme_support('align-wide');
    add_theme_support('responsive-embeds');
    add_theme_support('custom-line-height');
    add_theme_support('custom-spacing');
    add_theme_support('custom-units');
    add_theme_support('link-color');
    add_theme_support('border');
    add_theme_support('shadow');

    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');

    add_theme_support('editor-styles');
    add_editor_style('assets/css/editor.css');

    load_theme_textdomain('spielend-entdecken', get_template_directory() . '/languages');
}
add_action('after_setup_theme', 'se_setup');

function se_enqueue_assets() {
    $template_uri = get_template_directory_uri();
    $cache_buster = SE_THEME_VERSION . '.' . filemtime(get_template_directory() . '/assets/css/theme.css');
    
    wp_enqueue_style('se-fonts', $template_uri . '/assets/css/fonts.css', [], $cache_buster);
    wp_enqueue_style('se-theme', $template_uri . '/assets/css/theme.css', ['se-fonts'], $cache_buster);
    wp_enqueue_style('se-cookie-banner', $template_uri . '/assets/cookie-banner.css', [], $cache_buster);
    wp_enqueue_script('se-cookie-banner', $template_uri . '/assets/cookie-banner.js', [], $cache_buster, true);
    wp_enqueue_style('se-whatsapp', $template_uri . '/assets/css/whatsapp.css', [], $cache_buster);
    wp_enqueue_script('se-whatsapp', $template_uri . '/assets/js/whatsapp.js', [], $cache_buster, true);
    wp_enqueue_style('se-search', $template_uri . '/assets/css/search.css', [], $cache_buster);
    wp_enqueue_style('se-animations', $template_uri . '/assets/css/animations.css', [], $cache_buster);
    wp_enqueue_script('se-animations', $template_uri . '/assets/js/animations.js', [], $cache_buster, true);
    wp_enqueue_script('se-main', $template_uri . '/assets/js/main.js', [], $cache_buster, true);
    wp_enqueue_script('se-wc-translate', $template_uri . '/assets/js/wc-translate.js', [], $cache_buster, true);
    wp_enqueue_script('se-newsletter', $template_uri . '/assets/js/newsletter.js', [], $cache_buster, true);
}
add_action('wp_enqueue_scripts', 'se_enqueue_assets');

function se_admin_dashboard_widget() {
    // Altes Widget entfernen, neues (optimiertes) aktivieren
    remove_meta_box('spielend_overview', 'dashboard', 'normal');
}
add_action('wp_dashboard_setup', 'se_admin_dashboard_widget', 1);

function se_render_dashboard_widget() {
    // Wird durch inc/dashboard-optimized.php ersetzt (se_render_optimized_dashboard_widget).
    return;
}

function se_security_headers() {
    if (is_admin()) return;
    header('X-Frame-Options: SAMEORIGIN');
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
    header('X-XSS-Protection: 1; mode=block');
    if (function_exists('is_page') && is_page('kontakt')) {
        header('Cache-Control: no-cache, no-store, must-revalidate, max-age=0');
        header('Pragma: no-cache');
        header('Expires: 0');
    }
}
add_action('send_headers', 'se_security_headers');

/** WordPress-Version nicht öffentlich ausgeben (Sicherheit) */
function se_remove_generator_tag() {
    remove_action('wp_head', 'wp_generator');
}
add_action('init', 'se_remove_generator_tag');
add_filter('the_generator', '__return_empty_string');

/** Generator-Tags von Plugins entfernen (u. a. WooCommerce) */
add_filter('get_the_generator_html', '__return_empty_string');
add_filter('woocommerce_show_page_title', '__return_false');

function se_seo_meta() {
    $description = get_bloginfo('description');
    if (!$description) {
        $description = 'Hochwertiges Spielzeug seit 1902 – Spielend Entdecken in Tönisvorst. Nachhaltiges Holzspielzeug, kreative Puzzles & mehr. Jetzt stöbern!';
    }
    echo '<meta name="description" content="' . esc_attr($description) . '" />' . "\n";
    echo '<meta property="og:locale" content="de_DE" />' . "\n";
    echo '<meta property="og:title" content="' . esc_attr(get_bloginfo('name')) . '" />' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '" />' . "\n";
    echo '<meta property="og:type" content="website" />' . "\n";
    echo '<meta property="og:url" content="' . esc_url(home_url('/')) . '" />' . "\n";
    echo '<meta name="twitter:card" content="summary_large_image" />' . "\n";
    echo '<meta name="twitter:title" content="' . esc_attr(get_bloginfo('name')) . '" />' . "\n";
    echo '<meta name="twitter:description" content="' . esc_attr($description) . '" />' . "\n";
    $logo_id = get_theme_mod('custom_logo');
    if ($logo_id) {
        $logo_url = wp_get_attachment_image_url($logo_id, 'full');
        if ($logo_url) {
            echo '<meta property="og:image" content="' . esc_url($logo_url) . '" />' . "\n";
            echo '<meta name="twitter:image" content="' . esc_url($logo_url) . '" />' . "\n";
        }
    }
}
add_action('wp_head', 'se_seo_meta');

function se_remove_emoji_script() {
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
}
add_action('init', 'se_remove_emoji_script');

function se_optimize_woocommerce_scripts() {
    if (function_exists('is_woocommerce')) {
        if (!is_woocommerce() && !is_cart() && !is_checkout()) {
            wp_dequeue_style('woocommerce-general');
            wp_dequeue_style('woocommerce-layout');
            wp_dequeue_script('wc-cart-fragments');
            wp_dequeue_script('woocommerce');
            wp_dequeue_script('wc-add-to-cart');
        }
    }
}
add_action('wp_enqueue_scripts', 'se_optimize_woocommerce_scripts', 99);

require_once get_template_directory() . '/inc/theme-options.php';
require_once get_template_directory() . '/inc/dashboard-optimized.php';

/** Fallback: Social-Media-URLs falls theme_mods leer sind */
add_filter('theme_mod_spielend_social_facebook', function($val) {
    return $val ?: 'https://facebook.com/spielwaren_lessenich';
});
add_filter('theme_mod_spielend_social_instagram', function($val) {
    return $val ?: 'https://instagram.com/spielwaren_lessenich';
});
add_filter('theme_mod_spielend_social_tiktok', function($val) {
    return $val ?: 'https://tiktok.com/@spielwaren_lessenich';
});

function se_hero_shortcode() {
    $title    = spielend_opt('spielend_hero_title', 'Entdecke die Welt des Spielens');
    $subtitle = spielend_opt('spielend_hero_subtitle', 'Hochwertiges Spielzeug für neugierige Kinder – sicher, nachhaltig, kreativ');
    $cta_text = spielend_opt('spielend_hero_cta_text', 'Jetzt stöbern');
    $cta_url  = spielend_opt('spielend_hero_cta_url', '/shop');
    $cta2_text = spielend_opt('spielend_hero_cta2_text', '');
    $cta2_url  = spielend_opt('spielend_hero_cta2_url', '');

    $video  = spielend_opt('spielend_hero_video_url', '');
    if ('' === $video) {
        $video = 'https://spielend.ct.ws/wp-content/uploads/2026/08/spielend-reel.mp4';
    }
    $poster = spielend_opt('spielend_hero_poster', '');
    if ('' === $poster) {
        $logo = get_theme_mod('custom_logo');
        $poster = $logo ? wp_get_attachment_image_url($logo, 'full') : '';
    }

    // Trust badges: jede Zeile "Text|Link" (Link optional) ODER "Text||Text||..."
    $badges_raw = spielend_opt('spielend_hero_badges', '');
    $badges = array();
    // Support both newline-separated and ||-separated entries
    $badges_raw = str_replace('||', "\n", $badges_raw);
    foreach (preg_split('/\r\n|\r|\n/', $badges_raw) as $line) {
        $line = trim($line);
        if ('' === $line) continue;
        $parts = array_map('trim', explode('|', $line));
        $badges[] = array('text' => $parts[0], 'url' => isset($parts[1]) ? $parts[1] : '');
    }
    if (empty($badges)) {
        $badges = array(
            array('text' => 'Seit 1902', 'url' => ''),
            array('text' => 'Gratis Versand ab 50 €', 'url' => '/versand'),
            array('text' => 'Über 130 Produkte', 'url' => ''),
        );
    }

    // Hero eyebrow (optional)
    $eyebrow = spielend_opt('spielend_hero_eyebrow', 'Ihr Spielwarenladen am Niederrhein');

    // Trust indicators
    $trust_items = array(
        array('value' => '120+', 'label' => 'Jahre Erfahrung'),
        array('value' => '130+', 'label' => 'Produkte'),
        array('value' => '50€', 'label' => 'Gratis Versand ab'),
        array('value' => '180m²', 'label' => 'Ladenfläche'),
    );

    // Floating toys
    $float_toys = array('🧸', '🧩', '🎨', '🎲');

    ob_start();
    ?>
<div class="wp-block-cover alignfull se-hero" style="min-height:calc(100vh - 72px);">
    <span aria-hidden="true" class="wp-block-cover__background has-background-dim has-background-dim-60 has-background-gradient has-spielend-hero-gradient-background"></span>
    <?php if ($video) : ?>
    <video class="se-hero-video" autoplay muted loop playsinline preload="metadata"<?php echo $poster ? ' poster="' . esc_url($poster) . '"' : ''; ?>>
        <source src="<?php echo esc_url(str_replace('.mp4', '.webm', $video)); ?>" type="video/webm">
        <source src="<?php echo esc_url($video); ?>" type="video/mp4">
    </video>
    <?php endif; ?>
    
    <!-- Floating toy elements -->
    <div class="se-hero-toys" aria-hidden="true">
        <?php foreach ($float_toys as $index => $toy) : ?>
        <span class="se-hero__float se-hero__float--<?php echo $index + 1; ?>"><?php echo esc_html($toy); ?></span>
        <?php endforeach; ?>
    </div>

    <div class="wp-block-cover__inner-container se-hero__content">
        <?php if ($eyebrow) : ?>
        <span class="se-hero__eyebrow"><?php echo esc_html($eyebrow); ?></span>
        <?php endif; ?>
        
        <h1 class="wp-block-heading has-text-align-center has-base-color has-text-color se-hero-title"><?php echo esc_html($title); ?></h1>
        
        <?php if ($subtitle) : ?>
        <p class="has-text-align-center has-base-color has-text-color se-hero-subtitle"><?php echo esc_html($subtitle); ?></p>
        <?php endif; ?>
        
        <div class="wp-block-buttons is-content-justification-center se-hero__actions">
            <div class="wp-block-button"><a class="wp-block-button__link has-foreground-color has-accent-background-color has-text-color has-background wp-element-button btn btn--primary btn--lg" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_text); ?></a></div>
            <?php if ($cta2_text && $cta2_url) : ?>
            <div class="wp-block-button is-style-outline"><a class="wp-block-button__link has-base-color has-text-color wp-element-button btn btn--outline btn--lg" href="<?php echo esc_url($cta2_url); ?>"><?php echo esc_html($cta2_text); ?></a></div>
            <?php endif; ?>
        </div>
        
        <?php if (!empty($badges)) : ?>
        <ul class="se-hero-badges" style="display:flex;flex-wrap:nowrap;justify-content:center;gap:var(--space-3);margin-top:var(--space-10);list-style:none;overflow-x:auto;scrollbar-width:none;-ms-overflow-style:none;padding:var(--space-2) var(--space-4);margin-left:calc(-1 * var(--space-4));margin-right:calc(-1 * var(--space-4));padding-left:var(--space-4);padding-right:var(--space-4);">
            <?php foreach ($badges as $b) : ?>
            <?php $badge_html = se_render_badge_with_counter($b['text']); ?>
            <li class="se-hero-badge" style="flex-shrink:0;white-space:nowrap;">
                <?php if ($b['url']) : ?>
                    <a href="<?php echo esc_url($b['url']); ?>"><?php echo $badge_html; // phpcs:ignore ?></a>
                <?php else : ?>
                    <span><?php echo $badge_html; // phpcs:ignore ?></span>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
        
        <!-- Trust Indicators -->
        <div class="se-hero__trust">
            <?php foreach ($trust_items as $item) : ?>
            <div class="se-hero__trust-item">
                <span class="se-hero__trust-value"><?php echo esc_html($item['value']); ?></span>
                <span><?php echo esc_html($item['label']); ?></span>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>
    <?php
    return (string) ob_get_clean();
}

/**
 * Erkennt Zahlen in Badge-Texten und macht daraus animierte Counter.
 * "Seit 1902" -> "Seit <span class=se-counter data-target=1902>1902</span>"
 */
function se_render_badge_with_counter($text) {
    if (!preg_match('/\d{2,4}/', $text, $m)) {
        return esc_html($text);
    }
    $num = $m[0];
    $prefix = substr($text, 0, strpos($text, $num));
    $suffix = substr($text, strpos($text, $num) + strlen($num));
    return esc_html($prefix)
        . '<span class="se-counter" data-target="' . esc_attr($num) . '">' . esc_html($num) . '</span>'
        . esc_html($suffix);
}
add_shortcode('se_hero', 'se_hero_shortcode');

if (class_exists('WooCommerce')) {
    require_once get_template_directory() . '/inc/woocommerce.php';
}

require_once get_template_directory() . '/patterns/register-patterns.php';
require_once get_template_directory() . '/patterns/additional-patterns.php';
require_once get_template_directory() . '/inc/acf-fields.php';
require_once get_template_directory() . '/inc/schema.php';
require_once get_template_directory() . '/inc/bulk-meta-tool.php';
require_once get_template_directory() . '/inc/api-bulk-meta.php';
require_once get_template_directory() . '/inc/ajax-handlers.php';
require_once get_template_directory() . '/inc/security-hardening.php';
require_once get_template_directory() . '/inc/image-optimization.php';
