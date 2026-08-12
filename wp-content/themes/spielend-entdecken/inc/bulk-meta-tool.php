<?php
/**
 * Bulk-Import Tool für Produkt-Meta (Altersempfehlung, Nachhaltigkeit)
 * Admin-Seite: Tools → Spielend Bulk-Meta
 */

if (!defined('ABSPATH')) exit;

/**
 * Intelligente Defaults pro Kategorie
 */
function se_bulk_meta_get_defaults() {
    return array(
        'tonies' => array('age' => 3, 'sustainable' => true),
        'lego' => array('age' => 4, 'sustainable' => false),
        'holzspielzeug' => array('age' => 2, 'sustainable' => true),
        'puzzle' => array('age' => 6, 'sustainable' => false),
        'kreativ' => array('age' => 3, 'sustainable' => true),
        'buecher' => array('age' => 0, 'sustainable' => true),
        'papierflieger' => array('age' => 5, 'sustainable' => true),
        'ravensburger' => array('age' => 8, 'sustainable' => false),
    );
}

/**
 * Admin-Seite registrieren
 */
add_action('admin_menu', function() {
    add_submenu_page(
        'tools.php',
        'Spielend Bulk-Meta',
        'Spielend Bulk-Meta',
        'manage_woocommerce',
        'se-bulk-meta',
        'se_bulk_meta_page'
    );
});

/**
 * Admin-Seite rendern
 */
function se_bulk_meta_page() {
    // Sicherheit
    if (!current_user_can('manage_woocommerce')) {
        wp_die('Zugriff verweigert.');
    }

    // POST-Action: Bulk-Import starten
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['se_bulk_action'])) {
        check_admin_referer('se_bulk_meta_nonce');
        
        $action = sanitize_text_field($_POST['se_bulk_action']);
        $result = array('success' => 0, 'updated' => 0, 'error' => 0);

        if ($action === 'auto_fill') {
            // Auto-Fill nach Kategorie
            $result = se_bulk_meta_auto_fill();
        } elseif ($action === 'set_all_age') {
            $age = intval($_POST['age_value'] ?? 0);
            if ($age >= 0 && $age <= 18) {
                $result = se_bulk_meta_set_all_products('age', $age);
            }
        } elseif ($action === 'set_all_sustainable') {
            $result = se_bulk_meta_set_all_products('sustainable', 1);
        }

        echo '<div class="notice notice-success"><p>';
        echo 'Aktualisiert: ' . intval($result['updated']) . ' | ';
        echo 'Fehler: ' . intval($result['error']);
        echo '</p></div>';
    }

    // UI
    ?>
    <div class="wrap">
        <h1>Spielend – Trust-Elemente befüllen</h1>
        <p>Diese Daten erscheinen als Badges auf den Produktkarten und Produktseiten:
            <strong>„Ab X Jahren"</strong> und <strong>„Nachhaltig"</strong>. Mehr ausgefüllte Felder = mehr Vertrauen bei Kunden.</p>

        <div style="background: #fff; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #ddd; max-width: 600px;">
            <h2>1. Empfohlen: Auto-Fill nach Kategorie</h2>
            <p>Setzt automatisch passende Werte, basierend auf der Produkt-Kategorie. Schon befüllte Produkte bleiben unverändert:</p>
            <ul style="padding-left: 20px;">
                <li><strong>Tonies:</strong> Ab 3 Jahren, Nachhaltig</li>
                <li><strong>LEGO:</strong> Ab 4 Jahren</li>
                <li><strong>Holzspielzeug:</strong> Ab 2 Jahren, Nachhaltig</li>
                <li><strong>Puzzle:</strong> Ab 6 Jahren</li>
                <li><strong>Kreativ:</strong> Ab 3 Jahren, Nachhaltig</li>
                <li><strong>Bücher:</strong> Nachhaltig</li>
            </ul>
            <form method="POST" style="margin-top: 15px;">
                <?php wp_nonce_field('se_bulk_meta_nonce'); ?>
                <input type="hidden" name="se_bulk_action" value="auto_fill">
                <button type="submit" class="button button-primary">Auto-Fill starten</button>
            </form>
        </div>

        <div style="background: #fff; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #ddd; max-width: 600px;">
            <h2>2. Altersempfehlung für ALLE setzen</h2>
            <p>Überschreibt alle Produkte mit dem gleichen Wert. Nur sinnvoll, wenn alle Produkte dieselbe Altersgruppe haben.</p>
            <form method="POST">
                <?php wp_nonce_field('se_bulk_meta_nonce'); ?>
                <label for="age_value">Altersempfehlung (Jahre):</label><br>
                <input type="number" id="age_value" name="age_value" min="0" max="18" value="3" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 80px;"><br><br>
                <input type="hidden" name="se_bulk_action" value="set_all_age">
                <button type="submit" class="button button-primary">Auf alle setzen</button>
            </form>
        </div>

        <div style="background: #fff; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #ddd; max-width: 600px;">
            <h2>3. Alle Produkte als „Nachhaltig" markieren</h2>
            <p>Nur verwenden, wenn dein Sortiment tatsächlich überwiegend nachhaltig ist.</p>
            <form method="POST">
                <?php wp_nonce_field('se_bulk_meta_nonce'); ?>
                <input type="hidden" name="se_bulk_action" value="set_all_sustainable">
                <button type="submit" class="button button-primary">Alle als Nachhaltig</button>
            </form>
        </div>

        <div style="background: #f5f5f5; padding: 20px; border-radius: 8px; margin: 20px 0; border: 1px solid #ddd;">
            <h3>Status</h3>
            <p>
                <strong>Produkte mit Altersempfehlung:</strong> 
                <?php echo count(get_posts(array(
                    'post_type' => 'product',
                    'meta_key' => '_se_age_rating',
                    'meta_compare' => 'EXISTS',
                    'fields' => 'ids',
                    'numberposts' => -1,
                ))); ?>
            </p>
            <p>
                <strong>Produkte als Nachhaltig:</strong> 
                <?php echo count(get_posts(array(
                    'post_type' => 'product',
                    'meta_key' => '_se_sustainable',
                    'meta_value' => 'yes',
                    'fields' => 'ids',
                    'numberposts' => -1,
                ))); ?>
            </p>
            <p>
                <strong>Alle Produkte:</strong> 
                <?php echo wp_count_posts('product')->publish; ?>
            </p>
        </div>
    </div>
    <?php
}

