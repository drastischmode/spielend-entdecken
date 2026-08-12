<?php
if (!defined('ABSPATH')) exit;

function se_register_block_patterns() {
    $patterns = [
        'hero' => [
            'title'       => __('Hero Section', 'spielend-entdecken'),
            'description' => __('Full-width hero with heading, text, and CTA button.', 'spielend-entdecken'),
            'categories'  => ['hero'],
            'content'     => '<!-- wp:cover {"url":"https://placehold.co/1400x600/FF6B35/FFFFFF?text=Willkommen","dimRatio":50,"minHeight":500,"align":"full"} -->
<div class="wp-block-cover align-full" style="min-height:500px"><span aria-hidden="true" class="wp-block-cover__background has-background-dim"></span><div class="wp-block-cover__inner-container"><!-- wp:heading {"textAlign":"center","level":1,"style":{"typography":{"fontSize":"3.5rem"}},"textColor":"base"} --><h1 class="wp-block-heading has-text-align-center has-base-color has-text-color" style="font-size:3.5rem">Willkommen bei Spielend Entdecken</h1><!-- /wp:heading --><!-- wp:paragraph {"align":"center","style":{"typography":{"fontSize":"1.25rem"}},"textColor":"base"} --><p class="has-text-align-center has-base-color has-text-color" style="font-size:1.25rem">Hochwertiges Spielzeug für neugierige Kinder</p><!-- /wp:paragraph --><!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} --><div class="wp-block-buttons"><!-- wp:button {"backgroundColor":"accent","textColor":"foreground"} --><div class="wp-block-button"><a class="wp-block-button__link has-foreground-color has-accent-background-color has-text-color has-background wp-element-button" href="/shop">Jetzt entdecken</a></div><!-- /wp:button --></div><!-- /wp:buttons --></div></div><!-- /wp:cover -->',
        ],
        'newsletter' => [
            'title'       => __('Newsletter Signup', 'spielend-entdecken'),
            'description' => __('Newsletter signup form with email input and submit button.', 'spielend-entdecken'),
            'categories'  => ['call-to-action'],
            'content'     => '<!-- wp:group {"style":{"spacing":{"padding":{"top":"30px","bottom":"30px","left":"20px","right":"20px"}}},"backgroundColor":"secondary","textColor":"base","layout":{"type":"constrained"}} --><div class="wp-block-group has-base-color has-secondary-background-color has-text-color has-background" style="padding-top:30px;padding-bottom:30px;padding-left:20px;padding-right:20px"><!-- wp:heading {"textAlign":"center","level":3} --><h3 class="wp-block-heading has-text-align-center">10% Rabatt sichern!</h3><!-- /wp:heading --><!-- wp:paragraph {"align":"center"} --><p class="has-text-align-center">Abonniere unseren Newsletter und erhalte 10% auf deine erste Bestellung.</p><!-- /wp:paragraph --><!-- wp:html --><form action="#" method="post" style="display:flex;gap:8px;max-width:400px;margin:0 auto"><input type="email" name="email" placeholder="Deine E-Mail-Adresse" required style="flex:1;padding:12px 16px;border-radius:50px;border:2px solid #fff;background:transparent;color:#fff;font-size:1rem"><button type="submit" style="padding:12px 24px;border-radius:50px;border:none;background:#F9C80E;color:#2D2D2D;font-weight:600;cursor:pointer">Anmelden</button></form><!-- /wp:html --></div><!-- /wp:group -->',
        ],
        'category-grid' => [
            'title'       => __('Category Grid', 'spielend-entdecken'),
            'description' => __('4-column category grid with images for the homepage.', 'spielend-entdecken'),
            'categories'  => ['grid'],
            'content'     => '<!-- wp:columns {"style":{"spacing":{"margin":{"top":"30px"}}}} --><div class="wp-block-columns" style="margin-top:30px"><!-- wp:column --><div class="wp-block-column"><!-- wp:image {"id":1,"sizeSlug":"full","linkDestination":"custom"} --><figure class="wp-block-image size-full"><a href="/product-category/holzspielzeug"><img src="https://placehold.co/300x300/2B7A62/FFFFFF?text=Holzspielzeug" alt="Holzspielzeug"/></a></figure><!-- /wp:image --><!-- wp:heading {"textAlign":"center","level":3,"fontSize":"medium"} --><h3 class="wp-block-heading has-text-align-center has-medium-font-size">Holzspielzeug</h3><!-- /wp:heading --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:image {"id":2,"sizeSlug":"full","linkDestination":"custom"} --><figure class="wp-block-image size-full"><a href="/product-category/puzzle"><img src="https://placehold.co/300x300/FF6B35/FFFFFF?text=Puzzle+%26+Spiele" alt="Puzzle und Spiele"/></a></figure><!-- /wp:image --><!-- wp:heading {"textAlign":"center","level":3,"fontSize":"medium"} --><h3 class="wp-block-heading has-text-align-center has-medium-font-size">Puzzle &amp; Spiele</h3><!-- /wp:heading --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:image {"id":3,"sizeSlug":"full","linkDestination":"custom"} --><figure class="wp-block-image size-full"><a href="/product-category/kreativsets"><img src="https://placehold.co/300x300/F9C80E/2D2D2D?text=Kreativsets" alt="Kreativsets"/></a></figure><!-- /wp:image --><!-- wp:heading {"textAlign":"center","level":3,"fontSize":"medium"} --><h3 class="wp-block-heading has-text-align-center has-medium-font-size">Kreativsets</h3><!-- /wp:heading --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:image {"id":4,"sizeSlug":"full","linkDestination":"custom"} --><figure class="wp-block-image size-full"><a href="/product-category/buecher"><img src="https://placehold.co/300x300/2D2D2D/FFFFFF?text=B%C3%BCcher" alt="Bücher und Medien"/></a></figure><!-- /wp:image --><!-- wp:heading {"textAlign":"center","level":3,"fontSize":"medium"} --><h3 class="wp-block-heading has-text-align-center has-medium-font-size">Bücher</h3><!-- /wp:heading --></div><!-- /wp:column --></div><!-- /wp:columns -->',
        ],
        'testimonials' => [
            'title'       => __('Testimonials', 'spielend-entdecken'),
            'description' => __('Customer testimonials section with quote and rating.', 'spielend-entdecken'),
            'categories'  => ['testimonials'],
            'content'     => '<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"60px","bottom":"60px"}}},"backgroundColor":"base"} --><div class="wp-block-group align-full has-base-background-color has-background" style="padding-top:60px;padding-bottom:60px"><!-- wp:heading {"textAlign":"center","level":2} --><h2 class="wp-block-heading has-text-align-center">Was unsere Kunden sagen</h2><!-- /wp:heading --><!-- wp:columns {"style":{"spacing":{"margin":{"top":"30px"}}}} --><div class="wp-block-columns" style="margin-top:30px"><!-- wp:column --><div class="wp-block-column"><!-- wp:quote --><blockquote class="wp-block-quote"><p>"Schnelle Lieferung und tolle Qualität. Mein Sohn liebt das Holzspielzeug!"</p><cite>– Maria, Mama von Leo (3)</cite></blockquote><!-- /wp:quote --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:quote --><blockquote class="wp-block-quote"><p>"Endlich ein Spielzeugladen mit nachhaltigen Produkten und fairer Auswahl."</p><cite>– Thomas, Papa von Emma (5)</cite></blockquote><!-- /wp:quote --></div><!-- /wp:column --><!-- wp:column --><div class="wp-block-column"><!-- wp:quote --><blockquote class="wp-block-quote"><p>"Die Puzzle sind wunderschön gestaltet und machen der ganzen Familie Spaß!"</p><cite>– Sarah, Mutter von zwei Kindern</cite></blockquote><!-- /wp:quote --></div><!-- /wp:column --></div><!-- /wp:columns --></div><!-- /wp:group -->',
        ],
        'related-posts' => [
            'title'       => __('Related Posts', 'spielend-entdecken'),
            'description' => __('Grid of 3 related blog posts.', 'spielend-entdecken'),
            'categories'  => ['posts'],
            'content'     => '<!-- wp:group {"style":{"spacing":{"margin":{"top":"40px"}}}} --><div class="wp-block-group" style="margin-top:40px"><!-- wp:heading {"level":3} --><h3 class="wp-block-heading">Das könnte dich auch interessieren</h3><!-- /wp:heading --><!-- wp:latest-posts {"postsToShow":3,"displayFeaturedImage":true,"excerptLength":20} /--></div><!-- /wp:group -->',
        ],
    ];

    foreach ($patterns as $slug => $pattern) {
        register_block_pattern('spielend-entdecken/' . $slug, $pattern);
    }
}
add_action('init', 'se_register_block_patterns');
