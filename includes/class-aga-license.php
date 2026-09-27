<?php
/**
 * License client for keys sold on mdnishath.com (paid locally via EPS).
 *
 * Premium is unlocked by EITHER a valid mdnishath.com license (this class) OR a
 * paying Freemius account. When a valid key of ours is stored, Freemius is not
 * initialised at all (see autocomplete-google-address.php), so every premium
 * check must go through aga_is_pro() and every upgrade link through
 * aga_checkout_url() instead of calling google_autocomplete() directly.
 *
 * Token format (see the license server spec): "<payload>.<signature>", both
 * base64url without padding. The signature is Ed25519 over the ASCII bytes of
 * the payload segment. Payload JSON: { v, lic, pid, plan, dev, max, exp, iat, chk }.
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/includes
 */

defined( 'ABSPATH' ) || exit;

/**
 * License client.
 */
class AGA_License {

	/** Product slug on the license server. */
	const PRODUCT = 'autocomplete-google-address';

	/** Required key prefix. */
	const KEY_PREFIX = 'AGA-';

	/** Production license server. */
	const DEFAULT_BASE_URL = 'https://mdnishath.com';

	/** Production Ed25519 public key (raw, base64). Public, not a secret. */
	const DEFAULT_PUBLIC_KEY = '0AhzBp3L799jlLOcpSIzQoXHaC0MWP/mbdfb9WEQTdI=';

	/** Option holding key, token and last known state (autoload off). */
	const OPTION = 'aga_license';

	/**
	 * Sell Pro through the Freemius checkout as well as mdnishath.com?
	 * Off: every buy / upgrade link goes to mdnishath.com (cards worldwide, bKash, Nagad) and
	 * Freemius's upgrade menu and trial offers are hidden. Customers who already pay through
	 * Freemius keep Pro either way. Set to true (and release) to sell through Freemius again.
	 */
	const FREEMIUS_SALES = false;

	/** WP-Cron hook for the background refresh (hook name kept from the daily schedule). */
	const CRON_HOOK = 'aga_license_daily_refresh';

	/** How often WP-Cron re-validates the key (sites whose admin is rarely opened). */
	const CRON_RECURRENCE = 'hourly';

	/**
	 * Seconds between online validations on admin page loads. A license revoked, expired
	 * or moved on mdnishath.com switches Pro off within this time; the License page always
	 * asks the server. Offline, the signed token keeps working until its `chk`.
	 */
	const REFRESH_INTERVAL = 15 * MINUTE_IN_SECONDS;

	/** Seconds before retrying after a failed (network) check. */
	const RETRY_INTERVAL = 3600;

	/** HTTP timeout for license server calls the user is waiting on (activate / deactivate). */
	const HTTP_TIMEOUT = 10;

	/** Shorter timeout for the background daily check, which can run during an admin page load. */
	const REFRESH_TIMEOUT = 5;

	/**
	 * Per-request cache of the token check.
	 *
	 * @var array|false|null
	 */
	private static $payload_cache = null;

	/* --------------------------------------------------------------------
	 * Configuration
	 * ------------------------------------------------------------------ */

	/**
	 * License server base URL. Overridable via AGA_LICENSE_SERVER_URL in
	 * wp-config.php, but only on a local/development site with WP_DEBUG on.
	 *
	 * @return string
	 */
	public static function base_url() {
		if ( self::debug_overrides_allowed() && defined( 'AGA_LICENSE_SERVER_URL' ) && is_string( AGA_LICENSE_SERVER_URL ) && '' !== AGA_LICENSE_SERVER_URL ) {
			return untrailingslashit( AGA_LICENSE_SERVER_URL );
		}
		return self::DEFAULT_BASE_URL;
	}

	/**
	 * Raw base64 public key. Overridable via AGA_LICENSE_PUBLIC_KEY in
	 * wp-config.php, but only on a local/development site with WP_DEBUG on.
	 *
	 * @return string
	 */
	public static function public_key() {
		if ( self::debug_overrides_allowed() && defined( 'AGA_LICENSE_PUBLIC_KEY' ) && is_string( AGA_LICENSE_PUBLIC_KEY ) && '' !== AGA_LICENSE_PUBLIC_KEY ) {
			return AGA_LICENSE_PUBLIC_KEY;
		}
		return self::DEFAULT_PUBLIC_KEY;
	}

