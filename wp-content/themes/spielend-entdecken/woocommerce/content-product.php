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

<li class="product se-product-card" data-product-id="<?php echo esc_attr($product_id); ?>" data-product-sku="<?php echo esc_attr($product_sku); ?>">
    <div class="se-product-inner">
        <?php if ($product_is_on_sale) : ?>
            <span class="se-sale-badge"><?php echo esc_html__('Sale', 'woocommerce'); ?></span>
        <?php endif; ?>
        
        <?php if (!$product_in_stock) : ?>
            <span class="se-out-of-stock-badge"><?php echo esc_html__('Nicht verfügbar', 'woocommerce'); ?></span>
        <?php endif; ?>

        <div class="se-product-image-wrapper">
            <a href="<?php echo esc_url($product_url); ?>" class="se-product-image-link" aria-label="<?php echo esc_attr(sprintf(__('Mehr Details zu %s', 'woocommerce'), $product_title)); ?>">
                <?php echo $product_image; ?>
            </a>
            
            <div class="se-product-actions">
                <?php if ($product->is_type('simple') && $product->is_purchasable() && $product_in_stock) : ?>
                    <button type="button" class="se-quick-add button" data-product-id="<?php echo esc_attr($product_id); ?>" aria-label="<?php echo esc_attr(sprintf(__('%s in den Warenkorb', 'woocommerce'), $product_title)); ?>">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4z"/><line x1="3" y1="6" x2="21" y2="6"/><path d="M16 10a4 4 0 0 1-8 0"/></svg>
                        <span><?php esc_html_e('In den Warenkorb', 'woocommerce'); ?></span>
                    </button>
                <?php endif; ?>
                
                <button type="button" class="se-quick-view button" data-product-id="<?php echo esc_attr($product_id); ?>" aria-label="<?php echo esc_attr(sprintf(__('Schnellansicht %s', 'woocommerce'), $product_title)); ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span class="screen-reader-text"><?php echo esc_html__('Schnellansicht', 'woocommerce'); ?></span>
                </button>
                
                <button type="button" class="se-wishlist-toggle button" data-product-id="<?php echo esc_attr($product_id); ?>" aria-label="<?php echo esc_attr(sprintf(__('%s zur Wunschliste hinzufügen', 'woocommerce'), $product_title)); ?>">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"/></svg>
                    <span class="screen-reader-text"><?php esc_html_e('Zur Wunschliste', 'woocommerce'); ?></span>
                </button>
            </div>
        </div>

        <div class="se-product-content">
            <?php if ($product_categories) : ?>
                <div class="se-product-categories">
                    <?php echo implode(', ', array_map(function($cat) { return '<span class="se-category-tag">'.esc_html($cat).'</span>'; }, $product_categories)); ?>
                </div>
            <?php endif; ?>

            <h3 class="se-product-title">
                <a href="<?php echo esc_url($product_url); ?>"><?php echo esc_html($product_title); ?></a>
            </h3>

            <?php if ($product_rating > 0) : ?>
                <div class="se-product-rating" role="img" aria-label="<?php echo sprintf(esc_attr__('Bewertung: %s von 5', 'woocommerce'), $product_rating); ?>">
                    <div class="se-star-rating" style="width:<?php echo esc_attr($product_rating / 5 * 100); ?>%"></div>
                    <span class="se-review-count">(<?php echo absint($product_review_count); ?>)</span>
                </div>
            <?php endif; ?>

            <?php if ($product_short_description) : ?>
                <div class="se-product-excerpt"><?php echo wp_kses_post(wp_trim_words($product_short_description, 20)); ?></div>
            <?php endif; ?>

            <div class="se-product-price-wrapper">
                <span class="se-product-price"><?php echo $product_price; ?></span>
            </div>

            <div class="se-product-footer">
                <?php if ($product->is_type('simple') && $product->is_purchasable() && $product_in_stock) : ?>
                    <?php woocommerce_template_loop_add_to_cart(); ?>
                <?php elseif (!$product_in_stock) : ?>
                    <button type="button" class="button disabled" disabled><?php esc_html_e('Nicht verfügbar', 'woocommerce'); ?></button>
                <?php else : ?>
                    <a href="<?php echo esc_url($product_url); ?>" class="button"><?php esc_html_e('Details anzeigen', 'woocommerce'); ?></a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</li>