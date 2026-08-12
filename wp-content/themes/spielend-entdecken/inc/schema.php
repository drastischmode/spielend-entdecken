<?php
if (!defined('ABSPATH')) exit;

function se_add_json_ld() {
    if (is_singular('product')) {
        global $product;
        if (!$product) return;

        $schema = [
            '@context' => 'https://schema.org/',
            '@type' => 'Product',
            'name' => get_the_title(),
            'description' => get_the_excerpt() ?: wp_trim_words(get_the_content(), 50),
            'sku' => $product->get_sku(),
            'mpn' => $product->get_sku(),
            'brand' => [
                '@type' => 'Brand',
                'name' => 'Spielend Entdecken',
            ],
            'offers' => [
                '@type' => 'Offer',
                'url' => get_permalink(),
                'priceCurrency' => get_woocommerce_currency(),
                'price' => $product->get_price(),
                'priceValidUntil' => date('Y-m-d', strtotime('+1 year')),
                'availability' => $product->is_in_stock() ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
                'seller' => [
                    '@type' => 'Organization',
                    'name' => 'Spielend Entdecken',
                ],
            ],
            'image' => wp_get_attachment_image_url($product->get_image_id(), 'full') ?: get_template_directory_uri() . '/assets/images/placeholder.png',
        ];

        if ($product->get_average_rating()) {
            $schema['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => $product->get_average_rating(),
                'reviewCount' => $product->get_review_count(),
                'bestRating' => '5',
                'worstRating' => '1',
            ];
        }

        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }

    if (is_front_page() || is_home()) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'Store',
            'name' => 'Spielend Entdecken',
            'description' => 'Dein Spielzeugladen im Netz – Hochwertiges Spielzeug für neugierige Kinder. Sicher, nachhaltig, kreativ.',
            'url' => home_url(),
            'logo' => get_template_directory_uri() . '/assets/images/logo.png',
            'image' => get_template_directory_uri() . '/assets/images/og-home.jpg',
            'telephone' => get_option('se_contact_phone') ?: '+49 30 12345678',
            'email' => get_option('se_contact_email') ?: 'info@spielend-entdecken.de',
            'address' => [
                '@type' => 'PostalAddress',
                'streetAddress' => get_option('se_contact_address') ?: 'Musterstraße 123',
                'addressLocality' => 'Berlin',
                'postalCode' => '10115',
                'addressCountry' => 'DE',
            ],
            'geo' => [
                '@type' => 'GeoCoordinates',
                'latitude' => '52.5200',
                'longitude' => '13.4050',
            ],
            'openingHoursSpecification' => [
                [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
                    'opens' => '09:00',
                    'closes' => '18:00',
                ],
                [
                    '@type' => 'OpeningHoursSpecification',
                    'dayOfWeek' => 'Saturday',
                    'opens' => '10:00',
                    'closes' => '14:00',
                ],
            ],
            'priceRange' => '€€',
            'currenciesAccepted' => 'EUR',
            'paymentAccepted' => 'Cash, Credit Card, PayPal, Klarna, Apple Pay, Google Pay',
            'areaServed' => ['DE', 'AT'],
        ];

        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }

    if (is_singular('post')) {
        $schema = [
            '@context' => 'https://schema.org',
            '@type' => 'BlogPosting',
            'headline' => get_the_title(),
            'description' => get_the_excerpt() ?: wp_trim_words(get_the_content(), 50),
            'image' => get_the_post_thumbnail_url(get_the_ID(), 'full') ?: get_template_directory_uri() . '/assets/images/og-default.jpg',
            'author' => [
                '@type' => 'Person',
                'name' => get_the_author(),
                'url' => get_author_posts_url(get_the_author_meta('ID')),
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => 'Spielend Entdecken',
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => get_template_directory_uri() . '/assets/images/logo.png',
                ],
            ],
            'datePublished' => get_the_date('c'),
            'dateModified' => get_the_modified_date('c'),
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => get_permalink(),
            ],
        ];

        echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
    }

    // BreadcrumbList
    if (function_exists('yoast_breadcrumb') || function_exists('rank_math_the_breadcrumbs')) {
        // Handled by SEO plugin
    } else {
        $breadcrumbs = se_get_breadcrumbs();
        if ($breadcrumbs) {
            $schema = [
                '@context' => 'https://schema.org',
                '@type' => 'BreadcrumbList',
                'itemListElement' => $breadcrumbs,
            ];
            echo '<script type="application/ld+json">' . wp_json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
        }
    }

    // WebSite SearchAction
    $search_schema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'url' => home_url(),
        'name' => 'Spielend Entdecken',
        'potentialAction' => [
            '@type' => 'SearchAction',
            'target' => [
                '@type' => 'EntryPoint',
                'urlTemplate' => home_url('/?s={search_term_string}'),
            ],
            'query-input' => 'required name=search_term_string',
        ],
    ];
    echo '<script type="application/ld+json">' . wp_json_encode($search_schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) . '</script>';
}
add_action('wp_head', 'se_add_json_ld', 5);

