<?php
if (!defined('ABSPATH')) exit;

$current_user = wp_get_current_user();
$customer_id = get_current_user_id();
$order_count = wc_get_customer_order_count($customer_id);
$download_count = wc_get_customer_download_count($customer_id);
?>

<div class="woocommerce-MyAccount se-myaccount">
    <header class="se-myaccount-header">
        <h1><?php printf(esc_html__('Hallo, %s!', 'woocommerce'), esc_html($current_user->display_name)); ?></h1>
        <p class="se-member-since">Kunde seit <?php echo date_i18n(get_option('date_format'), strtotime($current_user->user_registered)); ?></p>
    </header>

    <div class="se-myaccount-stats">
        <div class="se-stat-card">
            <span class="se-stat-number"><?php echo absint($order_count); ?></span>
            <span class="se-stat-label">Bestellungen</span>
        </div>
        <div class="se-stat-card">
            <span class="se-stat-number"><?php echo absint($download_count); ?></span>
            <span class="se-stat-label">Downloads</span>
        </div>
        <div class="se-stat-card">
            <span class="se-stat-number"><?php echo absint(count(get_user_meta($customer_id, '_wc_wishlist', true) ?: [])); ?></span>
            <span class="se-stat-label">Wunschliste</span>
        </div>
        <div class="se-stat-card">
            <a href="<?php echo esc_url(wc_get_endpoint_url('edit-address', 'billing')); ?>" class="se-stat-link">
                <span class="se-stat-number">1</span>
                <span class="se-stat-label">Adressen</span>
            </a>
        </div>
    </div>

    <div class="se-myaccount-sections">
        <section class="se-section">
            <header class="se-section-header">
                <h2>Letzte Bestellungen</h2>
                <a href="<?php echo esc_url(wc_get_endpoint_url('orders')); ?>" class="se-view-all">Alle anzeigen →</a>
            </header>
            <?php
            $recent_orders = wc_get_orders([
                'customer_id' => $customer_id,
                'limit' => 3,
                'orderby' => 'date',
                'order' => 'DESC',
            ]);
            if ($recent_orders) : ?>
                <div class="se-orders-table">
                    <table>
                        <thead>
                            <tr>
                                <th>Bestellnummer</th>
                                <th>Datum</th>
                                <th>Status</th>
                                <th>Summe</th>
                                <th>Aktion</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_orders as $order) : ?>
                                <tr>
                                    <td><a href="<?php echo esc_url($order->get_view_order_url()); ?>"><?php echo esc_html($order->get_order_number()); ?></a></td>
                                    <td><?php echo date_i18n(get_option('date_format'), strtotime($order->get_date_created())); ?></td>
                                    <td><span class="se-order-status status-<?php echo esc_attr($order->get_status()); ?>"><?php echo esc_html(wc_get_order_status_name($order->get_status())); ?></span></td>
                                    <td><?php echo $order->get_formatted_order_total(); ?></td>
                                    <td><a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="button">Details</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else : ?>
                <p><?php esc_html_e('Du hast noch keine Bestellungen aufgegeben.', 'woocommerce'); ?></p>
                <a href="<?php echo esc_url(wc_get_endpoint_url('orders')); ?>" class="button"><?php esc_html_e('Jetzt shoppen', 'woocommerce'); ?></a>
            <?php endif; ?>
        </section>

        <section class="se-section">
            <header class="se-section-header">
                <h2>Downloads</h2>
                <a href="<?php echo esc_url(wc_get_endpoint_url('downloads')); ?>" class="se-view-all">Alle anzeigen →</a>
            </header>
            <?php
            $downloads = WC()->customer->get_downloadable_products();
            if ($downloads) : ?>
                <ul class="se-downloads-list">
                    <?php foreach (array_slice($downloads, 0, 3) as $download) : ?>
                        <li>
                            <a href="<?php echo esc_url($download['download_url']); ?>" class="se-download-link">
                                <span class="se-download-name"><?php echo esc_html($download['product_name']); ?></span>
                                <span class="se-download-date"><?php echo esc_html($download['download_name']); ?></span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else : ?>
                <p><?php esc_html_e('Keine Downloads verfügbar.', 'woocommerce'); ?></p>
            <?php endif; ?>
        </section>

        <section class="se-section">
            <header class="se-section-header">
                <h2>Deine Wunschliste</h2>
                <a href="<?php echo esc_url(wc_get_endpoint_url('wishlist')); ?>" class="se-view-all">Anzeigen →</a>
            </header>
            <p><?php esc_html_e('Produkte, die du für später gespeichert hast.', 'woocommerce'); ?></p>
            <a href="<?php echo esc_url(wc_get_endpoint_url('wishlist')); ?>" class="button">Zur Wunschliste</a>
        </section>

        <section class="se-section">
            <header class="se-section-header">
                <h2>Kürzlich angesehen</h2>
                <a href="#" class="se-view-all">Anzeigen →</a>
            </header>
            <div class="se-recently-viewed">
                <?php
                $recently_viewed = get_user_meta($customer_id, '_wc_recently_viewed', true);
                if ($recently_viewed) {
                    $product_ids = array_slice(array_reverse($recently_viewed), 0, 4);
                    foreach ($product_ids as $pid) {
                        $product = wc_get_product($pid);
                        if ($product) {
                            echo '<a href="' . esc_url($product->get_permalink()) . '" class="se-recent-product">';
                            echo $product->get_image('woocommerce_thumbnail');
                            echo '<span>' . esc_html($product->get_name()) . '</span>';
                            echo '</a>';
                        }
                    }
                } else {
                    esc_html_e('Noch keine Produkte angesehen.', 'woocommerce');
                }
                ?>
            </div>
        </section>
    </div>
</div>