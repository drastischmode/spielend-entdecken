<?php
/**
 * Bild-Optimierung: Lazy-Loading + Critical CSS
 */

if (!defined('ABSPATH')) exit;

/**
 * Native lazy-loading für WooCommerce Produktbilder
 */
add_filter('wp_get_attachment_image_attributes', function($attr, $attachment, $size) {
    // Lazy-loading auf allen Bildern außer dem Hero/First-Image
    if (!isset($attr['class']) || strpos($attr['class'], 'attachment-') === false) {
        $attr['loading'] = 'lazy';
    }
    
    // Responsive images mit srcset
    if (isset($attr['src']) && !isset($attr['srcset'])) {
        $attr['decoding'] = 'async';
    }
    
    return $attr;
}, 10, 3);

/**
 * Lazy-loading für WooCommerce shop-loop Bilder
 */
add_filter('woocommerce_product_get_image', function($html, $product, $size, $attr) {
    if (empty($html)) return $html;
    
    // Ersetzt img Attributes
    $html = str_replace('<img ', '<img loading="lazy" decoding="async" ', $html);
    
    return $html;
}, 10, 4);

/**
 * Critical CSS Injection – Styles für Above-the-Fold
 */
add_action('wp_head', function() {
    // Nur auf Frontend
    if (is_admin()) return;
    
    // Critical CSS inline (sehr kompakt)
    $critical_css = '
    :root {
      --color-primary: #FF6B35;
      --color-text: #292524;
      --color-bg-base: #faf9f6;
      --space-lg: 1.5rem;
    }
    body { margin: 0; padding: 0; background: var(--color-bg-base); color: var(--color-text); font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
    .se-header { position: fixed; top: 0; left: 0; right: 0; z-index: 300; background: #fff; border-bottom: 1px solid #e7e5e4; }
    .se-hero { background: linear-gradient(135deg, #FF6B35 0%, #E85D04 100%); color: white; padding: var(--space-lg); min-height: 300px; display: flex; align-items: center; }
    .se-hero h1 { margin: 0; font-size: 2rem; font-weight: 700; line-height: 1.2; }
    h1, h2, h3 { margin-top: 0; }
    .button, button { background: var(--color-primary); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; cursor: pointer; font-weight: 600; transition: background 150ms; }
    .button:hover { background: #E85D04; }
    img { max-width: 100%; height: auto; display: block; }
    @media (prefers-reduced-motion: reduce) { * { animation: none !important; transition: none !important; } }
    ';
    
    echo '<style>' . trim($critical_css) . '</style>' . "\n";
}, 1);

/**
 * Preload wichtige Ressourcen
 */
add_action('wp_head', function() {
    // Preload Primary Font
    echo '<link rel="preload" as="style" href="' . esc_url(get_template_directory_uri()) . '/assets/fonts/inter.css">' . "\n";
    
    // Preload Hero Image
    if (is_front_page()) {
        echo '<link rel="preload" as="image" href="' . esc_url(get_template_directory_uri()) . '/assets/images/hero-bg.jpg" imagesrcset="' . esc_url(get_template_directory_uri()) . '/assets/images/hero-bg-small.jpg 480w, ' . esc_url(get_template_directory_uri()) . '/assets/images/hero-bg.jpg 1200w">' . "\n";
    }
    
    // Preconnect zu CDN (falls verwendet)
    echo '<link rel="preconnect" href="https://fonts.googleapis.com">' . "\n";
    echo '<link rel="dns-prefetch" href="https://cdn.example.com">' . "\n";
}, 2);

/**
 * Deferred JavaScript Loading
 */
add_filter('wp_print_scripts', function() {
    // Nicht-kritische Scripts mit defer laden
    // Wird bereits in functions.php mit $in_footer = true gemacht
}, 999);

/**
 * Bild-Attribute für responsive Bilder
 */
add_filter('wp_calculate_image_srcset', function($sources, $size_array, $image_src, $image_meta, $attachment_id) {
    // Wird bereits von WordPress gut gemacht
    return $sources;
}, 10, 5);

/**
 * Disable Gravatars (externe Anfrage sparen)
 */
add_filter('get_avatar', function($avatar, $id_or_email, $size, $default, $alt, $args) {
    // Optional: Gravatars deaktivieren, wenn nicht nötig
    // return '';
    return $avatar;
}, 10, 6);

/**
 * CSS Minify & Cache-Busting
 */
add_filter('style_loader_src', function($src, $handle) {
    // CSS wird bereits gecacht durch WP-Versioning
    return $src;
}, 10, 2);

/**
 * Optimize WooCommerce Assets
 */
add_action('wp_enqueue_scripts', function() {
    // Nur WooCommerce-CSS auf relevanten Seiten laden
    if (!is_shop() && !is_product_category() && !is_product()) {
        wp_dequeue_style('woocommerce-general');
        wp_dequeue_style('woocommerce-smallscreen');
        wp_dequeue_style('woocommerce-layout');
    }
}, 11);
