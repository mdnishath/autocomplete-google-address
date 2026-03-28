<?php
defined( 'ABSPATH' ) || exit;

$is_paying  = function_exists( 'google_autocomplete' ) && google_autocomplete()->is_paying();
$woo_active = class_exists( 'WooCommerce' );
$languages  = function_exists( 'aga_get_languages' ) ? aga_get_languages() : array();
$countries  = function_exists( 'aga_get_countries' ) ? aga_get_countries() : array();
?>
<div class="aga-wizard-wrap">
    <div class="aga-wizard-card">

        <!-- Step indicators (dynamic: 3 dots default, 4 if WooCommerce selected) -->
        <div class="aga-wizard-steps" id="aga-wizard-steps">
            <div class="aga-wizard-step-indicator active" data-step="1"></div>
            <div class="aga-wizard-step-indicator" data-step="2"></div>
            <div class="aga-wizard-step-indicator aga-wizard-step-3-dot" style="display:none;" data-step="3"></div>
            <div class="aga-wizard-step-indicator" data-step="done"></div>
        </div>

        <!-- Step 1: API Key -->
        <div class="aga-wizard-step active" id="aga-wizard-step-1">
            <h1 class="aga-wizard-heading"><?php esc_html_e( 'Welcome to Autocomplete Google Address', 'autocomplete-google-address' ); ?></h1>
            <p class="aga-wizard-subheading"><?php esc_html_e( "Let's get you set up in under a minute.", 'autocomplete-google-address' ); ?></p>

            <label class="aga-wizard-label" for="aga-wizard-api-key"><?php esc_html_e( 'Google Maps API Key', 'autocomplete-google-address' ); ?></label>
            <input type="text" id="aga-wizard-api-key" class="aga-wizard-input" placeholder="AIza..." autocomplete="off" />
            <div class="aga-wizard-error" id="aga-wizard-api-error"><?php esc_html_e( 'Please enter your API key to continue.', 'autocomplete-google-address' ); ?></div>
            <p class="aga-wizard-helper">
                <?php
                printf(
                    /* translators: %s: link to Google Cloud Console */
                    esc_html__( "Don't have an API key? %s", 'autocomplete-google-address' ),
                    '<a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Get one from Google Cloud Console', 'autocomplete-google-address' ) . '</a>'
                );
                ?>
            </p>

            <div class="aga-wizard-btn-wrap">
                <button type="button" class="aga-wizard-btn" id="aga-wizard-next-1"><?php esc_html_e( 'Next', 'autocomplete-google-address' ); ?></button>
            </div>
        </div>

        <!-- Step 2: Form Type -->
        <div class="aga-wizard-step" id="aga-wizard-step-2">
            <h1 class="aga-wizard-heading"><?php esc_html_e( 'What form are you using?', 'autocomplete-google-address' ); ?></h1>
            <p class="aga-wizard-subheading"><?php esc_html_e( 'Select the form plugin you want to integrate with.', 'autocomplete-google-address' ); ?></p>

            <div class="aga-wizard-radio-cards">
                <!-- WooCommerce -->
                <?php
                $woo_autoselect = $woo_active && $is_paying;
                $woo_disabled   = ! $is_paying ? 'disabled' : '';
                $woo_classes    = ! $is_paying ? 'disabled' : '';
                if ( $woo_autoselect ) {
                    $woo_classes .= ' selected';
                }
                ?>
                <label class="aga-wizard-radio-card <?php echo esc_attr( $woo_classes ); ?>" data-value="woocommerce">
                    <input type="radio" name="aga_wizard_form_type" value="woocommerce" <?php echo $woo_disabled; ?> <?php checked( $woo_autoselect ); ?> />
                    <div class="aga-wizard-radio-card-title">
                        <span class="aga-wizard-radio-card-icon dashicons dashicons-cart"></span>
                        <?php esc_html_e( 'WooCommerce', 'autocomplete-google-address' ); ?>
                        <?php if ( $woo_active ) : ?>
                            <span class="aga-wizard-detected-badge"><?php esc_html_e( 'Detected', 'autocomplete-google-address' ); ?></span>
                        <?php endif; ?>
                        <?php if ( ! $is_paying ) : ?>
                            <span class="aga-wizard-pro-badge"><?php esc_html_e( 'Pro', 'autocomplete-google-address' ); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="aga-wizard-radio-card-desc"><?php esc_html_e( 'Auto-detect checkout address fields', 'autocomplete-google-address' ); ?></div>
                </label>

                <!-- Contact Form 7 -->
                <label class="aga-wizard-radio-card <?php echo ! $is_paying ? 'disabled' : ''; ?>" data-value="cf7">
                    <input type="radio" name="aga_wizard_form_type" value="cf7" <?php echo ! $is_paying ? 'disabled' : ''; ?> />
                    <div class="aga-wizard-radio-card-title">
                        <span class="aga-wizard-radio-card-icon dashicons dashicons-email"></span>
                        <?php esc_html_e( 'Contact Form 7', 'autocomplete-google-address' ); ?>
                        <?php if ( ! $is_paying ) : ?>
                            <span class="aga-wizard-pro-badge"><?php esc_html_e( 'Pro', 'autocomplete-google-address' ); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="aga-wizard-radio-card-desc"><?php esc_html_e( 'Popular free form plugin', 'autocomplete-google-address' ); ?></div>
                </label>

                <!-- WPForms -->
                <label class="aga-wizard-radio-card <?php echo ! $is_paying ? 'disabled' : ''; ?>" data-value="wpforms">
                    <input type="radio" name="aga_wizard_form_type" value="wpforms" <?php echo ! $is_paying ? 'disabled' : ''; ?> />
                    <div class="aga-wizard-radio-card-title">
                        <span class="aga-wizard-radio-card-icon dashicons dashicons-feedback"></span>
                        <?php esc_html_e( 'WPForms', 'autocomplete-google-address' ); ?>
                        <?php if ( ! $is_paying ) : ?>
                            <span class="aga-wizard-pro-badge"><?php esc_html_e( 'Pro', 'autocomplete-google-address' ); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="aga-wizard-radio-card-desc"><?php esc_html_e( 'Drag and drop form builder', 'autocomplete-google-address' ); ?></div>
                </label>

                <!-- Gravity Forms -->
                <label class="aga-wizard-radio-card <?php echo ! $is_paying ? 'disabled' : ''; ?>" data-value="gravity">
                    <input type="radio" name="aga_wizard_form_type" value="gravity" <?php echo ! $is_paying ? 'disabled' : ''; ?> />
                    <div class="aga-wizard-radio-card-title">
                        <span class="aga-wizard-radio-card-icon dashicons dashicons-list-view"></span>
                        <?php esc_html_e( 'Gravity Forms', 'autocomplete-google-address' ); ?>
                        <?php if ( ! $is_paying ) : ?>
                            <span class="aga-wizard-pro-badge"><?php esc_html_e( 'Pro', 'autocomplete-google-address' ); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="aga-wizard-radio-card-desc"><?php esc_html_e( 'Advanced form builder', 'autocomplete-google-address' ); ?></div>
                </label>

                <!-- Elementor Forms -->
                <label class="aga-wizard-radio-card <?php echo ! $is_paying ? 'disabled' : ''; ?>" data-value="elementor">
                    <input type="radio" name="aga_wizard_form_type" value="elementor" <?php echo ! $is_paying ? 'disabled' : ''; ?> />
                    <div class="aga-wizard-radio-card-title">
                        <span class="aga-wizard-radio-card-icon dashicons dashicons-admin-page"></span>
                        <?php esc_html_e( 'Elementor Forms', 'autocomplete-google-address' ); ?>
                        <?php if ( ! $is_paying ) : ?>
                            <span class="aga-wizard-pro-badge"><?php esc_html_e( 'Pro', 'autocomplete-google-address' ); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="aga-wizard-radio-card-desc"><?php esc_html_e( 'Elementor page builder forms', 'autocomplete-google-address' ); ?></div>
                </label>

                <!-- Fluent Forms -->
                <label class="aga-wizard-radio-card <?php echo ! $is_paying ? 'disabled' : ''; ?>" data-value="fluent_forms">
                    <input type="radio" name="aga_wizard_form_type" value="fluent_forms" <?php echo ! $is_paying ? 'disabled' : ''; ?> />
                    <div class="aga-wizard-radio-card-title">
                        <span class="aga-wizard-radio-card-icon dashicons dashicons-editor-table"></span>
                        <?php esc_html_e( 'Fluent Forms', 'autocomplete-google-address' ); ?>
                        <?php if ( ! $is_paying ) : ?>
                            <span class="aga-wizard-pro-badge"><?php esc_html_e( 'Pro', 'autocomplete-google-address' ); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="aga-wizard-radio-card-desc"><?php esc_html_e( 'Fast & lightweight form builder', 'autocomplete-google-address' ); ?></div>
                </label>

                <!-- Ninja Forms -->
                <label class="aga-wizard-radio-card <?php echo ! $is_paying ? 'disabled' : ''; ?>" data-value="ninja_forms">
                    <input type="radio" name="aga_wizard_form_type" value="ninja_forms" <?php echo ! $is_paying ? 'disabled' : ''; ?> />
                    <div class="aga-wizard-radio-card-title">
                        <span class="aga-wizard-radio-card-icon dashicons dashicons-superhero"></span>
                        <?php esc_html_e( 'Ninja Forms', 'autocomplete-google-address' ); ?>
                        <?php if ( ! $is_paying ) : ?>
                            <span class="aga-wizard-pro-badge"><?php esc_html_e( 'Pro', 'autocomplete-google-address' ); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="aga-wizard-radio-card-desc"><?php esc_html_e( 'Flexible drag and drop forms', 'autocomplete-google-address' ); ?></div>
                </label>

                <!-- Manual Setup -->
                <label class="aga-wizard-radio-card" data-value="manual">
                    <input type="radio" name="aga_wizard_form_type" value="manual" />
                    <div class="aga-wizard-radio-card-title">
                        <span class="aga-wizard-radio-card-icon dashicons dashicons-admin-generic"></span>
                        <?php esc_html_e( 'Manual Setup', 'autocomplete-google-address' ); ?>
                    </div>
                    <div class="aga-wizard-radio-card-desc"><?php esc_html_e( "I'll configure CSS selectors myself", 'autocomplete-google-address' ); ?></div>
                </label>
            </div>

            <div class="aga-wizard-error" id="aga-wizard-form-error"><?php esc_html_e( 'Please select a form type to continue.', 'autocomplete-google-address' ); ?></div>

            <div class="aga-wizard-btn-wrap">
                <button type="button" class="aga-wizard-btn" id="aga-wizard-next-2"><?php esc_html_e( 'Continue', 'autocomplete-google-address' ); ?></button>
            </div>
        </div>

        <!-- Step 3: WooCommerce Features (Pro only) -->
        <div class="aga-wizard-step" id="aga-wizard-step-3">
            <h1 class="aga-wizard-heading"><?php esc_html_e( 'Configure Checkout Features', 'autocomplete-google-address' ); ?></h1>
            <p class="aga-wizard-subheading"><?php esc_html_e( 'Enable the features you want on your WooCommerce checkout.', 'autocomplete-google-address' ); ?></p>

            <!-- Feature Toggles -->
            <div class="aga-wizard-features">
                <div class="aga-wizard-feature-row">
                    <div class="aga-wizard-feature-info">
                        <span class="dashicons dashicons-yes-alt aga-wizard-feature-icon"></span>
                        <div>
                            <strong><?php esc_html_e( 'Address Validation', 'autocomplete-google-address' ); ?></strong>
                            <span class="aga-wizard-feature-desc"><?php esc_html_e( 'Verify addresses with a visual badge (Verified / Partial match / Not verified).', 'autocomplete-google-address' ); ?></span>
                            <span class="aga-wizard-api-hint">
                                <span class="dashicons dashicons-info-outline"></span>
                                <?php
                                printf(
                                    esc_html__( 'Requires %s enabled in Google Cloud Console', 'autocomplete-google-address' ),
                                    '<a href="https://console.cloud.google.com/apis/library/addressvalidation.googleapis.com" target="_blank" rel="noopener noreferrer">Address Validation API</a>'
                                );
                                ?>
                            </span>
                        </div>
                    </div>
                    <label class="aga-switch">
                        <input type="checkbox" id="aga-wiz-address-validation" />
                        <span class="aga-slider"></span>
                    </label>
                </div>

                <div class="aga-wizard-feature-row">
                    <div class="aga-wizard-feature-info">
                        <span class="dashicons dashicons-location aga-wizard-feature-icon"></span>
                        <div>
                            <strong><?php esc_html_e( 'Geolocation Auto-detect', 'autocomplete-google-address' ); ?></strong>
                            <span class="aga-wizard-feature-desc"><?php esc_html_e( 'Add a GPS button that auto-fills the address based on the user\'s current location.', 'autocomplete-google-address' ); ?></span>
                            <span class="aga-wizard-api-hint">
                                <span class="dashicons dashicons-info-outline"></span>
                                <?php
                                printf(
                                    esc_html__( 'Requires %s enabled in Google Cloud Console', 'autocomplete-google-address' ),
                                    '<a href="https://console.cloud.google.com/apis/library/geocoding-backend.googleapis.com" target="_blank" rel="noopener noreferrer">Geocoding API</a>'
                                );
                                ?>
                            </span>
                        </div>
                    </div>
                    <label class="aga-switch">
                        <input type="checkbox" id="aga-wiz-geolocation" />
                        <span class="aga-slider"></span>
                    </label>
                </div>

                <div class="aga-wizard-feature-row">
                    <div class="aga-wizard-feature-info">
                        <span class="dashicons dashicons-book aga-wizard-feature-icon"></span>
                        <div>
                            <strong><?php esc_html_e( 'Saved Addresses', 'autocomplete-google-address' ); ?></strong>
                            <span class="aga-wizard-feature-desc"><?php esc_html_e( 'Show recently used addresses on focus for logged-in users. Speeds up repeat checkouts.', 'autocomplete-google-address' ); ?></span>
                        </div>
                    </div>
                    <label class="aga-switch">
                        <input type="checkbox" id="aga-wiz-saved-addresses" />
                        <span class="aga-slider"></span>
                    </label>
                </div>

                <div class="aga-wizard-feature-row">
                    <div class="aga-wizard-feature-info">
                        <span class="dashicons dashicons-admin-site-alt3 aga-wizard-feature-icon"></span>
                        <div>
                            <strong><?php esc_html_e( 'Map Picker', 'autocomplete-google-address' ); ?></strong>
                            <span class="aga-wizard-feature-desc"><?php esc_html_e( 'Let users pick an address by clicking or dragging a pin on the map. Works alongside autocomplete.', 'autocomplete-google-address' ); ?></span>
                            <span class="aga-wizard-api-hint">
                                <span class="dashicons dashicons-info-outline"></span>
                                <?php
                                printf(
                                    esc_html__( 'Requires %s and %s enabled in Google Cloud Console', 'autocomplete-google-address' ),
                                    '<a href="https://console.cloud.google.com/apis/library/maps-backend.googleapis.com" target="_blank" rel="noopener noreferrer">Maps JavaScript API</a>',
                                    '<a href="https://console.cloud.google.com/apis/library/geocoding-backend.googleapis.com" target="_blank" rel="noopener noreferrer">Geocoding API</a>'
                                );
                                ?>
                            </span>
                        </div>
                    </div>
                    <label class="aga-switch">
                        <input type="checkbox" id="aga-wiz-map-picker" />
                        <span class="aga-slider"></span>
                    </label>
                </div>
            </div>

            <!-- Advanced Settings -->
            <div class="aga-wizard-advanced">
                <h3 class="aga-wizard-section-title"><?php esc_html_e( 'Advanced Settings', 'autocomplete-google-address' ); ?></h3>
                <div class="aga-wizard-advanced-grid">
                    <div class="aga-wizard-field-group">
                        <label for="aga-wiz-country-restriction"><?php esc_html_e( 'Country Restriction', 'autocomplete-google-address' ); ?></label>
                        <select id="aga-wiz-country-restriction" class="aga-select2-countries" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Select countries (max 5)...', 'autocomplete-google-address' ); ?>" style="width:100%;">
                            <?php foreach ( $countries as $code => $name ) : ?>
                                <option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $name . ' (' . $code . ')' ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="aga-wizard-field-group">
                        <label for="aga-wiz-place-types"><?php esc_html_e( 'Place Types', 'autocomplete-google-address' ); ?></label>
                        <select id="aga-wiz-place-types" class="aga-wizard-input">
                            <option value=""><?php esc_html_e( 'All Types', 'autocomplete-google-address' ); ?></option>
                            <option value="address"><?php esc_html_e( 'Addresses only', 'autocomplete-google-address' ); ?></option>
                            <option value="geocode"><?php esc_html_e( 'Geocoded', 'autocomplete-google-address' ); ?></option>
                            <option value="establishment"><?php esc_html_e( 'Businesses', 'autocomplete-google-address' ); ?></option>
                            <option value="(regions)"><?php esc_html_e( 'Regions', 'autocomplete-google-address' ); ?></option>
                            <option value="(cities)"><?php esc_html_e( 'Cities', 'autocomplete-google-address' ); ?></option>
                        </select>
                    </div>
                    <div class="aga-wizard-field-group">
                        <label for="aga-wiz-language"><?php esc_html_e( 'Language', 'autocomplete-google-address' ); ?></label>
                        <select id="aga-wiz-language" class="aga-wizard-input">
                            <option value=""><?php esc_html_e( 'Default (site language)', 'autocomplete-google-address' ); ?></option>
                            <?php foreach ( $languages as $code => $name ) : ?>
                                <option value="<?php echo esc_attr( $code ); ?>"><?php echo esc_html( $name . ' (' . $code . ')' ); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Live Preview -->
            <div class="aga-wizard-preview">
                <h3 class="aga-wizard-section-title"><?php esc_html_e( 'Live Preview', 'autocomplete-google-address' ); ?></h3>
                <div id="aga-wiz-preview-container" style="position:relative;">
                    <input type="text" id="aga-wiz-preview-input" class="aga-wizard-input" placeholder="<?php esc_attr_e( 'Start typing an address to preview...', 'autocomplete-google-address' ); ?>" autocomplete="off" />
                </div>
                <div id="aga-wiz-preview-result" style="display:none; margin-top: 12px;">
                    <table class="widefat striped" id="aga-wiz-preview-result-table">
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <div class="aga-wizard-btn-wrap">
                <button type="button" class="aga-wizard-btn" id="aga-wizard-next-3"><?php esc_html_e( 'Continue', 'autocomplete-google-address' ); ?></button>
            </div>
        </div>

        <!-- Step 4: Done -->
        <div class="aga-wizard-step" id="aga-wizard-step-done">
            <div class="aga-wizard-success-icon">
                <svg viewBox="0 0 64 64" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="32" cy="32" r="32" fill="#00a32a"/>
                    <path d="M20 33l8 8 16-16" stroke="#fff" stroke-width="4" stroke-linecap="round" stroke-linejoin="round"/>
                </svg>
            </div>
            <h1 class="aga-wizard-heading"><?php esc_html_e( "You're all set!", 'autocomplete-google-address' ); ?></h1>
            <p class="aga-wizard-success-desc" id="aga-wizard-done-desc"></p>

            <div class="aga-wizard-btn-wrap">
                <a href="<?php echo esc_url( admin_url( 'edit.php?post_type=aga_form' ) ); ?>" class="aga-wizard-btn" id="aga-wizard-finish"><?php esc_html_e( 'Go to Dashboard', 'autocomplete-google-address' ); ?></a>
            </div>
        </div>

    </div>