	/**
	 * Test-server overrides are for the plugin's own development only. Many live sites
	 * leave WP_DEBUG on, so the environment must also be declared local/development
	 * (WP_ENVIRONMENT_TYPE); a production site always uses mdnishath.com and its key.
	 *
	 * @return bool
	 */
	private static function debug_overrides_allowed() {
		if ( ! defined( 'WP_DEBUG' ) || ! WP_DEBUG ) {
			return false;
		}
		$env = function_exists( 'wp_get_environment_type' ) ? wp_get_environment_type() : 'production';
		return in_array( $env, array( 'local', 'development' ), true );
	}

	/**
	 * Buy page for this product.
	 *
	 * @return string
	 */
	public static function buy_url() {
		return self::DEFAULT_BASE_URL . '/buy/' . self::PRODUCT;
	}

	/**
	 * Renewal page.
	 *
	 * @return string
	 */
	public static function renew_url() {
		$key = self::get_data()['key'];
		return self::DEFAULT_BASE_URL . '/renew' . ( '' !== $key ? '?key=' . rawurlencode( $key ) : '' );
	}

	/* --------------------------------------------------------------------
	 * Stored state
	 * ------------------------------------------------------------------ */

	/**
	 * Stored state with defaults.
	 *
	 * @return array
	 */
	public static function get_data() {
		$data = get_option( self::OPTION, array() );
		if ( ! is_array( $data ) ) {
			$data = array();
		}
		return array_merge(
			array(
				'key'          => '',
				'token'        => '',
				'license'      => array(),
				'error'        => '',
				'message'      => '',
				'checked_at'   => 0,
				'last_ok'      => false,
				'validated_at' => 0,
			),
			$data
		);
	}

	/**
	 * Persist state (never autoloaded).
	 *
	 * @param array $data State.
	 */
	private static function save_data( array $data ) {
		self::$payload_cache = null;
		if ( false === get_option( self::OPTION, false ) ) {
			add_option( self::OPTION, $data, '', 'no' );
		} else {
			update_option( self::OPTION, $data, false );
		}
	}

	/**
	 * Remove all stored license data.
	 */
	public static function clear() {
		self::$payload_cache = null;
		delete_option( self::OPTION );
	}

	/**
	 * Whether a key of ours is stored (valid or not).
	 *
	 * @return bool
	 */
	public static function has_key() {
		$data = self::get_data();
		return '' !== $data['key'];
	}

	/* --------------------------------------------------------------------
	 * Offline token verification
	 * ------------------------------------------------------------------ */

	/**
	 * Whether the stored token is valid right now (offline check, no network).
	 *
	 * @return bool
	 */
	public static function is_valid() {
		return false !== self::get_valid_payload();
	}

	/**
	 * Decoded payload of the stored token if it is valid, otherwise false.
	 *
	 * @return array|false
	 */
	public static function get_valid_payload() {
		if ( null === self::$payload_cache ) {
			$data                = self::get_data();
			self::$payload_cache = ( '' !== $data['key'] && '' !== $data['token'] )
				? self::verify_token( $data['token'], $data['key'] )
				: false;
		}
		return self::$payload_cache;
	}

