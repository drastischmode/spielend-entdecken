<?php
/**
 * Plugin Name:       Spielend Essentials
 * Plugin URI:        https://spielend-entdecken.de
 * Description:       Core functionality plugin for spielend-entdecken.de – custom post types, shortcodes, widgets
 * Version:           1.0.0
 * Author:            drastischmode
 * Author URI:        https://github.com/drastischmode
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       spielend-essentials
 * Domain Path:       /languages
 * Requires at least: 6.6
 * Requires PHP:      8.1
 */

defined( 'ABSPATH' ) || exit;

final class Spielend_Essentials {

	private const POST_TYPE_TESTIMONIAL = 'testimonial';
	private const POST_TYPE_SUBSCRIBER  = 'subscriber';
	private const POST_TYPE_CONTACT     = 'contact_message';

	private const REST_NAMESPACE = 'spielend/v1';

	private const CONTACT_NONCE_ACTION = 'spielend_contact_form';

	private static ?Spielend_Essentials $instance = null;

	public static function instance(): Spielend_Essentials {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	private function __construct() {
		add_action( 'init', array( $this, 'load_textdomain' ) );
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'rest_api_init', array( $this, 'register_rest_routes' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wishlist_scripts' ) );

		add_action( 'admin_post_spielend_contact_form', array( $this, 'handle_contact_form' ) );
		add_action( 'admin_post_nopriv_spielend_contact_form', array( $this, 'handle_contact_form' ) );

		add_shortcode( 'spielend_social_icons', array( $this, 'shortcode_social_icons' ) );
		add_shortcode( 'spielend_contact_info', array( $this, 'shortcode_contact_info' ) );
		add_shortcode( 'spielend_contact_form', array( $this, 'shortcode_contact_form' ) );
		add_shortcode( 'spielend_category_grid', array( $this, 'shortcode_category_grid' ) );
		add_shortcode( 'spielend_featured_products', array( $this, 'shortcode_featured_products' ) );
		add_shortcode( 'spielend_wishlist_button', array( $this, 'shortcode_wishlist_button' ) );
		add_shortcode( 'spielend_wishlist_page', array( $this, 'shortcode_wishlist_page' ) );
		add_filter( 'the_content', array( $this, 'clean_category_grid_output' ), 20 );
		add_filter( 'render_block', array( $this, 'clean_category_grid_output' ), 20 );
	}

	public function load_textdomain(): void {
		load_plugin_textdomain( 'spielend-essentials', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}

	public function register_post_types(): void {
		register_post_type(
			self::POST_TYPE_TESTIMONIAL,
			array(
				'labels'       => array(
					'name'               => __( 'Testimonials', 'spielend-essentials' ),
					'singular_name'      => __( 'Testimonial', 'spielend-essentials' ),
					'add_new'            => __( 'Neu hinzufügen', 'spielend-essentials' ),
					'add_new_item'       => __( 'Neues Testimonial hinzufügen', 'spielend-essentials' ),
					'edit_item'          => __( 'Testimonial bearbeiten', 'spielend-essentials' ),
					'new_item'           => __( 'Neues Testimonial', 'spielend-essentials' ),
					'view_item'          => __( 'Testimonial ansehen', 'spielend-essentials' ),
					'search_items'       => __( 'Testimonials durchsuchen', 'spielend-essentials' ),
					'not_found'          => __( 'Keine Testimonials gefunden.', 'spielend-essentials' ),
					'not_found_in_trash' => __( 'Keine Testimonials im Papierkorb.', 'spielend-essentials' ),
					'menu_name'          => __( 'Testimonials', 'spielend-essentials' ),
				),
				'public'       => true,
				'has_archive'  => true,
				'menu_icon'    => 'dashicons-format-quote',
				'supports'     => array( 'title', 'editor', 'thumbnail' ),
				'rewrite'      => array( 'slug' => 'testimonials', 'with_front' => false ),
				'show_in_rest' => true,
			)
		);

		register_post_type(
			self::POST_TYPE_SUBSCRIBER,
			array(
				'labels'              => array(
					'name'               => __( 'Newsletter-Abonnenten', 'spielend-essentials' ),
					'singular_name'      => __( 'Abonnent', 'spielend-essentials' ),
					'add_new'            => __( 'Neu hinzufügen', 'spielend-essentials' ),
					'add_new_item'       => __( 'Neuen Abonnenten hinzufügen', 'spielend-essentials' ),
					'edit_item'          => __( 'Abonnent bearbeiten', 'spielend-essentials' ),
					'new_item'           => __( 'Neuer Abonnent', 'spielend-essentials' ),
					'view_item'          => __( 'Abonnent ansehen', 'spielend-essentials' ),
					'search_items'       => __( 'Abonnenten durchsuchen', 'spielend-essentials' ),
					'not_found'          => __( 'Keine Abonnenten gefunden.', 'spielend-essentials' ),
					'not_found_in_trash' => __( 'Keine Abonnenten im Papierkorb.', 'spielend-essentials' ),
					'menu_name'          => __( 'Newsletter', 'spielend-essentials' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'menu_icon'           => 'dashicons-email-alt',
				'supports'            => array( 'title', 'custom-fields' ),
				'show_in_rest'        => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
			)
		);

		register_post_type(
			self::POST_TYPE_CONTACT,
			array(
				'labels'              => array(
					'name'               => __( 'Kontaktanfragen', 'spielend-essentials' ),
					'singular_name'      => __( 'Kontaktanfrage', 'spielend-essentials' ),
					'add_new'            => __( 'Neu hinzufügen', 'spielend-essentials' ),
					'add_new_item'       => __( 'Neue Kontaktanfrage hinzufügen', 'spielend-essentials' ),
					'edit_item'          => __( 'Kontaktanfrage bearbeiten', 'spielend-essentials' ),
					'new_item'           => __( 'Neue Kontaktanfrage', 'spielend-essentials' ),
					'view_item'          => __( 'Kontaktanfrage ansehen', 'spielend-essentials' ),
					'search_items'       => __( 'Kontaktanfragen durchsuchen', 'spielend-essentials' ),
					'not_found'          => __( 'Keine Kontaktanfragen gefunden.', 'spielend-essentials' ),
					'not_found_in_trash' => __( 'Keine Kontaktanfragen im Papierkorb.', 'spielend-essentials' ),
					'menu_name'          => __( 'Kontaktanfragen', 'spielend-essentials' ),
				),
				'public'              => false,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'menu_icon'           => 'dashicons-admin-comments',
				'supports'            => array( 'title', 'editor' ),
				'show_in_rest'        => false,
				'exclude_from_search' => true,
				'publicly_queryable'  => false,
			)
		);
	}

	public function register_rest_routes(): void {
		register_rest_route(
			self::REST_NAMESPACE,
			'/newsletter/subscribe',
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'rest_newsletter_subscribe' ),
				'permission_callback' => '__return_true',
				'args'                => array(
					'email'      => array(
						'description'       => __( 'E-Mail-Adresse des Abonnenten.', 'spielend-essentials' ),
						'type'              => 'string',
						'required'          => true,
						'sanitize_callback' => 'sanitize_email',
						'validate_callback' => static function ( $value ) {
							return is_email( $value );
						},
					),
					'first_name' => array(
						'description'       => __( 'Vorname des Abonnenten (optional).', 'spielend-essentials' ),
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
					'honeypot'   => array(
						'description'       => __( 'Spamschutz-Feld – bitte leer lassen.', 'spielend-essentials' ),
						'type'              => 'string',
						'required'          => false,
						'sanitize_callback' => 'sanitize_text_field',
					),
				),
			)
		);
	}

	public function rest_newsletter_subscribe( WP_REST_Request $request ) {
		$email      = (string) $request->get_param( 'email' );
		$first_name = (string) $request->get_param( 'first_name' );
		$honeypot   = (string) $request->get_param( 'honeypot' );

		if ( '' !== $honeypot ) {
			return new WP_REST_Response(
				array(
					'success' => true,
					'message' => __( 'Vielen Dank! Deine Anmeldung war erfolgreich.', 'spielend-essentials' ),
				),
				200
			);
		}

		if ( ! is_email( $email ) ) {
			return new WP_Error(
				'spielend_invalid_email',
				__( 'Bitte gib eine gültige E-Mail-Adresse ein.', 'spielend-essentials' ),
				array( 'status' => 400 )
			);
		}

		if ( get_page_by_title( $email, OBJECT, self::POST_TYPE_SUBSCRIBER ) ) {
			return new WP_Error(
				'spielend_already_subscribed',
				__( 'Diese E-Mail-Adresse ist bereits angemeldet.', 'spielend-essentials' ),
				array( 'status' => 409 )
			);
		}

		$post_id = wp_insert_post(
			array(
				'post_type'   => self::POST_TYPE_SUBSCRIBER,
				'post_status' => 'publish',
				'post_title'  => $email,
				'meta_input'  => array(
					'_spielend_email'      => $email,
					'_spielend_first_name' => $first_name,
				),
			)
		);

		if ( is_wp_error( $post_id ) ) {
			return new WP_Error(
				'spielend_subscribe_failed',
				__( 'Die Anmeldung ist fehlgeschlagen. Bitte versuche es später erneut.', 'spielend-essentials' ),
				array( 'status' => 500 )
			);
		}

		do_action( 'spielend_newsletter_subscribed', $post_id, $email, $first_name );

		return new WP_REST_Response(
			array(
				'success'       => true,
				'message'       => __( 'Vielen Dank! Deine Anmeldung war erfolgreich.', 'spielend-essentials' ),
				'subscriber_id' => $post_id,
			),
			200
		);
	}

	public function handle_contact_form(): void {
		wp_safe_redirect( $this->process_contact_form() );
		exit;
	}

	public function process_contact_form(): string {
		if ( ! isset( $_POST['spielend_contact_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['spielend_contact_nonce'] ) ), self::CONTACT_NONCE_ACTION ) ) {
			return $this->contact_redirect_url( 'error', __( 'Sicherheitsprüfung fehlgeschlagen. Bitte erneut versuchen.', 'spielend-essentials' ) );
		}

		$honeypot = isset( $_POST['spielend_hp'] ) ? sanitize_text_field( wp_unslash( $_POST['spielend_hp'] ) ) : '';
		if ( '' !== $honeypot ) {
			return $this->contact_redirect_url( 'success', __( 'Vielen Dank! Deine Nachricht wurde übermittelt.', 'spielend-essentials' ) );
		}

		$name    = isset( $_POST['spielend_name'] ) ? sanitize_text_field( wp_unslash( $_POST['spielend_name'] ) ) : '';
		$email   = isset( $_POST['spielend_email'] ) ? sanitize_email( wp_unslash( $_POST['spielend_email'] ) ) : '';
		$subject = isset( $_POST['spielend_subject'] ) ? sanitize_text_field( wp_unslash( $_POST['spielend_subject'] ) ) : '';
		$message = isset( $_POST['spielend_message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['spielend_message'] ) ) : '';

		$errors = array();

		if ( '' === $name || mb_strlen( $name ) > 100 ) {
			$errors[] = __( 'Bitte gib deinen Namen ein (max. 100 Zeichen).', 'spielend-essentials' );
		}

		if ( ! is_email( $email ) ) {
			$errors[] = __( 'Bitte gib eine gültige E-Mail-Adresse ein.', 'spielend-essentials' );
		}

		if ( '' === $subject || mb_strlen( $subject ) > 200 ) {
			$errors[] = __( 'Bitte gib einen Betreff ein (max. 200 Zeichen).', 'spielend-essentials' );
		}

		if ( '' === $message || mb_strlen( $message ) > 5000 ) {
			$errors[] = __( 'Bitte gib eine Nachricht ein (max. 5.000 Zeichen).', 'spielend-essentials' );
		}

		if ( ! empty( $errors ) ) {
			return $this->contact_redirect_url( 'error', implode( ' ', $errors ) );
		}

		$post_id = wp_insert_post(
			array(
				'post_type'    => self::POST_TYPE_CONTACT,
				'post_status'  => 'publish',
				'post_title'   => sprintf( '%s – %s', $subject, $name ),
				'post_content' => $message,
				'meta_input'   => array(
					'_spielend_name'    => $name,
					'_spielend_email'   => $email,
					'_spielend_subject' => $subject,
				),
			)
		);

		if ( ! is_wp_error( $post_id ) && apply_filters( 'spielend_send_contact_email', true ) ) {
			$headers = array( 'Reply-To: ' . $name . ' <' . $email . '>' );
			$body    = sprintf(
				"Name: %s\nE-Mail: %s\nBetreff: %s\n\n%s",
				$name,
				$email,
				$subject,
				$message
			);

			wp_mail(
				get_option( 'admin_email' ),
				sprintf( '[%s] %s', wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $subject ),
				$body,
				$headers
			);
		}

		return $this->contact_redirect_url( 'success', __( 'Vielen Dank! Deine Nachricht wurde übermittelt.', 'spielend-essentials' ) );
	}

	public function shortcode_social_icons( $atts ): string {
		$atts = shortcode_atts( array(), $atts, 'spielend_social_icons' );

		$networks = array(
			'facebook'  => array( 'label' => 'Facebook', 'svg' => '<path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/>' ),
			'instagram' => array( 'label' => 'Instagram', 'svg' => '<path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z"/>' ),
			'x'         => array( 'label' => 'X (Twitter)', 'svg' => '<path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 6.932ZM17.61 20.644h2.039L6.486 3.24H4.298Z"/>' ),
			'youtube'   => array( 'label' => 'YouTube', 'svg' => '<path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>' ),
			'tiktok'    => array( 'label' => 'TikTok', 'svg' => '<path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/>' ),
			'pinterest' => array( 'label' => 'Pinterest', 'svg' => '<path d="M12.017 0C5.396 0 .029 5.367.029 11.987c0 5.079 3.158 9.417 7.618 11.162-.105-.949-.199-2.403.041-3.439.219-.937 1.406-5.957 1.406-5.957s-.359-.72-.359-1.781c0-1.663.967-2.911 2.168-2.911 1.024 0 1.518.769 1.518 1.688 0 1.029-.653 2.567-.992 3.992-.285 1.193.6 2.165 1.775 2.165 2.128 0 3.768-2.245 3.768-5.487 0-2.861-2.063-4.869-5.008-4.869-3.41 0-5.409 2.562-5.409 5.199 0 1.033.394 2.143.889 2.741.099.12.112.225.085.345-.09.375-.293 1.199-.334 1.363-.053.225-.172.271-.401.165-1.495-.69-2.433-2.878-2.433-4.646 0-3.776 2.748-7.252 7.92-7.252 4.158 0 7.392 2.967 7.392 6.923 0 4.135-2.607 7.462-6.233 7.462-1.214 0-2.354-.629-2.758-1.379l-.749 2.848c-.269 1.045-1.004 2.352-1.498 3.146 1.123.345 2.306.535 3.55.535 6.607 0 11.985-5.365 11.985-11.987C24.009 5.367 18.641 0 12.017 0z"/>' ),
			'whatsapp'  => array( 'label' => 'WhatsApp', 'svg' => '<path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>' ),
		);

		$links = array();

		foreach ( $networks as $key => $network ) {
			$url = $this->get_theme_option( 'spielend_social_' . $key );
			if ( '' === $url ) {
				continue;
			}

			$links[] = sprintf(
				'<li class="spielend-social-icons__item"><a class="spielend-social-icons__link" href="%1$s" target="_blank" rel="noopener noreferrer" aria-label="%2$s"><svg class="spielend-social-icons__icon" viewBox="0 0 24 24" role="img" aria-hidden="true" focusable="false">%3$s</svg></a></li>',
				esc_url( $url ),
				esc_attr( $network['label'] ),
				$network['svg']
			);
		}

		if ( empty( $links ) ) {
			return '';
		}

		return '<ul class="spielend-social-icons">' . implode( "\n", $links ) . '</ul>';
	}

	public function shortcode_contact_info( $atts ): string {
		$atts = shortcode_atts( array( 'show' => 'all' ), $atts, 'spielend_contact_info' );
		$show = sanitize_key( $atts['show'] );

		$phone   = $this->get_theme_option( 'spielend_contact_phone' );
		$email   = $this->get_theme_option( 'spielend_contact_email' );
		$address = $this->get_theme_option( 'spielend_contact_address' );

		$items = array();

		if ( in_array( $show, array( 'all', 'phone' ), true ) && '' !== $phone ) {
			$items[] = sprintf(
				'<p class="spielend-contact-info__item spielend-contact-info__item--phone"><span class="spielend-contact-info__label">%1$s</span><a href="%2$s">%3$s</a></p>',
				esc_html__( 'Telefon:', 'spielend-essentials' ),
				esc_attr( 'tel:' . preg_replace( '/[^0-9+]/', '', $phone ) ),
				esc_html( $phone )
			);
		}

		if ( in_array( $show, array( 'all', 'email' ), true ) && '' !== $email ) {
			$obfuscated = antispambot( $email );

			$items[] = sprintf(
				'<p class="spielend-contact-info__item spielend-contact-info__item--email"><span class="spielend-contact-info__label">%1$s</span><a href="%2$s">%3$s</a></p>',
				esc_html__( 'E-Mail:', 'spielend-essentials' ),
				esc_attr( 'mailto:' . $obfuscated ),
				esc_html( $obfuscated )
			);
		}

		if ( in_array( $show, array( 'all', 'address' ), true ) && '' !== $address ) {
			$items[] = sprintf(
				'<p class="spielend-contact-info__item spielend-contact-info__item--address"><span class="spielend-contact-info__label">%1$s</span><span class="spielend-contact-info__value">%2$s</span></p>',
				esc_html__( 'Adresse:', 'spielend-essentials' ),
				nl2br( esc_html( $address ) )
			);
		}

		if ( empty( $items ) ) {
			return '';
		}

		return '<div class="spielend-contact-info">' . implode( "\n", $items ) . '</div>';
	}

	public function shortcode_contact_form( $atts ): string {
		$atts = shortcode_atts( array( 'title' => '' ), $atts, 'spielend_contact_form' );

		$status  = isset( $_GET['spielend_contact'] ) ? sanitize_key( wp_unslash( $_GET['spielend_contact'] ) ) : '';
		$message = isset( $_GET['spielend_message'] ) ? sanitize_text_field( wp_unslash( $_GET['spielend_message'] ) ) : '';

		ob_start();
		?>
		<div class="spielend-contact-form" id="spielend-contact-form">
			<?php if ( '' !== $atts['title'] ) : ?>
				<h2 class="spielend-contact-form__title"><?php echo esc_html( $atts['title'] ); ?></h2>
			<?php endif; ?>

			<?php if ( 'success' === $status ) : ?>
				<p class="spielend-contact-form__notice spielend-contact-form__notice--success"><?php echo esc_html( '' !== $message ? $message : __( 'Vielen Dank! Deine Nachricht wurde übermittelt.', 'spielend-essentials' ) ); ?></p>
			<?php elseif ( 'error' === $status ) : ?>
				<p class="spielend-contact-form__notice spielend-contact-form__notice--error"><?php echo esc_html( '' !== $message ? $message : __( 'Es ist ein Fehler aufgetreten. Bitte versuche es erneut.', 'spielend-essentials' ) ); ?></p>
			<?php endif; ?>

			<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
				<?php wp_nonce_field( self::CONTACT_NONCE_ACTION, 'spielend_contact_nonce' ); ?>
				<input type="hidden" name="action" value="spielend_contact_form" />

				<p class="spielend-contact-form__honeypot" aria-hidden="true">
					<label for="spielend_hp"><?php esc_html_e( 'Feld bitte leer lassen', 'spielend-essentials' ); ?></label>
					<input type="text" id="spielend_hp" name="spielend_hp" tabindex="-1" autocomplete="off" />
				</p>

				<p class="spielend-contact-form__field">
					<label for="spielend_name"><?php esc_html_e( 'Name *', 'spielend-essentials' ); ?></label>
					<input type="text" id="spielend_name" name="spielend_name" required maxlength="100" />
				</p>

				<p class="spielend-contact-form__field">
					<label for="spielend_email"><?php esc_html_e( 'E-Mail *', 'spielend-essentials' ); ?></label>
					<input type="email" id="spielend_email" name="spielend_email" required />
				</p>

				<p class="spielend-contact-form__field">
					<label for="spielend_subject"><?php esc_html_e( 'Betreff *', 'spielend-essentials' ); ?></label>
					<input type="text" id="spielend_subject" name="spielend_subject" required maxlength="200" />
				</p>

				<p class="spielend-contact-form__field">
					<label for="spielend_message"><?php esc_html_e( 'Nachricht *', 'spielend-essentials' ); ?></label>
					<textarea id="spielend_message" name="spielend_message" rows="6" required maxlength="5000"></textarea>
				</p>

				<p class="spielend-contact-form__field">
					<button type="submit" class="spielend-contact-form__submit"><?php esc_html_e( 'Nachricht senden', 'spielend-essentials' ); ?></button>
				</p>
			</form>
		</div>
		<?php
		return (string) ob_get_clean();
	}

	public function shortcode_category_grid( $atts ): string {
		$atts = shortcode_atts(
			array(
				'count' => 8,
				'order' => 'count',
			),
			$atts,
			'spielend_category_grid'
		);

		if ( ! function_exists( 'is_woocommerce' ) ) {
			return '';
		}

		$count = max( 1, (int) $atts['count'] );

		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'number'     => $count,
				'orderby'    => 'count',
				'order'      => 'DESC',
			)
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return '';
		}

		$cards = array();

		foreach ( $terms as $term ) {
			$link   = get_term_link( $term );
			$thumb  = get_term_meta( $term->term_id, 'thumbnail_id', true );
			$image  = '';

			if ( $thumb ) {
				$alt   = $term->name . ' – Spielzeug entdecken';
				$image = wp_get_attachment_image( $thumb, 'medium', false, array( 'loading' => 'lazy', 'alt' => $alt ) );
			}

			if ( '' === $image ) {
				$image = sprintf(
					'<div class="spielend-category-grid__placeholder" aria-hidden="true"></div>'
				);
			}

			$cards[] = sprintf(
				'<a class="spielend-category-grid__card" href="%1$s"><figure class="spielend-category-grid__figure">%2$s</figure><span class="spielend-category-grid__title">%3$s</span><span class="spielend-category-grid__count">%4$s %5$s</span></a>',
				esc_url( $link ),
				$image,
				esc_html( $term->name ),
				esc_html( (string) $term->count ),
				esc_html__( 'Produkte', 'spielend-essentials' )
			);
		}

		return '<div class="spielend-category-grid">' . implode( "\n", $cards ) . '</div>';
	}

	/** Bestseller/Hervorgehobene Produkte als sauberes HTML (Block-Bug: leere Titel) */
	public function shortcode_featured_products( $atts ): string {
		$atts = shortcode_atts(
			array(
				'count'  => 8,
				'ids'    => '',
				'orderby' => 'featured',
			),
			$atts,
			'spielend_featured_products'
		);

		if ( ! function_exists( 'wc_get_product' ) ) {
			return '';
		}

		$args = array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => max( 1, (int) $atts['count'] ),
			'no_found_rows'  => true,
		);

		if ( ! empty( $atts['ids'] ) ) {
			$args['post__in'] = array_map( 'intval', explode( ',', $atts['ids'] ) );
			$args['orderby']  = 'post__in';
		} else {
			$args['meta_key'] = '_featured';
			$args['meta_value'] = 'yes';
		}

		$products = get_posts( $args );
		if ( empty( $products ) ) {
			return '';
		}

		$items = '';
		foreach ( $products as $post ) {
			$product = wc_get_product( $post->ID );
			if ( ! $product ) {
				continue;
			}
			$img = $product->get_image( 'woocommerce_thumbnail', array( 'loading' => 'lazy', 'alt' => $product->get_name() ) );
			if ( '' === $img ) {
				$img = '<span class="spielend-product-ph" aria-hidden="true"></span>';
			}
			$items .= sprintf(
				'<a class="se-prod-card" href="%1$s">
					<span class="se-prod-card__img">%2$s</span>
					<span class="se-prod-card__title">%3$s</span>
					<span class="se-prod-card__price">%4$s</span>
					<span class="se-prod-card__btn wp-element-button">%5$s</span>
				</a>',
				esc_url( $product->get_permalink() ),
				$img,
				esc_html( $product->get_name() ),
				wp_kses_post( $product->get_price_html() ),
				esc_html__( 'In den Warenkorb', 'spielend-essentials' )
			);
		}

		return '<div class="se-featured-grid">' . $items . '</div>';
	}

	/**
	 * Bereinigt den Shortcode-Output: entfernt vom Block-Renderer eingefügte
	 * <p>…</p>- und <br>-Fragmente, die in <a>-Tags landen und das Grid-Markup
	 * zerteilen (Bild- und Titel-Link werden sonst zwei separate <a>-Elemente).
	 */
	public function clean_category_grid_output( string $output ): string {
		if ( has_shortcode( $output, 'spielend_category_grid' ) || false !== strpos( $output, 'spielend-category-grid' ) ) {
			// 1) Von wpautop/Block-Renderer eingefügte <p>/</p>/<br> entfernen.
			$output = preg_replace( '#<(/?p|br)\b[^>]*>#i', '', $output );

			// 2) wpautop zerteilt <figure> (Block-Element) aus dem <a> heraus,
			//    wodurch Bild-Karte und Titel-Karte zwei separate <a> werden.
			//    → Benachbarte gleich-Href-Karten wieder zu einer zusammenführen.
			if ( preg_match_all( '#<a\s+class="spielend-category-grid__card"[^>]*href="([^"]+)"[^>]*>(.*?)</a>#is', $output, $m, PREG_SET_ORDER ) ) {
				$merged = array();
				$order  = array();
				foreach ( $m as $seg ) {
					$href = $seg[1];
					if ( ! isset( $merged[ $href ] ) ) {
						$merged[ $href ] = $seg[2];
						$order[]         = $href;
					} else {
						$merged[ $href ] .= $seg[2];
					}
				}
				$cards = array();
				foreach ( $order as $href ) {
					$cards[] = '<a class="spielend-category-grid__card" href="' . esc_url( $href ) . '">' . $merged[ $href ] . '</a>';
				}
				if ( $cards ) {
					$output = '<div class="spielend-category-grid">' . implode( "\n", $cards ) . '</div>';
				}
			}

			$output = preg_replace( '#\s{2,}#', ' ', $output );
			$output = trim( $output );
		}
		return $output;
	}

	public function enqueue_styles(): void {
		wp_register_style( 'spielend-essentials', false, array(), '1.0.0' );
		wp_enqueue_style( 'spielend-essentials' );
		wp_add_inline_style( 'spielend-essentials', $this->get_styles() );
	}

	/** Wunschzettel: Cookie-basiertes Wishlist-System (ohne externes Plugin) */
	public function shortcode_wishlist_button( $atts ): string {
		$atts = shortcode_atts( array( 'product_id' => 0 ), $atts, 'spielend_wishlist_button' );
		$product_id = (int) $atts['product_id'];
		if ( ! $product_id ) {
			$product_id = get_the_ID();
		}
		if ( ! $product_id || 'product' !== get_post_type( $product_id ) ) {
			return '';
		}
		$nonce = wp_create_nonce( 'spielend_wishlist' );
		return sprintf(
			'<button type="button" class="spielend-wishlist-btn" data-id="%1$d" data-nonce="%2$s" aria-label="Auf die Wunschliste">&#9829;<span class="spielend-wishlist-btn__label">Wunschliste</span></button>',
			$product_id,
			esc_attr( $nonce )
		);
	}

	public function shortcode_wishlist_page(): string {
		$ids = isset( $_COOKIE['spielend_wishlist'] ) ? array_filter( array_map( 'intval', explode( ',', sanitize_text_field( wp_unslash( $_COOKIE['spielend_wishlist'] ) ) ) ) ) : array();

		if ( empty( $ids ) ) {
			return '<p class="spielend-wishlist-empty">Deine Wunschliste ist leer. Entdecke unsere <a href="/shop/">Produkte</a>!</p>';
		}

		$items = '';
		foreach ( $ids as $id ) {
			$product = wc_get_product( $id );
			if ( ! $product ) {
				continue;
			}
			$img = $product->get_image( 'thumbnail' );
			$items .= sprintf(
				'<div class="spielend-wishlist-item" data-id="%1$d">
					<div class="spielend-wishlist-item__img">%2$s</div>
					<div class="spielend-wishlist-item__info">
						<a class="spielend-wishlist-item__title" href="%3$s">%4$s</a>
						<span class="spielend-wishlist-item__price">%5$s</span>
						<a class="button wp-element-button" href="%6$s">In den Warenkorb</a>
					</div>
					<button type="button" class="spielend-wishlist-remove" data-id="%1$d" aria-label="Entfernen">&times;</button>
				</div>',
				$id,
				$img,
				esc_url( $product->get_permalink() ),
				esc_html( $product->get_name() ),
				wp_kses_post( $product->get_price_html() ),
				esc_url( add_query_arg( 'add-to-cart', $id, $product->get_permalink() ) )
			);
		}
		return '<div class="spielend-wishlist">' . $items . '</div>';
	}

	public function wishlist_scripts(): void {
		if ( is_page( 'wunschzettel' ) || ( function_exists( 'is_product' ) && is_product() ) ) {
			$inline = <<<JS
(function(){
  document.addEventListener('click', function(e){
    var btn = e.target.closest('.spielend-wishlist-btn');
    if (btn) {
      e.preventDefault();
      var id = btn.getAttribute('data-id');
      var list = (document.cookie.match(/(?:^|; )spielend_wishlist=([^;]*)/) || [,''])[1];
      var ids = list ? list.split(',').filter(Boolean) : [];
      if (ids.indexOf(id) === -1) ids.push(id);
      document.cookie = 'spielend_wishlist=' + ids.join(',') + '; path=/; max-age=31536000';
      btn.classList.add('is-active');
      btn.textContent = '♥ Auf der Wunschliste';
    }
    var rm = e.target.closest('.spielend-wishlist-remove');
    if (rm) {
      var id = rm.getAttribute('data-id');
      var list = (document.cookie.match(/(?:^|; )spielend_wishlist=([^;]*)/) || [,''])[1];
      var ids = (list ? list.split(',').filter(Boolean) : []).filter(function(x){ return x !== id; });
      document.cookie = 'spielend_wishlist=' + ids.join(',') + '; path=/; max-age=31536000';
      var item = rm.closest('.spielend-wishlist-item');
      if (item) item.remove();
    }
  });
})();
JS;
			wp_register_script( 'spielend-wishlist', false, array(), '1.0.0', true );
			wp_enqueue_script( 'spielend-wishlist' );
			wp_add_inline_script( 'spielend-wishlist', $inline );
		}
	}

	private function get_theme_option( string $key ): string {
		$value = get_theme_mod( $key, '' );
		if ( '' === $value ) {
			$value = get_option( $key, '' );
		}

		return is_string( $value ) ? $value : '';
	}

	private function contact_redirect_url( string $status, string $message ): string {
		return add_query_arg(
			array(
				'spielend_contact' => $status,
				'spielend_message' => $message,
			),
			wp_get_referer() ?: home_url( '/' )
		);
	}

	private function get_styles(): string {
		return <<<CSS
.spielend-social-icons{display:flex;flex-wrap:wrap;gap:.75rem;list-style:none;margin:0;padding:0}
.spielend-social-icons__link{display:inline-flex;align-items:center;justify-content:center;width:2.5rem;height:2.5rem;border-radius:9999px;background:var(--wp--preset--color--primary,#1e293b);color:#fff;text-decoration:none;transition:opacity .2s ease}
.spielend-social-icons__link:hover,.spielend-social-icons__link:focus{opacity:.85}
.spielend-social-icons__icon{width:1.25rem;height:1.25rem;fill:currentColor}
.spielend-contact-info{display:grid;gap:.5rem}
.spielend-contact-info__item{margin:0;display:flex;gap:.5rem;align-items:baseline}
.spielend-contact-info__label{font-weight:600}
.spielend-contact-form__field{margin:0 0 1rem}
.spielend-contact-form label{display:block;margin-bottom:.25rem;font-weight:600}
.spielend-contact-form input[type="text"],.spielend-contact-form input[type="email"],.spielend-contact-form textarea{width:100%;padding:.6rem .75rem;border:1px solid #cbd5e1;border-radius:.375rem;background:#fff;box-sizing:border-box}
.spielend-contact-form textarea{resize:vertical}
.spielend-contact-form .spielend-contact-form__honeypot{position:absolute;left:-9999px;top:auto;width:1px;height:1px;overflow:hidden}
.spielend-contact-form__notice{padding:.75rem 1rem;border-radius:.375rem;margin:0 0 1rem}
.spielend-contact-form__notice--success{background:#ecfdf5;border:1px solid #a7f3d0;color:#065f46}
.spielend-contact-form__notice--error{background:#fef2f2;border:1px solid #fecaca;color:#991b1b}
.spielend-category-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(180px,1fr));gap:1rem}
.spielend-category-grid__card{display:flex;flex-direction:column;align-items:center;gap:.5rem;padding:1.25rem;border-radius:16px;background:var(--wp--preset--color--base,#fff);box-shadow:0 1px 3px rgba(45,45,45,.12);text-decoration:none;color:var(--wp--preset--color--foreground,#2d2d2d);transition:transform .15s ease,box-shadow .15s ease}
.spielend-category-grid__card:hover,.spielend-category-grid__card:focus{transform:translateY(-2px);box-shadow:0 6px 16px rgba(45,45,45,.15)}
.spielend-category-grid__figure{width:100%;aspect-ratio:1;margin:0;overflow:hidden;border-radius:12px;background:var(--wp--preset--color--background,#fafaf8)}
.spielend-category-grid__figure img{width:100%;height:100%;object-fit:cover;display:block}
.spielend-category-grid__placeholder{width:100%;height:100%;background:linear-gradient(135deg,var(--wp--preset--color--secondary,#2b7a62),var(--wp--preset--color--primary,#ff6b35))}
.spielend-category-grid__title{font-weight:700;text-align:center;line-height:1.2}
.spielend-category-grid__count{font-size:.85rem;color:var(--wp--preset--color--secondary,#2b7a62)}
.spielend-contact-form__submit{cursor:pointer;padding:.6rem 1.25rem;border:0;border-radius:.375rem;background:var(--wp--preset--color--primary,#1e293b);color:#fff;font-weight:600}
.se-featured-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:1.25rem;max-width:1200px;margin:0 auto}
.se-prod-card{display:flex;flex-direction:column;gap:.6rem;padding:1rem;border-radius:20px;background:var(--wp--preset--color--surface,#fff);box-shadow:0 1px 3px rgba(41,37,36,.1);border:1px solid rgba(120,113,108,.12);text-decoration:none;color:var(--wp--preset--color--foreground,#292524);transition:transform .25s ease,box-shadow .25s ease}
.se-prod-card:hover,.se-prod-card:focus-visible{transform:translateY(-4px);box-shadow:0 12px 28px rgba(204,77,0,.14);outline:3px solid var(--wp--preset--color--primary,#cc4d00);outline-offset:2px}
.se-prod-card__img{width:100%;aspect-ratio:1;border-radius:14px;overflow:hidden;background:var(--wp--preset--color--background,#faf9f6)}
.se-prod-card__img img{width:100%;height:100%;object-fit:cover;display:block}
.se-prod-card__title{font-weight:600;font-size:.95rem;line-height:1.3;text-align:center}
.se-prod-card__price{font-weight:700;color:var(--wp--preset--color--primary,#cc4d00);text-align:center}
.se-prod-card__btn{display:inline-flex;align-items:center;justify-content:center;margin:0 auto;padding:.5rem 1rem;border-radius:9999px;background:var(--wp--preset--color--primary,#cc4d00);color:#fff;font-size:.85rem;font-weight:600;text-align:center}
CSS;
	}
}

Spielend_Essentials::instance();

register_activation_hook(
	__FILE__,
	static function (): void {
		Spielend_Essentials::instance()->register_post_types();
		flush_rewrite_rules();
	}
);

register_deactivation_hook(
	__FILE__,
	static function (): void {
		flush_rewrite_rules();
	}
);