function se_get_breadcrumbs() {
    $breadcrumbs = [];
    $position = 1;

    // Home
    $breadcrumbs[] = [
        '@type' => 'ListItem',
        'position' => $position++,
        'name' => 'Startseite',
        'item' => home_url(),
    ];

    if (is_category() || is_singular('post')) {
        $breadcrumbs[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => 'Blog',
            'item' => get_permalink(get_option('page_for_posts')),
        ];
    }

    if (is_product_category()) {
        $breadcrumbs[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => 'Shop',
            'item' => get_permalink(wc_get_page_id('shop')),
        ];

        $cat = get_queried_object();
        $ancestors = get_ancestors($cat->term_id, 'product_cat');
        $ancestors = array_reverse($ancestors);
        foreach ($ancestors as $ancestor_id) {
            $ancestor = get_term($ancestor_id, 'product_cat');
            $breadcrumbs[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $ancestor->name,
                'item' => get_term_link($ancestor),
            ];
        }
        $breadcrumbs[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => single_cat_title('', false),
            'item' => get_term_link($cat),
        ];
    } elseif (is_product()) {
        $breadcrumbs[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => 'Shop',
            'item' => get_permalink(wc_get_page_id('shop')),
        ];

        $categories = wp_get_post_terms(get_the_ID(), 'product_cat');
        if ($categories) {
            $cat = $categories[0];
            $ancestors = get_ancestors($cat->term_id, 'product_cat');
            $ancestors = array_reverse($ancestors);
            foreach ($ancestors as $ancestor_id) {
                $ancestor = get_term($ancestor_id, 'product_cat');
                $breadcrumbs[] = [
                    '@type' => 'ListItem',
                    'position' => $position++,
                    'name' => $ancestor->name,
                    'item' => get_term_link($ancestor),
                ];
            }
            $breadcrumbs[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $cat->name,
                'item' => get_term_link($cat),
            ];
        }
        $breadcrumbs[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => get_the_title(),
            'item' => get_permalink(),
        ];
    } elseif (is_category()) {
        $breadcrumbs[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => 'Blog',
            'item' => get_permalink(get_option('page_for_posts')),
        ];
        $cat = get_queried_object();
        $breadcrumbs[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => single_cat_title('', false),
            'item' => get_category_link($cat),
        ];
    } elseif (is_singular('post')) {
        $breadcrumbs[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => 'Blog',
            'item' => get_permalink(get_option('page_for_posts')),
        ];
        $categories = get_the_category();
        if ($categories) {
            $breadcrumbs[] = [
                '@type' => 'ListItem',
                'position' => $position++,
                'name' => $categories[0]->name,
                'item' => get_category_link($categories[0]),
            ];
        }
        $breadcrumbs[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => get_the_title(),
            'item' => get_permalink(),
        ];
    } elseif (is_page() && !is_front_page()) {
        $breadcrumbs[] = [
            '@type' => 'ListItem',
            'position' => $position++,
            'name' => get_the_title(),
            'item' => get_permalink(),
        ];
    }

    return $breadcrumbs ?: null;
}