	/**
	 * Verify a token per the spec. Returns the payload or false.
	 *
	 * @param string $token Token "<payload>.<signature>".
	 * @param string $key   The key the token must belong to (optional).
	 * @param bool   $check_time Whether to enforce chk/exp (false only for display).
	 * @return array|false
	 */
	public static function verify_token( $token, $key = '', $check_time = true ) {
		if ( ! is_string( $token ) || '' === $token ) {
			return false;
		}
		$parts = explode( '.', $token );
		if ( 2 !== count( $parts ) || '' === $parts[0] || '' === $parts[1] ) {
			return false;
		}
		list( $payload_b64, $sig_b64 ) = $parts;

		$signature  = self::base64url_decode( $sig_b64 );
		$public_key = base64_decode( self::public_key(), true );
		if ( false === $signature || false === $public_key || 64 !== strlen( $signature ) || 32 !== strlen( $public_key ) ) {
			return false;
		}

		// 1. Signature over the ASCII payload segment (not the decoded JSON).
		if ( ! self::ensure_sodium() ) {
			return false;
		}
		try {
			if ( ! sodium_crypto_sign_verify_detached( $signature, $payload_b64, $public_key ) ) {
				return false;
			}
		} catch ( Exception $e ) {
			return false;
		} catch ( Error $e ) {
			return false;
		}

		$json = self::base64url_decode( $payload_b64 );
		if ( false === $json ) {
			return false;
		}
		$payload = json_decode( $json, true );
		if ( ! is_array( $payload ) ) {
			return false;
		}

		// 2. Version and product.
		if ( ! isset( $payload['v'], $payload['pid'] ) || 1 !== (int) $payload['v'] || self::PRODUCT !== $payload['pid'] ) {
			return false;
		}

		// Token must belong to the stored key.
		if ( '' !== $key && ( ! isset( $payload['lic'] ) || self::normalise_key( $key ) !== $payload['lic'] ) ) {
			return false;
		}

		// 3. Device = this site, compared after the server's normalisation.
		if ( ! isset( $payload['dev'] ) || ! is_string( $payload['dev'] ) || self::normalise_device_id( $payload['dev'] ) !== self::device_id_normalised() ) {
			return false;
		}

		if ( $check_time ) {
			$now = time();
			// 4. Must have re-validated online before chk.
			if ( ! isset( $payload['chk'] ) || ! is_numeric( $payload['chk'] ) || $now >= (int) $payload['chk'] ) {
				return false;
			}
			// 5. exp is null (lifetime) or in the future.
			if ( ! array_key_exists( 'exp', $payload ) ) {
				return false;
			}
			if ( null !== $payload['exp'] && ( ! is_numeric( $payload['exp'] ) || $now >= (int) $payload['exp'] ) ) {
				return false;
			}
		}

		return $payload;
	}

	/**
	 * Make sure sodium (native or WordPress's bundled sodium_compat) is loaded.
	 *
	 * @return bool
	 */
	private static function ensure_sodium() {
		if ( ! function_exists( 'sodium_crypto_sign_verify_detached' ) && defined( 'ABSPATH' ) && defined( 'WPINC' ) ) {
			$compat = ABSPATH . WPINC . '/sodium_compat/autoload.php';
			if ( file_exists( $compat ) ) {
				require_once $compat;
			}
		}
		return function_exists( 'sodium_crypto_sign_verify_detached' );
	}

	/**
	 * Decode base64url without padding.
	 *
	 * @param string $input Input.
	 * @return string|false
	 */
	private static function base64url_decode( $input ) {
		if ( ! is_string( $input ) || ! preg_match( '/^[A-Za-z0-9_-]+$/', $input ) ) {
			return false;
		}
		$b64 = strtr( $input, '-_', '+/' );
		$pad = strlen( $b64 ) % 4;
		if ( 1 === $pad ) {
			return false;
		}
		if ( $pad ) {
			$b64 .= str_repeat( '=', 4 - $pad );
		}
		return base64_decode( $b64, true );
	}

	/**
	 * Same normalisation as the server: trim, upper-case, strip whitespace.
	 *
	 * @param string $key Key.
	 * @return string
	 */
	public static function normalise_key( $key ) {
		return (string) preg_replace( '/\s+/', '', strtoupper( trim( (string) $key ) ) );
	}

	/**
	 * Same normalisation as the server for site device ids: trim, lower-case,
	 * strip http(s)://, a leading www. and trailing slashes.
	 *
	 * @param string $id Device id.
	 * @return string
	 */
	public static function normalise_device_id( $id ) {
		$id = strtolower( trim( (string) $id ) );
		$id = preg_replace( '#^https?://#', '', $id );
		$id = preg_replace( '#^www\.#', '', $id );
		$id = preg_replace( '#/+$#', '', $id );
		return (string) $id;
	}

	/**
	 * This site's device id as sent to the server.
	 *
	 * @return string
	 */
	public static function device_id() {
		return home_url();
	}

	/**
	 * @return string
	 */
	private static function device_id_normalised() {
		return self::normalise_device_id( self::device_id() );
	}

	/* --------------------------------------------------------------------
	 * Server calls
	 * ------------------------------------------------------------------ */

