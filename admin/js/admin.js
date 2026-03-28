(function ($) {
    'use strict';

    $(function () {

        // ---- Select2 for page selector ----
        if ($.fn.select2 && $('#Nish_aga_load_on_pages').length) {
            $('#Nish_aga_load_on_pages').select2({
                placeholder: "Search and select pages...",
                width: '100%'
            });
        }

        // ---- Mode Toggling Logic ----
        var modeRadios = $('input[name="Nish_aga_mode"]');
        var modePanels = $('.aga-mode-panel');

        function toggleModePanels() {
            var selectedMode = modeRadios.filter(':checked').val();
            modePanels.removeClass('active');
            $('#aga-panel-' + selectedMode).addClass('active');
        }

        toggleModePanels();
        modeRadios.on('change', toggleModePanels);

        // ---- Settings Page Tabs ----
        $('.aga-tab').on('click', function () {
            var target = $(this).data('tab');
            $('.aga-tab').removeClass('active');
            $(this).addClass('active');
            $('.aga-tab-panel').removeClass('active');
            $('.aga-tab-panel[data-tab="' + target + '"]').addClass('active');
            // Track active tab so sanitize knows which checkboxes to reset.
            $('#aga-active-tab').val(target);
        });

        // ---- Preset Auto-Fill ----
        function getPresetData() {
            return window.aga_presets || {};
        }

        function applyPreset(presetKey, flash) {
            var presetData = getPresetData();
            if (!presetKey || !presetData[presetKey]) return;

            var preset = presetData[presetKey];
            var selectors = preset.selectors || {};

            // Fill trigger field
            if (selectors.main_selector) {
                $('#aga_main_selector').val(selectors.main_selector);
                if (flash) aga_flashField('#aga_main_selector');
            }

            // Switch to smart mapping mode and toggle panel
            $('#mode_smart_mapping').prop('checked', true);
            toggleModePanels();

            // Fill smart mapping fields
            var fieldMap = {
                'street': '#aga_street_selector',
                'city': '#aga_city_selector',
                'state': '#aga_state_selector',
                'zip': '#aga_zip_selector',
                'country': '#aga_country_selector'
            };

            $.each(fieldMap, function (key, selector) {
                if (selectors[key]) {
                    $(selector).val(selectors[key]);
                    if (flash) aga_flashField(selector);
                }
            });

            // Show helper text
            var $desc = $('#aga_form_preset').closest('.aga-preset-selector').find('.description');
            if (preset.description) {
                var msg = flash ? '<strong style="color: #00a32a;">Fields filled!</strong> ' : '';
                $desc.html(msg + preset.description);
            }
        }

        // Apply on dropdown change
        $('#aga_form_preset').on('change', function () {
            applyPreset($(this).val(), true);
        });

        // "Apply Preset" button
        $('#aga-apply-preset-btn').on('click', function (e) {
            e.preventDefault();
            var key = $('#aga_form_preset').val();
            if (!key) {
                alert('Please select a form plugin first.');
                return;
            }
            applyPreset(key, true);
        });

        function aga_flashField(selector) {
            $(selector).css('background-color', '#e6ffe6');
            setTimeout(function () {
                $(selector).css('background-color', '');
            }, 2000);
        }

        // Language single-select
        if ($('.aga-select2-language').length) {
            $('.aga-select2-language').select2({
                allowClear: true,
                width: '100%'
            });
        }

        // Country restriction multi-select (max 5)
        if ($('.aga-select2-countries').length) {
            $('.aga-select2-countries').select2({
                maximumSelectionLength: 5,
                allowClear: true,
                width: '100%'
            });
        }

        // ---- API Key Validation Button ----
        $('#aga-validate-key-btn').on('click', function (e) {
            e.preventDefault();
            var $btn = $(this);
            var $status = $('#aga-key-status');
            var apiKey = $('#api_key').val();

            if (!apiKey || apiKey.indexOf('\u2022') !== -1) {
                $status.text('Enter a new key first').removeClass('success').addClass('error');
                return;
            }

            $btn.prop('disabled', true);
            $status.text('Checking...').removeClass('success error');

            // Test the API key with a simple geocode request
            $.ajax({
                url: 'https://maps.googleapis.com/maps/api/geocode/json',
                data: {
                    address: 'New York',
                    key: apiKey
                },
                success: function (response) {
                    if (response.status === 'OK') {
                        $status.text('Valid API key').removeClass('error').addClass('success');
                    } else if (response.status === 'REQUEST_DENIED') {
                        $status.text('Invalid key or API not enabled').removeClass('success').addClass('error');
                    } else {
                        $status.text('Key works but returned: ' + response.status).removeClass('error').addClass('success');
                    }
                },
                error: function () {
                    $status.text('Could not validate key').removeClass('success').addClass('error');
                },
                complete: function () {
                    $btn.prop('disabled', false);
                }
            });
        });

    });

    // ---- Live Preview ----
    (function () {
        var previewInput = document.getElementById('aga-live-preview-input');
        if (!previewInput) return;

        var container = document.getElementById('aga-live-preview-container');
        var resultDiv = document.getElementById('aga-preview-result');
        var resultTbody = document.querySelector('#aga-preview-result-table tbody');
        var dropdown = null;
        var debounceTimer = null;
        var activeIndex = -1;
        var sessionToken = null;
        var isSelecting = false;
        var mapsReady = false;

        function ensureGoogleMaps(callback) {
            if (window.google && window.google.maps && window.google.maps.places) {
                mapsReady = true;
                callback();
                return;
            }

            if (window.google && window.google.maps) {
                google.maps.importLibrary('places').then(function () {
                    mapsReady = true;
                    callback();
                });
                return;
            }

            var apiKey = (typeof aga_admin_data !== 'undefined' && aga_admin_data.api_key) ? aga_admin_data.api_key : '';
            if (!apiKey) {
                console.warn('AGA Live Preview: No API key found. Cannot load Google Maps.');
                return;
            }

            var script = document.createElement('script');
            script.src = 'https://maps.googleapis.com/maps/api/js?key=' + encodeURIComponent(apiKey) + '&libraries=places&callback=_agaAdminMapsReady';
            script.async = true;
            script.defer = true;
            window._agaAdminMapsReady = function () {
                mapsReady = true;
                callback();
            };
            script.onerror = function () {
                console.warn('AGA Live Preview: Failed to load Google Maps API.');
            };
            document.head.appendChild(script);
        }

        function createDropdown() {
            if (dropdown) return;
            container.style.position = 'relative';
            dropdown = document.createElement('ul');
            dropdown.className = 'aga-autocomplete-dropdown';
            dropdown.style.display = 'none';
            dropdown.style.zIndex = '99999';
            dropdown.style.position = 'absolute';
            dropdown.style.zIndex = '999999';
            dropdown.style.left = '0';
            dropdown.style.right = '0';
            dropdown.style.top = previewInput.offsetHeight + 'px';
            dropdown.style.background = '#fff';
            dropdown.style.border = '1px solid #ccc';
            dropdown.style.borderTop = 'none';
            dropdown.style.listStyle = 'none';
            dropdown.style.margin = '0';
            dropdown.style.padding = '0';
            dropdown.style.maxHeight = '250px';
            dropdown.style.overflowY = 'auto';
            dropdown.style.boxShadow = '0 4px 12px rgba(0,0,0,0.1)';
            container.appendChild(dropdown);
        }

        function hideDropdown() {
            if (dropdown) dropdown.style.display = 'none';
            activeIndex = -1;
        }

        function highlightItem(items, index) {
            items.forEach(function (item, i) {
                item.style.backgroundColor = i === index ? '#f0f0f0' : '';
            });
            activeIndex = index;
        }

        var placesService = null;

        // Read current form config values for preview filtering
        function buildNewApiRequest(query) {
            var request = { input: query, sessionToken: sessionToken };

            // Country restriction
            var countrySel = document.getElementById('aga_country_restriction');
            if (countrySel) {
                var countries = Array.from(countrySel.selectedOptions).map(function(o) { return o.value.toUpperCase(); }).slice(0, 5);
                if (countries.length) {
                    request.includedRegionCodes = countries;
                }
            }

            // Place types
            var typesSel = document.querySelector('select[name="Nish_aga_place_types"]');
            if (typesSel && typesSel.value) {
                var typeMap = {
                    'address':       'street_address',
                    'geocode':       'geocode',
                    'establishment': 'establishment',
                    '(regions)':     'administrative_area_level_1',
                    '(cities)':      'locality'
                };
                request.includedPrimaryTypes = [typeMap[typesSel.value] || typesSel.value];
            }

            return request;
        }

        function fetchSuggestions(query) {
            createDropdown();
            dropdown.innerHTML = '<li style="padding:10px 12px;color:#888;font-size:13px;">Searching...</li>';
            dropdown.style.display = 'block';

            if (google.maps.places.AutocompleteSuggestion && typeof google.maps.places.AutocompleteSuggestion.fetchAutocompleteSuggestions === 'function') {
                var request = buildNewApiRequest(query);
                google.maps.places.AutocompleteSuggestion.fetchAutocompleteSuggestions(request)
                    .then(function (result) {
                        var suggestions = result.suggestions || [];
                        if (suggestions.length) {
                            renderDropdown(suggestions);
                        } else {
                            dropdown.innerHTML = '<li style="padding:10px 12px;color:#888;font-size:13px;">No results found</li>';
                            setTimeout(hideDropdown, 2000);
                        }
                    })
                    .catch(function (err) {
                        console.warn('AGA Live Preview:', err);
                        dropdown.innerHTML = '<li style="padding:10px 12px;color:#c00;font-size:13px;">API error \u2014 please enable <a href="https://console.cloud.google.com/apis/library/places.googleapis.com" target="_blank" style="color:#c00;text-decoration:underline;">Places API (New)</a></li>';
                    });
            } else {
                dropdown.innerHTML = '<li style="padding:10px 12px;color:#c00;font-size:13px;">Places API not available</li>';
            }
        }

        function renderDropdown(suggestions) {
            dropdown.innerHTML = '';
            activeIndex = -1;

            suggestions.forEach(function (suggestion, index) {
                var prediction = suggestion.placePrediction;
                if (!prediction) return;

                var li = document.createElement('li');
                li.className = 'aga-autocomplete-item';
                li.textContent = prediction.text.toString();
                li.style.padding = '10px 12px';
                li.style.cursor = 'pointer';
                li.style.fontSize = '13px';
                li.style.borderBottom = '1px solid #f0f0f0';

                li.addEventListener('mouseenter', function () {
                    var items = dropdown.querySelectorAll('.aga-autocomplete-item');
                    highlightItem(items, index);
                });

                li.addEventListener('mouseleave', function () {
                    li.style.backgroundColor = '';
                });

                li.addEventListener('click', function () {
                    selectPlace(prediction);
                    hideDropdown();
                });

                dropdown.appendChild(li);
            });

            // Google attribution
            var attribution = document.createElement('li');
            attribution.style.padding = '6px 12px';
            attribution.style.textAlign = 'right';
            attribution.style.backgroundColor = '#fafafa';
            attribution.innerHTML = '<img src="https://maps.gstatic.com/mapfiles/api-3/images/powered-by-google-on-white3_hdpi.png" alt="Powered by Google" height="14" />';
            dropdown.appendChild(attribution);

            dropdown.style.display = 'block';
        }

        function selectPlace(prediction) {
            isSelecting = true;

            // Handle legacy predictions
            if (prediction._legacy) {
                if (!placesService) {
                    var div = document.createElement('div');
                    placesService = new google.maps.places.PlacesService(div);
                }
                placesService.getDetails({ placeId: prediction.placeId, fields: ['formatted_address', 'geometry', 'address_components', 'place_id'] }, function (place, status) {
                    if (status !== google.maps.places.PlacesServiceStatus.OK || !place) {
                        isSelecting = false;
                        return;
                    }
                    displayResult({
                        formattedAddress: place.formatted_address,
                        location: place.geometry ? place.geometry.location : null,
                        addressComponents: place.address_components ? place.address_components.map(function (c) { return { types: c.types, longText: c.long_name, shortText: c.short_name }; }) : [],
                        id: place.place_id
                    });
                });
                return;
            }

            var placeObj = prediction.toPlace();
            placeObj.fetchFields({ fields: ['formattedAddress', 'location', 'addressComponents', 'id'] }).then(function () {
                displayResult(placeObj);
            });
        }

        function displayResult(place) {
                previewInput.value = place.formattedAddress || '';

                // Extract address components
                var components = {};
                if (place.addressComponents) {
                    place.addressComponents.forEach(function (comp) {
                        comp.types.forEach(function (type) {
                            components[type] = {
                                long: comp.longText || comp.long_name || '',
                                short: comp.shortText || comp.short_name || ''
                            };
                        });
                    });
                }

                var streetNumber = components['street_number'] ? components['street_number'].long : '';
                var route = components['route'] ? components['route'].long : '';
                var street = (streetNumber + ' ' + route).trim();
                var city = components['locality'] ? components['locality'].long : (components['sublocality_level_1'] ? components['sublocality_level_1'].long : '');
                var state = components['administrative_area_level_1'] ? components['administrative_area_level_1'].long : '';
                var zip = components['postal_code'] ? components['postal_code'].long : '';
                var country = components['country'] ? components['country'].long : '';
                var lat = place.location ? place.location.lat() : '';
                var lng = place.location ? place.location.lng() : '';
                var placeId = place.id || '';

                var rows = [
                    ['Full Address', place.formattedAddress || ''],
                    ['Street', street],
                    ['City', city],
                    ['State', state],
                    ['Zip/Postal Code', zip],
                    ['Country', country],
                    ['Latitude', lat],
                    ['Longitude', lng],
                    ['Place ID', placeId]
                ];

                resultTbody.innerHTML = '';
                rows.forEach(function (row) {
                    var tr = document.createElement('tr');
                    var th = document.createElement('td');
                    th.style.fontWeight = '600';
                    th.style.width = '140px';
                    th.textContent = row[0];
                    var td = document.createElement('td');
                    td.textContent = row[1];
                    tr.appendChild(th);
                    tr.appendChild(td);
                    resultTbody.appendChild(tr);
                });

                resultDiv.style.display = 'block';

                sessionToken = new google.maps.places.AutocompleteSessionToken();

                setTimeout(function () {
                    isSelecting = false;
                }, 100);
        }

        // Initialize on first focus
        var initialized = false;
        previewInput.addEventListener('focus', function () {
            if (initialized) return;
            initialized = true;

            ensureGoogleMaps(function () {
                sessionToken = new google.maps.places.AutocompleteSessionToken();
            });
        });

        previewInput.addEventListener('input', function () {
            if (isSelecting) return;

            var query = previewInput.value.trim();
            if (debounceTimer) clearTimeout(debounceTimer);

            if (query.length < 2) {
                hideDropdown();
                return;
            }

            debounceTimer = setTimeout(function () {
                if (!mapsReady) {
                    ensureGoogleMaps(function () {
                        sessionToken = new google.maps.places.AutocompleteSessionToken();
                        fetchSuggestions(query);
                    });
                } else {
                    if (!sessionToken) {
                        sessionToken = new google.maps.places.AutocompleteSessionToken();
                    }
                    fetchSuggestions(query);
                }
            }, 300);
        });

        previewInput.addEventListener('keydown', function (e) {
            if (!dropdown) return;
            var items = dropdown.querySelectorAll('.aga-autocomplete-item');
            if (!items.length || dropdown.style.display === 'none') return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                activeIndex = Math.min(activeIndex + 1, items.length - 1);
                highlightItem(items, activeIndex);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                highlightItem(items, activeIndex);
            } else if (e.key === 'Enter' && activeIndex >= 0) {
                e.preventDefault();
                items[activeIndex].click();
            } else if (e.key === 'Escape') {
                hideDropdown();
            }
        });

        document.addEventListener('click', function (e) {
            if (!container.contains(e.target)) {
                hideDropdown();
            }
        });

        // Reactive: re-fetch preview when config fields change
        function refetchPreview() {
            var val = previewInput.value.trim();
            if (val.length >= 2 && mapsReady) {
                if (debounceTimer) clearTimeout(debounceTimer);
                fetchSuggestions(val);
            }
        }

        // Country restriction (Select2)
        var countryField = document.getElementById('aga_country_restriction');
        if (countryField) {
            $(countryField).on('change', refetchPreview);
        }

        // Place types
        var typesField = document.querySelector('select[name="Nish_aga_place_types"]');
        if (typesField) {
            typesField.addEventListener('change', refetchPreview);
        }

        // Language
        var langField = document.querySelector('select[name="Nish_aga_language_override"]');
        if (langField) {
            $(langField).on('change', refetchPreview);
        }
    })();

})(jQuery);
