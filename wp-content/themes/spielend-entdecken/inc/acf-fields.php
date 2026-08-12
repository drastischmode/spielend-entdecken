<?php
if (!defined('ABSPATH')) exit;

function se_register_acf_fields() {
    if (!function_exists('acf_add_local_field_group')) return;

    // Product Custom Fields
    acf_add_local_field_group([
        'key' => 'group_product_details',
        'title' => 'Produkt-Details',
        'fields' => [
            [
                'key' => 'field_age_recommendation',
                'label' => 'Altersempfehlung',
                'name' => 'age_recommendation',
                'type' => 'select',
                'choices' => [
                    '0-2' => '0-2 Jahre',
                    '3-5' => '3-5 Jahre',
                    '6-8' => '6-8 Jahre',
                    '9-11' => '9-11 Jahre',
                    '12+' => '12+ Jahre',
                ],
                'default_value' => '3-5',
                'required' => 1,
            ],
            [
                'key' => 'field_material',
                'label' => 'Material',
                'name' => 'material',
                'type' => 'text',
                'placeholder' => 'z.B. Holz, Kunststoff, Stoff, Metall',
                'required' => 0,
            ],
            [
                'key' => 'field_safety_certificates',
                'label' => 'Sicherheitszertifikate',
                'name' => 'safety_certificates',
                'type' => 'repeater',
                'sub_fields' => [
                    [
                        'key' => 'field_cert_name',
                        'label' => 'Zertifikat',
                        'name' => 'cert_name',
                        'type' => 'text',
                        'placeholder' => 'z.B. CE, EN71, FSC, Öko-Tex',
                    ],
                    [
                        'key' => 'field_cert_url',
                        'label' => 'Zertifikat-URL (optional)',
                        'name' => 'cert_url',
                        'type' => 'url',
                    ],
                ],
                'layout' => 'row',
                'button_label' => 'Zertifikat hinzufügen',
            ],
            [
                'key' => 'field_product_video',
                'label' => 'Produkt-Video (YouTube/Vimeo URL)',
                'name' => 'product_video',
                'type' => 'url',
                'required' => 0,
            ],
            [
                'key' => 'field_age_warning',
                'label' => 'Alterswarnung anzeigen',
                'name' => 'age_warning',
                'type' => 'true_false',
                'default_value' => 0,
                'ui' => 1,
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'product',
                ],
            ],
        ],
    ]);

    // Page Hero Fields
    acf_add_local_field_group([
        'key' => 'group_page_hero',
        'title' => 'Seiten-Hero',
        'fields' => [
            [
                'key' => 'field_hero_image',
                'label' => 'Hero-Bild',
                'name' => 'hero_image',
                'type' => 'image',
                'return_format' => 'array',
                'preview_size' => 'medium',
            ],
            [
                'key' => 'field_hero_subtitle',
                'label' => 'Hero-Untertitel',
                'name' => 'hero_subtitle',
                'type' => 'textarea',
                'rows' => 2,
            ],
            [
                'key' => 'field_hero_cta_text',
                'label' => 'CTA-Button Text',
                'name' => 'hero_cta_text',
                'type' => 'text',
            ],
            [
                'key' => 'field_hero_cta_url',
                'label' => 'CTA-Button URL',
                'name' => 'hero_cta_url',
                'type' => 'url',
            ],
            [
                'key' => 'field_hero_alignment',
                'label' => 'Inhalt Ausrichtung',
                'name' => 'hero_alignment',
                'type' => 'select',
                'choices' => [
                    'center' => 'Zentriert',
                    'left' => 'Links',
                    'right' => 'Rechts',
                ],
                'default_value' => 'center',
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'post_type',
                    'operator' => '==',
                    'value' => 'page',
                ],
            ],
        ],
    ]);

    // Global Theme Options (already in theme-options.php but extending)
    acf_add_local_field_group([
        'key' => 'group_global_options',
        'title' => 'Globale Theme-Optionen',
        'fields' => [
            [
                'key' => 'field_shipping_free_threshold',
                'label' => 'Kostenloser Versand ab (€)',
                'name' => 'shipping_free_threshold',
                'type' => 'number',
                'default_value' => 49,
                'min' => 0,
            ],
            [
                'key' => 'field_express_shipping_cost',
                'label' => 'Express-Versand Kosten (€)',
                'name' => 'express_shipping_cost',
                'type' => 'number',
                'default_value' => 9.90,
            ],
            [
                'key' => 'field_google_maps_api_key',
                'label' => 'Google Maps API Key',
                'name' => 'google_maps_api_key',
                'type' => 'text',
            ],
            [
                'key' => 'field_gtm_id',
                'label' => 'Google Tag Manager ID (GTM-XXXX)',
                'name' => 'gtm_id',
                'type' => 'text',
            ],
            [
                'key' => 'field_maintenance_mode',
                'label' => 'Wartungsmodus',
                'name' => 'maintenance_mode',
                'type' => 'true_false',
                'default_value' => 0,
            ],
        ],
        'location' => [
            [
                [
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'spielend-theme-options',
                ],
            ],
        ],
    ]);
}
add_action('acf/init', 'se_register_acf_fields');

// Helper functions
function se_get_product_age() {
    return get_field('age_recommendation') ?: '3-5';
}

function se_get_product_material() {
    return get_field('material') ?: '';
}

function se_get_product_certificates() {
    return get_field('safety_certificates') ?: [];
}

function se_get_product_video() {
    return get_field('product_video') ?: '';
}

function se_get_hero_data() {
    return [
        'image' => get_field('hero_image'),
        'subtitle' => get_field('hero_subtitle'),
        'cta_text' => get_field('hero_cta_text'),
        'cta_url' => get_field('hero_cta_url'),
        'alignment' => get_field('hero_alignment') ?: 'center',
    ];
}

function se_get_global_option($key, $default = '') {
    return get_field($key, 'option') ?: $default;
}