	/**
	 * POST to the license server.
	 *
	 * @param string $action  activate|validate|deactivate.
	 * @param array  $body    JSON body.
	 * @param int    $timeout Seconds.
	 * @return array { network_error: bool, status: int, body: array }
	 */
	private static function request( $action, array $body, $timeout = self::HTTP_TIMEOUT ) {
		$response = wp_remote_post(
			self::base_url() . '/api/license/' . $action,
			array(
				'timeout' => $timeout,
				'headers' => array(
					'Content-Type' => 'application/json',
					'Accept'       => 'application/json',
				),
				'body'    => wp_json_encode( $body ),
			)
		);

		if ( is_wp_error( $response ) ) {
			return array(
				'network_error' => true,
				'status'        => 0,
				'body'          => array( 'message' => $response->get_error_message() ),
			);
		}

		$status  = (int) wp_remote_retrieve_response_code( $response );
		$decoded = json_decode( wp_remote_retrieve_body( $response ), true );

		// Every license-API reply carries a boolean "ok". Anything else (an HTML 404 from a
		// site that isn't deployed, an HTML 403 from a proxy/CDN or security plugin) is not
		// an answer about the key, so treat it like a network failure — never lock on it.
		if ( ! is_array( $decoded ) || ! isset( $decoded['ok'] ) || ! is_bool( $decoded['ok'] ) ) {
			return array(
				'network_error' => true,
				'status'        => $status,
				'body'          => array(),
			);
		}

		return array(
			// 5xx and 429 are treated like a network failure (offline grace).
			'network_error' => ( $status >= 500 || 429 === $status || 0 === $status ),
			'status'        => $status,
			'body'          => $decoded,
		);
	}

	/**
	 * Body fields every call needs.
	 *
	 * @param string $key Key.
	 * @return array
	 */
	private static function base_body( $key ) {
		return array(
			'key'      => self::normalise_key( $key ),
			'product'  => self::PRODUCT,
			'deviceId' => self::device_id(),
		);
	}

	/**
	 * Human message from a response, with a fallback.
	 *
	 * @param array  $result   Result of request().
	 * @param string $fallback Fallback text.
	 * @return string
	 */
	private static function message_from( array $result, $fallback ) {
		if ( ! empty( $result['body']['message'] ) && is_string( $result['body']['message'] ) ) {
			return $result['body']['message'];
		}
		return $fallback;
	}

	/**
	 * Keep only the license summary fields we display.
	 *
	 * @param mixed $license License object from the server.
	 * @return array
	 */
	private static function sanitize_license_info( $license ) {
		if ( ! is_array( $license ) ) {
			return array();
		}
		return array(
			'plan'           => isset( $license['plan'] ) ? sanitize_text_field( (string) $license['plan'] ) : '',
			'expiresAt'      => isset( $license['expiresAt'] ) && is_string( $license['expiresAt'] ) ? sanitize_text_field( $license['expiresAt'] ) : null,
			'maxActivations' => isset( $license['maxActivations'] ) && is_numeric( $license['maxActivations'] ) ? (int) $license['maxActivations'] : null,
			'activations'    => isset( $license['activations'] ) && is_numeric( $license['activations'] ) ? (int) $license['activations'] : null,
		);
	}

