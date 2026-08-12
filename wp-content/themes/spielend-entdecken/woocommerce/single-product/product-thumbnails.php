<?php
if (!defined('ABSPATH')) exit;

global $product;

if (!$product) return;

$image_ids = $product->get_gallery_image_ids();
$has_gallery = $image_ids && $product->get_image_id();
$thumbnail_size = apply_filters('single_product_small_thumbnail_size', 'shop_thumbnail');
?>

<div class="woocommerce-product-gallery__trigger flex-control-nav flex-control-thumbs" role="list" aria-label="Produktbilder">
    <button type="button" class="flex-active" data-index="0" aria-label="Bild 1">
        <?php echo wp_get_attachment_image($product->get_image_id(), $thumbnail_size); ?>
    </button>
    <?php if ($has_gallery && $image_ids): ?>
        <?php $i = 1; foreach ($image_ids as $image_id): ?>
            <button type="button" data-index="<?php echo $i++; ?>" aria-label="Bild <?php echo $i; ?>">
                <?php echo wp_get_attachment_image($image_id, $thumbnail_size); ?>
            </button>
        <?php endforeach; ?>
    <?php endif; ?>
</div>