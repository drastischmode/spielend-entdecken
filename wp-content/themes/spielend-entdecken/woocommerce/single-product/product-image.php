<?php
if (!defined('ABSPATH')) exit;

global $product;

if (!$product) return;

$product_id = $product->get_id();
$image_ids = $product->get_gallery_image_ids();
$has_gallery = $image_ids && $product->get_image_id();
$main_image = $product->get_image('large');
$thumbnail_size = apply_filters('single_product_small_thumbnail_size', 'shop_thumbnail');
?>

<div class="woocommerce-product-gallery woocommerce-product-gallery--with-images woocommerce-product-gallery--columns-4"
     data-columns="4"
     data-allow_zoom="true"
     data-allow_lightbox="true"
     data-allow_slider="true"
     data-product-id="<?php echo $product_id; ?>"
     style="opacity: 1; transition: opacity .25s ease-in-out;">

    <figure class="woocommerce-product-gallery__wrapper">
        <div class="woocommerce-product-gallery__image slide first"
             data-thumb="<?php echo esc_url(wp_get_attachment_image_url($product->get_image_id(), $thumbnail_size)); ?>">
            <?php echo $main_image; ?>
        </div>

        <?php if ($has_gallery && $image_ids): ?>
            <?php foreach ($image_ids as $image_id): ?>
                <div class="woocommerce-product-gallery__image slide"
                     data-thumb="<?php echo esc_url(wp_get_attachment_image_url($image_id, $thumbnail_size)); ?>">
                    <?php echo wp_get_attachment_image($image_id, 'large'); ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </figure>

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
</div>