	/**
	 * Activate a key on this site.
	 *
	 * @param string $raw_key Key typed by the user.
	 * @return true|WP_Error
	 */
	public static function activate( $raw_key ) {
		$key = self::normalise_key( $raw_key );
		if ( 0 !== strpos( $key, self::KEY_PREFIX ) || ! preg_match( '/^[A-Z0-9-]{8,64}$/', $key ) ) {
			return new WP_Error( 'bad_key', __( 'That is not a mdnishath.com key (they look like AGA-XXXXX-XXXXX-XXXXX-XXXXX). A Freemius license key goes in Plugins → Autocomplete Google Address → Activate License.', 'autocomplete-google-address' ) );
		}

		$body               = self::base_body( $key );
		$body['deviceName'] = function_exists( 'mb_substr' ) ? mb_substr( (string) get_bloginfo( 'name' ), 0, 120 ) : substr( (string) get_bloginfo( 'name' ), 0, 120 );
		if ( '' === trim( $body['deviceName'] ) ) {
			unset( $body['deviceName'] );
		}

		$result = self::request( 'activate', $body );

		if ( $result['network_error'] ) {
			return new WP_Error( 'network', self::message_from( $result, __( 'Could not reach the license server. Please try again.', 'autocomplete-google-address' ) ) );
		}
		if ( empty( $result['body']['ok'] ) || empty( $result['body']['token'] ) ) {
			$code = isset( $result['body']['error'] ) ? sanitize_key( $result['body']['error'] ) : 'error';
			return new WP_Error( $code, self::message_from( $result, __( 'Activation failed.', 'autocomplete-google-address' ) ) );
		}

		$token = (string) $result['body']['token'];
		if ( false === self::verify_token( $token, $key ) ) {
			return new WP_Error( 'bad_token', __( 'The license server answered, but its response could not be verified for this site. Please contact support.', 'autocomplete-google-address' ) );
		}

		self::save_data(
			array(
				'key'          => $key,
				'token'        => $token,
				'license'      => self::sanitize_license_info( $result['body']['license'] ?? null ),
				'error'        => '',
				'message'      => '',
				'checked_at'   => time(),
				'last_ok'      => true,
				'validated_at' => time(),
			)
		);

		return true;
	}

	/**
	 * Deactivate this site and forget the key.
	 *
	 * On a network failure the key is kept (so the slot can be freed later) and
	 * an error is returned. Any definite answer from the server removes it.
	 *
	 * @return true|WP_Error
	 */
	public static function deactivate() {
		$data = self::get_data();
		if ( '' === $data['key'] ) {
			return true;
		}

		$result = self::request( 'deactivate', self::base_body( $data['key'] ) );

		if ( $result['network_error'] ) {
			return new WP_Error( 'network', self::message_from( $result, __( 'Could not reach the license server. Please try again.', 'autocomplete-google-address' ) ) );
		}

		self::clear();
		return true;
	}

	/**
	 * Refresh the token online if due (every 15 minutes on admin loads, hourly after a
	 * failure).
	 *
	 * @param bool     $force   Ignore the throttle.
	 * @param int|null $max_age Re-check when the last check is older than this many seconds
	 *                          (overrides the normal interval, e.g. on the License page).
	 */
	public static function maybe_refresh( $force = false, $max_age = null ) {
		$data = self::get_data();
		if ( '' === $data['key'] ) {
			return;
		}

		$interval = null !== $max_age ? (int) $max_age : ( $data['last_ok'] ? self::REFRESH_INTERVAL : self::RETRY_INTERVAL );
		if ( ! $force && ( time() - (int) $data['checked_at'] ) < $interval ) {
			return;
		}

		// Record the attempt first so concurrent requests don't pile up.
		$data['checked_at'] = time();
		self::save_data( $data );

		$result = self::request( 'validate', self::base_body( $data['key'] ), self::REFRESH_TIMEOUT );

		if ( $result['network_error'] ) {
			// Keep the stored token; it stays valid until its chk.
			$data['last_ok'] = false;
			self::save_data( $data );
			return;
		}

		if ( ! empty( $result['body']['ok'] ) && ! empty( $result['body']['token'] ) ) {
			$token = (string) $result['body']['token'];
			if ( false !== self::verify_token( $token, $data['key'] ) ) {
				$data['token']        = $token;
				$data['license']      = self::sanitize_license_info( $result['body']['license'] ?? null );
				$data['error']        = '';
				$data['message']      = '';
				$data['last_ok']      = true;
				$data['validated_at'] = time();
			} else {
				// Unverifiable answer: keep the old token (grace until chk), retry sooner.
				$data['last_ok'] = false;
			}
			self::save_data( $data );
			return;
		}

		if ( 403 === $result['status'] ) {
			// revoked / expired / not_activated / invalid_key / wrong_product: lock premium.
			$data['token']   = '';
			$data['error']   = isset( $result['body']['error'] ) ? sanitize_key( $result['body']['error'] ) : 'invalid';
			$data['message'] = sanitize_text_field( self::message_from( $result, __( 'This license is no longer valid.', 'autocomplete-google-address' ) ) );
			$data['last_ok'] = true; // A definite answer: back to the normal interval.
			self::save_data( $data );
			return;
		}

		// 400 / unexpected: treat as a transient failure.
		$data['last_ok'] = false;
		self::save_data( $data );
	}

