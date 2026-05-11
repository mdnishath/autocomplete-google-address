<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://profiles.wordpress.org/nishatbd31/
 * @since      1.0.0
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/public
 */

defined( 'ABSPATH' ) || exit;

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/public
 * @author     Md Nishath Khandakar <https://profiles.wordpress.org/nishatbd31/>
 */
class AGA_Frontend {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

    /**
     * A list of form config IDs to be loaded on the current page.
     *
     * @since    1.0.0
     * @access   private
     * @var      array
     */
    private $forms_to_load = array();

    /**
     * A flag to ensure Google Maps API is enqueued only once.
     *
     * @since    1.0.0
     * @access   private
     * @var      boolean
     */
    private static $gmaps_enqueued = false;

    /**
     * Count of configs already localized so the wp_footer fallback re-localizes
     * only when new shortcode configs were appended after the wp_enqueue_scripts pass.
     *
     * @var int
     */
    private $localized_count = 0;


	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

		// Visual Selector Tool iframe mode — hide admin bar and add helper styles.
		if ( isset( $_GET['aga_vst'] ) && '1' === $_GET['aga_vst'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			add_filter( 'show_admin_bar', '__return_false' );
			add_action( 'wp_head', array( $this, 'vst_iframe_styles' ) );
		}
	}