</div>

<script>
(function() {
    'use strict';

    var steps      = document.querySelectorAll('.aga-wizard-step');
    var indicators = document.querySelectorAll('.aga-wizard-step-indicator');
    var step3Dot   = document.querySelector('.aga-wizard-step-3-dot');
    var currentStep = 1;
    var hasStep3    = false; // true when WooCommerce is selected

    function updateIndicators() {
        // Show/hide the step 3 dot based on whether we have the features step
        step3Dot.style.display = hasStep3 ? '' : 'none';
    }

    function goToStep(stepId) {
        steps.forEach(function(el) { el.classList.remove('active'); });
        document.getElementById('aga-wizard-step-' + stepId).classList.add('active');

        // Map stepId to indicator position
        var stepOrder = hasStep3 ? ['1', '2', '3', 'done'] : ['1', '2', 'done'];
        var stepIndex = stepOrder.indexOf(String(stepId));

        indicators.forEach(function(dot) {
            if (dot.style.display === 'none') return;
            dot.classList.remove('active', 'completed');
        });

        var visibleDots = [];
        indicators.forEach(function(dot) {
            if (dot.style.display !== 'none') visibleDots.push(dot);
        });

        visibleDots.forEach(function(dot, i) {
            dot.classList.remove('active', 'completed');
            if (i < stepIndex) {
                dot.classList.add('completed');
            } else if (i === stepIndex) {
                dot.classList.add('active');
            }
        });

        currentStep = stepId;
    }

    // Step 1 - Next
    document.getElementById('aga-wizard-next-1').addEventListener('click', function() {
        var apiKey = document.getElementById('aga-wizard-api-key').value.trim();
        var error  = document.getElementById('aga-wizard-api-error');

        if (!apiKey) {
            error.style.display = 'block';
            return;
        }

        error.style.display = 'none';
        goToStep(2);
    });

    // Radio card selection
    var cards = document.querySelectorAll('.aga-wizard-radio-card');
    cards.forEach(function(card) {
        card.addEventListener('click', function(e) {
            if (card.classList.contains('disabled')) {
                e.preventDefault();
                return;
            }
            cards.forEach(function(c) { c.classList.remove('selected'); });
            card.classList.add('selected');
            card.querySelector('input[type="radio"]').checked = true;

            // Toggle step 3 dot visibility
            var val = card.querySelector('input[type="radio"]').value;
            hasStep3 = (val === 'woocommerce');
            updateIndicators();
        });
    });

    // Initialize step 3 dot if WooCommerce is pre-selected
    var preSelected = document.querySelector('input[name="aga_wizard_form_type"]:checked');
    if (preSelected && preSelected.value === 'woocommerce') {
        hasStep3 = true;
        updateIndicators();
    }

    // Step 2 - Continue
    document.getElementById('aga-wizard-next-2').addEventListener('click', function() {
        var selected = document.querySelector('input[name="aga_wizard_form_type"]:checked');
        var error    = document.getElementById('aga-wizard-form-error');

        if (!selected) {
            error.style.display = 'block';
            return;
        }

        error.style.display = 'none';
        var formType = selected.value;

        if (formType === 'woocommerce') {
            // Go to features step
            goToStep(3);
            initPreview();
            return;
        }

        // Non-WooCommerce: save directly
        saveWizard(formType, {});
    });

    // Step 3 - Continue (WooCommerce features)
    document.getElementById('aga-wizard-next-3').addEventListener('click', function() {
        var features = {
            address_validation: document.getElementById('aga-wiz-address-validation').checked ? '1' : '0',
            geolocation:        document.getElementById('aga-wiz-geolocation').checked ? '1' : '0',
            saved_addresses:    document.getElementById('aga-wiz-saved-addresses').checked ? '1' : '0',
            map_picker:         document.getElementById('aga-wiz-map-picker').checked ? '1' : '0',
            country_restriction: Array.from(document.getElementById('aga-wiz-country-restriction').selectedOptions).map(function(o) { return o.value; }).join(','),
            place_types:        document.getElementById('aga-wiz-place-types').value,
            language:           document.getElementById('aga-wiz-language').value,
        };

        saveWizard('woocommerce', features);
    });

    function saveWizard(formType, features) {
        var btn = formType === 'woocommerce'
            ? document.getElementById('aga-wizard-next-3')
            : document.getElementById('aga-wizard-next-2');

        var apiKey = document.getElementById('aga-wizard-api-key').value.trim();

        btn.disabled = true;
        btn.innerHTML = '<span class="aga-wizard-spinner"></span> Saving...';

        var data = new FormData();
        data.append('action', 'aga_save_wizard');
        data.append('nonce', aga_admin_data.nonce);
        data.append('api_key', apiKey);
        data.append('form_type', formType);

        // Append feature params
        for (var key in features) {
            if (features.hasOwnProperty(key)) {
                data.append(key, features[key]);
            }
        }

        fetch(aga_admin_data.ajax_url, {
            method: 'POST',
            credentials: 'same-origin',
            body: data
        })
        .then(function(response) { return response.json(); })
        .then(function(result) {
            btn.disabled = false;
            btn.textContent = '<?php echo esc_js( __( 'Continue', 'autocomplete-google-address' ) ); ?>';

            if (result.success) {
                var desc   = '';
                var finishBtn = document.getElementById('aga-wizard-finish');

                switch (formType) {
                    case 'woocommerce':
                        desc = '<?php echo esc_js( __( 'WooCommerce checkout is configured! Your billing and shipping address fields will autocomplete automatically with all selected features.', 'autocomplete-google-address' ) ); ?>';
                        break;
                    case 'cf7':
                        desc = '<?php echo esc_js( __( 'A Contact Form 7 configuration has been created and activated globally.', 'autocomplete-google-address' ) ); ?>';
                        break;
                    case 'wpforms':
                        desc = '<?php echo esc_js( __( 'A WPForms configuration has been created and activated globally.', 'autocomplete-google-address' ) ); ?>';
                        break;
                    case 'gravity':
                        desc = '<?php echo esc_js( __( 'A Gravity Forms configuration has been created and activated globally.', 'autocomplete-google-address' ) ); ?>';
                        break;
                    case 'elementor':
                        desc = '<?php echo esc_js( __( 'An Elementor Forms configuration has been created and activated globally.', 'autocomplete-google-address' ) ); ?>';
                        break;
                    case 'fluent_forms':
                        desc = '<?php echo esc_js( __( 'A Fluent Forms configuration has been created and activated globally.', 'autocomplete-google-address' ) ); ?>';
                        break;
                    case 'ninja_forms':
                        desc = '<?php echo esc_js( __( 'A Ninja Forms configuration has been created and activated globally.', 'autocomplete-google-address' ) ); ?>';
                        break;
                    case 'manual':
                        desc = '<?php echo esc_js( __( 'Your API key has been saved. You can now create a form configuration with your own CSS selectors.', 'autocomplete-google-address' ) ); ?>';
                        break;
                }

                document.getElementById('aga-wizard-done-desc').textContent = desc;

                if (result.data && result.data.redirect) {
                    finishBtn.href = result.data.redirect;
                }

                goToStep('done');
            } else {
                var msg = (result.data && result.data.message) ? result.data.message : '<?php echo esc_js( __( 'Something went wrong. Please try again.', 'autocomplete-google-address' ) ); ?>';
                alert(msg);
            }
        })
        .catch(function() {
            btn.disabled = false;
            btn.textContent = '<?php echo esc_js( __( 'Continue', 'autocomplete-google-address' ) ); ?>';
            alert('<?php echo esc_js( __( 'Network error. Please try again.', 'autocomplete-google-address' ) ); ?>');
        });
    }

    // Allow Enter key on API key field
    document.getElementById('aga-wizard-api-key').addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            document.getElementById('aga-wizard-next-1').click();
        }
    });

    // ---- Live Preview ----
    var previewInitialized = false;
    var mapsReady = false;

    function initPreview() {
        if (previewInitialized) return;
        previewInitialized = true;

        var apiKey = document.getElementById('aga-wizard-api-key').value.trim();
        if (!apiKey) return;

        // Load Google Maps
        var lang = document.getElementById('aga-wiz-language').value;

        if (window.google && window.google.maps && window.google.maps.places) {
            mapsReady = true;
            setupPreviewAutocomplete();
        } else if (window.google && window.google.maps) {
            google.maps.importLibrary('places').then(function() {
                mapsReady = true;
                setupPreviewAutocomplete();
            });
        } else {
            var scriptUrl = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(apiKey) + '&libraries=places&callback=_agaWizMapsReady';
            if (lang) {
                scriptUrl += '&language=' + encodeURIComponent(lang);
            }
            var script = document.createElement('script');
            script.src = scriptUrl;
            script.async = true;
            script.defer = true;
            window._agaWizMapsReady = function() {
                mapsReady = true;
                setupPreviewAutocomplete();
            };
            document.head.appendChild(script);
        }
    }

    function setupPreviewAutocomplete() {
        var input = document.getElementById('aga-wiz-preview-input');
        var container = document.getElementById('aga-wiz-preview-container');
        var resultDiv = document.getElementById('aga-wiz-preview-result');
        var resultTbody = document.querySelector('#aga-wiz-preview-result-table tbody');
        var dropdown = null;
        var debounceTimer = null;
        var sessionToken = null;
        var activeIndex = -1;

        function getPreviewCountries() {
            var sel = document.getElementById('aga-wiz-country-restriction');
            return Array.from(sel.selectedOptions).map(function(o) { return o.value.toUpperCase(); }).slice(0, 5);
        }

        function getPreviewPlaceTypes() {
            return document.getElementById('aga-wiz-place-types').value;
        }

        function getPreviewLanguage() {
            return document.getElementById('aga-wiz-language').value;
        }

        // Build request for the NEW AutocompleteSuggestion API
        // Note: language is set at Maps JS script load level, not per-request.
        function getNewApiRequest(query) {
            var request = { input: query, sessionToken: sessionToken };

            var countries = getPreviewCountries();
            if (countries.length) {
                request.includedRegionCodes = countries;
            }

            var types = getPreviewPlaceTypes();
            if (types) {
                var typeMap = {
                    'address':       'street_address',
                    'geocode':       'geocode',
                    'establishment': 'establishment',
                    '(regions)':     'administrative_area_level_1',
                    '(cities)':      'locality'
                };
                request.includedPrimaryTypes = [typeMap[types] || types];
            }

            return request;
        }

        function createSessionToken() {
            if (google.maps.places.AutocompleteSessionToken) {
                sessionToken = new google.maps.places.AutocompleteSessionToken();
            }
        }
        createSessionToken();

        function createDropdown() {
            if (dropdown) return;
            dropdown = document.createElement('ul');
            dropdown.className = 'aga-autocomplete-dropdown';
            dropdown.style.cssText = 'display:none;position:absolute;z-index:999999;left:0;right:0;top:' + input.offsetHeight + 'px;background:#fff;border:1px solid #ccc;border-top:none;list-style:none;margin:0;padding:0;max-height:250px;overflow-y:auto;box-shadow:0 4px 12px rgba(0,0,0,0.1);border-radius:0 0 8px 8px;';
            container.appendChild(dropdown);
        }

        function hideDropdown() {
            if (dropdown) dropdown.style.display = 'none';
            activeIndex = -1;
        }

        function fetchSuggestions(query) {
            createDropdown();
            dropdown.innerHTML = '<li style="padding:10px 12px;color:#888;font-size:13px;">Searching...</li>';
            dropdown.style.display = 'block';

            if (google.maps.places.AutocompleteSuggestion && typeof google.maps.places.AutocompleteSuggestion.fetchAutocompleteSuggestions === 'function') {
                var request = getNewApiRequest(query);
                google.maps.places.AutocompleteSuggestion.fetchAutocompleteSuggestions(request)
                    .then(function(result) {
                        var suggestions = result.suggestions || [];
                        if (suggestions.length) {
                            renderDropdown(suggestions);
                        } else {
                            dropdown.innerHTML = '<li style="padding:10px 12px;color:#888;font-size:13px;">No results found</li>';
                        }
                    })
                    .catch(function(err) {
                        console.warn('AGA Wizard Preview:', err);
                        dropdown.innerHTML = '<li style="padding:10px 12px;color:#c00;font-size:13px;">API error — please enable <a href="https://console.cloud.google.com/apis/library/places.googleapis.com" target="_blank" style="color:#c00;text-decoration:underline;">Places API (New)</a> in Google Cloud Console</li>';
                    });
            } else {
                dropdown.innerHTML = '<li style="padding:10px 12px;color:#c00;font-size:13px;">Places API not available. Please enable <a href="https://console.cloud.google.com/apis/library/places.googleapis.com" target="_blank" style="color:#c00;text-decoration:underline;">Places API (New)</a> in Google Cloud Console</li>';
            }
        }

        function renderDropdown(suggestions) {
            dropdown.innerHTML = '';
            activeIndex = -1;

            suggestions.forEach(function(suggestion, index) {
                var prediction = suggestion.placePrediction;
                if (!prediction) return;
                var li = document.createElement('li');
                li.textContent = prediction.text.toString();
                li.style.cssText = 'padding:10px 12px;cursor:pointer;font-size:13px;border-bottom:1px solid #f0f0f0;';
                li.addEventListener('mouseenter', function() { li.style.backgroundColor = '#f0f0f0'; });
                li.addEventListener('mouseleave', function() { li.style.backgroundColor = ''; });
                li.addEventListener('click', function() {
                    selectPlace(prediction);
                    hideDropdown();
                });
                dropdown.appendChild(li);
            });

            // Google attribution
            var attr = document.createElement('li');
            attr.style.cssText = 'padding:6px 12px;text-align:right;background:#fafafa;';
            attr.innerHTML = '<img src="https://maps.gstatic.com/mapfiles/api-3/images/powered-by-google-on-white3_hdpi.png" alt="Powered by Google" height="14" />';
            dropdown.appendChild(attr);
            dropdown.style.display = 'block';
        }

        function selectPlace(prediction) {
            var placeId = prediction.placeId || (prediction._legacy && prediction._legacy.place_id);

            // Try new Place class
            if (google.maps.places.Place) {
                var place = new google.maps.places.Place({ id: placeId });
                place.fetchFields({ fields: ['formattedAddress', 'location', 'addressComponents', 'id'] })
                    .then(function(resp) {
                        var p = resp.place || resp;
                        displayResult(p);
                    })
                    .catch(function() {
                        fetchPlaceLegacy(placeId);
                    });
            } else {
                fetchPlaceLegacy(placeId);
            }
        }

        function fetchPlaceLegacy(placeId) {
            var div = document.createElement('div');
            var service = new google.maps.places.PlacesService(div);
            service.getDetails({ placeId: placeId, fields: ['formatted_address', 'geometry', 'address_components', 'place_id'] }, function(place, status) {
                if (status !== google.maps.places.PlacesServiceStatus.OK || !place) return;
                displayResult({
                    formattedAddress: place.formatted_address,
                    location: place.geometry ? place.geometry.location : null,
                    addressComponents: place.address_components ? place.address_components.map(function(c) { return { types: c.types, longText: c.long_name, shortText: c.short_name }; }) : [],
                    id: place.place_id
                });
            });
        }

        function displayResult(place) {
            input.value = place.formattedAddress || '';
            createSessionToken();

            // Result table
            resultTbody.innerHTML = '';
            var components = place.addressComponents || [];
            components.forEach(function(comp) {
                var tr = document.createElement('tr');
                tr.innerHTML = '<td><strong>' + (comp.types ? comp.types.join(', ') : '') + '</strong></td><td>' + (comp.longText || comp.long_name || '') + '</td><td>' + (comp.shortText || comp.short_name || '') + '</td>';
                resultTbody.appendChild(tr);
            });

            if (place.location) {
                var lat = typeof place.location.lat === 'function' ? place.location.lat() : place.location.lat;
                var lng = typeof place.location.lng === 'function' ? place.location.lng() : place.location.lng;
                var tr = document.createElement('tr');
                tr.innerHTML = '<td><strong>coordinates</strong></td><td colspan="2">' + lat.toFixed(6) + ', ' + lng.toFixed(6) + '</td>';
                resultTbody.appendChild(tr);
            }

            resultDiv.style.display = 'block';
        }

        // Input event
        input.addEventListener('input', function() {
            var val = input.value.trim();
            clearTimeout(debounceTimer);
            if (val.length < 2) { hideDropdown(); return; }
            debounceTimer = setTimeout(function() { fetchSuggestions(val); }, 250);
        });

        // Keyboard nav
        input.addEventListener('keydown', function(e) {
            if (!dropdown || dropdown.style.display === 'none') return;
            var items = dropdown.querySelectorAll('li');
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
                items.forEach(function(li, i) { li.style.backgroundColor = i === activeIndex ? '#f0f0f0' : ''; });
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                items.forEach(function(li, i) { li.style.backgroundColor = i === activeIndex ? '#f0f0f0' : ''; });
            } else if (e.key === 'Enter' && activeIndex >= 0) {
                e.preventDefault();
                items[activeIndex].click();
            } else if (e.key === 'Escape') {
                hideDropdown();
            }
        });

        // Close on outside click
        document.addEventListener('click', function(e) {
            if (!container.contains(e.target)) hideDropdown();
        });

        // --- Reactive: re-fetch preview when any setting changes ---
        function refetchPreview() {
            var val = input.value.trim();
            if (val.length >= 2) {
                clearTimeout(debounceTimer);
                fetchSuggestions(val);
            }
        }

        // Country restriction (Select2 fires 'change' on the original <select>)
        var countrySelect = document.getElementById('aga-wiz-country-restriction');
        if (typeof jQuery !== 'undefined' && jQuery.fn.select2) {
            jQuery(countrySelect).on('change', refetchPreview);
        } else {
            countrySelect.addEventListener('change', refetchPreview);
        }

        // Place types & language dropdowns
        document.getElementById('aga-wiz-place-types').addEventListener('change', refetchPreview);
        document.getElementById('aga-wiz-language').addEventListener('change', refetchPreview);

    }

})();
</script>
