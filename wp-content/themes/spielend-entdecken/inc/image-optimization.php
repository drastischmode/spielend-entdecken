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
    
    // Critical CSS inline (sehr kompakt, konsistent mit theme.css WCAG-Palette)
    $critical_css = '
    :root {
      --color-primary: #CC4D00;
      --color-text: #292524;
      --color-bg-base: #faf9f6;
      --space-lg: 1.5rem;
    }
    body { margin: 0; padding: 0; background: var(--color-bg-base); color: var(--color-text); font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif; }
    .se-header { position: fixed; top: 0; left: 0; right: 0; z-index: 300; background: #fff; border-bottom: 1px solid #e7e5e4; }
    .se-hero { background: linear-gradient(135deg, #CC4D00 0%, #E85D04 100%); color: white; padding: var(--space-lg); min-height: 300px; display: flex; align-items: center; }
    .se-hero h1 { margin: 0; font-size: 2rem; font-weight: 700; line-height: 1.2; }
    h1, h2, h3 { margin-top: 0; }
    .button, button { background: var(--color-primary); color: white; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; cursor: pointer; font-weight: 600; transition: background 150ms; }
    .button:hover { background: #E85D04; }
    img { max-width: 100%; height: auto; display: block; }
    @media (prefers-reduced-motion: reduce) { * { animation: none !important; transition: none !important; } }
    ';
    
    echo '<style id="se-critical-css">' . trim($critical_css) . '</style>' . "\n";
}, 1);

/**
 * Preload wichtige Ressourcen (echte, existierende Dateien)
 */
add_action('wp_head', function() {
    $uri = get_template_directory_uri();
    echo '<link rel="preload" as="font" type="font/woff2" crossorigin href="' . esc_url($uri) . '/assets/fonts/inter/Inter-VariableFont_slnt,wght.woff2">' . "\n";
    echo '<link rel="preload" as="font" type="font/woff2" crossorigin href="' . esc_url($uri) . '/assets/fonts/fredoka/Fredoka-VariableFont_wght.woff2">' . "\n";
}, 2);

/**
 * Dequeue Block-Library-Styles außerhalb des Editors (Performance)
 * (aus critical-css.php übernommen, um Duplikate zu vermeiden)
 */
function se_dequeue_block_library_css() {
    wp_dequeue_style('wp-block-library');
    wp_dequeue_style('wp-block-library-theme');
    wp_dequeue_style('wc-blocks-style');
}
add_action('wp_enqueue_scripts', 'se_dequeue_block_library_css', 100);

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

    // Tracking-/Attributions-Scripts nur im Checkout laden (Performance)
    if (!is_checkout()) {
        wp_dequeue_script('wc-order-attribution');
        wp_dequeue_script('sourcebuster-js');
    }

    // Unnötige WooCommerce-Block-Dependencies auf Nicht-Shop-Seiten entfernen.
    // Der Mini-Cart im Header braucht nur mini-cart-frontend + wc-blocks.
    // Diese Scripts werden sonst global geladen, sind aber nur für den
    // Block-Editor/Checkout relevant.
    $wc_block_deps = array(
        'wc-settings',
        'wc-types',
        'wc-price-format',
        'wc-blocks-middleware',
    );
    if (!is_woocommerce() && !is_cart() && !is_checkout()) {
        foreach ($wc_block_deps as $handle) {
            wp_dequeue_script($handle);
        }
    }
}, 12);