	/* --------------------------------------------------------------------
	 * Status for display
	 * ------------------------------------------------------------------ */

	/**
	 * Status summary for the License page.
	 *
	 * @return array { state: none|active|expired|invalid, ... }
	 */
	public static function get_status() {
		$data    = self::get_data();
		$payload = self::get_valid_payload();
		$status  = array(
			'state'       => 'none',
			'key'         => $data['key'],
			'plan'        => $data['license']['plan'] ?? '',
			'expires'     => null,
			'lifetime'    => false,
			'activations' => $data['license']['activations'] ?? null,
			'max'         => $data['license']['maxActivations'] ?? null,
			'message'     => $data['message'],
			'validated'   => (int) $data['validated_at'],
		);

		if ( '' === $data['key'] ) {
			return $status;
		}

		// Read exp from the stored token even if it is past chk/exp, for display.
		$display = $payload ? $payload : self::verify_token( $data['token'], $data['key'], false );
		if ( is_array( $display ) ) {
			if ( array_key_exists( 'exp', $display ) && null === $display['exp'] ) {
				$status['lifetime'] = true;
			} elseif ( isset( $display['exp'] ) && is_numeric( $display['exp'] ) ) {
				$status['expires'] = (int) $display['exp'];
			}
			if ( null === $status['max'] && isset( $display['max'] ) && is_numeric( $display['max'] ) ) {
				$status['max'] = (int) $display['max'];
			}
		} elseif ( ! empty( $data['license']['expiresAt'] ) ) {
			$ts = strtotime( $data['license']['expiresAt'] );
			if ( $ts ) {
				$status['expires'] = $ts;
			}
		}

		if ( $payload ) {
			$status['state'] = 'active';
		} elseif ( 'expired' === $data['error'] || ( $status['expires'] && time() >= $status['expires'] ) ) {
			$status['state'] = 'expired';
		} else {
			$status['state'] = 'invalid';
			if ( '' === $status['message'] ) {
				$status['message'] = __( 'This license could not be verified for this site. It will be re-checked automatically when the license server is reachable, or you can activate the key again.', 'autocomplete-google-address' );
			}
		}

		return $status;
	}

	/* --------------------------------------------------------------------
	 * Hooks
	 * ------------------------------------------------------------------ */

	/**
	 * Register WP hooks (cron). Called from the main plugin file.
	 *
	 * @param string $plugin_file Main plugin file.
	 */
	public static function init( $plugin_file ) {
		add_action( self::CRON_HOOK, array( __CLASS__, 'cron_refresh' ) );
		add_action( 'init', array( __CLASS__, 'schedule_cron' ) );
		add_action( 'admin_notices', array( __CLASS__, 'admin_notice' ) );
		register_deactivation_hook( $plugin_file, array( __CLASS__, 'unschedule_cron' ) );
	}

	/**
	 * Tell the site admin when a stored key stops unlocking Pro (expired / revoked /
	 * unverifiable), or is about to expire. Not shown on the License page itself.
	 */
	public static function admin_notice() {
		if ( ! current_user_can( 'manage_options' ) || ! self::has_key() ) {
			return;
		}
		// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- read-only screen check.
		if ( isset( $_GET['page'] ) && 'aga-license' === $_GET['page'] ) {
			return;
		}

		$status      = self::get_status();
		$license_url = admin_url( 'edit.php?post_type=aga_form&page=aga-license' );
		$renew_url   = self::renew_url();
		$class       = '';
		$text        = '';

		if ( 'expired' === $status['state'] ) {
			$class = 'notice-error';
			$text  = __( 'Your Autocomplete Google Address Pro license has expired, so Pro features are switched off.', 'autocomplete-google-address' );
		} elseif ( 'invalid' === $status['state'] ) {
			$class = 'notice-warning';
			$text  = '' !== $status['message'] ? $status['message'] : __( 'Your Autocomplete Google Address Pro license could not be verified.', 'autocomplete-google-address' );
		} elseif ( 'active' === $status['state'] && ! $status['lifetime'] && $status['expires'] && $status['expires'] - time() < 7 * DAY_IN_SECONDS ) {
			$class = 'notice-warning';
			/* translators: %s: expiry date */
			$text = sprintf( __( 'Your Autocomplete Google Address Pro license expires on %s. Renew to keep Pro features running.', 'autocomplete-google-address' ), date_i18n( get_option( 'date_format' ), $status['expires'] ) );
		}

		if ( '' === $text ) {
			return;
		}
		printf(
			'<div class="notice %1$s"><p>%2$s <a href="%3$s" target="_blank" rel="noopener">%4$s</a> · <a href="%5$s">%6$s</a></p></div>',
			esc_attr( $class ),
			esc_html( $text ),
			esc_url( $renew_url ),
			esc_html__( 'Renew license', 'autocomplete-google-address' ),
			esc_url( $license_url ),
			esc_html__( 'License page', 'autocomplete-google-address' )
		);
	}

