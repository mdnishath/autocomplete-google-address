<?php
/**
 * Provides the admin area view for the help page with diagnostics dashboard.
 *
 * @package    Autocomplete_Google_Address
 * @subpackage Autocomplete_Google_Address/admin/views
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="wrap aga-help-page">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

    <!-- Quick Links -->
    <div class="aga-quick-links">
        <a href="https://wordpress.org/plugins/autocomplete-google-address/#description" target="_blank" rel="noopener noreferrer" class="aga-quick-link">
            <span class="dashicons dashicons-media-document"></span>
            <?php esc_html_e( 'Documentation', 'autocomplete-google-address' ); ?>
        </a>
        <a href="https://wa.me/8801767591988?text=<?php echo rawurlencode( 'Hi, I need help with the Autocomplete Google Address plugin.' ); ?>" target="_blank" rel="noopener noreferrer" class="aga-quick-link">
            <span class="dashicons dashicons-format-chat"></span>
            <?php esc_html_e( 'WhatsApp Support', 'autocomplete-google-address' ); ?>
        </a>
        <a href="https://wordpress.org/support/plugin/autocomplete-google-address/reviews/#new-post" target="_blank" rel="noopener noreferrer" class="aga-quick-link">
            <span class="dashicons dashicons-star-filled"></span>
            <?php esc_html_e( 'Leave a Review', 'autocomplete-google-address' ); ?>
        </a>
    </div>

    <div class="aga-help-columns">
        <!-- Main content -->
        <div>
            <!-- Diagnostics -->
            <div class="aga-card">
                <div class="aga-card-header">
                    <h2>
                        <span class="dashicons dashicons-heart"></span>
                        <?php esc_html_e( 'Diagnostics', 'autocomplete-google-address' ); ?>
                    </h2>
                    <button type="button" id="aga-run-diagnostics" class="button button-primary aga-run-diagnostics-btn">
                        <span class="dashicons dashicons-update aga-icon-inline"></span>
                        <?php esc_html_e( 'Run Diagnostics', 'autocomplete-google-address' ); ?>
                    </button>
                </div>
                <div class="aga-card-body">
                    <div id="aga-health-results">
                        <p class="aga-text-center aga-text-muted"><?php esc_html_e( 'Click "Run Diagnostics" to check your plugin configuration.', 'autocomplete-google-address' ); ?></p>
                    </div>
                </div>
            </div>

            <!-- How to Use -->
            <div class="aga-card">
                <div class="aga-card-header">
                    <h2><?php esc_html_e( 'How to Use', 'autocomplete-google-address' ); ?></h2>
                </div>
                <div class="aga-card-body">
                    <h3><?php esc_html_e( '1. Configure your Settings', 'autocomplete-google-address' ); ?></h3>
                    <p><?php echo wp_kses_post( __( 'Go to <strong>Google Address &raquo; Settings</strong> and enter your Google Maps API key. This is required for the plugin to work.', 'autocomplete-google-address' ) ); ?></p>

                    <h3><?php esc_html_e( '2. Create a New Form Configuration', 'autocomplete-google-address' ); ?></h3>
                    <p><?php echo wp_kses_post( __( 'Go to <strong>Google Address &raquo; Add New</strong>. Give your configuration a name and choose a mode (Single Line or Smart Mapping).', 'autocomplete-google-address' ) ); ?></p>

                    <h3><?php esc_html_e( '3. Find Your Field Selectors', 'autocomplete-google-address' ); ?></h3>
                    <p><?php esc_html_e( 'Go to the page on your site with the form you want to add autocomplete to. Right-click on the form field and choose "Inspect" from the browser menu. Find the id or class of the input field.', 'autocomplete-google-address' ); ?></p>

                    <h3><?php esc_html_e( '4. Configure the Mapping', 'autocomplete-google-address' ); ?></h3>
                    <p><?php esc_html_e( 'Copy the selector and paste it into the "Main Autocomplete Field Selector" field in your configuration. If you are using Smart Mapping, do the same for all the other address fields.', 'autocomplete-google-address' ); ?></p>

                    <h3><?php esc_html_e( '5. Add the Shortcode to Your Page', 'autocomplete-google-address' ); ?></h3>
                    <p><?php esc_html_e( 'After saving your configuration, a shortcode will be generated (e.g., [aga_form id="123"]). Copy this shortcode and paste it into the content of the page where your form is located.', 'autocomplete-google-address' ); ?></p>

                    <h3><?php esc_html_e( 'For Developers', 'autocomplete-google-address' ); ?></h3>
                    <p><?php esc_html_e( 'You can also use the PHP function aga_render_form_config( $id ) in your theme templates to load a configuration programmatically.', 'autocomplete-google-address' ); ?></p>

                    <hr style="margin: 20px 0;" />

                    <h2><?php esc_html_e( 'Pro Features Guide', 'autocomplete-google-address' ); ?></h2>

                    <h3>🗺️ <?php esc_html_e( 'Map Picker', 'autocomplete-google-address' ); ?></h3>
                    <p><?php esc_html_e( 'Shows an interactive Google Map below the address field. Users can click anywhere on the map or drag the pin to select a precise location. The address fields auto-fill via reverse geocoding. The map auto-centers on the restricted country.', 'autocomplete-google-address' ); ?></p>

                    <h3>⚠️ <?php esc_html_e( 'PO Box Detection', 'autocomplete-google-address' ); ?></h3>
                    <p><?php esc_html_e( 'Automatically detects PO Box, APO, FPO, DPO, and military addresses and shows a warning banner. Useful for stores that cannot ship to PO Boxes.', 'autocomplete-google-address' ); ?></p>

                    <h3>🔔 <?php esc_html_e( 'Address Verification Webhook', 'autocomplete-google-address' ); ?></h3>
                    <p><?php echo wp_kses_post( __( 'Go to <strong>Settings &raquo; General</strong> and enter a webhook URL (Slack, Zapier, or custom). The plugin sends a notification whenever an invalid or suspicious address is submitted.', 'autocomplete-google-address' ) ); ?></p>

                    <h3>🏷️ <?php esc_html_e( 'White Label Mode', 'autocomplete-google-address' ); ?></h3>
                    <p><?php echo wp_kses_post( __( 'Go to <strong>Settings &raquo; General</strong> and enable White Label Mode. Set a custom menu name to rebrand the plugin in the WordPress admin. Ideal for agencies building client sites.', 'autocomplete-google-address' ) ); ?></p>

                    <h3>📊 <?php esc_html_e( 'Checkout Abandonment Tracking', 'autocomplete-google-address' ); ?></h3>
                    <p><?php echo wp_kses_post( __( 'Enable in <strong>Settings &raquo; General</strong>. Tracks when users start typing an address but leave without completing the form. Events appear in the Analytics dashboard.', 'autocomplete-google-address' ) ); ?></p>

                    <h3>🔌 <?php esc_html_e( 'REST API', 'autocomplete-google-address' ); ?></h3>
                    <p><?php esc_html_e( 'For headless and React storefronts:', 'autocomplete-google-address' ); ?></p>
                    <ul style="list-style: disc; padding-left: 20px;">
                        <li><code>GET /wp-json/aga/v1/config</code> — <?php esc_html_e( 'Returns all active form configs and API key', 'autocomplete-google-address' ); ?></li>
                        <li><code>POST /wp-json/aga/v1/validate</code> — <?php esc_html_e( 'Validates an address (requires authentication)', 'autocomplete-google-address' ); ?></li>
                    </ul>

                    <h3>🌙 <?php esc_html_e( 'Dark Mode & RTL', 'autocomplete-google-address' ); ?></h3>
                    <p><?php esc_html_e( 'The autocomplete dropdown automatically adapts to dark mode (prefers-color-scheme: dark) and supports right-to-left languages like Arabic and Hebrew.', 'autocomplete-google-address' ); ?></p>

                    <hr style="margin: 20px 0;" />

                    <h2><?php esc_html_e( 'Supported Form Plugins', 'autocomplete-google-address' ); ?></h2>
                    <ul style="list-style: disc; padding-left: 20px;">
                        <li><strong>WooCommerce</strong> — <?php esc_html_e( 'Zero-config, auto-detects classic & block checkout', 'autocomplete-google-address' ); ?></li>
                        <li><strong>Contact Form 7</strong></li>
                        <li><strong>WPForms</strong></li>
                        <li><strong>Gravity Forms</strong></li>
                        <li><strong>Elementor Pro Forms</strong> — <?php esc_html_e( 'Native widget + form field', 'autocomplete-google-address' ); ?></li>
                        <li><strong>Fluent Forms</strong> — <?php esc_html_e( 'New in v5.2.0', 'autocomplete-google-address' ); ?></li>
                        <li><strong>Ninja Forms</strong> — <?php esc_html_e( 'New in v5.2.0', 'autocomplete-google-address' ); ?></li>
                        <li><strong><?php esc_html_e( 'Any HTML form', 'autocomplete-google-address' ); ?></strong> — <?php esc_html_e( 'Via CSS selector mapping', 'autocomplete-google-address' ); ?></li>
                    </ul>

                    <hr style="margin: 20px 0;" />

                    <h2><?php esc_html_e( 'Required Google APIs', 'autocomplete-google-address' ); ?></h2>
                    <p><?php esc_html_e( 'Enable these in your Google Cloud Console:', 'autocomplete-google-address' ); ?></p>
                    <ul style="list-style: disc; padding-left: 20px;">
                        <li><strong>Places API (New)</strong> — <?php esc_html_e( 'Required for autocomplete', 'autocomplete-google-address' ); ?></li>
                        <li><strong>Maps JavaScript API</strong> — <?php esc_html_e( 'Required for Map Picker', 'autocomplete-google-address' ); ?></li>
                        <li><strong>Geocoding API</strong> — <?php esc_html_e( 'Required for Geolocation & Map Picker drag', 'autocomplete-google-address' ); ?></li>
                        <li><strong>Address Validation API</strong> — <?php esc_html_e( 'Required for Address Validation badges', 'autocomplete-google-address' ); ?></li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div>
            <div class="aga-card">
                <div class="aga-card-header">
                    <h3><?php esc_html_e( 'Plugin Resources', 'autocomplete-google-address' ); ?></h3>
                </div>
                <div class="aga-card-body">
                    <ul>
                        <li>
                            <a href="https://wordpress.org/plugins/autocomplete-google-address/#description" target="_blank" rel="noopener noreferrer">
                                <span class="dashicons dashicons-media-document"></span>
                                <?php esc_html_e( 'Documentation', 'autocomplete-google-address' ); ?>
                            </a>
                        </li>
                        <li>
                            <a href="https://wa.me/8801767591988?text=<?php echo rawurlencode( 'Hi, I need help with the Autocomplete Google Address plugin.' ); ?>" target="_blank" rel="noopener noreferrer">
                                <span class="dashicons dashicons-format-chat"></span>
                                <?php esc_html_e( 'WhatsApp Support', 'autocomplete-google-address' ); ?>
                            </a>
                        </li>
                        <li>
                            <a href="https://wordpress.org/support/plugin/autocomplete-google-address/reviews/#new-post" target="_blank" rel="noopener noreferrer">
                                <span class="dashicons dashicons-star-filled"></span>
                                <?php esc_html_e( 'Rate this Plugin', 'autocomplete-google-address' ); ?>
                            </a>
                        </li>
                        <li>
                            <a href="https://wordpress.org/support/plugin/autocomplete-google-address/" target="_blank" rel="noopener noreferrer">
                                <span class="dashicons dashicons-sos"></span>
                                <?php esc_html_e( 'Support Forum', 'autocomplete-google-address' ); ?>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>

            <div class="aga-card">
                <div class="aga-card-header">
                    <h3><?php esc_html_e( 'System Info', 'autocomplete-google-address' ); ?></h3>
                </div>
                <div class="aga-card-body">
                    <p><strong>PHP:</strong> <?php echo esc_html( phpversion() ); ?></p>
                    <p><strong>WordPress:</strong> <?php echo esc_html( get_bloginfo( 'version' ) ); ?></p>
                    <p><strong>Plugin:</strong> <?php echo esc_html( defined( 'AGA_VERSION' ) ? AGA_VERSION : '—' ); ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function($) {
    var $btn      = $('#aga-run-diagnostics');
    var $results  = $('#aga-health-results');

    $btn.on('click', function() {
        $btn.prop('disabled', true);
        $results.html(
            '<div class="aga-health-loading">' +
                '<span class="spinner"></span> <?php echo esc_js( __( 'Running diagnostics...', 'autocomplete-google-address' ) ); ?>' +
            '</div>'
        );

        $.post(aga_admin_data.ajax_url, {
            action: 'aga_health_check',
            nonce:  aga_admin_data.nonce
        }, function(response) {
            $btn.prop('disabled', false);

            if ( ! response.success || ! response.data ) {
                $results.html('<p style="color:#d63638;">Diagnostics failed. Please try again.</p>');
                return;
            }

            var html = '<table class="aga-health-table">';
            html += '<thead><tr><th style="width:40px;"><?php echo esc_js( __( 'Status', 'autocomplete-google-address' ) ); ?></th><th><?php echo esc_js( __( 'Check', 'autocomplete-google-address' ) ); ?></th><th><?php echo esc_js( __( 'Details', 'autocomplete-google-address' ) ); ?></th></tr></thead>';
            html += '<tbody>';

            $.each(response.data, function(i, item) {
                var icon = '';
                if ( item.status === 'success' ) {
                    icon = '<span class="aga-status-icon aga-status-success">&#10003;</span>';
                } else if ( item.status === 'error' ) {
                    icon = '<span class="aga-status-icon aga-status-error">&#10007;</span>';
                } else {
                    icon = '<span class="aga-status-icon aga-status-warning">!</span>';
                }

                html += '<tr>';
                html += '<td>' + icon + '</td>';
                html += '<td class="aga-health-name">' + $('<span>').text(item.name).html() + '</td>';
                html += '<td class="aga-health-details">' + $('<span>').text(item.details).html() + '</td>';
                html += '</tr>';
            });

            html += '</tbody></table>';
            $results.html(html);
        }).fail(function() {
            $btn.prop('disabled', false);
            $results.html('<p style="color:#d63638;">Diagnostics request failed. Please check your connection and try again.</p>');
        });
    });
})(jQuery);
</script>
