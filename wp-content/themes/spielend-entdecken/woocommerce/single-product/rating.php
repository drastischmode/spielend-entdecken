<?php
if (!defined('ABSPATH')) exit;

global $product;

if (!wc_review_ratings_enabled() || !$product->get_review_count()) return;
?>

<div class="woocommerce-product-rating">
    <div class="star-rating" role="img" aria-label="<?php echo sprintf(esc_attr__('Bewertung: %s von 5', 'woocommerce'), $product->get_average_rating()); ?>">
        <span style="width:<?php echo esc_attr($product->get_average_rating() / 5 * 100); ?>%"></span>
    </div>
    <a href="#reviews" class="woocommerce-review-link" rel="nofollow">
        <?php
        printf(
            _n('%s Bewertung', '%s Bewertungen', $product->get_review_count(), 'woocommerce'),
            '<span class="count">' . absint($product->get_review_count()) . '</span>'
        );
        ?>
    </a>
</div>