	/**
	 * Cron callback.
	 */
	public static function cron_refresh() {
		self::maybe_refresh( true );
	}

	/**
	 * Schedule the background refresh while a key is stored; unschedule otherwise.
	 */
	public static function schedule_cron() {
		$scheduled = wp_next_scheduled( self::CRON_HOOK );
		if ( self::has_key() ) {
			// Sites updated from a version that scheduled it daily move to the new recurrence.
			if ( $scheduled && self::CRON_RECURRENCE !== wp_get_schedule( self::CRON_HOOK ) ) {
				wp_clear_scheduled_hook( self::CRON_HOOK );
				$scheduled = false;
			}
			if ( ! $scheduled ) {
				wp_schedule_event( time() + HOUR_IN_SECONDS, self::CRON_RECURRENCE, self::CRON_HOOK );
			}
		} elseif ( $scheduled ) {
			wp_clear_scheduled_hook( self::CRON_HOOK );
		}
	}

	/**
	 * Plugin deactivation.
	 */
	public static function unschedule_cron() {
		wp_clear_scheduled_hook( self::CRON_HOOK );
	}
}

/**
 * Whether premium features are unlocked: a valid mdnishath.com license OR a
 * paying Freemius account OR a Freemius trial (the trial requires a card, so it
 * should unlock Pro for its duration).
 *
 * @return bool
 */
function aga_is_pro() {
	if ( AGA_License::is_valid() ) {
		return true;
	}
	if ( function_exists( 'google_autocomplete' ) ) {
		$fs = google_autocomplete();
		if ( is_object( $fs ) && method_exists( $fs, 'is_paying' ) ) {
			return (bool) $fs->is_paying() || ( method_exists( $fs, 'is_trial' ) && (bool) $fs->is_trial() );
		}
	}
	return false;
}

/**
 * True when Pro comes from Freemius (paying or trial) and not from one of our keys.
 * Such sites don't need anything from our licensing: no License menu, no key prompts.
 *
 * @return bool
 */
function aga_pro_via_freemius() {
	if ( AGA_License::is_valid() || ! function_exists( 'google_autocomplete' ) ) {
		return false;
	}
	$fs = google_autocomplete();
	return is_object( $fs ) && method_exists( $fs, 'is_paying' )
		&& ( (bool) $fs->is_paying() || ( method_exists( $fs, 'is_trial' ) && (bool) $fs->is_trial() ) );
}

/**
 * Upgrade / buy link: our buy page when our license is in use or Freemius is
 * not loaded, otherwise the Freemius checkout.
 *
 * @return string
 */
function aga_checkout_url() {
	if ( aga_freemius_sales_enabled() && ! AGA_License::has_key() && function_exists( 'google_autocomplete' ) ) {
		$fs = google_autocomplete();
		if ( is_object( $fs ) && method_exists( $fs, 'checkout_url' ) ) {
			return $fs->checkout_url();
		}
	}
	return AGA_License::buy_url();
}

/**
 * Freemius checkout (USD) — empty when Freemius isn't loaded (e.g. a site using our key).
 *
 * @return string
 */
function aga_freemius_checkout_url() {
	if ( aga_freemius_sales_enabled() && function_exists( 'google_autocomplete' ) ) {
		$fs = google_autocomplete();
		if ( is_object( $fs ) && method_exists( $fs, 'checkout_url' ) ) {
			return (string) $fs->checkout_url();
		}
	}
	return '';
}

/**
 * Whether Pro is also sold through Freemius (see AGA_License::FREEMIUS_SALES).
 *
 * @return bool
 */
