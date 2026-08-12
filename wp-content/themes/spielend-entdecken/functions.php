<?php
if (!defined('ABSPATH')) exit;

define('SE_THEME_VERSION', '1.0.0');

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
    wp_enqueue_style('se-theme', $template_uri . '/assets/css/theme.css', [], SE_THEME_VERSION);
}
add_action('wp_enqueue_scripts', 'se_enqueue_assets');

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

if (class_exists('WooCommerce')) {
    require_once get_template_directory() . '/inc/woocommerce.php';
}

require_once get_template_directory() . '/patterns/register-patterns.php';
require_once get_template_directory() . '/patterns/additional-patterns.php';
require_once get_template_directory() . '/inc/acf-fields.php';
require_once get_template_directory() . '/inc/schema.php';
require_once get_template_directory() . '/inc/critical-css.php';
