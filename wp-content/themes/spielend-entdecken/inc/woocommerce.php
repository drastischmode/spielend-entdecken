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
