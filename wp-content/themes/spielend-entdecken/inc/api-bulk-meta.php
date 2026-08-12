<?php
/**
 * REST API endpoint für Bulk-Meta-Import
 * Endpoint: POST /wp-json/spielend/v1/bulk-meta
 * Auth: Basic Auth (wp_nonce)
 */

if (!defined('ABSPATH')) exit;

/**
 * REST API Endpoint registrieren
 */
add_action('rest_api_init', function() {
    register_rest_route('spielend/v1', '/bulk-meta', array(
        'methods' => 'POST',
        'callback' => 'se_api_bulk_meta_handler',
        'permission_callback' => 'se_api_bulk_meta_permission',
    ));
});

/**
 * Permission Check: nur Admin
 */
function se_api_bulk_meta_permission($request) {
    return current_user_can('manage_woocommerce');
}

/**
 * Handler
 */
function se_api_bulk_meta_handler($request) {
    $params = $request->get_json_params();
    $action = $params['action'] ?? 'auto_fill';

    $result = array('success' => false, 'updated' => 0, 'error' => 0, 'message' => '');

    if ($action === 'auto_fill') {
        $result = se_bulk_meta_auto_fill();
        $result['success'] = true;
        $result['message'] = 'Auto-Fill nach Kategorie abgeschlossen';
    } elseif ($action === 'set_all_age') {
        $age = intval($params['age'] ?? 3);
        if ($age >= 0 && $age <= 18) {
            $result = se_bulk_meta_set_all_products('age', $age);
            $result['success'] = true;
            $result['message'] = "Altersempfehlung auf $age Jahre gesetzt";
        }
    } elseif ($action === 'set_all_sustainable') {
        $result = se_bulk_meta_set_all_products('sustainable', 1);
        $result['success'] = true;
        $result['message'] = 'Alle als Nachhaltig markiert';
    }

    return rest_ensure_response($result);
}