	/**
	 * Output minimal styles when page is loaded inside the Visual Selector Tool iframe.
	 */
	public function vst_iframe_styles() {
		echo '<style id="aga-vst-iframe-styles">'
			. 'html { margin-top: 0 !important; }'
			. '#wpadminbar { display: none !important; }'
			. 'body { cursor: crosshair !important; }'
			. '</style>';
	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
		$should_load = apply_filters( 'aga_should_load_frontend', ! empty( $this->forms_to_load ) );
		if ( ! $should_load ) {
            return;
        }

		wp_enqueue_style( $this->plugin_name, AGA_PLUGIN_URL . 'public/css/frontend.css', array(), filemtime( AGA_PLUGIN_DIR . 'public/css/frontend.css' ), 'all' );
	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {
		$should_load = apply_filters( 'aga_should_load_frontend', ! empty( $this->forms_to_load ) );
		if ( ! $should_load ) {
            return;
        }

        $this->enqueue_google_maps_api();

		wp_enqueue_script( $this->plugin_name, AGA_PLUGIN_URL . 'public/js/frontend.js', array( 'jquery' ), filemtime( AGA_PLUGIN_DIR . 'public/js/frontend.js' ), true );

        $this->localize_script_data();
	}
    
    /**
     * Handles the [aga_form] shortcode.
     *
     * @param array $atts Shortcode attributes.
     * @return string Empty string. The shortcode's purpose is to enqueue scripts.
     */
    public function render_shortcode( $atts ) {
        $atts = shortcode_atts(
            array(
                'id' => 0,
            ),
            $atts,
            'aga_form'
        );

        $id = absint( $atts['id'] );
        if ( $id ) {
            $this->prepare_scripts_for_form( $id );
        }

        return ''; // The shortcode itself outputs nothing.
    }
    
    /**
     * Adds a form ID to the list of forms to be loaded on the page.
     *
     * @param int $form_id The ID of the form config post.
     */
    public function prepare_scripts_for_form( $form_id ) {
        if ( ! in_array( $form_id, $this->forms_to_load, true ) ) {
            $this->forms_to_load[] = $form_id;
        }
    }

    /**
     * Adds the Google Maps bootstrap loader script to the footer.
     * This is the recommended way to load the API asynchronously.
     */
    private function enqueue_google_maps_api() {
        if ( self::$gmaps_enqueued || aga_get_setting( 'do_not_load_gmaps_api' ) ) {
            return;
        }

        $api_key = aga_get_setting( 'api_key' );
        if ( empty( $api_key ) ) {
            return;
        }
        
        $language = '';
        if ( ! empty( $this->forms_to_load ) ) {
            foreach ( $this->forms_to_load as $form_id ) {
                $lang_override = get_post_meta( $form_id, 'Nish_aga_language_override', true );
                if ( ! empty( $lang_override ) ) {
                    $language = $lang_override;
                    break;
                }
            }
        }
        
        if ( empty( $language ) ) {
            $language = get_locale();
        }

        $api_url = add_query_arg(
            array(
                'key'       => $api_key,
                'libraries' => 'places',
                'language'  => $language,
                'v'         => 'weekly',
                'loading'   => 'async',
            ),
            'https://maps.googleapis.com/maps/api/js'
        );

        wp_enqueue_script( 'aga-google-maps', esc_url( $api_url ), array(), null, true );
        
        self::$gmaps_enqueued = true;
    }
    
    /**
     * Localizes the form configuration data for the frontend script.
     */
    private function localize_script_data() {
        // Skip if nothing new was added since the previous pass — wp_localize_script
        // appends a duplicate <script> tag each time it's called, so we guard here.
        if ( $this->localized_count === count( $this->forms_to_load ) && $this->localized_count > 0 ) {
            return;
        }

        $configs = array();

        // Remove duplicates and ensure we have valid IDs.
        $this->forms_to_load = array_unique( array_map( 'absint', $this->forms_to_load ) );

        foreach ( $this->forms_to_load as $form_id ) {
            $form_post = get_post( $form_id );
            if ( $form_post && 'aga_form' === $form_post->post_type ) {
                $configs[] = AGA_Autocomplete::get_js_config( $form_id );
            }
        }

        // Allow other modules (e.g., WooCommerce) to inject configs.
        $configs = apply_filters( 'aga_form_configs', $configs );

        wp_localize_script( $this->plugin_name, 'aga_form_configs', $configs );

        $settings = get_option( 'Nish_aga_settings' );
        $is_paying = function_exists( 'google_autocomplete' ) && google_autocomplete()->is_paying();

        $frontend_data = array(
            'ajax_url'     => admin_url( 'admin-ajax.php' ),
            'nonce'        => wp_create_nonce( 'aga_frontend_nonce' ),
            'is_logged_in' => is_user_logged_in(),
        );

        // Pass custom attribution text if set (Pro)
        if ( $is_paying && ! empty( $settings['attribution_text'] ) ) {
            $frontend_data['attribution_text'] = sanitize_text_field( $settings['attribution_text'] );
        }

        // White label flag.
        $frontend_data['white_label'] = aga_get_setting( 'white_label' ) === '1';

        // Checkout abandonment tracking flag.
        $frontend_data['track_abandonment'] = aga_get_setting( 'track_abandonment' ) === '1';

        // Map zoom level from Appearance settings (default 17).
        $frontend_data['map_zoom'] = intval( aga_get_setting( 'map_zoom' ) ?: 17 );

        // Server-side IP geolocation (avoids CORS errors from client-side fetch).
        $ip_geo = self::get_ip_geolocation();
        if ( $ip_geo ) {
            $frontend_data['ip_geo'] = $ip_geo;
        }

        wp_localize_script( $this->plugin_name, 'aga_frontend_data', $frontend_data );

        $this->localized_count = count( $this->forms_to_load );
    }

    /**
     * Finds and prepares any globally or page-specifically activated forms.
     * This runs on every page load on the frontend to check for configs that should be active.
     *
     * @since 1.1.0
     */
    public function load_automatic_forms() {
        // Single query: get all forms that are either global or page-specific.
        $all_forms = get_posts( array(
            'post_type'      => 'aga_form',
            'posts_per_page' => -1,
            'post_status'    => 'publish',
            'meta_query'     => array(
                'relation' => 'OR',
                array(
                    'key'   => 'Nish_aga_activate_globally',
                    'value' => '1',
                ),
                array(
                    'key'     => 'Nish_aga_load_on_pages',
                    'compare' => 'EXISTS',
                ),
            ),
            'fields' => 'ids',
        ) );

        if ( empty( $all_forms ) ) {
            return;
        }

        // Filter in PHP: keep global forms and page-specific forms matching the current page.
        $forms_to_load   = array();
        $current_page_id = get_queried_object_id();

        foreach ( $all_forms as $form_id ) {
            $global = get_post_meta( $form_id, 'Nish_aga_activate_globally', true );
            if ( '1' === $global ) {
                $forms_to_load[] = $form_id;
                continue;
            }

            if ( $current_page_id ) {
                $pages = get_post_meta( $form_id, 'Nish_aga_load_on_pages', true );
                if ( is_array( $pages ) && in_array( $current_page_id, $pages, true ) ) {
                    $forms_to_load[] = $form_id;
                }
            }
        }

        foreach ( $forms_to_load as $form_id ) {
            $this->prepare_scripts_for_form( $form_id );
        }
    }
    /**
     * Outputs custom CSS styles for the autocomplete dropdown.
     * Only outputs when the user is on a Pro plan.
     *
     * @since 1.2.0
     */
    public function output_custom_styles() {
        if ( ! function_exists( 'google_autocomplete' ) || ! google_autocomplete()->is_paying() ) {
            return;
        }

        $options = get_option( 'Nish_aga_settings' );

        $bg_color      = ! empty( $options['dropdown_bg_color'] ) ? $options['dropdown_bg_color'] : '#ffffff';
        $text_color    = ! empty( $options['dropdown_text_color'] ) ? $options['dropdown_text_color'] : '#333333';
        $hover_color   = ! empty( $options['dropdown_hover_color'] ) ? $options['dropdown_hover_color'] : '#f0f0f0';
        $border_color  = ! empty( $options['dropdown_border_color'] ) ? $options['dropdown_border_color'] : '#dddddd';
        $border_radius = isset( $options['dropdown_border_radius'] ) && '' !== $options['dropdown_border_radius'] ? absint( $options['dropdown_border_radius'] ) : 4;
        $font_size     = isset( $options['dropdown_font_size'] ) && '' !== $options['dropdown_font_size'] ? absint( $options['dropdown_font_size'] ) : 14;
        $max_height    = isset( $options['dropdown_max_height'] ) && '' !== $options['dropdown_max_height'] ? absint( $options['dropdown_max_height'] ) : 250;

        ?>
        <style id="aga-custom-dropdown-styles">
            .aga-autocomplete-dropdown {
                background-color: <?php echo esc_attr( $bg_color ); ?> !important;
                border-color: <?php echo esc_attr( $border_color ); ?> !important;
                border-radius: <?php echo esc_attr( $border_radius ); ?>px !important;
                max-height: <?php echo esc_attr( $max_height ); ?>px !important;
                font-size: <?php echo esc_attr( $font_size ); ?>px !important;
            }
            .aga-autocomplete-dropdown .aga-autocomplete-item {
                color: <?php echo esc_attr( $text_color ); ?> !important;
                font-size: <?php echo esc_attr( $font_size ); ?>px !important;
                border-bottom-color: <?php echo esc_attr( $border_color ); ?> !important;
            }
            .aga-autocomplete-dropdown .aga-autocomplete-item:hover,
            .aga-autocomplete-dropdown .aga-autocomplete-item--active {
                background-color: <?php echo esc_attr( $hover_color ); ?> !important;
            }
            <?php
            $attr_text_color = ! empty( $options['attribution_text_color'] ) ? $options['attribution_text_color'] : '';
            $attr_bg_color   = ! empty( $options['attribution_bg_color'] ) ? $options['attribution_bg_color'] : '';
            $attr_font_size  = isset( $options['attribution_font_size'] ) && '' !== $options['attribution_font_size'] ? absint( $options['attribution_font_size'] ) : 0;
            if ( $attr_text_color || $attr_bg_color || $attr_font_size ) : ?>
            .aga-autocomplete-attribution {
                <?php if ( $attr_text_color ) : ?>color: <?php echo esc_attr( $attr_text_color ); ?> !important;<?php endif; ?>
                <?php if ( $attr_bg_color ) : ?>background-color: <?php echo esc_attr( $attr_bg_color ); ?> !important;<?php endif; ?>
                <?php if ( $attr_font_size ) : ?>font-size: <?php echo esc_attr( $attr_font_size ); ?>px !important;<?php endif; ?>
            }
            <?php endif; ?>
        </style>
        <?php
    }

    /**
     * Outputs inline JS in wp_footer that blocks WooCommerce checkout submission
     * when the visitor selects an address in a country not in the store's allowed list.
     */
    public function output_checkout_blocklist_script() {
        if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
            return;
        }
        ?>
        <script>
        (function ($) {
        'use strict';

        function getMainAddressInput() {
            return document.querySelector('input[role="combobox"][data-aga-init="1"]');
        }

        function isUnsupportedCountrySelected() {
            var input = getMainAddressInput();
            return !!(input && input._agaUnsupportedCountry);
        }

        function getUnsupportedCountryName() {
            var input = getMainAddressInput();
            return (input && input._agaUnsupportedCountryName)
                ? input._agaUnsupportedCountryName
                : 'this country/region';
        }

        function removeUnsupportedCountryNotice() {
            $('.aga-checkout-country-error').remove();
        }

        function showUnsupportedCountryNotice() {
            removeUnsupportedCountryNotice();

            var message = 'Not verified — We currently do not ship to ' + getUnsupportedCountryName() + '.';

            var $wrapper = $('.woocommerce-notices-wrapper').first();
            if (!$wrapper.length) {
                var $form = $('form.checkout');
                if (!$form.length) return;

                $wrapper = $('<div class="woocommerce-notices-wrapper"></div>');
                $form.prepend($wrapper);
            }

            $wrapper.prepend(
                $('<div class="woocommerce-error aga-checkout-country-error" role="alert"></div>').text(message)
            );

            $('html, body').animate({
                scrollTop: $wrapper.offset().top - 120
            }, 200);
        }

        function refreshPlaceOrderState() {
            var $button = $('#place_order');
            if (!$button.length) return;

            if (!$button.data('aga-original-text')) {
                $button.data('aga-original-text', $.trim($button.text()));
            }
            if (!$button.data('aga-original-value')) {
                $button.data('aga-original-value', $button.val());
            }

            if (isUnsupportedCountrySelected()) {
                var blockedText = 'Please select a supported address';
                $button
                    .prop('disabled', true)
                    .attr('aria-disabled', 'true')
                    .addClass('aga-place-order-disabled')
                    .text(blockedText)
                    .val(blockedText)
                    .attr('data-value', blockedText);
            } else {
                var originalText = $button.data('aga-original-text') || 'Place order';
                var originalValue = $button.data('aga-original-value') || originalText;
                $button
                    .prop('disabled', false)
                    .removeAttr('aria-disabled')
                    .removeClass('aga-place-order-disabled')
                    .text(originalText)
                    .val(originalValue)
                    .attr('data-value', originalValue);

                removeUnsupportedCountryNotice();
            }
        }

        $(document.body).on('updated_checkout change', function () {
            refreshPlaceOrderState();
        });

        $(document).on('click', '#place_order', function (e) {
            if (isUnsupportedCountrySelected()) {
                e.preventDefault();
                e.stopImmediatePropagation();
                showUnsupportedCountryNotice();
                refreshPlaceOrderState();
                return false;
            }
        });

        $(document).on('checkout_place_order', 'form.checkout', function () {
            if (isUnsupportedCountrySelected()) {
                showUnsupportedCountryNotice();
                refreshPlaceOrderState();
                return false;
            }
            return true;
        });

        $(function () {
            refreshPlaceOrderState();
        });

        })(jQuery);
        </script>
        <?php
    }

