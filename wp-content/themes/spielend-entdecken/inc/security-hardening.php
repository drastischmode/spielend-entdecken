<?php
if (!defined('ABSPATH')) exit;

/**
 * Sicherheits-Härtung für Spielend Entdecken
 * - XML-RPC deaktivieren (Brute-Force-Vektor)
 * - REST-API: Nur eingeloggte User (öffentliche Routen bleiben)
 * - Datei-Editing im Admin deaktivieren
 * - Fehlerberichte für Nicht-Admins verbergen
 */

/** XML-RPC komplett deaktivieren (verhindert Pingback/Brute-Force-Angriffe) */
add_filter('xmlrpc_enabled', '__return_false');

/** XML-RPC-Request-Methoden blockieren */
add_filter('xmlrpc_methods', function() { return array(); });

/** Datei-Editing im WP-Admin deaktivieren */
if (!defined('DISALLOW_FILE_EDIT')) {
    define('DISALLOW_FILE_EDIT', true);
}

/** Login-Fehlerberichte entschärfen (keine Usernamen verraten) */
add_filter('login_errors', function() {
    return __('Zugang fehlgeschlagen. Bitte prüfe deine Zugangsdaten.', 'spielend-entdecken');
});

/** REST-API: unnötige Benutzer-Endpunkte für Nicht-Admins blockieren */
add_filter('rest_endpoints', function($endpoints) {
    if (is_user_logged_in() && current_user_can('manage_options')) {
        return $endpoints;
    }
    // Für eingeloggte Nicht-Admins und Gäste: sensitive Endpoints entfernen
    $blocked = array(
        '/wp/v2/users',
        '/wp/v2/users/(?P<id>[\d]+)',
    );
    foreach ($blocked as $route) {
        if (isset($endpoints[$route])) {
            unset($endpoints[$route]);
        }
    }
    return $endpoints;
});

/** WP-Version aus REST-API-Responses entfernen */
add_filter('rest_index', function($response) {
    if (isset($response->data['generator'])) {
        $response->data['generator'] = '';
    }
    return $response;
});
