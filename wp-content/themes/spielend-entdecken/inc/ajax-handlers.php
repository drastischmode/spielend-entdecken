<?php
if (!defined('ABSPATH')) exit;

/**
 * AJAX-Handler für das Theme – deckt alle von main.js verwendeten Actions ab.
 * Vorher existierten KEINE Handler, weshalb Suche, Quick-View,
 * Mini-Cart-Zähler und Wunschliste nicht funktionierten.
 */

/** Suche-Autocomplete */
add_action('wp_ajax_se_autocomplete_search', 'se_ajax_autocomplete_search');
add_action('wp_ajax_nopriv_se_autocomplete_search', 'se_ajax_autocomplete_search');
function se_ajax_autocomplete_search() {
    check_ajax_referer('se_ajax_nonce', 'nonce');
    $q = isset($_GET['q']) ? sanitize_text_field(wp_unslash($_GET['q'])) : '';
    if (mb_strlen($q) < 2) {
        wp_send_json(array('success' => true, 'items' => array()));
    }

    $posts = get_posts(array(
        'post_type'      => array('product', 'post'),
        'post_status'    => 'publish',
        'posts_per_page' => 8,
        's'              => $q,
    ));

    $items = array();
    foreach ($posts as $post) {
        $url  = get_permalink($post);
        $img  = get_the_post_thumbnail_url($post, 'thumbnail');
        $type = ('product' === $post->post_type) ? 'product' : 'post';
        $price = '';
        if ('product' === $post->post_type && function_exists('wc_get_product')) {
            $product = wc_get_product($post);
            if ($product) $price = $product->get_price_html();
        }
        $items[] = array(
            'title' => $post->post_title,
            'url'   => $url,
            'image' => $img ?: '',
            'type'  => $type,
            'price' => wp_strip_all_tags($price),
        );
    }
    wp_send_json(array('success' => true, 'results' => $items));
}

/** Mini-Cart-Anzahl */
add_action('wp_ajax_woocommerce_get_cart_count', 'se_ajax_cart_count');
add_action('wp_ajax_nopriv_woocommerce_get_cart_count', 'se_ajax_cart_count');
function se_ajax_cart_count() {
    $count = function_exists('WC') && WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
    wp_send_json(array('success' => true, 'count' => (int) $count));
}

/** Quick-View: Produkt-HTML zurückgeben */
add_action('wp_ajax_se_quick_view', 'se_ajax_quick_view');
add_action('wp_ajax_nopriv_se_quick_view', 'se_ajax_quick_view');
function se_ajax_quick_view() {
    check_ajax_referer('se_ajax_nonce', 'nonce');
    $product_id = isset($_GET['product_id']) ? intval($_GET['product_id']) : 0;
    $product = $product_id ? wc_get_product($product_id) : false;
    if (!$product) {
        wp_send_json(array('success' => false, 'data' => 'Produkt nicht gefunden'), 404);
    }

    ob_start();
    ?>
    <div class="se-qv">
        <div class="se-qv__img">
            <?php echo wp_kses_post($product->get_image('woocommerce_single')); ?>
            <?php if ($product->is_on_sale()) : ?>
                <span class="se-qv__sale">Sale</span>
            <?php endif; ?>
        </div>
        <div class="se-qv__info">
            <h2 class="se-qv__title"><?php echo esc_html($product->get_name()); ?></h2>
            <div class="se-qv__price"><?php echo wp_kses_post($product->get_price_html()); ?></div>
            <p class="se-qv__desc"><?php echo esc_html(wp_trim_words($product->get_short_description() ?: $product->get_description(), 25)); ?></p>
            <?php echo se_product_trust_badges($product->get_id()); ?>
            <a href="<?php echo esc_url($product->get_permalink()); ?>" class="button se-qv__detail">Details ansehen</a>
        </div>
    </div>
    <?php
    $html = ob_get_clean();
    wp_send_json(array('success' => true, 'html' => $html));
}

/** Wunschliste toggeln (Cookie-basiert, wie im Plugin) */
add_action('wp_ajax_se_wishlist_toggle', 'se_ajax_wishlist_toggle');
add_action('wp_ajax_nopriv_se_wishlist_toggle', 'se_ajax_wishlist_toggle');
function se_ajax_wishlist_toggle() {
    check_ajax_referer('se_ajax_nonce', 'nonce');
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
    $add = isset($_POST['add']) ? filter_var($_POST['add'], FILTER_VALIDATE_BOOLEAN) : true;
    if (!$product_id) {
        wp_send_json(array('success' => false, 'data' => 'Keine Produkt-ID'), 400);
    }

    $wishlist = array();
    if (isset($_COOKIE['se_wishlist'])) {
        $decoded = json_decode(wp_unslash($_COOKIE['se_wishlist']), true);
        if (is_array($decoded)) $wishlist = array_map('intval', $decoded);
    }

    if ($add) {
        if (!in_array($product_id, $wishlist, true)) $wishlist[] = $product_id;
    } else {
        $wishlist = array_values(array_diff($wishlist, array($product_id)));
    }

    setcookie('se_wishlist', wp_json_encode($wishlist), time() + YEAR_IN_SECONDS, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);

    wp_send_json(array(
        'success'   => true,
        'count'     => count($wishlist),
        'wishlist'  => $wishlist,
        'is_active' => $add,
    ));
}

/** Localize Nonce für main.js */
function se_localize_ajax_script() {
    wp_localize_script('se-main', 'se_ajax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce'   => wp_create_nonce('se_ajax_nonce'),
        'cart_url' => function_exists('wc_get_cart_url') ? esc_url_raw(wc_get_cart_url()) : '/warenkorb/',
        'checkout_url' => function_exists('wc_get_checkout_url') ? esc_url_raw(wc_get_checkout_url()) : '/kasse/',
    ));
}
add_action('wp_enqueue_scripts', 'se_localize_ajax_script', 99);