/**
 * Auto-Fill: Altersempfehlung + Nachhaltigkeit nach Kategorie
 */
function se_bulk_meta_auto_fill() {
    $defaults = se_bulk_meta_get_defaults();
    $result = array('updated' => 0, 'error' => 0);

    // Alle Produkte holen
    $products = get_posts(array(
        'post_type' => 'product',
        'numberposts' => -1,
        'fields' => 'ids',
    ));

    foreach ($products as $product_id) {
        $product = wc_get_product($product_id);
        if (!$product) continue;

        // Erste Kategorie des Produkts
        $categories = $product->get_category_ids();
        if (empty($categories)) continue;

        $category_id = $categories[0];
        $category = get_term($category_id, 'product_cat');
        if (!$category) continue;

        $slug = $category->slug;
        $default = $defaults[$slug] ?? null;
        if (!$default) continue;

        // Nur setzen, wenn nicht bereits gesetzt
        $current_age = get_post_meta($product_id, '_se_age_rating', true);
        if (!$current_age && isset($default['age']) && $default['age'] > 0) {
            update_post_meta($product_id, '_se_age_rating', intval($default['age']));
            $result['updated']++;
        }

        if ($default['sustainable'] ?? false) {
            $current_sustainable = get_post_meta($product_id, '_se_sustainable', true);
            if ('yes' !== $current_sustainable) {
                update_post_meta($product_id, '_se_sustainable', 'yes');
                $result['updated']++;
            }
        }
    }

    return $result;
}

/**
 * Alle Produkte: ein Meta-Feld setzen
 */
function se_bulk_meta_set_all_products($field, $value) {
    $result = array('updated' => 0, 'error' => 0);

    $products = get_posts(array(
        'post_type' => 'product',
        'numberposts' => -1,
        'fields' => 'ids',
    ));

    foreach ($products as $product_id) {
        if ($field === 'age') {
            update_post_meta($product_id, '_se_age_rating', $value);
        } elseif ($field === 'sustainable') {
            update_post_meta($product_id, '_se_sustainable', 'yes');
        }
        $result['updated']++;
    }

    return $result;
}
