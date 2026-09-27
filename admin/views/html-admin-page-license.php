<?php
/**
 * Provides the admin area view for the License page (keys bought on mdnishath.com).
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/admin/views
 */

defined( 'ABSPATH' ) || exit;

$aga_status = AGA_License::get_status();
$aga_notice = get_transient( 'aga_license_notice_' . get_current_user_id() );
if ( $aga_notice ) {
	delete_transient( 'aga_license_notice_' . get_current_user_id() );
}

$aga_freemius_paying = aga_pro_via_freemius();
$aga_date_format     = get_option( 'date_format' );

// Mask the key: show the prefix and the last group only.
$aga_masked_key = '';
if ( '' !== $aga_status['key'] ) {
	$aga_groups     = explode( '-', $aga_status['key'] );
	$aga_last       = array_pop( $aga_groups );
	$aga_first      = array_shift( $aga_groups );
	$aga_masked_key = $aga_first . '-' . implode( '-', array_fill( 0, count( $aga_groups ), '*****' ) ) . ( $aga_groups ? '-' : '' ) . $aga_last;
}

$aga_state_labels = array(
	'active'  => array( __( 'Active', 'autocomplete-google-address' ), '#00a32a', 'dashicons-yes-alt' ),
	'expired' => array( __( 'Expired', 'autocomplete-google-address' ), '#d63638', 'dashicons-warning' ),
	'invalid' => array( __( 'Invalid', 'autocomplete-google-address' ), '#d63638', 'dashicons-dismiss' ),
	'none'    => array( __( 'No license key', 'autocomplete-google-address' ), '#646970', 'dashicons-lock' ),
);
$aga_state = isset( $aga_state_labels[ $aga_status['state'] ] ) ? $aga_state_labels[ $aga_status['state'] ] : $aga_state_labels['none'];

