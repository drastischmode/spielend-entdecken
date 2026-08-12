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

/**
 * Trust-Elemente (Design-DNA): Altersempfehlung + Nachhaltigkeits-Meta-Felder
 * ------------------------------------------------------------
 * Produkt-Admin: Tab "Allgemein" → Altersempfehlung + Nachhaltig-Checkbox
 * Frontend:     Badges auf Produktkarten + Produktseite
 */

function se_product_trust_meta_fields() {
    woocommerce_wp_text_input(array(
        'id'          => '_se_age_rating',
        'label'       => __('Altersempfehlung (Jahre)', 'spielend-entdecken'),
        'description' => __('z.B. 3 für "Ab 3 Jahren" – wird als Trust-Badge angezeigt.', 'spielend-entdecken'),
        'type'        => 'number',
        'custom_attributes' => array('min' => '0', 'max' => '18', 'step' => '1'),
    ));
    woocommerce_wp_checkbox(array(
        'id'          => '_se_sustainable',
        'label'       => __('Nachhaltig', 'spielend-entdecken'),
        'description' => __('Markiere das Produkt als nachhaltig (grünes Badge).', 'spielend-entdecken'),
    ));
}
add_action('woocommerce_product_options_general_product_data', 'se_product_trust_meta_fields');

function se_save_product_trust_meta_fields($product_id) {
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['_se_age_rating'])) {
        $age = intval($_POST['_se_age_rating']);
        update_post_meta($product_id, '_se_age_rating', $age > 0 && $age <= 18 ? $age : '');
    } else {
        delete_post_meta($product_id, '_se_age_rating');
    }
    if (isset($_POST['_se_sustainable'])) {
        update_post_meta($product_id, '_se_sustainable', 'yes');
    } else {
        delete_post_meta($product_id, '_se_sustainable');
    }
}
add_action('woocommerce_process_product_meta', 'se_save_product_trust_meta_fields');

/** Helfer: Trust-Badges für ein Produkt rendern (Karte + Einzelseite) */
function se_product_trust_badges($product_id = 0, $compact = false) {
    if (!$product_id) return '';
    $age = get_post_meta($product_id, '_se_age_rating', true);
    $sustainable = get_post_meta($product_id, '_se_sustainable', true);
    if (!$age && 'yes' !== $sustainable) return '';

    $items = array();
    if ($age) {
        $items[] = '<span class="se-trust-badge se-trust-badge--age" title="Altersempfehlung">'
            . '<svg class="se-trust-badge__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>'
            . '<span>' . esc_html('Ab ' . $age . ' Jahren') . '</span></span>';
    }
    if ('yes' === $sustainable) {
        $items[] = '<span class="se-trust-badge se-trust-badge--eco" title="Nachhaltig">'
            . '<svg class="se-trust-badge__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22c4-4 8-9 8-14 0-2-1-4-3-4-3 0-5 3-5 6 0-3-2-6-5-6-2 0-3 2-3 4 0 5 4 10 8 14z"/></svg>'
            . '<span>Nachhaltig</span></span>';
    }
    if (empty($items)) return '';
    return '<div class="se-trust-badges' . ($compact ? ' se-trust-badges--compact' : '') . '">' . implode('', $items) . '</div>';
}

/** Trust-Badges auf Produktkarten (Shop-Loop) – direkt über dem Preis */
function se_loop_product_trust_badges() {
    global $product;
    if (!$product) return;
    echo se_product_trust_badges($product->get_id(), true);
}
add_action('woocommerce_after_shop_loop_item_title', 'se_loop_product_trust_badges', 4);

/**
 * Trust-Badges in Block-basierten Produkt-Loops (Query Loop / FSE-Shop)
 * Fügt nach dem Produkt-Titel die Badges ein, da klassische Hooks dort
 * nicht feuern.
 */
function se_block_loop_trust_badges($block_content, $block) {
    if (empty($block['blockName']) || 'woocommerce/product-title' !== $block['blockName']) {
        return $block_content;
    }
    // Nur im Shop/Kategorie-Kontext rendern
    if (!is_shop() && !is_product_category() && !is_product_tag() && !wp_doing_ajax()) {
        return $block_content;
    }
    $post_id = get_the_ID();
    if (!$post_id) return $block_content;
    $badges = se_product_trust_badges($post_id, true);
    if (!$badges) return $block_content;
    return $block_content . $badges;
}
add_filter('render_block', 'se_block_loop_trust_badges', 10, 2);

/** Trust-Elemente auf der Produktseite: Versand + Altersempfehlung + Tradition */
function se_single_product_trust() {
    global $product;
    if (!$product) return;
    ?>
    <div class="se-trust-row">
        <div class="se-trust-row__item">
            <svg class="se-trust-row__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 7h11v8H3z"/><path d="M14 10h4l3 3v2h-7z"/><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/></svg>
            <span><strong>Gratis Versand</strong> ab 50 €</span>
        </div>
        <div class="se-trust-row__item">
            <svg class="se-trust-row__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.8 4.6a5.5 5.5 0 0 0-7.8 0L12 5.6l-1-1a5.5 5.5 0 0 0-7.8 7.8l1 1L12 21.2l7.8-7.8 1-1a5.5 5.5 0 0 0 0-7.8z"/></svg>
            <span>Fachberatung <strong>seit 1902</strong></span>
        </div>
        <div class="se-trust-row__item">
            <svg class="se-trust-row__icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M12 22c4-4 8-9 8-14 0-2-1-4-3-4-3 0-5 3-5 6 0-3-2-6-5-6-2 0-3 2-3 4 0 5 4 10 8 14z"/></svg>
            <span>Sicher &amp; geprüft</span>
        </div>
    </div>
    <?php
    echo se_product_trust_badges($product->get_id());
}
add_action('woocommerce_single_product_summary', 'se_single_product_trust', 12);
