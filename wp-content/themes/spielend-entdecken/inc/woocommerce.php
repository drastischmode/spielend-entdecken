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

    // Kuratierte Reihenfolge: Shop-Welten zuerst, Sonderangebote am Ende.
    // Nicht gelistete Kategorien werden nach Produktanzahl angehängt (max 12).
    $priority = array(
        'tonies',
        'lego',
        'brettspiele',
        'kartenspiele',
        'baby-kleinkind',
        'edurino',
        'wichtelwelt',
        'hobby-horsing',
        'dies-das',
        'geschenkideen-fuer-erwachsene',
        'giessen-mit-keraflott',
        'sonderangebote',
    );

    $cats = get_terms(array('taxonomy' => 'product_cat', 'hide_empty' => true));
    if (is_wp_error($cats) || empty($cats)) return;

    $by_slug = array();
    foreach ($cats as $cat) {
        $by_slug[$cat->slug] = $cat;
    }

    $ordered = array();
    foreach ($priority as $slug) {
        if (isset($by_slug[$slug])) {
            $ordered[] = $by_slug[$slug];
            unset($by_slug[$slug]);
        }
    }
    $rest = array_values($by_slug);
    usort($rest, function ($a, $b) {
        return $b->count - $a->count;
    });
    $ordered = array_merge($ordered, $rest);
    $ordered = array_slice($ordered, 0, 12);

    $current = is_product_category() ? get_queried_object_id() : 0;
    $items = array('<a href="' . esc_url(get_permalink(wc_get_page_id('shop'))) . '" class="se-qnav-item' . (is_shop() ? ' is-current' : '') . '"' . (is_shop() ? ' aria-current="page"' : '') . '>Alle</a>');
    foreach ($ordered as $cat) {
        $items[] = '<a href="' . esc_url(get_term_link($cat)) . '" class="se-qnav-item' . ($cat->term_id === $current ? ' is-current' : '') . '"' . ($cat->term_id === $current ? ' aria-current="page"' : '') . '>' . esc_html($cat->name) . '</a>';
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

/** ============================================
 * PRODUCT CARD V2 MARKUP INJECTION
 * ============================================ */

/** 1. Product Image Wrapper */
function se_wc_product_image_html($html, $product) {
    $product_id = $product->get_id();
    $product_title = $product->get_name();
    $product_url = $product->get_permalink();
    $img_html = $product->get_image('woocommerce_thumbnail');
    
    if (empty($img_html)) {
        $img_html = '<span class="se-prod-card__placeholder" aria-hidden="true"></span>';
    }
    
    $badges_html = '';
    if ($product->is_on_sale()) {
        $badges_html .= '<span class="se-prod-card__badge se-prod-card__badge--sale">' . esc_html__('Sale', 'woocommerce') . '</span>';
    }
    if ($product->is_featured()) {
        $badges_html .= '<span class="se-prod-card__badge se-prod-card__badge--featured">' . esc_html__('Empfohlen', 'woocommerce') . '</span>';
    }
    $meta = get_post_meta($product->get_id(), '_spielend_product_new', true);
    if ($meta === 'yes') {
        $badges_html .= '<span class="se-prod-card__badge se-prod-card__badge--new">' . esc_html__('Neu', 'woocommerce') . '</span>';
    }
    $badges_html = $badges_html ? '<div class="se-prod-card__badges">' . $badges_html . '</div>' : '';
    
    $wishlist_btn = sprintf(
        '<button type="button" class="se-prod-card__wishlist" data-id="%d" aria-label="%s" title="%s">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06a5.5 5.5 0 0 0 7.78 7.78l1.06 1.06a5.5 5.5 0 0 0 7.78 7.78l1.06-1.06a5.5 5.5 0 0 0 7.78-7.78l-1.06-1.06a5.5 5.5 0 0 0-7.78 0z"></path></svg>
        </button>',
        $product->get_id(),
        esc_attr__( 'Zur Wunschliste hinzufügen', 'woocommerce' ),
        esc_attr__( 'Zur Wunschliste hinzufügen', 'woocommerce' )
    );
    
    $quick_btn = sprintf(
        '<button type="button" class="se-prod-card__quick" data-id="%d" aria-label="%s" title="%s">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
        </button>',
        $product->get_id(),
        esc_attr__( 'Schnellansicht', 'woocommerce' ),
        esc_attr__( 'Schnellansicht', 'woocommerce' )
    );
    
    $overlay = '<div class="se-prod-card__overlay"></div>';
    
    return $badges_html . 
           '<div class="se-prod-card__image">' .
           '<a href="' . esc_url($product->get_permalink()) . '" class="se-prod-card__image-link" aria-label="' . esc_attr(sprintf(__('Mehr Details zu %s', 'woocommerce'), $product->get_name())) . '">' .
           $product->get_image('woocommerce_thumbnail') .
           '</a>' .
           '</div>' .
           $overlay .
           $wishlist_btn .
           $quick_btn;
}
add_filter('woocommerce_before_shop_loop_item_title', function($html) {
    global $product;
    if (!$product) return $html;
    // We'll inject our markup before the title
    return se_wc_product_image_html('', $product);
}, 10, 2);

/** 2. Product Title Wrapper */
add_filter('woocommerce_template_loop_product_title', function($html) {
    return '<h3 class="se-prod-card__title"><a href="' . esc_url(get_the_permalink()) . '">' . $html . '</a></h3>';
});

/** 3. Trust Badges after Title (already hooked at priority 4) */
/** se_loop_product_trust_badges already hooked */

/** 4. Product Rating */
add_filter('woocommerce_template_loop_rating', function($html) {
    global $product;
    if (!$product || $product->get_average_rating() <= 0) return '';
    $rating = $product->get_average_rating();
    $count = $product->get_review_count();
    $full = floor($product->get_average_rating());
    $half = $product->get_average_rating() - $full >= 0.5;
    $stars = '';
    for ($i = 0; $i < 5; $i++) {
        if ($i < $full) $stars .= '★';
        elseif ($i == $full && $half) $stars .= '☆';
        else $stars .= '☆';
    }
    return '<div class="se-prod-card__rating-wrap"><div class="se-prod-card__rating" role="img" aria-label="' . esc_attr(sprintf(__('Bewertung: %s von 5', 'woocommerce'), $product->get_average_rating())) . '"><div class="se-prod-card__stars">' . $stars . '</div></div><span class="se-prod-card__rating-count">(' . absint($product->get_review_count()) . ')</span></div>';
});

/** 5. Price Wrapper */
add_filter('woocommerce_template_loop_price', function($html, $product) {
    if ($product->is_on_sale()) {
        $html = preg_replace('/(<del[^>]*>.*?<\/del>)/i', '<span class="se-prod-card__price-old">$1</span>', $html);
        $html = preg_replace('/(<ins[^>]*>.*?<\/ins>)/i', '<span class="se-prod-card__price-current">$1</span>', $html);
    } else {
        $html = '<span class="se-prod-card__price-current">' . $html . '</span>';
    }
    return '<div class="se-prod-card__price">' . $html . '</div>';
}, 10, 2);

/** 6. Add to Cart Button */
add_filter('woocommerce_loop_add_to_cart_link', function($html, $product) {
    if (!$product->is_type('simple') || !$product->is_purchasable() || !$product->is_in_stock()) {
        return $html;
    }
    $label = sprintf(__('In den Warenkorb', 'woocommerce'), $product->get_name());
    $html = sprintf(
        '<button type="button" class="se-prod-card__add btn btn--primary btn--sm" data-id="%d" aria-label="%s">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
            <span>%s</span>
        </button>',
        $product->get_id(),
        esc_attr(sprintf(__('In den Warenkorb', 'woocommerce'), $product->get_name())),
        esc_html__('In den Warenkorb', 'woocommerce')
    );
    return '<div class="se-prod-card__add-wrap">' . $html . '</div>';
}, 10, 2);

/** 7. Product Content Wrapper - inject content div after title */
add_action('woocommerce_after_shop_loop_item_title', function() {
    echo '<div class="se-prod-card__content">';
}, 20);

add_action('woocommerce_after_shop_loop_item', function() {
    echo '</div>';
}, 14);

/** 8. Product Card Inner Wrapper - overlay & quick actions */
add_action('woocommerce_before_shop_loop_item_title', function() {
    global $product;
    if (!$product) return;
    
    // Wishlist button
    $wishlist_btn = sprintf(
        '<button type="button" class="se-prod-card__wishlist" data-id="%d" aria-label="%s" title="%s">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06a5.5 5.5 0 0 0 7.78 7.78l1.06 1.06a5.5 5.5 0 0 0 7.78 7.78l1.06-1.06a5.5 5.5 0 0 0 7.78-7.78l-1.06-1.06a5.5 5.5 0 0 0-7.78 0z"></path></svg>
        </button>',
        $product->get_id(),
        esc_attr__('Zur Wunschliste hinzufügen', 'woocommerce'),
        esc_attr__('Zur Wunschliste hinzufügen', 'woocommerce')
    );
    
    // Quick view button
    $quick_btn = sprintf(
        '<button type="button" class="se-prod-card__quick" data-id="%d" aria-label="%s" title="%s">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
        </button>',
        $product->get_id(),
        esc_attr__('Schnellansicht', 'woocommerce'),
        esc_attr__('Schnellansicht', 'woocommerce')
    );
    
    // Overlay
    echo '<div class="se-prod-card__overlay"></div>';
    echo $wishlist_btn;
    echo $quick_btn;
}, 4);

/** ============================================
 * WOOCOMMERCE BLOCKS (GUTENBERG) PRODUCT CARD V2
 * ============================================ */

/** Product Image Block */
add_filter('render_block', function($block_content, $block) {
    if (empty($block['blockName']) || 'woocommerce/product-image' !== $block['blockName']) {
        return $block_content;
    }
    if (!is_shop() && !is_product_category() && !is_product_tag() && !wp_doing_ajax()) {
        return $block_content;
    }
    
    $post_id = get_the_ID();
    if (!$post_id) return $block_content;
    
    $product = wc_get_product($post_id);
    if (!$product) return $block_content;
    
    $badges_html = '';
    if ($product->is_on_sale()) {
        $badges_html .= '<span class="se-prod-card__badge se-prod-card__badge--sale">' . esc_html__('Sale', 'woocommerce') . '</span>';
    }
    if ($product->is_featured()) {
        $badges_html .= '<span class="se-prod-card__badge se-prod-card__badge--featured">' . esc_html__('Empfohlen', 'woocommerce') . '</span>';
    }
    $meta = get_post_meta($product->get_id(), '_spielend_product_new', true);
    if ($meta === 'yes') {
        $badges_html .= '<span class="se-prod-card__badge se-prod-card__badge--new">' . esc_html__('Neu', 'woocommerce') . '</span>';
    }
    $badges_html = $badges_html ? '<div class="se-prod-card__badges">' . $badges_html . '</div>' : '';
    
    $wishlist_btn = sprintf(
        '<button type="button" class="se-prod-card__wishlist" data-id="%d" aria-label="%s" title="%s">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06a5.5 5.5 0 0 0 7.78 7.78l1.06 1.06a5.5 5.5 0 0 0 7.78 7.78l1.06-1.06a5.5 5.5 0 0 0 7.78-7.78l-1.06-1.06a5.5 5.5 0 0 0-7.78 0z"></path></svg>
        </button>',
        get_the_ID(),
        esc_attr__('Zur Wunschliste hinzufügen', 'woocommerce'),
        esc_attr__('Zur Wunschliste hinzufügen', 'woocommerce')
    );
    
    $quick_btn = sprintf(
        '<button type="button" class="se-prod-card__quick" data-id="%d" aria-label="%s" title="%s">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
        </button>',
        get_the_ID(),
        esc_attr__('Schnellansicht', 'woocommerce'),
        esc_attr__('Schnellansicht', 'woocommerce')
    );
    
    $overlay = '<div class="se-prod-card__overlay"></div>';
    
    // Wrap the image
    $image_html = '<div class="se-prod-card__image">' . $block_content . '</div>';
    
    return '<div class="se-prod-card__image-wrapper">' . $badges_html . $block_content . $overlay . '</div>';
}, 10, 2);

/** Product Price Block */
add_filter('render_block', function($block_content, $block) {
    if (empty($block['blockName']) || 'woocommerce/product-price' !== $block['blockName']) {
        return $block_content;
    }
    if (!is_shop() && !is_product_category() && !is_product_tag() && !wp_doing_ajax()) {
        return $block_content;
    }
    
    $post_id = get_the_ID();
    if (!$post_id) return $block_content;
    
    $product = wc_get_product($post_id);
    if (!$product) return $block_content;
    
    // Transform price HTML to our classes
    $html = $block_content;
    if ($product->is_on_sale()) {
        $html = preg_replace('/(<del[^>]*>.*?<\/del>)/i', '<span class="se-prod-card__price-old">$1</span>', $html);
        $html = preg_replace('/(<ins[^>]*>.*?<\/ins>)/i', '<span class="se-prod-card__price-current">$1</span>', $html);
    } else {
        $html = preg_replace('/(<bdi[^>]*>.*?<\/bdi>)/i', '<span class="se-prod-card__price-current">$1</span>', $html);
    }
    return '<div class="se-prod-card__price">' . $html . '</div>';
}, 10, 2);

/** Product Button (Add to Cart) Block */
add_filter('render_block', function($block_content, $block) {
    if (empty($block['blockName']) || 'woocommerce/product-button' !== $block['blockName']) {
        return $block_content;
    }
    if (!is_shop() && !is_product_category() && !is_product_tag() && !wp_doing_ajax()) {
        return $block_content;
    }
    
    $post_id = get_the_ID();
    if (!$post_id) return $block_content;
    
    $product = wc_get_product($post_id);
    if (!$product || !$product->is_type('simple') || !$product->is_purchasable() || !$product->is_in_stock()) {
        return $block_content;
    }
    
    $label = sprintf(__('In den Warenkorb', 'woocommerce'), get_the_title());
    $btn_html = sprintf(
        '<button type="button" class="se-prod-card__add btn btn--primary btn--sm" data-id="%d" aria-label="%s">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
            <span>%s</span>
        </button>',
        get_the_ID(),
        esc_attr(sprintf(__('In den Warenkorb', 'woocommerce'), get_the_title())),
        esc_html__('In den Warenkorb', 'woocommerce')
    );
    
    return '<div class="se-prod-card__add-wrap">' . $btn_html . '</div>';
}, 10, 2);

/** Product Title Block - add our classes and link wrapper */
add_filter('render_block', function($block_content, $block) {
    if (empty($block['blockName']) || 'woocommerce/product-title' !== $block['blockName']) {
        return $block_content;
    }
    if (!is_shop() && !is_product_category() && !is_product_tag() && !wp_doing_ajax()) {
        return $block_content;
    }
    
    // Add our title classes
    $block_content = preg_replace('/<h2([^>]*)>/i', '<h3 class="se-prod-card__title"$1>', $block_content);
    $block_content = preg_replace('/<\/h2>/i', '</h3>', $block_content);
    
    // Wrap link with our class
    $block_content = preg_replace('/<a([^>]*)href="([^"]*)"([^>]*)>/i', '<a$1href="$2"$3 class="se-prod-card__title-link">', $block_content);
    
    return '<div class="se-prod-card__content">' . $block_content;
}, 10, 2);

/** Product Rating Block */
add_filter('render_block', function($block_content, $block) {
    if (empty($block['blockName']) || 'woocommerce/product-rating' !== $block['blockName']) {
        return $block_content;
    }
    if (!is_shop() && !is_product_category() && !is_product_tag() && !wp_doing_ajax()) {
        return $block_content;
    }
    
    $post_id = get_the_ID();
    if (!$post_id) return $block_content;
    
    $product = wc_get_product($post_id);
    if (!$product || $product->get_average_rating() <= 0) return '';
    
    $rating = $product->get_average_rating();
    $count = $product->get_review_count();
    $full = floor($rating);
    $half = $rating - $full >= 0.5;
    $stars = '';
    for ($i = 0; $i < 5; $i++) {
        if ($i < $full) $stars .= '★';
        elseif ($i == $full && $half) $stars .= '☆';
        else $stars .= '☆';
    }
    
    return '<div class="se-prod-card__rating-wrap"><div class="se-prod-card__rating" role="img" aria-label="' . esc_attr(sprintf(__('Bewertung: %s von 5', 'woocommerce'), $product->get_average_rating())) . '"><div class="se-prod-card__stars">' . $stars . '</div></div><span class="se-prod-card__rating-count">(' . absint($product->get_review_count()) . ')</span></div>';
}, 10, 2);

/** Wrap product card content after title */
add_filter('render_block', function($block_content, $block) {
    if (empty($block['blockName']) || 'woocommerce/product-title' !== $block['blockName']) {
        return $block_content;
    }
    if (!is_shop() && !is_product_category() && !is_product_tag() && !wp_doing_ajax()) {
        return $block_content;
    }
    // Open content div after title
    return $block_content . '<div class="se-prod-card__content">';
}, 20, 2);

/** Close content div after button */
add_filter('render_block', function($block_content, $block) {
    if (empty($block['blockName']) || 'woocommerce/product-button' !== $block['blockName']) {
        return $block_content;
    }
    if (!is_shop() && !is_product_category() && !is_product_tag() && !wp_doing_ajax()) {
        return $block_content;
    }
    return $block_content . '</div>';
}, 30, 2);

/** Image wrapper - badges, overlay, wishlist, quick view */
add_filter('render_block', function($block_content, $block) {
    if (empty($block['blockName']) || 'woocommerce/product-image' !== $block['blockName']) {
        return $block_content;
    }
    if (!is_shop() && !is_product_category() && !is_product_tag() && !wp_doing_ajax()) {
        return $block_content;
    }
    
    $post_id = get_the_ID();
    if (!$post_id) return $block_content;
    
    $product = wc_get_product($post_id);
    if (!$product) return $block_content;
    
    $badges_html = '';
    if ($product->is_on_sale()) {
        $badges_html .= '<span class="se-prod-card__badge se-prod-card__badge--sale">' . esc_html__('Sale', 'woocommerce') . '</span>';
    }
    if ($product->is_featured()) {
        $badges_html .= '<span class="se-prod-card__badge se-prod-card__badge--featured">' . esc_html__('Empfohlen', 'woocommerce') . '</span>';
    }
    $meta = get_post_meta($product->get_id(), '_spielend_product_new', true);
    if ($meta === 'yes') {
        $badges_html .= '<span class="se-prod-card__badge se-prod-card__badge--new">' . esc_html__('Neu', 'woocommerce') . '</span>';
    }
    $badges_html = $badges_html ? '<div class="se-prod-card__badges">' . $badges_html . '</div>' : '';
    
    $wishlist_btn = sprintf(
        '<button type="button" class="se-prod-card__wishlist" data-id="%d" aria-label="%s" title="%s">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06a5.5 5.5 0 0 0 7.78 7.78l1.06 1.06a5.5 5.5 0 0 0 7.78 7.78l1.06-1.06a5.5 5.5 0 0 0 7.78-7.78l-1.06-1.06a5.5 5.5 0 0 0-7.78 0z"></path></svg>
        </button>',
        get_the_ID(),
        esc_attr__('Zur Wunschliste hinzufügen', 'woocommerce'),
        esc_attr__('Zur Wunschliste hinzufügen', 'woocommerce')
    );
    
    $quick_btn = sprintf(
        '<button type="button" class="se-prod-card__quick" data-id="%d" aria-label="%s" title="%s">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
        </button>',
        get_the_ID(),
        esc_attr__('Schnellansicht', 'woocommerce'),
        esc_attr__('Schnellansicht', 'woocommerce')
    );
    
    $overlay = '<div class="se-prod-card__overlay"></div>';
    
    return '<div class="se-prod-card__image-wrapper">' . $badges_html . $block_content . $overlay . $wishlist_btn . '</div>';
}, 20, 2);

/** ============================================
 * WOOCOMMERCE PRODUCT TEMPLATE BLOCK
 * ============================================ */
add_filter('render_block', function($block_content, $block) {
    if (empty($block['blockName']) || 'woocommerce/product-template' !== $block['blockName']) {
        return $block_content;
    }
    if (!is_shop() && !is_product_category() && !is_product_tag() && !wp_doing_ajax()) {
        return $block_content;
    }
    
    // The template contains the inner blocks. We'll let them render and then wrap.
    return $block_content;
}, 10, 2);

/** Fix duplicate image wrapper issue - remove the second image filter */
remove_filter('render_block', function($block_content, $block) {
    if (empty($block['blockName']) || 'woocommerce/product-image' !== $block['blockName']) {
        return $block_content;
    }
    if (!is_shop() && !is_product_category() && !is_product_tag() && !wp_doing_ajax()) {
        return $block_content;
    }
    
    $post_id = get_the_ID();
    if (!$post_id) return $block_content;
    
    $product = wc_get_product($post_id);
    if (!$product) return $block_content;
    
    $badges_html = '';
    if ($product->is_on_sale()) {
        $badges_html .= '<span class="se-prod-card__badge se-prod-card__badge--sale">' . esc_html__('Sale', 'woocommerce') . '</span>';
    }
    if ($product->is_featured()) {
        $badges_html .= '<span class="se-prod-card__badge se-prod-card__badge--featured">' . esc_html__('Empfohlen', 'woocommerce') . '</span>';
    }
    $meta = get_post_meta($product->get_id(), '_spielend_product_new', true);
    if ($meta === 'yes') {
        $badges_html .= '<span class="se-prod-card__badge se-prod-card__badge--new">' . esc_html__('Neu', 'woocommerce') . '</span>';
    }
    $badges_html = $badges_html ? '<div class="se-prod-card__badges">' . $badges_html . '</div>' : '';
    
    $wishlist_btn = sprintf(
        '<button type="button" class="se-prod-card__wishlist" data-id="%d" aria-label="%s" title="%s">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06a5.5 5.5 0 0 0 7.78 7.78l1.06 1.06a5.5 5.5 0 0 0 7.78 7.78l1.06-1.06a5.5 5.5 0 0 0 7.78-7.78l-1.06-1.06a5.5 5.5 0 0 0-7.78 0z"></path></svg>
        </button>',
        get_the_ID(),
        esc_attr__('Zur Wunschliste hinzufügen', 'woocommerce'),
        esc_attr__('Zur Wunschliste hinzufügen', 'woocommerce')
    );
    
    $quick_btn = sprintf(
        '<button type="button" class="se-prod-card__quick" data-id="%d" aria-label="%s" title="%s">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
        </button>',
        get_the_ID(),
        esc_attr__('Schnellansicht', 'woocommerce'),
        esc_attr__('Schnellansicht', 'woocommerce')
    );
    
    $overlay = '<div class="se-prod-card__overlay"></div>';
    
    // Wrap the image
    $image_html = '<div class="se-prod-card__image">' . $block_content . '</div>';
    
    return '<div class="se-prod-card__image-wrapper">' . $badges_html . $block_content . $overlay . $wishlist_btn . '</div>';
}, 10, 2);

/** Product Price Block - ensure price-old and price-current classes */
add_filter('render_block', function($block_content, $block) {
    if (empty($block['blockName']) || 'woocommerce/product-price' !== $block['blockName']) {
        return $block_content;
    }
    if (!is_shop() && !is_product_category() && !is_product_tag() && !wp_doing_ajax()) {
        return $block_content;
    }
    
    $post_id = get_the_ID();
    if (!$post_id) return $block_content;
    
    $product = wc_get_product($post_id);
    if (!$product) return $block_content;
    
    $html = $block_content;
    if ($product->is_on_sale()) {
        $html = preg_replace('/(<del[^>]*>.*?<\/del>)/i', '<span class="se-prod-card__price-old">$1</span>', $html);
        $html = preg_replace('/(<ins[^>]*>.*?<\/ins>)/i', '<span class="se-prod-card__price-current">$1</span>', $html);
    } else {
        $html = preg_replace('/(<bdi[^>]*>.*?<\/bdi>)/i', '<span class="se-prod-card__price-current">$1</span>', $html);
    }
    return '<div class="se-prod-card__price">' . $html . '</div>';
}, 10, 2);

/** Product Rating Block */
add_filter('render_block', function($block_content, $block) {
    if (empty($block['blockName']) || 'woocommerce/product-rating' !== $block['blockName']) {
        return $block_content;
    }
    if (!is_shop() && !is_product_category() && !is_product_tag() && !wp_doing_ajax()) {
        return $block_content;
    }
    
    $post_id = get_the_ID();
    if (!$post_id) return $block_content;
    
    $product = wc_get_product($post_id);
    if (!$product || $product->get_average_rating() <= 0) return '';
    
    $rating = $product->get_average_rating();
    $count = $product->get_review_count();
    $full = floor($rating);
    $half = $rating - $full >= 0.5;
    $stars = '';
    for ($i = 0; $i < 5; $i++) {
        if ($i < $full) $stars .= '★';
        elseif ($i == $full && $half) $stars .= '☆';
        else $stars .= '☆';
    }
    
    return '<div class="se-prod-card__rating-wrap"><div class="se-prod-card__rating" role="img" aria-label="' . esc_attr(sprintf(__('Bewertung: %s von 5', 'woocommerce'), $product->get_average_rating())) . '"><div class="se-prod-card__stars">' . $stars . '</div></div><span class="se-prod-card__rating-count">(' . absint($product->get_review_count()) . ')</span></div>';
}, 10, 2);

/** Product Title Block - add our classes and wrap content */
add_filter('render_block', function($block_content, $block) {
    if (empty($block['blockName']) || 'woocommerce/product-title' !== $block['blockName']) {
        return $block_content;
    }
    if (!is_shop() && !is_product_category() && !is_product_tag() && !wp_doing_ajax()) {
        return $block_content;
    }
    
    // Add our title classes
    $block_content = preg_replace('/<h2([^>]*)>/i', '<h3 class="se-prod-card__title"$1>', $block_content);
    $block_content = preg_replace('/<\/h2>/i', '</h3>', $block_content);
    
    // Wrap link with our class
    $block_content = preg_replace('/<a([^>]*)href="([^"]*)"([^>]*)>/i', '<a$1href="$2"$3 class="se-prod-card__title-link">', $block_content);
    
    return '<div class="se-prod-card__content">' . $block_content;
}, 10, 2);

/** Close content div after button */
add_filter('render_block', function($block_content, $block) {
    if (empty($block['blockName']) || 'woocommerce/product-button' !== $block['blockName']) {
        return $block_content;
    }
    if (!is_shop() && !is_product_category() && !is_product_tag() && !wp_doing_ajax()) {
        return $block_content;
    }
    return $block_content . '</div>';
}, 30, 2);

/** Image wrapper - badges, overlay, wishlist, quick view - SINGLE FILTER */
add_filter('render_block', function($block_content, $block) {
    if (empty($block['blockName']) || 'woocommerce/product-image' !== $block['blockName']) {
        return $block_content;
    }
    if (!is_shop() && !is_product_category() && !is_product_tag() && !wp_doing_ajax()) {
        return $block_content;
    }
    
    $post_id = get_the_ID();
    if (!$post_id) return $block_content;
    
    $product = wc_get_product($post_id);
    if (!$product) return $block_content;
    
    $badges_html = '';
    if ($product->is_on_sale()) {
        $badges_html .= '<span class="se-prod-card__badge se-prod-card__badge--sale">' . esc_html__('Sale', 'woocommerce') . '</span>';
    }
    if ($product->is_featured()) {
        $badges_html .= '<span class="se-prod-card__badge se-prod-card__badge--featured">' . esc_html__('Empfohlen', 'woocommerce') . '</span>';
    }
    $meta = get_post_meta($product->get_id(), '_spielend_product_new', true);
    if ($meta === 'yes') {
        $badges_html .= '<span class="se-prod-card__badge se-prod-card__badge--new">' . esc_html__('Neu', 'woocommerce') . '</span>';
    }
    $badges_html = $badges_html ? '<div class="se-prod-card__badges">' . $badges_html . '</div>' : '';
    
    $wishlist_btn = sprintf(
        '<button type="button" class="se-prod-card__wishlist" data-id="%d" aria-label="%s" title="%s">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06a5.5 5.5 0 0 0 7.78 7.78l1.06 1.06a5.5 5.5 0 0 0 7.78 7.78l1.06-1.06a5.5 5.5 0 0 0 7.78-7.78l-1.06-1.06a5.5 5.5 0 0 0-7.78 0z"></path></svg>
        </button>',
        get_the_ID(),
        esc_attr__('Zur Wunschliste hinzufügen', 'woocommerce'),
        esc_attr__('Zur Wunschliste hinzufügen', 'woocommerce')
    );
    
    $quick_btn = sprintf(
        '<button type="button" class="se-prod-card__quick" data-id="%d" aria-label="%s" title="%s">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
        </button>',
        get_the_ID(),
        esc_attr__('Schnellansicht', 'woocommerce'),
        esc_attr__('Schnellansicht', 'woocommerce')
    );
    
    $overlay = '<div class="se-prod-card__overlay"></div>';
    
    return '<div class="se-prod-card__image-wrapper">' . $badges_html . $block_content . $overlay . $wishlist_btn . '</div>';
}, 20, 2);
