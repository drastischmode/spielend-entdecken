<?php
if (!defined('ABSPATH')) exit;

/**
 * Verbessertes Dashboard-Widget (ersetzt se_render_dashboard_widget in functions.php)
 * Zeigt: KPI-Karten, Produkt-Meta-Status (Trust-Badges), Schnellzugriff, ToDos
 */

function se_optimized_dashboard_widget() {
    wp_add_dashboard_widget(
        'spielend_overview_v2',
        'Spielend Entdecken – Übersicht',
        'se_render_optimized_dashboard_widget',
        null,
        array(),
        'high'
    );
}
add_action('wp_dashboard_setup', 'se_optimized_dashboard_widget');

function se_render_optimized_dashboard_widget() {
    $orders_open = 0;
    $sales_today = 0;
    $products = 0;
    $reviews = 0;
    $products_with_age = 0;
    $products_sustainable = 0;
    $orders_pending = 0;

    if (class_exists('WooCommerce')) {
        $orders_open = count(wc_get_orders(array('status' => array('processing', 'on-hold'), 'limit' => -1)));
        $today = date('Y-m-d');
        $orders_today = wc_get_orders(array('date_created' => '>=' . $today, 'status' => array('completed', 'processing'), 'limit' => -1));
        foreach ($orders_today as $o) {
            $sales_today += (float) $o->get_total();
        }
        $products = (int) wp_count_posts('product')->publish;

        // Trust-Meta-Status
        if ($products > 0) {
            $ids = get_posts(array('post_type' => 'product', 'post_status' => 'publish', 'numberposts' => -1, 'fields' => 'ids'));
            foreach ($ids as $id) {
                if (get_post_meta($id, '_se_age_rating', true)) $products_with_age++;
                if ('yes' === get_post_meta($id, '_se_sustainable', true)) $products_sustainable++;
            }
        }
    }
    $reviews = (int) get_comments(array('post_type' => 'product', 'status' => 'approve', 'count' => true));
    ?>
<div class="se-dash">
    <div class="se-dash-kpis">
        <div class="se-dash-kpi" style="background:#FFF8F0;border-color:#FFE0C2;">
            <div class="se-dash-num" style="color:#CC4D00;"><?php echo esc_html($orders_open); ?></div>
            <div class="se-dash-label">Offene Bestellungen</div>
        </div>
        <div class="se-dash-kpi" style="background:#F0FAF6;border-color:#C8E8DC;">
            <div class="se-dash-num" style="color:#2B7A62;"><?php echo number_format($sales_today, 2, ',', '.'); ?> €</div>
            <div class="se-dash-label">Umsatz heute</div>
        </div>
        <div class="se-dash-kpi" style="background:#FFFBE6;border-color:#F5E6A8;">
            <div class="se-dash-num" style="color:#B8860B;"><?php echo esc_html($products); ?></div>
            <div class="se-dash-label">Produkte im Shop</div>
        </div>
        <div class="se-dash-kpi" style="background:#F3F0FF;border-color:#DED6F5;">
            <div class="se-dash-num" style="color:#6C4DF4;"><?php echo esc_html($reviews); ?></div>
            <div class="se-dash-label">Bewertungen</div>
        </div>
    </div>

    <?php if (class_exists('WooCommerce') && $products > 0) : ?>
    <h4>Trust-Elemente (Altersempfehlung &amp; Nachhaltigkeit)</h4>
    <div class="se-dash-progress">
        <div class="se-dash-progress-row">
            <span>Altersempfehlung</span>
            <div class="se-dash-bar"><div style="width:<?php echo $products ? round($products_with_age / $products * 100) : 0; ?>%"></div></div>
            <strong><?php echo esc_html($products_with_age); ?>/<?php echo esc_html($products); ?></strong>
        </div>
        <div class="se-dash-progress-row">
            <span>Nachhaltig markiert</span>
            <div class="se-dash-bar"><div style="width:<?php echo $products ? round($products_sustainable / $products * 100) : 0; ?>%"></div></div>
            <strong><?php echo esc_html($products_sustainable); ?>/<?php echo esc_html($products); ?></strong>
        </div>
    </div>
    <?php if ($products_with_age < $products || $products_sustainable < $products) : ?>
        <p style="font-size:12px;color:#B8860B;margin:8px 0 0;">
            💡 Tipp: Fülle die Trust-Elemente für mehr Kundenvertrauen. Unter <a href="tools.php?page=se-bulk-meta">Tools → Spielend Bulk-Meta</a> kannst du alle Produkte automatisch befüllen.
        </p>
    <?php endif; ?>
    <?php endif; ?>

    <div class="se-dash-actions">
        <a href="edit.php?post_type=shop_order" class="button button-primary">Bestellungen</a>
        <a href="edit.php?post_type=product" class="button">Produkte</a>
        <a href="post-new.php?post_type=post" class="button">Blog schreiben</a>
        <a href="admin.php?page=spielend-theme-options&tab=hero" class="button">Startseite/Hero</a>
        <a href="tools.php?page=se-bulk-meta" class="button">Bulk-Meta</a>
        <a href="plugins.php" class="button">Plugins</a>
    </div>
    <p class="se-dash-view"><a href="<?php echo esc_url(home_url('/')); ?>" target="_blank" rel="noopener">Website ansehen ↗</a></p>
</div>
<style>
.se-dash{padding:4px 2px}
.se-dash-kpis{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:16px}
.se-dash-kpi{flex:1;min-width:120px;border:1px solid;border-radius:10px;padding:12px;text-align:center}
.se-dash-num{font-size:24px;font-weight:700;line-height:1.2}
.se-dash-label{font-size:12px;color:#666;margin-top:2px}
.se-dash h4{margin:0 0 10px;font-size:13px;color:#444}
.se-dash-progress{display:grid;gap:8px;margin-bottom:14px}
.se-dash-progress-row{display:flex;align-items:center;gap:10px;font-size:12px}
.se-dash-progress-row span{flex:0 0 130px;color:#555}
.se-dash-progress-row strong{flex:0 0 40px;text-align:right}
.se-dash-bar{flex:1;height:10px;background:#eee;border-radius:5px;overflow:hidden}
.se-dash-bar div{height:100%;background:linear-gradient(90deg,#2B7A62,#4CB39A);border-radius:5px}
.se-dash-actions{display:flex;gap:6px;flex-wrap:wrap;margin-top:4px}
.se-dash-view{margin:12px 0 0;font-size:12px}
</style>
    <?php
}
