<?php
/**
 * form-checkout.php – Custom WooCommerce Checkout
 */
if (!defined('ABSPATH')) exit;
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1">
<?php wp_head(); ?>
</head>
<body <?php body_class('se-checkout-body'); ?>>
<?php
do_action('woocommerce_before_checkout_form', $checkout);

if ($checkout->is_registration_enabled() && $checkout->is_registration_required() && !is_user_logged_in()) {
    echo '<p>' . esc_html__('Bitte melde dich an, um zur Kasse zu gehen.', 'woocommerce') . '</p>';
    echo '<div class="se-checkout-login">' . do_shortcode('[woocommerce_my_account]') . '</div>';
    return;
}

wc_print_notices();
?>

<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url(wc_get_checkout_url()); ?>" enctype="multipart/form-data">
    <div class="se-checkout-grid">
        <div class="se-checkout-billing">
            <h3><?php esc_html_e('Rechnungsdetails', 'woocommerce'); ?></h3>
            <?php do_action('woocommerce_checkout_billing', $checkout); ?>
            <h3><?php esc_html_e('Zusätzliche Angaben', 'woocommerce'); ?></h3>
            <?php do_action('woocommerce_checkout_shipping', $checkout); ?>
        </div>
        <div class="se-checkout-summary">
            <h3><?php esc_html_e('Deine Bestellung', 'woocommerce'); ?></h3>
            <?php do_action('woocommerce_checkout_before_order_review'); ?>
            <div id="order_review" class="woocommerce-checkout-review-order">
                <?php do_action('woocommerce_checkout_order_review'); ?>
            </div>
            <?php do_action('woocommerce_checkout_after_order_review'); ?>
        </div>
    </div>
</form>

<style>
.se-checkout-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 2rem;
    max-width: 1200px;
    margin: 0 auto;
    padding: 2rem;
}
.se-checkout-billing {
    background: #fff;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
.se-checkout-summary {
    background: #f8f8f8;
    border-radius: 12px;
    padding: 2rem;
    box-shadow: 0 2px 8px rgba(0,0,0,0.08);
}
@media (max-width: 768px) {
    .se-checkout-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<?php do_action('woocommerce_after_checkout_form', $checkout); ?>
<?php wp_footer(); ?>
</body>
</html>