function aga_freemius_sales_enabled() {
	return (bool) apply_filters( 'aga_freemius_sales', AGA_License::FREEMIUS_SALES );
}

/**
 * The plugin's License page — where both ways to buy are shown side by side.
 *
 * @return string
 */
function aga_license_page_url() {
	return admin_url( 'edit.php?post_type=aga_form&page=aga-license' );
}

/**
 * How to buy Pro: mdnishath.com (any card worldwide, bKash, Nagad), plus the Freemius
 * checkout while Freemius sales are on. Sites that already hold one of our keys get a
 * single "Renew license" button instead.
 *
 * @param string $variant   'banner' (white buttons on the purple banner), 'hero' (large) or 'inline'.
 * @param bool   $show_note Show the "paste your key on the License page" hint.
 */
function aga_render_upgrade_options( $variant = 'inline', $show_note = true ) {
	$on_dark  = 'banner' === $variant;
	$btn_base = 'display:inline-flex;flex-direction:column;align-items:flex-start;gap:2px;min-width:210px;padding:10px 18px;border-radius:6px;text-decoration:none;font-weight:600;line-height:1.3;box-sizing:border-box;';
	$btn      = $on_dark
		? $btn_base . 'background:#fff;color:#4361ee;box-shadow:0 2px 8px rgba(0,0,0,0.15);'
		: $btn_base . 'background:#4361ee;color:#fff;border:1px solid #4361ee;';
	$sub      = $on_dark ? 'font-size:12px;font-weight:400;color:#5b6477;' : 'font-size:12px;font-weight:400;color:rgba(255,255,255,0.85);';
	$size     = 'hero' === $variant ? 'font-size:16px;padding:14px 22px;' : 'font-size:14px;';
	$note     = $on_dark ? 'margin:12px 0 0;font-size:12.5px;color:rgba(255,255,255,0.85);' : 'margin:10px 0 0;font-size:12.5px;color:#646970;';
	$link     = $on_dark ? 'color:#fff;text-decoration:underline;' : '';

	echo '<div class="aga-upgrade-options">';

	if ( AGA_License::has_key() ) {
		printf(
			'<a href="%1$s" target="_blank" rel="noopener" style="%2$s">%3$s<span style="%4$s">%5$s</span></a>',
			esc_url( AGA_License::renew_url() ),
			esc_attr( $btn . $size ),
			esc_html__( 'Renew your license', 'autocomplete-google-address' ),
			esc_attr( $sub ),
			esc_html__( 'Any card, bKash or Nagad · same key, time added', 'autocomplete-google-address' )
		);
		echo '</div>';
		return;
	}

	echo '<div style="display:flex;flex-wrap:wrap;gap:10px;">';
	printf(
		'<a href="%1$s" target="_blank" rel="noopener" style="%2$s">%3$s<span style="%4$s">%5$s</span></a>',
		esc_url( AGA_License::buy_url() ),
		esc_attr( $btn . $size ),
		esc_html__( 'Get Pro · Visa · Mastercard · Amex', 'autocomplete-google-address' ),
		esc_attr( $sub ),
		esc_html__( 'Any card, worldwide · also bKash / Nagad · instant key', 'autocomplete-google-address' )
	);
	$freemius = aga_freemius_checkout_url();
	if ( '' !== $freemius ) {
		printf(
			'<a href="%1$s" style="%2$s">%3$s<span style="%4$s">%5$s</span></a>',
			esc_url( $freemius ),
			esc_attr( $btn . $size ),
			esc_html__( 'Pay with Card · PayPal', 'autocomplete-google-address' ),
			esc_attr( $sub ),
			esc_html__( 'In USD via Freemius · activates automatically', 'autocomplete-google-address' )
		);
	}
	echo '</div>';

	if ( ! $show_note ) {
		echo '</div>';
		return;
	}
	printf(
		'<p style="%1$s">%2$s <a href="%3$s" style="%4$s">%5$s</a></p>',
		esc_attr( $note ),
		esc_html__( 'Bought on mdnishath.com? Paste your key on the', 'autocomplete-google-address' ),
		esc_url( aga_license_page_url() ),
		esc_attr( $link ),
		esc_html__( 'License page', 'autocomplete-google-address' )
	);
	echo '</div>';
}

