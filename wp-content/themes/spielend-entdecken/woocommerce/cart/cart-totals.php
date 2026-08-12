<?php
if (!defined('ABSPATH')) exit;

$totals = WC()->cart->get_totals();

if (empty($totals)) return;
?>

<div class="se-cart-totals-wrapper">
    <h2><?php esc_html_e('Warenkorb-Summe', 'woocommerce'); ?></h2>

    <table class="shop_table shop_table_responsive se-cart-totals">
        <tbody>
            <tr class="cart-subtotal">
                <th><?php esc_html_e('Zwischensumme', 'woocommerce'); ?></th>
                <td data-title="<?php esc_attr_e('Zwischensumme', 'woocommerce'); ?>"><?php wc_cart_totals_subtotal_html(); ?></td>
            </tr>

            <?php foreach (WC()->cart->get_coupons() as $code => $coupon) : ?>
                <tr class="cart-discount coupon-<?php echo esc_attr(sanitize_title($code)); ?>">
                    <th><?php wc_cart_totals_coupon_label($coupon); ?></th>
                    <td data-title="<?php echo esc_attr(wc_cart_totals_coupon_label($coupon)); ?>"><?php wc_cart_totals_coupon_html($coupon); ?></td>
                </tr>
            <?php endforeach; ?>

            <?php if (WC()->cart->needs_shipping() && WC()->cart->show_shipping()) : ?>
                <?php do_action('woocommerce_cart_totals_before_shipping'); ?>
                <?php wc_cart_totals_shipping_html(); ?>
                <?php do_action('woocommerce_cart_totals_after_shipping'); ?>
            <?php elseif (WC()->cart->needs_shipping() && 'yes' === get_option('woocommerce_enable_shipping_calc')) : ?>
                <tr class="shipping">
                    <th><?php esc_html_e('Versand', 'woocommerce'); ?></th>
                    <td data-title="<?php esc_attr_e('Versand', 'woocommerce'); ?>">
                        <a href="#" class="se-shipping-calculator-trigger" data-toggle="shipping-calculator"><?php esc_html_e('Versandkosten berechnen', 'woocommerce'); ?></a>
                        <div class="se-shipping-calculator" style="display:none;">
                            <?php woocommerce_shipping_calculator(); ?>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>

            <?php foreach (WC()->cart->get_fees() as $fee) : ?>
                <tr class="fee">
                    <th><?php echo esc_html($fee->name); ?></th>
                    <td data-title="<?php echo esc_attr($fee->name); ?>"><?php wc_cart_totals_fee_html($fee); ?></td>
                </tr>
            <?php endforeach; ?>

            <?php if (wc_tax_enabled() && !WC()->cart->display_prices_including_tax()) : ?>
                <?php if ('itemized' === get_option('woocommerce_tax_total_display')) : ?>
                    <?php foreach (WC()->cart->get_tax_totals() as $code => $tax) : ?>
                        <tr class="tax-rate tax-rate-<?php echo esc_attr(sanitize_title($code)); ?>">
                            <th><?php echo esc_html($tax->label); ?></th>
                            <td data-title="<?php echo esc_attr($tax->label); ?>"><?php echo wp_kses_post($tax->formatted_amount); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr class="tax-total">
                        <th><?php esc_html_e('MwSt.', 'woocommerce'); ?></th>
                        <td data-title="<?php esc_attr_e('MwSt.', 'woocommerce'); ?>"><?php wc_cart_totals_taxes_total_html(); ?></td>
                    </tr>
                <?php endif; ?>
            <?php endif; ?>

            <?php do_action('woocommerce_cart_totals_before_order_total'); ?>

            <tr class="order-total">
                <th><?php esc_html_e('Gesamt', 'woocommerce'); ?></th>
                <td data-title="<?php esc_attr_e('Gesamt', 'woocommerce'); ?>"><?php wc_cart_totals_order_total_html(); ?></td>
            </tr>

            <?php do_action('woocommerce_cart_totals_after_order_total'); ?>
        </tbody>
    </table>

    <div class="wc-proceed-to-checkout">
        <a href="<?php echo esc_url(wc_get_checkout_url()); ?>" class="checkout-button button alt wc-forward">
            <?php esc_html_e('Zur Kasse', 'woocommerce'); ?>
        </a>
    </div>

    <?php do_action('woocommerce_proceed_to_checkout'); ?>

    <?php wc_print_notices(); ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const trigger = document.querySelector('.se-shipping-calculator-trigger');
    if (trigger) {
        trigger.addEventListener('click', function(e) {
            e.preventDefault();
            const calc = document.querySelector('.se-shipping-calculator');
            if (calc) {
                calc.style.display = calc.style.display === 'none' ? 'block' : 'none';
            }
        });
    }
});
</script>