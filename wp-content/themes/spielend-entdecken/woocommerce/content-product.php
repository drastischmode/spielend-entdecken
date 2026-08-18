<?php
if (!defined('ABSPATH')) exit;

global $product;

if (!is_a($product, 'WC_Product')) {
    $product = wc_get_product(get_the_ID());
}

if (!$product) return;

$product_id = $product->get_id();
$product_url = $product->get_permalink();
$product_title = $product->get_name();
$product_image = $product->get_image('woocommerce_thumbnail');
$product_price = $product->get_price_html();
$product_rating = $product->get_average_rating();
$product_review_count = $product->get_review_count();
$product_is_on_sale = $product->is_on_sale();
$product_sku = $product->get_sku();
$product_categories = wp_get_post_terms($product_id, 'product_cat', ['fields' => 'names']);
$product_in_stock = $product->is_in_stock();
$product_short_description = $product->get_short_description();
?>

<li class="product se-prod-card" data-product-id="<?php echo esc_attr($product_id); ?>" data-product-sku="<?php echo esc_attr($product_sku); ?>">
    <!-- Badges -->
    <?php
    $badges_html = '';
    if ($product_is_on_sale) {
        $badges_html .= '<span class="se-prod-card__badge se-prod-card__badge--sale">' . esc_html__('Sale', 'woocommerce') . '</span>';
    }
    if ($product->is_featured()) {
        $badges_html .= '<span class="se-prod-card__badge se-prod-card__badge--featured">' . esc_html__('Empfohlen', 'woocommerce') . '</span>';
    }
    $meta = get_post_meta($product->get_id(), '_spielend_product_new', true);
    if ($meta === 'yes') {
        $badges_html .= '<span class="se-prod-card__badge se-prod-card__badge--new">' . esc_html__('Neu', 'woocommerce') . '</span>';
    }
    if (!empty($badges_html)) {
        echo '<div class="se-prod-card__badges">' . $badges_html . '</div>';
    }
    ?>

    <!-- Wishlist Button -->
    <button type="button" class="se-prod-card__wishlist" data-id="<?php echo esc_attr($product_id); ?>" aria-label="<?php echo esc_attr(sprintf(__('Zur Wunschliste hinzufügen', 'woocommerce'), $product_title)); ?>" title="<?php echo esc_attr(sprintf(__('Zur Wunschliste hinzufügen', 'woocommerce'), $product_title)); ?>">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06a5.5 5.5 0 0 0 7.78 7.78l1.06 1.06a5.5 5.5 0 0 0 7.78 7.78l1.06-1.06a5.5 5.5 0 0 0 7.78-7.78l-1.06-1.06a5.5 5.5 0 0 0-7.78 0z"></path></svg>
    </button>

    <!-- Quick View Button -->
    <button type="button" class="se-prod-card__quick" data-id="<?php echo esc_attr($product_id); ?>" aria-label="<?php echo esc_attr(sprintf(__('Schnellansicht %s', 'woocommerce'), $product_title)); ?>" title="<?php echo esc_attr(sprintf(__('Schnellansicht', 'woocommerce'), $product_title)); ?>">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>
    </button>

    <!-- Product Image -->
    <div class="se-prod-card__image">
        <a href="<?php echo esc_url($product_url); ?>" class="se-prod-card__image-link" aria-label="<?php echo esc_attr(sprintf(__('Mehr Details zu %s', 'woocommerce'), $product_title)); ?>">
            <?php
            $img_html = $product->get_image('woocommerce_thumbnail');
            if (empty($img_html)) {
                echo '<span class="se-prod-card__placeholder" aria-hidden="true"></span>';
            } else {
                echo $img_html;
            }
            ?>
        </a>
    </div>

    <div class="se-prod-card__overlay"></div>

    <!-- Product Content -->
    <div class="se-prod-card__content">
        <?php if ($product->get_categories()) : ?>
        <div class="se-prod-card__categories">
            <?php echo implode(', ', array_map(function($cat) { return '<span class="se-category-tag">'.esc_html($cat).'</span>'; }, $product->get_categories())); ?>
        </div>
        <?php endif; ?>

        <h3 class="se-prod-card__title">
            <a href="<?php echo esc_url($product->get_permalink()); ?>"><?php echo esc_html($product->get_name()); ?></a>
        </h3>

        <?php if ($product->get_average_rating() > 0) : ?>
        <div class="se-prod-card__rating-wrap">
            <div class="se-prod-card__rating" role="img" aria-label="<?php echo sprintf(esc_attr__('Bewertung: %s von 5', 'woocommerce'), $product->get_average_rating()); ?>">
                <div class="se-prod-card__stars">
                    <?php
                    $rating = $product->get_average_rating();
                    $full_stars = floor($rating);
                    $has_half = $rating - $full_stars >= 0.5;
                    for ($i = 0; $i < 5; $i++) {
                        if ($i < $full_stars) {
                            echo '★';
                        } elseif ($i == $full_stars && $rating - $full_stars >= 0.5) {
                            echo '☆';
                        } else {
                            echo '☆';
                        }
                    }
                    ?>
                </div>
                <span class="se-prod-card__rating-count">(<?php echo absint($product->get_review_count()); ?>)</span>
            </div>
        </div>
        <?php endif; ?>

        <div class="se-prod-card__price">
            <?php
            $price_html = $product->get_price_html();
            if ($product->is_on_sale()) {
                $price_html = preg_replace(
                    '/(<del[^>]*>.*?<\/del>)/i',
                    '<span class="se-prod-card__price-old">$1</span>',
                    $price_html
                );
                $price_html = preg_replace(
                    '/(<ins[^>]*>.*?<\/ins>)/i',
                    '<span class="se-prod-card__price-current">$1</span>',
                    $price_html
                );
            } else {
                $price_html = '<span class="se-prod-card__price-current">' . $price_html . '</span>';
            }
            echo $price_html;
            ?>
        </div>

        <!-- Add to Cart Button -->
        <?php if ($product->is_type('simple') && $product->is_purchasable() && $product->is_in_stock()) : ?>
        <button type="button" class="se-prod-card__add btn btn--primary btn--sm" data-id="<?php echo esc_attr($product->get_id()); ?>" aria-label="<?php echo esc_attr(sprintf(__('In den Warenkorb', 'woocommerce'), $product->get_name())); ?>">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"></path><line x1="3" y1="6" x2="21" y2="6"></line><path d="M16 10a4 4 0 0 1-8 0"></path></svg>
            <span><?php esc_html_e('In den Warenkorb', 'woocommerce'); ?></span>
        </button>
        <?php elseif (!$product->is_in_stock()) : ?>
        <button type="button" class="se-prod-card__add btn btn--primary btn--sm" disabled><?php esc_html_e('Nicht verfügbar', 'woocommerce'); ?></button>
        <?php else : ?>
        <a href="<?php echo esc_url($product->get_permalink()); ?>" class="btn btn--outline btn--sm"><?php esc_html_e('Details anzeigen', 'woocommerce'); ?></a>
        <?php endif; ?>
    </div>
</li>