    public function add_async_attribute( $tag, $handle ) {
        if ( 'aga-google-maps' !== $handle ) {
            return $tag;
        }
        return str_replace( ' src', ' async src', $tag );
    }

    /**
     * Outputs an HTML comment with the plugin's runtime state when ?aga_debug=1
     * is present in the URL. Use this to view-source a page (logged-in vs
     * logged-out / different browser / incognito) and compare what was actually
     * detected and enqueued for that request.
     *
     * Note: if a page-caching plugin is serving cached HTML, the comment will
     * reflect whatever state existed when the cache entry was generated — that
     * is itself the diagnostic signal.
     */
    public function maybe_print_debug_comment() {
        if ( empty( $_GET['aga_debug'] ) || '1' !== (string) $_GET['aga_debug'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        $api_key  = aga_get_setting( 'api_key' );
        $info = array(
            'plugin_version'     => defined( 'AGA_VERSION' ) ? AGA_VERSION : 'unknown',
            'is_user_logged_in'  => is_user_logged_in() ? 'yes' : 'no',
            'queried_object_id'  => (int) get_queried_object_id(),
            'forms_to_load'      => array_values( array_map( 'intval', (array) $this->forms_to_load ) ),
            'gmaps_enqueued'     => self::$gmaps_enqueued ? 'yes' : 'no',
            'main_script_done'   => wp_script_is( $this->plugin_name, 'done' ) ? 'yes' : 'no',
            'main_script_queued' => wp_script_is( $this->plugin_name, 'enqueued' ) ? 'yes' : 'no',
            'gmaps_script_done'  => wp_script_is( 'aga-google-maps', 'done' ) ? 'yes' : 'no',
            'gmaps_script_queued'=> wp_script_is( 'aga-google-maps', 'enqueued' ) ? 'yes' : 'no',
            'api_key_set'        => ! empty( $api_key ) ? 'yes' : 'no',
            'is_checkout'        => ( function_exists( 'is_checkout' ) && is_checkout() ) ? 'yes' : 'no',
            'should_load_filter' => apply_filters( 'aga_should_load_frontend', ! empty( $this->forms_to_load ) ) ? 'yes' : 'no',
        );

        echo "\n<!-- AGA-DEBUG " . esc_html( wp_json_encode( $info ) ) . " -->\n";
    }

    /**
     * Server-side IP geolocation to avoid CORS errors from client-side fetch.
     * Cached for 24 hours per visitor IP.
     *
     * @return array|null { lat: float, lng: float } or null on failure.
     */
    public static function get_ip_geolocation() {
        $ip = self::get_visitor_ip();
        if ( ! $ip || in_array( $ip, array( '127.0.0.1', '::1' ), true ) ) {
            return null;
        }

        $cache_key = 'aga_ip_geo_' . md5( $ip );
        $cached    = get_transient( $cache_key );
        if ( false !== $cached ) {
            return $cached;
        }

        $response = wp_remote_get( 'https://ipapi.co/' . $ip . '/json/', array(
            'timeout'    => 3,
            'user-agent' => 'AutocompleteGoogleAddress/' . AGA_VERSION,
        ) );

        if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) {
            // Cache the failure too so we don't retry on every page load.
            set_transient( $cache_key, null, HOUR_IN_SECONDS );
            return null;
        }

        $body = json_decode( wp_remote_retrieve_body( $response ), true );
        if ( empty( $body['latitude'] ) || empty( $body['longitude'] ) ) {
            set_transient( $cache_key, null, HOUR_IN_SECONDS );
            return null;
        }

        $geo = array(
            'lat' => (float) $body['latitude'],
            'lng' => (float) $body['longitude'],
        );

        set_transient( $cache_key, $geo, DAY_IN_SECONDS );

        return $geo;
    }

    /**
     * Get the visitor's real IP address.
     */
    private static function get_visitor_ip() {
        $headers = array(
            'HTTP_CF_CONNECTING_IP', // Cloudflare
            'HTTP_X_FORWARDED_FOR',
            'HTTP_X_REAL_IP',
            'REMOTE_ADDR',
        );
        foreach ( $headers as $header ) {
            if ( ! empty( $_SERVER[ $header ] ) ) {
                $ip = $_SERVER[ $header ];
                // X-Forwarded-For can be comma-separated.
                if ( strpos( $ip, ',' ) !== false ) {
                    $ip = trim( explode( ',', $ip )[0] );
                }
                if ( filter_var( $ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE ) ) {
                    return $ip;
                }
            }
        }
        return isset( $_SERVER['REMOTE_ADDR'] ) ? $_SERVER['REMOTE_ADDR'] : null;
    }

}
