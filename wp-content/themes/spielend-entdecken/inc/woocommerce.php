<?php
if (!defined('ABSPATH')) exit;

function se_woocommerce_setup() {
    add_theme_support('woocommerce');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'se_woocommerce_setup');

function se_woocommerce_css() {
    if (is_shop() || is_product_category() || is_product() || is_cart() || is_checkout()) {
        wp_enqueue_style('se-woocommerce', get_template_directory_uri() . '/assets/css/woocommerce.css', [], SE_THEME_VERSION);
    }
}
add_action('wp_enqueue_scripts', 'se_woocommerce_css');

remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

function se_before_shop_loop_item() {
    echo '<div class="se-product-card">';
}
add_action('woocommerce_before_shop_loop_item', 'se_before_shop_loop_item', 5);

function se_after_shop_loop_item() {
    echo '</div>';
}
add_action('woocommerce_after_shop_loop_item', 'se_after_shop_loop_item', 15);

add_filter('woocommerce_show_page_title', '__return_false');

function se_related_products_limit() {
    return 4;
}
add_filter('woocommerce_output_related_products_args', function($args) {
    $args['posts_per_page'] = 4;
    $args['columns'] = 4;
    return $args;
});

add_filter('woocommerce_gallery_image_size', function() { return 'medium_large'; });

add_filter('woocommerce_breadcrumb_defaults', function($defaults) {
    return array_merge($defaults, [
        'delimiter' => ' › ',
        'wrap_before' => '<nav class="se-breadcrumb">',
        'wrap_after' => '</nav>',
        'before' => '<span>',
        'after' => '</span>',
    ]);
});

/** Wunschzettel-Button auf der Produktseite (nach Add-to-Cart) */
function se_add_wishlist_button() {
    if (shortcode_exists('spielend_wishlist_button')) {
        echo do_shortcode('[spielend_wishlist_button]');
    }
}
add_action('woocommerce_single_product_summary', 'se_add_wishlist_button', 35);

/** Breadcrumb: WooCommerce Block-Template rendert bereits wc-block-breadcrumbs.
 *  Wir deaktivieren unsere eigenen, um Duplikate zu vermeiden. */

/** Shop-Seite: eigenes Breadcrumb über wp_body_open (Block-Template hat keins) */
function se_shop_breadcrumb_body_open() {
    if (is_shop()) {
        $shop_url = get_permalink(wc_get_page_id('shop'));
        echo '<div class="se-body-breadcrumb-wrap"><nav class="se-breadcrumb" aria-label="Breadcrumb">'
            . '<a href="' . esc_url(home_url('/')) . '">Home</a>'
            . '<span class="se-breadcrumb-sep"> › </span>'
            . '<span class="se-breadcrumb-current">Shop</span>'
            . '</nav></div>';
    }
}
add_action('wp_body_open', 'se_shop_breadcrumb_body_open', 5);

/** Kategorie-Schnellauswahl über dem Produkt-Grid */
function se_category_quick_nav() {
    if (!is_shop() && !is_product_category()) return;
    $cats = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => true, 'number' => 12, 'orderby' => 'count', 'order' => 'DESC'));
    if (is_wp_error($cats) || empty($cats)) return;

    $current = is_product_category() ? get_queried_object_id() : 0;
    $items = array('<a href="' . esc_url(get_permalink(wc_get_page_id('shop'))) . '" class="se-qnav-item' . (is_shop() ? ' is-current' : '') . '">Alle</a>');
    foreach ($cats as $cat) {
        $items[] = '<a href="' . esc_url(get_term_link($cat)) . '" class="se-qnav-item' . ($cat->term_id === $current ? ' is-current' : '') . '">' . esc_html($cat->name) . '</a>';
    }
    echo '<div class="se-category-quicknav" role="navigation" aria-label="Kategorien">' . implode('', $items) . '</div>';
}
add_action('woocommerce_before_shop_loop', 'se_category_quick_nav', 5);

/** Suche auf Produkte beschränken */
function se_restrict_search_to_products($query) {
    if (!is_admin() && $query->is_search() && $query->is_main_query()) {
        $query->set('post_type', 'product');
    }
}
add_action('pre_get_posts', 'se_restrict_search_to_products');