// Free trial: its own wording, and an upgrade (same key) instead of a renewal.
$aga_is_trial  = $aga_status['trial'];
$aga_days_left = ( 'active' === $aga_status['state'] && $aga_status['expires'] ) ? max( 0, (int) ceil( ( $aga_status['expires'] - time() ) / DAY_IN_SECONDS ) ) : 0;
if ( $aga_is_trial && 'active' === $aga_status['state'] ) {
	/* translators: %d: days left in the free trial */
	$aga_state = array( sprintf( _n( 'Free trial — %d day left', 'Free trial — %d days left', $aga_days_left, 'autocomplete-google-address' ), $aga_days_left ), '#dba617', 'dashicons-clock' );
} elseif ( $aga_is_trial && 'expired' === $aga_status['state'] ) {
	$aga_state = array( __( 'Free trial ended', 'autocomplete-google-address' ), '#d63638', 'dashicons-warning' );
}
$aga_can_trial = AGA_License::can_start_trial();
$aga_trial     = AGA_License::trial_info();
?>
<div class="wrap" id="aga-settings-page">
	<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

	<?php if ( is_array( $aga_notice ) && ! empty( $aga_notice['message'] ) ) : ?>
		<div class="notice notice-<?php echo 'success' === $aga_notice['type'] ? 'success' : 'error'; ?> is-dismissible">
			<p><?php echo esc_html( $aga_notice['message'] ); ?></p>
		</div>
	<?php endif; ?>

	<div class="aga-card">
		<div class="aga-card-header">
			<h2><?php esc_html_e( 'License status', 'autocomplete-google-address' ); ?></h2>
		</div>
		<div class="aga-card-body">
			<div class="aga-field-group">
				<p style="font-size:15px;margin:0;">
					<span class="dashicons <?php echo esc_attr( $aga_state[2] ); ?> aga-icon-inline" style="color:<?php echo esc_attr( $aga_state[1] ); ?>;"></span>
					<strong style="color:<?php echo esc_attr( $aga_state[1] ); ?>;"><?php echo esc_html( $aga_state[0] ); ?></strong>
				</p>
			</div>

			<?php if ( '' !== $aga_status['key'] ) : ?>
				<table class="widefat striped" style="max-width:640px;">
					<tbody>
						<tr>
							<th scope="row"><?php esc_html_e( 'License key', 'autocomplete-google-address' ); ?></th>
							<td><code><?php echo esc_html( $aga_masked_key ); ?></code></td>
						</tr>
						<?php if ( '' !== $aga_status['plan'] ) : ?>
							<tr>
								<th scope="row"><?php esc_html_e( 'Plan', 'autocomplete-google-address' ); ?></th>
								<td><?php echo esc_html( $aga_is_trial ? __( 'Free trial', 'autocomplete-google-address' ) : $aga_status['plan'] ); ?></td>
							</tr>
						<?php endif; ?>
						<tr>
							<th scope="row"><?php esc_html_e( 'Expires', 'autocomplete-google-address' ); ?></th>
							<td>
								<?php
								if ( $aga_status['lifetime'] ) {
									esc_html_e( 'Never (lifetime)', 'autocomplete-google-address' );
								} elseif ( $aga_status['expires'] ) {
									echo esc_html( wp_date( $aga_date_format, $aga_status['expires'] ) );
								} else {
									echo '&mdash;';
								}
								?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Sites used', 'autocomplete-google-address' ); ?></th>
							<td>
								<?php
								if ( null !== $aga_status['activations'] && null !== $aga_status['max'] ) {
									/* translators: 1: sites used, 2: sites allowed */
									echo esc_html( sprintf( __( '%1$d of %2$d', 'autocomplete-google-address' ), $aga_status['activations'], $aga_status['max'] ) );
								} elseif ( null !== $aga_status['activations'] ) {
									/* translators: %d: sites used */
									echo esc_html( sprintf( __( '%d (unlimited)', 'autocomplete-google-address' ), $aga_status['activations'] ) );
								} else {
									echo '&mdash;';
								}
								?>
							</td>
						</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'This site', 'autocomplete-google-address' ); ?></th>
							<td><code><?php echo esc_html( AGA_License::device_id() ); ?></code></td>
						</tr>
						<?php if ( $aga_status['validated'] ) : ?>
							<tr>
								<th scope="row"><?php esc_html_e( 'Last checked', 'autocomplete-google-address' ); ?></th>
								<td><?php echo esc_html( wp_date( $aga_date_format . ' ' . get_option( 'time_format' ), $aga_status['validated'] ) ); ?></td>
							</tr>
						<?php endif; ?>
					</tbody>
				</table>

				<?php if ( 'active' !== $aga_status['state'] && '' !== $aga_status['message'] ) : ?>
					<div class="notice notice-error inline" style="margin-top:16px;">
						<p><?php echo esc_html( $aga_status['message'] ); ?></p>
					</div>
				<?php endif; ?>

				<?php if ( $aga_is_trial ) : ?>
					<p style="margin-top:16px;">
						<a href="<?php echo esc_url( AGA_License::renew_url() ); ?>" class="button button-primary button-hero" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Upgrade to Pro', 'autocomplete-google-address' ); ?></a>
					</p>
					<p class="description">
						<?php esc_html_e( 'Pay with Visa, Mastercard or Amex from any country (or bKash / Nagad). Your trial key becomes a full license, so nothing on this site needs to change.', 'autocomplete-google-address' ); ?>
					</p>
				<?php elseif ( 'expired' === $aga_status['state'] ) : ?>
					<p>
						<a href="<?php echo esc_url( AGA_License::renew_url() ); ?>" class="button button-primary" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Renew license', 'autocomplete-google-address' ); ?></a>
					</p>
				<?php endif; ?>

				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin-top:16px;">
					<input type="hidden" name="action" value="aga_license_deactivate" />
					<?php wp_nonce_field( 'aga_license_deactivate' ); ?>
					<button type="submit" class="button button-secondary" onclick="return confirm('<?php echo esc_js( __( 'Deactivate the license on this site? Pro features will be turned off here and the site slot is freed.', 'autocomplete-google-address' ) ); ?>');">
						<?php esc_html_e( 'Deactivate on this site', 'autocomplete-google-address' ); ?>
					</button>
				</form>
			<?php elseif ( $aga_freemius_paying ) : ?>
				<p><?php esc_html_e( 'Pro is active on this site through your Freemius account. You do not need a license key.', 'autocomplete-google-address' ); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<?php if ( $aga_can_trial ) : ?>
		<div class="aga-card" id="aga-trial">
			<div class="aga-card-header">
				<?php /* translators: %d: trial length in days */ ?>
				<h2><?php echo esc_html( sprintf( __( 'Try Pro free for %d days', 'autocomplete-google-address' ), $aga_trial['days'] ) ); ?></h2>
			</div>
			<div class="aga-card-body">
				<p><?php esc_html_e( 'Every Pro feature, on this site, with no card and no payment details. When the trial ends, Pro switches off unless you upgrade — the free features keep working.', 'autocomplete-google-address' ); ?></p>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="aga_license_trial" />
					<?php wp_nonce_field( 'aga_license_trial' ); ?>
					<div class="aga-field-group">
						<label for="aga_trial_email"><strong><?php esc_html_e( 'Your email', 'autocomplete-google-address' ); ?></strong></label>
						<div class="aga-api-key-row">
							<input type="email" id="aga_trial_email" name="aga_trial_email" value="<?php echo esc_attr( (string) get_option( 'admin_email' ) ); ?>" class="regular-text" required />
							<?php /* translators: %d: trial length in days */ ?>
							<button type="submit" class="button button-primary"><?php echo esc_html( sprintf( __( 'Start %d-day free trial', 'autocomplete-google-address' ), $aga_trial['days'] ) ); ?></button>
						</div>
						<p class="description">
							<?php esc_html_e( 'Your trial key and a reminder before the trial ends are sent to this email. This email and your site address are sent to mdnishath.com to start the trial. One free trial per website.', 'autocomplete-google-address' ); ?>
						</p>
					</div>
				</form>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( 'active' !== $aga_status['state'] && ! $aga_freemius_paying ) : ?>
		<div class="aga-card">
			<div class="aga-card-header">
				<h2><?php echo esc_html( aga_freemius_sales_enabled() ? __( 'Get Pro — choose how to pay', 'autocomplete-google-address' ) : __( 'Get Pro', 'autocomplete-google-address' ) ); ?></h2>
			</div>
			<div class="aga-card-body">
				<?php aga_render_upgrade_options( 'inline', false ); ?>
				<p class="description" style="margin-top:12px;">
					<?php
					if ( aga_freemius_sales_enabled() ) {
						esc_html_e( 'Visa, Mastercard or Amex from any country, bKash or Nagad on mdnishath.com: you get a license key by email instantly — paste it below. Card / PayPal (Freemius): Pro switches on automatically after checkout.', 'autocomplete-google-address' );
					} else {
						esc_html_e( 'Pay with Visa, Mastercard or Amex from any country (or bKash / Nagad). Your license key arrives by email instantly — paste it below.', 'autocomplete-google-address' );
					}
					?>
				</p>
			</div>
		</div>
	<?php endif; ?>

	<?php if ( 'active' !== $aga_status['state'] ) : ?>
		<div class="aga-card">
			<div class="aga-card-header">
				<h2><?php echo esc_html( '' !== $aga_status['key'] ? __( 'Activate a license key again', 'autocomplete-google-address' ) : __( 'Activate a license key', 'autocomplete-google-address' ) ); ?></h2>
			</div>
			<div class="aga-card-body">
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<input type="hidden" name="action" value="aga_license_activate" />
					<?php wp_nonce_field( 'aga_license_activate' ); ?>
					<div class="aga-field-group">
						<label for="aga_license_key"><strong><?php esc_html_e( 'License key', 'autocomplete-google-address' ); ?></strong></label>
						<div class="aga-api-key-row">
							<input type="text" id="aga_license_key" name="aga_license_key" value="<?php echo esc_attr( $aga_status['key'] ); ?>" class="regular-text" placeholder="AGA-XXXXX-XXXXX-XXXXX-XXXXX" autocomplete="off" spellcheck="false" required />
							<button type="submit" class="button button-primary"><?php esc_html_e( 'Activate', 'autocomplete-google-address' ); ?></button>
						</div>
						<p class="description"><?php esc_html_e( 'Paste the key from your purchase email. One activation is used per website.', 'autocomplete-google-address' ); ?></p>
					</div>
				</form>

				<?php if ( function_exists( 'google_autocomplete' ) ) : ?>
					<p class="description">
						<?php
						printf(
							/* translators: %s: link to the Plugins screen */
							esc_html__( 'Have a Freemius license key instead? Activate it from %s → Autocomplete Google Address → Activate License.', 'autocomplete-google-address' ),
							'<a href="' . esc_url( admin_url( 'plugins.php' ) ) . '">' . esc_html__( 'Plugins', 'autocomplete-google-address' ) . '</a>'
						);
						?>
					</p>
				<?php endif; ?>
			</div>
		</div>
	<?php endif; ?>
</div>
