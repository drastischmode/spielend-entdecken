<?php
if (!defined('ABSPATH')) exit;

class Spielend_Gift_Finder {
    public function __construct() {
        add_shortcode('spielend_gift_finder', [$this, 'render_shortcode']);
        add_action('wp_ajax_spielend_gift_finder_results', [$this, 'ajax_results']);
        add_action('wp_ajax_nopriv_spielend_gift_finder_results', [$this, 'ajax_results']);
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
    }

    public function enqueue_assets() {
        wp_enqueue_script('spielend-gift-finder', get_template_directory_uri() . '/assets/js/gift-finder.js', [], '1.0.0', true);
        wp_localize_script('spielend-gift-finder', 'spielend_gift_finder', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('spielend_gift_finder_nonce'),
            'steps' => ['alter', 'interesse', 'budget'],
            'texts' => [
                'title' => 'Geschenk-Finder',
                'subtitle' => 'Finde in 3 Schritten das perfekte Geschenk',
                'btn_next' => 'Weiter',
                'btn_back' => 'Zurück',
                'btn_results' => 'Empfehlungen anzeigen',
                'step_alter' => 'Für wen suchst du?',
                'step_interesse' => 'Was mag das Kind gern?',
                'step_budget' => 'Was möchtest du ausgeben?',
                'no_results' => 'Leider keine passenden Produkte gefunden. Versuche andere Filter.',
                'why_match' => 'Warum passt das?',
            ],
        ]);
    }

    public function render_shortcode() {
        ob_start();
        ?>
        <div class="spielend-gift-finder" data-step="1">
            <div class="gift-finder-header">
                <h2><?php esc_html_e('Geschenk-Finder', 'spielend'); ?></h2>
                <p class="gift-finder-subtitle"><?php esc_html_e('Finde in 3 Schritten das perfekte Geschenk', 'spielend'); ?></p>
                <div class="gift-finder-progress" role="progressbar" aria-valuenow="1" aria-valuemin="1" aria-valuemax="3">
                    <div class="gift-finder-progress__steps">
                        <span class="gift-finder-progress__step active" data-step="1"><span>1</span> Alter</span>
                        <span class="gift-finder-progress__step" data-step="2"><span>2</span> Interesse</span>
                        <span class="gift-finder-progress__step" data-step="3"><span>3</span> Budget</span>
                    </div>
                    <div class="gift-finder-progress__bar"><div class="gift-finder-progress__fill"></div></div>
                </div>
            </div>

            <form class="gift-finder-form" id="gift-finder-form">
                <input type="hidden" name="action" value="spielend_gift_finder_results">
                <input type="hidden" name="nonce" value="<?php echo wp_create_nonce('spielend_gift_finder_nonce'); ?>">

                <!-- Schritt 1: Alter -->
                <div class="gift-finder-step active" data-step="1">
                    <h3><?php esc_html_e('Für wen suchst du?', 'spielend'); ?></h3>
                    <div class="gift-finder-options" role="radiogroup" aria-label="Alter wählen">
                        <label class="gift-finder-option">
                            <input type="radio" name="alter" value="baby" required>
                            <span class="gift-finder-option__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2z"/><path d="M12 6v6l4 2"/></svg></span>
                            <span class="gift-finder-option__label">Baby (0-1 J.)</span>
                        </label>
                        <label class="gift-finder-option">
                            <input type="radio" name="alter" value="kleinkind">
                            <span class="gift-finder-option__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2z"/><path d="M12 6v6l4 2"/></svg></span>
                            <span class="gift-finder-option__label">Kleinkind (1-3 J.)</span>
                        </label>
                        <label class="gift-finder-option">
                            <input type="radio" name="alter" value="vorschule">
                            <span class="gift-finder-option__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2z"/><path d="M12 6v6l4 2"/></svg></span>
                            <span class="gift-finder-option__label">Vorschule (3-6 J.)</span>
                        </label>
                        <label class="gift-finder-option">
                            <input type="radio" name="alter" value="schulkind">
                            <span class="gift-finder-option__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2z"/><path d="M12 6v6l4 2"/></svg></span>
                            <span class="gift-finder-option__label">Schulkind (6-10 J.)</span>
                        </label>
                        <label class="gift-finder-option">
                            <input type="radio" name="alter" value="teens">
                            <span class="gift-finder-option__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2z"/><path d="M12 6v6l4 2"/></svg></span>
                            <span class="gift-finder-option__label">Teens (10+ J.)</span>
                        </label>
                    </div>
                    <div class="gift-finder-actions">
                        <button type="button" class="btn btn--primary gift-finder-btn-next" data-next="2"><?php esc_html_e('Weiter', 'spielend'); ?></button>
                    </div>
                </div>

                <!-- Schritt 2: Interesse -->
                <div class="gift-finder-step" data-step="2" hidden>
                    <h3><?php esc_html_e('Was mag das Kind gern?', 'spielend'); ?></h3>
                    <div class="gift-finder-options" role="radiogroup" aria-label="Interesse wählen">
                        <label class="gift-finder-option">
                            <input type="radio" name="interesse" value="bauen" required>
                            <span class="gift-finder-option__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h10a2 2 0 0 1 2 2v5"/></svg></span>
                            <span class="gift-finder-option__label">Bauen & Konstruieren</span>
                        </label>
                        <label class="gift-finder-option">
                            <input type="radio" name="interesse" value="kreativ">
                            <span class="gift-finder-option__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2z"/><path d="M12 6v6l4 2"/></svg></span>
                            <span class="gift-finder-option__label">Kreativ & Malen</span>
                        </label>
                        <label class="gift-finder-option">
                            <input type="radio" name="interesse" value="lernen">
                            <span class="gift-finder-option__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2z"/><path d="M12 6v6l4 2"/></svg></span>
                            <span class="gift-finder-option__label">Lernen & Entdecken</span>
                        </label>
                        <label class="gift-finder-option">
                            <input type="radio" name="interesse" value="rollenspiel">
                            <span class="gift-finder-option__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2z"/><path d="M12 6v6l4 2"/></svg></span>
                            <span class="gift-finder-option__label">Rollenspiel</span>
                        </label>
                        <label class="gift-finder-option">
                            <input type="radio" name="interesse" value="bewegung">
                            <span class="gift-finder-option__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2z"/><path d="M12 6v6l4 2"/></svg></span>
                            <span class="gift-finder-option__label">Bewegung & Sport</span>
                        </label>
                        <label class="gift-finder-option">
                            <input type="radio" name="interesse" value="sammeln">
                            <span class="gift-finder-option__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2z"/><path d="M12 6v6l4 2"/></svg></span>
                            <span class="gift-finder-option__label">Sammeln & Tauschen</span>
                        </label>
                    </div>
                    <div class="gift-finder-actions">
                        <button type="button" class="btn btn--ghost gift-finder-btn-back" data-prev="1"><?php esc_html_e('Zurück', 'spielend'); ?></button>
                        <button type="button" class="btn btn--primary gift-finder-btn-next" data-next="3"><?php esc_html_e('Weiter', 'spielend'); ?></button>
                    </div>
                </div>

                <!-- Schritt 3: Budget -->
                <div class="gift-finder-step" data-step="3" hidden>
                    <h3><?php esc_html_e('Was möchtest du ausgeben?', 'spielend'); ?></h3>
                    <div class="gift-finder-options" role="radiogroup" aria-label="Budget wählen">
                        <label class="gift-finder-option">
                            <input type="radio" name="budget" value="unter_25" required>
                            <span class="gift-finder-option__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></span>
                            <span class="gift-finder-option__label">Unter 25 €</span>
                        </label>
                        <label class="gift-finder-option">
                            <input type="radio" name="budget" value="25_50">
                            <span class="gift-finder-option__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></span>
                            <span class="gift-finder-option__label">25 - 50 €</span>
                        </label>
                        <label class="gift-finder-option">
                            <input type="radio" name="budget" value="50_100">
                            <span class="gift-finder-option__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></span>
                            <span class="gift-finder-option__label">50 - 100 €</span>
                        </label>
                        <label class="gift-finder-option">
                            <input type="radio" name="budget" value="ueber_100">
                            <span class="gift-finder-option__icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></span>
                            <span class="gift-finder-option__label">Über 100 €</span>
                        </label>
                    </div>
                    <div class="gift-finder-actions">
                        <button type="button" class="btn btn--ghost gift-finder-btn-back" data-prev="2"><?php esc_html_e('Zurück', 'spielend'); ?></button>
                        <button type="submit" class="btn btn--primary"><?php esc_html_e('Empfehlungen anzeigen', 'spielend'); ?></button>
                    </div>
                </div>
            </form>

            <div class="gift-finder-results" hidden>
                <h3><?php esc_html_e('Deine Empfehlungen', 'spielend'); ?></h3>
                <p class="gift-finder-results__subtitle"><?php esc_html_e('Basierend auf deinen Antworten haben wir diese Produkte für dich ausgewählt:', 'spielend'); ?></p>
                <div class="gift-finder-results__grid"></div>
                <div class="gift-finder-results__actions">
                    <button type="button" class="btn btn--ghost gift-finder-btn-restart"><?php esc_html_e('Neue Suche starten', 'spielend'); ?></button>
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }

    public function ajax_results() {
        check_ajax_referer('spielend_gift_finder_nonce', 'nonce');

        $alter = sanitize_text_field($_POST['alter'] ?? '');
        $interesse = sanitize_text_field($_POST['interesse'] ?? '');
        $budget = sanitize_text_field($_POST['budget'] ?? '');

        $args = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'posts_per_page' => 5,
            'meta_query' => ['relation' => 'AND'],
            'tax_query' => ['relation' => 'AND'],
        ];

        // Alter-Mapping zu Kategorien
        $alter_map = [
            'baby' => ['baby', 'baby-kleinkind'],
            'kleinkind' => ['baby-kleinkind', 'kleinkind'],
            'vorschule' => ['vorschule', 'kindergarten'],
            'schulkind' => ['schulkind', 'grundschule'],
            'teens' => ['teens', 'jugend'],
        ];

        // Interesse-Mapping zu Kategorien/Tags
        $interesse_map = [
            'bauen' => ['bauen', 'konstruieren', 'bauklotze', 'lego', 'magnetbau'],
            'kreativ' => ['kreativ', 'malen', 'basteln', 'zeichnen', 'kreativ'],
            'lernen' => ['lernen', 'experimentieren', 'wissenschaft', 'stem'],
            'rollenspiel' => ['rollenspiel', 'puppen', 'figuren', 'kostueme'],
            'bewegung' => ['bewegung', 'sport', 'draussen', 'fahrrad', 'roller'],
            'sammeln' => ['sammeln', 'tauschen', 'karten', 'figuren', 'sammelkarten'],
        ];

        // Budget-Mapping
        $budget_map = [
            'unter_25' => [0, 25],
            '25_50' => [25, 50],
            '50_100' => [50, 100],
            'ueber_100' => [100, 999999],
        ];

        // Taxonomy Query
        $tax_queries = ['relation' => 'AND'];

        if ($alter && isset($alter_map[$alter])) {
            $tax_queries[] = [
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $alter_map[$alter],
                'operator' => 'IN',
            ];
        }

        if ($interesse && isset($interesse_map[$interesse])) {
            $tax_queries[] = [
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $interesse_map[$interesse],
                'operator' => 'IN',
            ];
        }

        if ($budget && isset($budget_map[$budget])) {
            $args['meta_query'] = [[
                'key' => '_price',
                'value' => $budget_map[$budget],
                'type' => 'NUMERIC',
                'compare' => 'BETWEEN',
            ]];
        }

        if (!empty($tax_queries)) {
            $args['tax_query'] = $tax_queries;
        }

        // Sortierung: Empfohlen (Featured) zuerst, dann Bewertung, dann Neuest
        $args['orderby'] = ['meta_value_num' => 'DESC', 'date' => 'DESC'];
        $args['meta_key'] = '_featured';

        $query = new WP_Query($args);

        if (!$query->have_posts()) {
            wp_send_json_error(['message' => __('Leider keine passenden Produkte gefunden. Versuche andere Filter.', 'spielend')]);
        }

        $html = '';
         while ($query->have_posts()) {
            $query->the_post();
            global $product;
            $product_id = get_the_ID();
            $product_title = get_the_title();
            $product_url = get_permalink();
            $product_image = get_the_post_thumbnail(null, 'woocommerce_thumbnail');
            $product_is_on_sale = $product->is_on_sale();

            // "Warum passt das?" - Begründung generieren
            $why_match = [];
            if ($alter) $why_match[] = 'Alter: ' . ucfirst($alter);
            if ($interesse) $why_match[] = 'Interesse: ' . ucfirst($interesse);
            if ($budget) $why_match[] = 'Budget: ' . $this->format_budget_label($budget);

            // Badges
            $badges = '';
            if ($product_is_on_sale) $badges .= '<span class="se-prod-card__badge se-prod-card__badge--sale">Sale</span>';
            if ($product->is_featured()) $badges .= '<span class="se-prod-card__badge se-prod-card__badge--featured">Empfohlen</span>';
            $meta_new = get_post_meta(get_the_ID(), '_spielend_product_new', true);
            if ($meta_new === 'yes') $badges .= '<span class="se-prod-card__badge se-prod-card__badge--new">Neu</span>';
            $badges = $badges ? '<div class="se-prod-card__badges">' . $badges . '</div>' : '';

            // Rating
            $rating_html = '';
            $rating = $product->get_average_rating();
            if ($rating > 0) {
                $full = floor($rating);
                $stars = '';
                for ($i = 0; $i < 5; $i++) {
                    if ($i < $full) $stars .= '★';
                    elseif ($i == $full && $rating - $full >= 0.5) $stars .= '★';
                    else $stars .= '☆';
                }
                $rating_html = '<div class="se-prod-card__rating"><span class="se-prod-card__stars">' . $stars . '</span><span class="se-prod-card__rating-count">(' . $product->get_review_count() . ')</span></div>';
            }

            // Price
            $price_html = $product->get_price_html();
            if ($product_is_on_sale) {
                $price_html = preg_replace('/(<del[^>]*>.*?<\/del>)/i', '<span class="se-prod-card__price-old">$1</span>', $price_html);
                $price_html = preg_replace('/(<ins[^>]*>.*?<\/ins>)/i', '<span class="se-prod-card__price-current">$1</span>', $price_html);
            } else {
                $price_html = '<span class="se-prod-card__price-current">' . $product->get_price_html() . '</span>';
            }
            $price_html = '<div class="se-prod-card__price">' . $price_html . '</div>';

            $html .= '
            <article class="gift-finder-result se-prod-card" data-product-id="' . esc_attr($product_id) . '">
                <div class="gift-finder-result__image">
                    <a href="' . esc_url($product_url) . '">' . $product_image . '</a>
                    <div class="se-prod-card__overlay"></div>
                    <button type="button" class="se-prod-card__wishlist" data-id="' . esc_attr($product_id) . '" aria-label="Zur Wunschliste hinzufügen"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06a5.5 5.5 0 0 0 7.78 7.78l1.06 1.06a5.5 5.5 0 0 0 7.78 7.78l1.06-1.06a5.5 5.5 0 0 0 7.78-7.78l-1.06-1.06a5.5 5.5 0 0 0-7.78 0z"></path></svg></button>
                    <button type="button" class="se-prod-card__quick" data-id="' . $product_id . '" aria-label="Schnellansicht"><svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg></button>
                    <div class="se-prod-card__overlay"></div>
                </div>
                <div class="se-prod-card__content">
                    ' . $badges . '
                    <h3 class="gift-finder-result__title"><a href="' . esc_url($product_url) . '">' . esc_html($product_title) . '</a></h3>
                    <div class="gift-finder-result__why">' . implode(' · ', $why_match) . '</div>
                    ' . $rating_html . '
                    <div class="gift-finder-result__price">' . $price_html . '</div>
                    <button type="button" class="se-prod-card__add btn btn--primary btn--sm" data-id="' . $product_id . '" aria-label="In den Warenkorb">In den Warenkorb</button>
                </div>
            </article>';
        }
        wp_reset_postdata();

        wp_send_json_success(['html' => $html, 'count' => $query->found_posts]);
    }

    private function format_budget_label($budget) {
        $labels = [
            'unter_25' => 'unter 25 €',
            '25_50' => '25–50 €',
            '50_100' => '50–100 €',
            'ueber_100' => 'über 100 €',
        ];
        return $labels[$budget] ?? $budget;
    }
}

new Spielend_Gift_Finder();
