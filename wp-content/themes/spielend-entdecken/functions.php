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
    wp_enqueue_style('se-fonts', $template_uri . '/assets/css/fonts.css', [], SE_THEME_VERSION);
    wp_enqueue_style('se-theme', $template_uri . '/assets/css/theme.css', ['se-fonts'], SE_THEME_VERSION);
    wp_enqueue_style('se-cookie-banner', $template_uri . '/assets/cookie-banner.css', [], SE_THEME_VERSION);
    wp_enqueue_script('se-cookie-banner', $template_uri . '/assets/cookie-banner.js', [], SE_THEME_VERSION, true);
    wp_enqueue_style('se-whatsapp', $template_uri . '/assets/css/whatsapp.css', [], SE_THEME_VERSION);
    wp_enqueue_script('se-whatsapp', $template_uri . '/assets/js/whatsapp.js', [], SE_THEME_VERSION, true);
    wp_enqueue_style('se-search', $template_uri . '/assets/css/search.css', [], SE_THEME_VERSION);
    wp_enqueue_style('se-animations', $template_uri . '/assets/css/animations.css', [], SE_THEME_VERSION);
    wp_enqueue_script('se-animations', $template_uri . '/assets/js/animations.js', [], SE_THEME_VERSION, true);
    wp_enqueue_script('se-main', $template_uri . '/assets/js/main.js', [], SE_THEME_VERSION, true);
    wp_enqueue_script('se-wc-translate', $template_uri . '/assets/js/wc-translate.js', [], SE_THEME_VERSION, true);
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

function se_seo_meta() {
    $description = get_bloginfo('description');
    if (!$description) {
        $description = 'Hochwertiges Spielzeug seit 1902 – Spielend Entdecken in Tönisvorst. Nachhaltiges Holzspielzeug, kreative Puzzles & mehr. Jetzt stöbern!';
    }
    echo '<meta name="description" content="' . esc_attr($description) . '" />' . "\n";
    echo '<meta property="og:title" content="' . esc_attr(get_bloginfo('name')) . '" />' . "\n";
    echo '<meta property="og:description" content="' . esc_attr($description) . '" />' . "\n";
    echo '<meta property="og:type" content="website" />' . "\n";
    echo '<meta property="og:url" content="' . esc_url(home_url('/')) . '" />' . "\n";
    $logo_id = get_theme_mod('custom_logo');
    if ($logo_id) {
        $logo_url = wp_get_attachment_image_url($logo_id, 'full');
        if ($logo_url) {
            echo '<meta property="og:image" content="' . esc_url($logo_url) . '" />' . "\n";
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

    ob_start();
    ?>
<div class="wp-block-cover alignfull se-hero" style="min-height:500px;">
    <span aria-hidden="true" class="wp-block-cover__background has-background-dim has-background-dim-60 has-background-gradient has-spielend-hero-gradient-background"></span>
    <?php if ($video) : ?>
    <video class="se-hero-video" autoplay muted loop playsinline preload="metadata"<?php echo $poster ? ' poster="' . esc_url($poster) . '"' : ''; ?>>
        <source src="<?php echo esc_url(str_replace('.mp4', '.webm', $video)); ?>" type="video/webm">
        <source src="<?php echo esc_url($video); ?>" type="video/mp4">
    </video>
    <?php endif; ?>
    <div class="se-hero-toys" aria-hidden="true">
        <span class="se-hero-toy se-hero-toy--1">🧸</span>
        <span class="se-hero-toy se-hero-toy--2">🧩</span>
        <span class="se-hero-toy se-hero-toy--3">🎨</span>
        <span class="se-hero-toy se-hero-toy--4">🎲</span>
    </div>
    <div class="wp-block-cover__inner-container se-hero-content">
        <h1 class="wp-block-heading has-text-align-center has-base-color has-text-color se-hero-title"><?php echo esc_html($title); ?></h1>
        <?php if ($subtitle) : ?>
        <p class="has-text-align-center has-base-color has-text-color se-hero-subtitle"><?php echo esc_html($subtitle); ?></p>
        <?php endif; ?>
        <div class="wp-block-buttons is-content-justification-center se-hero-buttons">
            <div class="wp-block-button"><a class="wp-block-button__link has-foreground-color has-accent-background-color has-text-color has-background wp-element-button" href="<?php echo esc_url($cta_url); ?>"><?php echo esc_html($cta_text); ?></a></div>
            <?php if ($cta2_text && $cta2_url) : ?>
            <div class="wp-block-button is-style-outline"><a class="wp-block-button__link has-base-color has-text-color wp-element-button" href="<?php echo esc_url($cta2_url); ?>"><?php echo esc_html($cta2_text); ?></a></div>
            <?php endif; ?>
        </div>
        <?php if (!empty($badges)) : ?>
        <ul class="se-hero-badges">
            <?php foreach ($badges as $b) : ?>
            <?php $badge_html = se_render_badge_with_counter($b['text']); ?>
            <li class="se-hero-badge">
                <?php if ($b['url']) : ?>
                    <a href="<?php echo esc_url($b['url']); ?>"><?php echo $badge_html; // phpcs:ignore ?></a>
                <?php else : ?>
                    <span><?php echo $badge_html; // phpcs:ignore ?></span>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
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
require_once get_template_directory() . '/inc/image-optimization.php';
