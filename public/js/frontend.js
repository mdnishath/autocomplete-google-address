(function ($) {
    'use strict';

    // Analytics tracking helper (Pro feature — fire-and-forget).
    var agaTrack = {
        _lastSearchTime: 0,
        _minInterval: 5000, // max 1 search event per 5 seconds

        send: function (eventType, extra) {
            if (typeof aga_frontend_data === 'undefined' || !aga_frontend_data.ajax_url) return;

            var data = {
                action: 'aga_track_event',
                nonce: aga_frontend_data.nonce,
                event_type: eventType
            };

            if (extra) {
                if (extra.form_id) data.form_id = extra.form_id;
                if (extra.country) data.country = extra.country;
                if (extra.city) data.city = extra.city;
            }

            // Use sendBeacon for fire-and-forget when available, else plain AJAX.
            if (navigator.sendBeacon) {
                var formData = new FormData();
                for (var key in data) {
                    if (data.hasOwnProperty(key)) formData.append(key, data[key]);
                }
                navigator.sendBeacon(aga_frontend_data.ajax_url, formData);
            } else {
                $.post(aga_frontend_data.ajax_url, data);
            }
        },

        trackSearch: function (formId) {
            var now = Date.now();
            if (now - agaTrack._lastSearchTime < agaTrack._minInterval) return;
            agaTrack._lastSearchTime = now;
            agaTrack.send('search', { form_id: formId || 0 });
        },

        trackSelection: function (formId, country, city) {
            agaTrack.send('selection', { form_id: formId || 0, country: country || '', city: city || '' });
        }
    };

    var aga = {
        _selectionId: 0,

        init: function () {
            if (typeof window.aga_form_configs === 'undefined' || window.aga_form_configs.length === 0) {
                return;
            }

            // Conflict detection: note if Google Maps API was already loaded by another source
            if (window.google && window.google.maps) {
                console.info('Autocomplete Google Address: Google Maps API detected from another source. Using existing instance.');
            }

            var checkAttempts = 0;
            var maxAttempts = 200; // 20 seconds max
            var checkGoogle = setInterval(function () {
                checkAttempts++;
                if (checkAttempts > maxAttempts) {
                    clearInterval(checkGoogle);
                    console.warn('Autocomplete Google Address: Google Maps API did not load within 20 seconds.');
                    return;
                }
                if (typeof window.google !== 'undefined' && typeof window.google.maps !== 'undefined') {
                    clearInterval(checkGoogle);
                    if (typeof google.maps.importLibrary === 'function') {
                        google.maps.importLibrary('places').then(function () {
                            aga.run();
                        });
                    } else if (typeof google.maps.places !== 'undefined') {
                        aga.run();
                    } else {
                        var checkPlaces = setInterval(function () {
                            if (typeof google.maps.places !== 'undefined') {
                                clearInterval(checkPlaces);
                                aga.run();
                            }
                        }, 100);
                        // Timeout for places check too
                        setTimeout(function () { clearInterval(checkPlaces); }, 10000);
                    }
                }
            }, 100);
        },

        // Detect if new Places API (AutocompleteSuggestion) is available.
        useNewAPI: typeof google !== 'undefined' && google.maps && google.maps.places && typeof google.maps.places.AutocompleteSuggestion !== 'undefined',

        run: function () {
            // Re-check at run time since API may have loaded after init.
            aga.useNewAPI = typeof google.maps.places.AutocompleteSuggestion !== 'undefined';
            window.aga_form_configs.forEach(function (config) {
                aga.setupAutocomplete(config);
            });
        },

        setupAutocomplete: function (config) {
            var mainInput = document.querySelector(config.main_selector);
            if (!mainInput) {
                return;
            }

            // Skip if already initialized (prevents double-init on dynamic re-renders).
            if (mainInput.getAttribute('data-aga-init') === '1') {
                return;
            }
            mainInput.setAttribute('data-aga-init', '1');

            var wrapper = mainInput.parentNode;
            if (window.getComputedStyle(wrapper).position === 'static') {
                wrapper.style.position = 'relative';
            }

            var dropdown = document.createElement('ul');
            dropdown.className = 'aga-autocomplete-dropdown';
            dropdown.style.display = 'none';
            dropdown.setAttribute('role', 'listbox');
            dropdown.setAttribute('aria-label', 'Address suggestions');
            wrapper.appendChild(dropdown);

            var state = {
                sessionToken: aga.useNewAPI ? new google.maps.places.AutocompleteSessionToken() : null,
                debounceTimer: null,
                activeIndex: -1,
                isSelecting: false,
                isFetching: false,
                legacyService: aga.useNewAPI ? null : new google.maps.places.AutocompleteService()
            };

            // iOS Safari ignores autocomplete="off", use a non-standard value to suppress native suggestions
            mainInput.setAttribute('autocomplete', 'aga-none');
            mainInput.setAttribute('autocorrect', 'off');
            mainInput.setAttribute('spellcheck', 'false');
            mainInput.setAttribute('role', 'combobox');
            mainInput.setAttribute('aria-autocomplete', 'list');
            mainInput.setAttribute('aria-expanded', 'false');
            mainInput.setAttribute('aria-haspopup', 'listbox');

            mainInput.addEventListener('input', function () {
                if (state.isSelecting) return;
                if (mainInput.getAttribute('data-aga-suppress')) return;

                var query = mainInput.value.trim();

                if (state.debounceTimer) {
                    clearTimeout(state.debounceTimer);
                }

                if (query.length < 2) {
                    aga.hideDropdown(dropdown);
                    return;
                }

                state.debounceTimer = setTimeout(function () {
                    aga.fetchSuggestions(query, config, state, dropdown, mainInput);
                }, 300);
            });

            mainInput.addEventListener('keydown', function (e) {
                var items = dropdown.querySelectorAll('.aga-autocomplete-item');
                if (!items.length || dropdown.style.display === 'none') return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    state.activeIndex = Math.min(state.activeIndex + 1, items.length - 1);
                    aga.highlightItem(items, state.activeIndex);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    state.activeIndex = Math.max(state.activeIndex - 1, 0);
                    aga.highlightItem(items, state.activeIndex);
                } else if (e.key === 'Enter' && state.activeIndex >= 0) {
                    e.preventDefault();
                    items[state.activeIndex].click();
                } else if (e.key === 'Escape') {
                    aga.hideDropdown(dropdown);
                    state.activeIndex = -1;
                }
            });

            // Close dropdown on outside click/touch (iOS needs touchstart)
            function closeOnOutside(e) {
                if (!wrapper.contains(e.target)) {
                    aga.hideDropdown(dropdown);
                    state.activeIndex = -1;
                }
            }
            document.addEventListener('click', closeOnOutside);
            document.addEventListener('touchstart', closeOnOutside, { passive: true });

            // Saved Addresses focus handler (Pro feature)
            if (config.saved_addresses && aga_frontend_data.is_logged_in) {
                aga.setupSavedAddresses(mainInput, dropdown, config, state);
            }

            // Geolocation button (Pro feature)
            if (config.geolocation) {
                aga.setupGeolocationButton(mainInput, wrapper, config);
            }

            // Map Picker (Pro feature) — only on frontend, not in WP admin
            var isAdmin = document.body.classList.contains('wp-admin');
            if (config.map_picker && !isAdmin) {
                aga.setupMapPickerButton(mainInput, wrapper, config, state);
            }

            // Checkout abandonment tracking
            if (typeof aga_frontend_data !== 'undefined' && aga_frontend_data.track_abandonment) {
                var addressEntered = false;
                state.formSubmitted = false;

                mainInput.addEventListener('change', function () {
                    if (mainInput.value.trim().length > 5) {
                        addressEntered = true;
                    }
                });

                window.addEventListener('beforeunload', function () {
                    if (addressEntered && !state.formSubmitted) {
                        if (navigator.sendBeacon) {
                            var data = new FormData();
                            data.append('action', 'aga_track_event');
                            data.append('nonce', aga_frontend_data.nonce);
                            data.append('event_type', 'abandonment');
                            data.append('form_id', config.form_id || '');
                            navigator.sendBeacon(aga_frontend_data.ajax_url, data);
                        }
                    }
                });

                var form = mainInput.closest('form');
                if (form) {
                    form.addEventListener('submit', function () {
                        state.formSubmitted = true;
                    });
                }
            }
        },

        setupGeolocationButton: function (mainInput, wrapper, config) {
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'aga-geolocation-btn';
            btn.title = 'Use my current location';
            btn.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="10" r="3"/><path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"/></svg>';

            // Add padding-right to the input so text doesn't overlap the button
            mainInput.style.paddingRight = '38px';

            wrapper.appendChild(btn);

            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();

                if (window.location.protocol !== 'https:' && window.location.hostname !== 'localhost') {
                    aga.showGeolocationError(btn, 'HTTPS required for geolocation');
                    return;
                }

                if (!navigator.geolocation) {
                    aga.showGeolocationError(btn, 'Geolocation not supported');
                    return;
                }

                // Show loading state
                btn.classList.add('aga-loading');
                btn.disabled = true;

                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        var latLng = {
                            lat: position.coords.latitude,
                            lng: position.coords.longitude
                        };

                        // Use existing reverseGeocode method
                        aga.reverseGeocode(latLng, mainInput, config);

                        // Center map picker if enabled
                        if (config.map_picker) {
                            aga.centerMapPicker(latLng, config);
                        }

                        // Remove loading state
                        btn.classList.remove('aga-loading');
                        btn.disabled = false;
                    },
                    function (error) {
                        btn.classList.remove('aga-loading');
                        btn.disabled = false;

                        var msg = 'Location unavailable';
                        if (error.code === error.PERMISSION_DENIED) {
                            msg = 'Location access denied';
                        } else if (error.code === error.TIMEOUT) {
                            msg = 'Location request timed out';
                        }
                        aga.showGeolocationError(btn, msg);
                    },
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 300000
                    }
                );
            });
        },

        showGeolocationError: function (btn, message) {
            // Remove any existing error tooltip
            var existing = btn.parentNode.querySelector('.aga-geolocation-error');
            if (existing) existing.remove();

            var tooltip = document.createElement('div');
            tooltip.className = 'aga-geolocation-error';
            tooltip.textContent = message;
            btn.parentNode.appendChild(tooltip);

            // Position relative to button
            var btnRect = btn.getBoundingClientRect();
            var wrapperRect = btn.parentNode.getBoundingClientRect();
            tooltip.style.right = (wrapperRect.right - btnRect.right) + 'px';
            tooltip.style.top = (btn.offsetTop + btn.offsetHeight + 4) + 'px';

            setTimeout(function () {
                if (tooltip.parentNode) tooltip.remove();
            }, 3000);
        },

        setupMapPickerButton: function (mainInput, wrapper, config, state) {
            // Look for an existing map container (rendered by shortcode/Elementor/widget)
            // Search up to 3 levels of parents to find it
            var mapContainer = null;
            var searchParent = wrapper.parentNode;
            for (var i = 0; i < 3 && searchParent && !mapContainer; i++) {
                mapContainer = searchParent.querySelector('.aga-map-picker-container');
                searchParent = searchParent.parentNode;
            }

            if (!mapContainer) {
                // Create one dynamically for standard forms (WooCommerce, CF7, etc.)
                mapContainer = document.createElement('div');
                mapContainer.className = 'aga-map-picker-container';
                // Insert after the wrapper (address input container)
                if (wrapper.nextSibling) {
                    wrapper.parentNode.insertBefore(mapContainer, wrapper.nextSibling);
                } else {
                    wrapper.parentNode.appendChild(mapContainer);
                }
            }

            var pickerMap = null;
            var pickerMarker = null;

            // Read map zoom from global settings (default 17)
            var mapZoom = (typeof aga_frontend_data !== 'undefined' && aga_frontend_data.map_zoom) ? parseInt(aga_frontend_data.map_zoom, 10) : 17;

            // Store references on config so selectPlace can update the map
            config._mapPicker = {
                getMap: function () { return pickerMap; },
                getMarker: function () { return pickerMarker; },
                centerOn: function (latLng) {
                    if (pickerMap) {
                        pickerMap.setCenter(latLng);
                        pickerMap.setZoom(mapZoom);
                    }
                    if (pickerMarker) {
                        pickerMarker.position = latLng;
                    }
                }
            };

            // Country center coordinates (last fallback)
            var countryCenters = {
                US: { lat: 39.8283, lng: -98.5795, zoom: 4 },
                GB: { lat: 51.5074, lng: -0.1278, zoom: 6 },
                UK: { lat: 51.5074, lng: -0.1278, zoom: 6 },
                CA: { lat: 56.1304, lng: -106.3468, zoom: 4 },
                AU: { lat: -25.2744, lng: 133.7751, zoom: 4 },
                DE: { lat: 51.1657, lng: 10.4515, zoom: 6 },
                FR: { lat: 46.2276, lng: 2.2137, zoom: 6 },
                IN: { lat: 20.5937, lng: 78.9629, zoom: 5 },
                BR: { lat: -14.235, lng: -51.9253, zoom: 4 },
                JP: { lat: 36.2048, lng: 138.2529, zoom: 5 },
                CN: { lat: 35.8617, lng: 104.1954, zoom: 4 },
                MX: { lat: 23.6345, lng: -102.5528, zoom: 5 },
                IT: { lat: 41.8719, lng: 12.5674, zoom: 6 },
                ES: { lat: 40.4637, lng: -3.7492, zoom: 6 },
                NL: { lat: 52.1326, lng: 5.2913, zoom: 7 },
                BD: { lat: 23.685, lng: 90.3563, zoom: 7 },
                PK: { lat: 30.3753, lng: 69.3451, zoom: 5 },
                NZ: { lat: -40.9006, lng: 174.886, zoom: 5 },
                AE: { lat: 23.4241, lng: 53.8478, zoom: 7 },
                SA: { lat: 23.8859, lng: 45.0792, zoom: 5 },
                SG: { lat: 1.3521, lng: 103.8198, zoom: 11 },
                ZA: { lat: -30.5595, lng: 22.9375, zoom: 5 },
                IE: { lat: 53.1424, lng: -7.6921, zoom: 7 },
                SE: { lat: 60.1282, lng: 18.6435, zoom: 5 },
                NO: { lat: 60.472, lng: 8.4689, zoom: 5 },
                DK: { lat: 56.2639, lng: 9.5018, zoom: 7 },
                FI: { lat: 61.9241, lng: 25.7482, zoom: 5 },
                PL: { lat: 51.9194, lng: 19.1451, zoom: 6 },
                AT: { lat: 47.5162, lng: 14.5501, zoom: 7 },
                CH: { lat: 46.8182, lng: 8.2275, zoom: 8 },
                BE: { lat: 50.5039, lng: 4.4699, zoom: 8 },
                PT: { lat: 39.3999, lng: -8.2245, zoom: 6 },
                PH: { lat: 12.8797, lng: 121.774, zoom: 6 },
                TH: { lat: 15.87, lng: 100.9925, zoom: 6 },
                MY: { lat: 4.2105, lng: 101.9758, zoom: 6 },
                KR: { lat: 35.9078, lng: 127.7669, zoom: 7 },
                TR: { lat: 38.9637, lng: 35.2433, zoom: 6 },
                EG: { lat: 26.8206, lng: 30.8025, zoom: 6 },
                NG: { lat: 9.082, lng: 8.6753, zoom: 6 },
                KE: { lat: -0.0236, lng: 37.9062, zoom: 6 },
                GH: { lat: 7.9465, lng: -1.0232, zoom: 7 },
                AR: { lat: -38.4161, lng: -63.6167, zoom: 4 },
                CL: { lat: -35.6751, lng: -71.543, zoom: 4 },
                CO: { lat: 4.5709, lng: -74.2973, zoom: 5 },
            };

            // Get country fallback center (used only if geolocation fails)
            var countryfallback = { lat: 20, lng: 0, zoom: 2 };
            if (config.component_restrictions && config.component_restrictions.country) {
                var restricted = config.component_restrictions.country;
                var code = Array.isArray(restricted) ? restricted[0] : restricted;
                code = (code || '').toUpperCase();
                if (countryCenters[code]) {
                    countryfallback = countryCenters[code];
                }
            }

            // Start with country fallback — geolocation will override once ready
            var center = { lat: countryfallback.lat, lng: countryfallback.lng };
            var initZoom = countryfallback.zoom;

            // Place the marker and center map on a location
            function showLocation(latLng, zoom) {
                if (pickerMap) {
                    pickerMap.setCenter(latLng);
                    pickerMap.setZoom(zoom);
                }
                if (pickerMarker) {
                    pickerMarker.position = latLng;
                    pickerMarker.map = pickerMap; // Make visible
                }
            }

            // Try to get user's actual location
            function locateUser() {
                // Priority 1: Browser geolocation (GPS/WiFi — exact location)
                if (navigator.geolocation) {
                    navigator.geolocation.getCurrentPosition(
                        function (pos) {
                            showLocation(
                                { lat: pos.coords.latitude, lng: pos.coords.longitude },
                                mapZoom
                            );
                        },
                        function () {
                            // Priority 2: IP-based geolocation
                            ipGeolocate();
                        },
                        { enableHighAccuracy: true, timeout: 8000, maximumAge: 300000 }
                    );
                } else {
                    ipGeolocate();
                }
            }

            function ipGeolocate() {
                try {
                    fetch('https://ipapi.co/json/', { mode: 'cors' })
                        .then(function (res) { return res.json(); })
                        .then(function (data) {
                            if (data && data.latitude && data.longitude) {
                                showLocation(
                                    { lat: parseFloat(data.latitude), lng: parseFloat(data.longitude) },
                                    Math.min(mapZoom, 14)
                                );
                            } else {
                                // IP lookup returned no coords — show country fallback
                                showLocation(center, initZoom);
                            }
                        })
                        .catch(function () {
                            showLocation(center, initZoom);
                        });
                } catch (e) {
                    showLocation(center, initZoom);
                }
            }

            // Initialize map — marker hidden until we have a real location
            function initMap() {
                pickerMap = new google.maps.Map(mapContainer, {
                    center: center,
                    zoom: initZoom,
                    disableDefaultUI: true,
                    zoomControl: true,
                    streetViewControl: false,
                    mapId: 'aga_map_picker_' + config.form_id,
                });

                google.maps.importLibrary('marker').then(function (markerLib) {
                    pickerMarker = new markerLib.AdvancedMarkerElement({
                        map: null, // Hidden initially — no map assigned
                        position: center,
                        gmpDraggable: true,
                    });

                    // Locate user — marker becomes visible once location is found
                    locateUser();

                    // Drag marker to pick address
                    pickerMarker.addListener('dragend', function () {
                        var pos = pickerMarker.position;
                        var latLng = { lat: pos.lat, lng: pos.lng };
                        pickerMap.setCenter(latLng);
                        state.isSelecting = true;
                        aga.reverseGeocode(latLng, mainInput, config);
                        setTimeout(function () { state.isSelecting = false; }, 500);
                    });

                    // Click on map to move marker
                    pickerMap.addListener('click', function (ev) {
                        var latLng = { lat: ev.latLng.lat(), lng: ev.latLng.lng() };
                        pickerMarker.position = latLng;
                        pickerMap.setCenter(latLng);
                        state.isSelecting = true;
                        aga.reverseGeocode(latLng, mainInput, config);
                        setTimeout(function () { state.isSelecting = false; }, 500);
                    });
                });
            }

            // Initialize right away
            initMap();
        },

        fetchSuggestions: function (query, config, state, dropdown, mainInput) {
            state.isFetching = true;
            state.fetchId = (state.fetchId || 0) + 1;
            var currentFetchId = state.fetchId;
            aga.showLoading(dropdown, mainInput);

            if (aga.useNewAPI) {
                // New Places API
                var request = {
                    input: query,
                    sessionToken: state.sessionToken
                };

                if (config.component_restrictions && config.component_restrictions.country) {
                    var country = config.component_restrictions.country;
                    request.includedRegionCodes = Array.isArray(country) ? country : [country];
                }

                if (config.place_types) {
                    var typeMap = {
                        'address': ['street_address', 'subpremise', 'premise'],
                        'geocode': ['geocode'],
                        'establishment': ['establishment'],
                        '(regions)': ['locality', 'sublocality', 'administrative_area_level_1', 'administrative_area_level_2', 'country'],
                        '(cities)': ['locality']
                    };
                    if (typeMap[config.place_types]) {
                        request.includedPrimaryTypes = typeMap[config.place_types];
                    }
                }

                google.maps.places.AutocompleteSuggestion.fetchAutocompleteSuggestions(request)
                    .then(function (result) {
                        state.isFetching = false;
                        if (state.fetchId !== currentFetchId) return; // stale fetch, discard
                        var suggestions = result.suggestions || [];
                        if (suggestions.length) {
                            aga.renderDropdown(suggestions, dropdown, mainInput, config, state);
                            agaTrack.trackSearch(config.form_id);
                        } else {
                            aga.showNoResults(dropdown, mainInput);
                        }
                    })
                    .catch(function (err) {
                        state.isFetching = false;
                        console.warn('Autocomplete Google Address: Suggestion fetch failed:', err);
                        aga.hideDropdown(dropdown);
                    });
            } else {
                // Legacy AutocompleteService fallback
                var legacyRequest = { input: query };

                if (config.component_restrictions && config.component_restrictions.country) {
                    legacyRequest.componentRestrictions = { country: config.component_restrictions.country };
                }

                if (config.place_types) {
                    legacyRequest.types = [config.place_types];
                }

                state.legacyService.getPlacePredictions(legacyRequest, function (predictions, status) {
                    state.isFetching = false;
                    if (state.fetchId !== currentFetchId) return; // stale fetch, discard
                    if (status === google.maps.places.PlacesServiceStatus.OK && predictions && predictions.length) {
                        // Wrap legacy predictions to match new API shape
                        var suggestions = predictions.map(function (p) {
                            return {
                                placePrediction: {
                                    text: { toString: function () { return p.description; } },
                                    placeId: p.place_id,
                                    _legacy: true,
                                    toPlace: function () { return null; }
                                }
                            };
                        });
                        aga.renderDropdown(suggestions, dropdown, mainInput, config, state);
                        agaTrack.trackSearch(config.form_id);
                    } else {
                        aga.showNoResults(dropdown, mainInput);
                    }
                });
            }
        },

        showLoading: function (dropdown, mainInput) {
            dropdown.innerHTML = '<li class="aga-autocomplete-status">Searching...</li>';
            aga.positionDropdown(dropdown, mainInput);
            dropdown.style.display = 'block';
        },

        showNoResults: function (dropdown, mainInput) {
            dropdown.innerHTML = '<li class="aga-autocomplete-status">No results found</li>';
            aga.positionDropdown(dropdown, mainInput);
            dropdown.style.display = 'block';
            setTimeout(function () {
                aga.hideDropdown(dropdown);
            }, 2000);
        },

        positionDropdown: function (dropdown, mainInput) {
            var inputRect = mainInput.getBoundingClientRect();
            var spaceBelow = window.innerHeight - inputRect.bottom;
            var dropdownHeight = 250;

            dropdown.style.left = mainInput.offsetLeft + 'px';
            dropdown.style.width = mainInput.offsetWidth + 'px';

            if (spaceBelow < dropdownHeight && inputRect.top > dropdownHeight) {
                // Show above the input
                dropdown.style.top = 'auto';
                dropdown.style.bottom = (mainInput.parentNode.offsetHeight - mainInput.offsetTop) + 'px';
                dropdown.classList.add('aga-dropdown-above');
            } else {
                // Show below the input
                dropdown.style.top = (mainInput.offsetTop + mainInput.offsetHeight) + 'px';
                dropdown.style.bottom = 'auto';
                dropdown.classList.remove('aga-dropdown-above');
            }
        },

        renderDropdown: function (suggestions, dropdown, mainInput, config, state) {
            dropdown.innerHTML = '';
            state.activeIndex = -1;

            suggestions.forEach(function (suggestion, index) {
                var prediction = suggestion.placePrediction;
                if (!prediction) return;

                var li = document.createElement('li');
                li.className = 'aga-autocomplete-item';
                li.setAttribute('role', 'option');
                li.textContent = prediction.text.toString();
                // iOS Safari fix: cursor pointer enables click events on dynamically created elements
                li.style.cursor = 'pointer';

                li.addEventListener('mouseenter', function () {
                    var items = dropdown.querySelectorAll('.aga-autocomplete-item');
                    aga.highlightItem(items, index);
                });

                // Use both click and touchend for iOS compatibility
                var selectHandler = function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    aga.selectPlace(prediction, mainInput, config, state);
                    aga.hideDropdown(dropdown);
                };
                li.addEventListener('click', selectHandler);
                li.addEventListener('touchend', selectHandler);

                dropdown.appendChild(li);
            });

            // Attribution (Google ToS requires attribution when using their API)
            var attribution = document.createElement('li');
            attribution.className = 'aga-autocomplete-attribution';
            if (typeof aga_frontend_data !== 'undefined' && aga_frontend_data.attribution_text) {
                attribution.textContent = aga_frontend_data.attribution_text;
            } else {
                attribution.innerHTML = '<img src="https://maps.gstatic.com/mapfiles/api-3/images/powered-by-google-on-white3_hdpi.png" alt="Powered by Google" height="14" />';
            }
            dropdown.appendChild(attribution);

            aga.positionDropdown(dropdown, mainInput);
            dropdown.style.display = 'block';
            mainInput.setAttribute('aria-expanded', 'true');
        },

        selectPlace: function (prediction, mainInput, config, state) {
            state.isSelecting = true;
            var selectionId = ++aga._selectionId;

            if (aga.useNewAPI && !prediction._legacy) {
                // New Places API
                var place = prediction.toPlace();
                var fields = ['formattedAddress', 'location', 'id', 'addressComponents'];
                if (config.mode !== 'smart_mapping') {
                    // addressComponents needed for analytics tracking even without smart_mapping
                }

                place.fetchFields({ fields: fields }).then(function () {
                    if (aga._selectionId !== selectionId) return; // stale selection, discard
                    mainInput.value = place.formattedAddress || '';
                    mainInput.dispatchEvent(new Event('change', { bubbles: true }));

                    if (aga.detectPOBox(mainInput.value)) {
                        aga.showPOBoxWarning(mainInput);
                    } else {
                        aga.removePOBoxWarning(mainInput);
                    }

                    aga.applyMapping(place, config);

                    if (config.map_picker && place.location) {
                        aga.centerMapPicker(place.location, config);
                    }

                    if (aga.useNewAPI) {
                        state.sessionToken = new google.maps.places.AutocompleteSessionToken();
                    }
                    setTimeout(function () { state.isSelecting = false; }, 100);

                    if (config.address_validation) {
                        aga.validateAddress(mainInput, place.formattedAddress || '', place.id || '');
                    }

                    // Save address for logged-in users (Pro)
                    if (config.saved_addresses && aga_frontend_data.is_logged_in) {
                        aga.saveAddress(place.formattedAddress || '', place.location, place.id || '', place.addressComponents || []);
                    }

                    // Analytics: track selection with country and city.
                    var _trackCountry = '', _trackCity = '';
                    if (place.addressComponents) {
                        place.addressComponents.forEach(function (c) {
                            if (c.types && c.types.indexOf('country') !== -1) _trackCountry = c.shortText || c.short_name || '';
                            if (c.types && c.types.indexOf('locality') !== -1) _trackCity = c.longText || c.long_name || '';
                        });
                    }
                    agaTrack.trackSelection(config.form_id, _trackCountry, _trackCity);
                });
            } else {
                // Legacy PlacesService fallback
                var placeId = prediction.placeId;
                var tempDiv = document.createElement('div');
                var service = new google.maps.places.PlacesService(tempDiv);

                var detailFields = ['formatted_address', 'geometry', 'place_id', 'address_components'];

                service.getDetails({ placeId: placeId, fields: detailFields }, function (result, status) {
                    if (aga._selectionId !== selectionId) return; // stale selection, discard
                    if (status === google.maps.places.PlacesServiceStatus.OK && result) {
                        mainInput.value = result.formatted_address || '';
                        mainInput.dispatchEvent(new Event('change', { bubbles: true }));

                        if (aga.detectPOBox(mainInput.value)) {
                            aga.showPOBoxWarning(mainInput);
                        } else {
                            aga.removePOBoxWarning(mainInput);
                        }

                        // Convert legacy result to new API shape for applyMapping
                        var placeObj = {
                            formattedAddress: result.formatted_address,
                            location: result.geometry ? result.geometry.location : null,
                            id: result.place_id,
                            addressComponents: result.address_components ? result.address_components.map(function (c) {
                                return {
                                    types: c.types,
                                    longText: c.long_name,
                                    shortText: c.short_name
                                };
                            }) : []
                        };

                        aga.applyMapping(placeObj, config);

                        if (config.map_picker && placeObj.location) {
                            aga.centerMapPicker(placeObj.location, config);
                        }

                        if (config.address_validation) {
                            aga.validateAddress(mainInput, placeObj.formattedAddress || '', placeObj.id || '');
                        }

                        // Save address for logged-in users (Pro)
                        if (config.saved_addresses && aga_frontend_data.is_logged_in) {
                            var loc = placeObj.location;
                            aga.saveAddress(placeObj.formattedAddress || '', loc, placeObj.id || '', placeObj.addressComponents || []);
                        }

                        // Analytics: track selection with country and city.
                        var _tCountry = '', _tCity = '';
                        if (placeObj.addressComponents) {
                            placeObj.addressComponents.forEach(function (c) {
                                if (c.types && c.types.indexOf('country') !== -1) _tCountry = c.shortText || c.short_name || '';
                                if (c.types && c.types.indexOf('locality') !== -1) _tCity = c.longText || c.long_name || '';
                            });
                        }
                        agaTrack.trackSelection(config.form_id, _tCountry, _tCity);
                    }

                    setTimeout(function () { state.isSelecting = false; }, 100);
                });
            }
        },

        detectPOBox: function (address) {
            if (!address) return false;
            var patterns = [
                /\bP\.?\s*O\.?\s*Box\b/i,
                /\bPost\s*Office\s*Box\b/i,
                /\bPOB\b/i,
                /\bAPO\b/i,
                /\bFPO\b/i,
                /\bDPO\b/i,
                /\bGeneral\s*Delivery\b/i
            ];
            return patterns.some(function(p) { return p.test(address); });
        },

        showPOBoxWarning: function (mainInput) {
            var existing = mainInput.parentNode.querySelector('.aga-pobox-warning');
            if (existing) return; // already showing

            var warning = document.createElement('div');
            warning.className = 'aga-pobox-warning';
            warning.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-2px;margin-right:4px;"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>This appears to be a PO Box or military address. Some carriers cannot deliver to PO Boxes.';
            mainInput.parentNode.appendChild(warning);
        },

        removePOBoxWarning: function (mainInput) {
            var existing = mainInput.parentNode.querySelector('.aga-pobox-warning');
            if (existing) existing.remove();
        },

        validateAddress: function (mainInput, address, placeId) {
            if (!address || typeof aga_frontend_data === 'undefined') return;

            var badge = aga.getOrCreateValidationBadge(mainInput);
            badge.className = 'aga-validation-badge aga-validation-loading';
            badge.textContent = '';
            badge.title = '';

            $.ajax({
                url: aga_frontend_data.ajax_url,
                type: 'POST',
                data: {
                    action: 'aga_validate_address',
                    nonce: aga_frontend_data.nonce,
                    address: address,
                    place_id: placeId
                },
                success: function (response) {
                    if (response.success && response.data) {
                        var level = response.data.level;
                        var message = response.data.message;
                        var symbols = {
                            valid: '\u2713',
                            warning: '\u26A0',
                            invalid: '\u2717'
                        };
                        var labels = {
                            valid: 'Verified',
                            warning: 'Partial match',
                            invalid: 'Not verified'
                        };
                        badge.className = 'aga-validation-badge aga-validation-' + level + ' aga-validation-visible';
                        badge.innerHTML = '<span>' + (symbols[level] || '') + '</span> ' + (labels[level] || message);
                        badge.title = message;
                    } else {
                        badge.className = 'aga-validation-badge aga-validation-invalid aga-validation-visible';
                        badge.innerHTML = '<span>\u2717</span> Not verified';
                        badge.title = (response.data && response.data.message) || 'Validation failed.';
                    }
                },
                error: function () {
                    badge.className = 'aga-validation-badge aga-validation-invalid aga-validation-visible';
                    badge.innerHTML = '<span>\u2717</span> Not verified';
                    badge.title = 'Validation request failed.';
                }
            });
        },

        getOrCreateValidationBadge: function (mainInput) {
            var existing = mainInput.parentNode.querySelector('.aga-validation-badge');
            if (existing) return existing;

            var badge = document.createElement('span');
            badge.className = 'aga-validation-badge';
            mainInput.parentNode.insertBefore(badge, mainInput.nextSibling);
            return badge;
        },

        /**
         * Center the map picker on a location after autocomplete selection.
         */
        centerMapPicker: function (location, config) {
            if (!config._mapPicker) return;
            var lat = typeof location.lat === 'function' ? location.lat() : parseFloat(location.lat);
            var lng = typeof location.lng === 'function' ? location.lng() : parseFloat(location.lng);
            config._mapPicker.centerOn({ lat: lat, lng: lng });
        },

        reverseGeocode: function (latLng, mainInput, config) {
            var lat = typeof latLng.lat === 'function' ? latLng.lat() : latLng.lat;
            var lng = typeof latLng.lng === 'function' ? latLng.lng() : latLng.lng;
            var location = { lat: lat, lng: lng };

            // Suppress autocomplete dropdown while setting value from map
            mainInput.setAttribute('data-aga-suppress', '1');

            // Try Geocoding API first (requires Geocoding API enabled)
            var geocoder = new google.maps.Geocoder();
            geocoder.geocode({ location: location }, function (results, status) {
                if (status === 'OK' && results[0]) {
                    var result = results[0];
                    mainInput.value = result.formatted_address || '';
                    mainInput.dispatchEvent(new Event('change', { bubbles: true }));

                    if (aga.detectPOBox(mainInput.value)) {
                        aga.showPOBoxWarning(mainInput);
                    } else {
                        aga.removePOBoxWarning(mainInput);
                    }

                    // Update lat/lng with exact drop position
                    aga.updateLatLngFields(location, config);

                    if (config.selectors.place_id) {
                        aga.setFieldValue(config.selectors.place_id, result.place_id);
                    }

                    // Update smart mapping fields
                    if (config.mode === 'smart_mapping' && result.address_components) {
                        var components = aga.parseReverseComponents(result.address_components);
                        aga.applyParsedComponents(components, config);
                    }

                    // Re-validate after drag
                    if (config.address_validation) {
                        aga.validateAddress(mainInput, result.formatted_address || '', result.place_id || '');
                    }

                    setTimeout(function () { mainInput.removeAttribute('data-aga-suppress'); }, 300);
                } else {
                    // Geocoding API not enabled — just update coordinates
                    console.warn('Autocomplete Google Address: Geocoding failed (' + status + '). Enable the Geocoding API in Google Cloud Console for drag-to-update.');
                    aga.updateLatLngFields(location, config);
                    mainInput.value = lat.toFixed(6) + ', ' + lng.toFixed(6);
                    mainInput.dispatchEvent(new Event('change', { bubbles: true }));
                    setTimeout(function () { mainInput.removeAttribute('data-aga-suppress'); }, 300);
                }
            });
        },

        parseReverseComponents: function (components) {
            var parsed = {
                street_number: '', route: '', locality: '', sublocality: '',
                administrative_area_level_1_long: '', administrative_area_level_1_short: '',
                administrative_area_level_2: '',
                country_long: '', country_short: '', postal_code: ''
            };
            components.forEach(function (c) {
                (c.types || []).forEach(function (type) {
                    switch (type) {
                        case 'street_number': parsed.street_number = c.long_name; break;
                        case 'route': parsed.route = c.long_name; break;
                        case 'locality': parsed.locality = c.long_name; break;
                        case 'sublocality_level_1':
                        case 'sublocality':
                            if (!parsed.locality) parsed.sublocality = c.long_name; break;
                        case 'administrative_area_level_1':
                            parsed.administrative_area_level_1_long = c.long_name;
                            parsed.administrative_area_level_1_short = c.short_name; break;
                        case 'administrative_area_level_2':
                            parsed.administrative_area_level_2 = c.long_name; break;
                        case 'country':
                            parsed.country_long = c.long_name;
                            parsed.country_short = c.short_name; break;
                        case 'postal_code': parsed.postal_code = c.long_name; break;
                    }
                });
            });
            return parsed;
        },

        applyParsedComponents: function (components, config) {
            if (config.selectors.country) {
                var countryPrimary = (config.formats && config.formats.country === 'short') ? components.country_short : components.country_long;
                var countryAlt = (config.formats && config.formats.country === 'short') ? components.country_long : components.country_short;
                aga.setFieldValue(config.selectors.country, countryPrimary, countryAlt);
            }
            if (config.selectors.street) {
                aga.setFieldValue(config.selectors.street, (components.street_number + ' ' + components.route).trim());
            }
            if (config.selectors.city) {
                aga.setFieldValue(config.selectors.city, components.locality || components.sublocality || components.administrative_area_level_2 || '');
            }

            var statePrimary = (config.formats && config.formats.state === 'short') ? components.administrative_area_level_1_short : components.administrative_area_level_1_long;
            var stateAlt = (config.formats && config.formats.state === 'short') ? components.administrative_area_level_1_long : components.administrative_area_level_1_short;
            var zipValue = components.postal_code;

            setTimeout(function () {
                if (config.selectors.state) {
                    aga.setFieldValue(config.selectors.state, statePrimary, stateAlt);
                }
                if (config.selectors.zip) {
                    aga.setFieldValue(config.selectors.zip, zipValue);
                }
            }, 500);
        },

        updateLatLngFields: function (latLng, config) {
            var lat = typeof latLng.lat === 'function' ? latLng.lat() : latLng.lat;
            var lng = typeof latLng.lng === 'function' ? latLng.lng() : latLng.lng;
            if (config.selectors.lat) {
                aga.setFieldValue(config.selectors.lat, lat);
            }
            if (config.selectors.lng) {
                aga.setFieldValue(config.selectors.lng, lng);
            }
        },

        highlightItem: function (items, index) {
            items.forEach(function (item, i) {
                item.classList.toggle('aga-autocomplete-item--active', i === index);
                item.setAttribute('aria-selected', i === index ? 'true' : 'false');
            });
        },

        hideDropdown: function (dropdown) {
            dropdown.style.display = 'none';
            dropdown.innerHTML = '';
            // Update aria-expanded on the associated input
            var wrapper = dropdown.parentNode;
            if (wrapper) {
                var input = wrapper.querySelector('[role="combobox"]');
                if (input) {
                    input.setAttribute('aria-expanded', 'false');
                }
            }
        },

        applyMapping: function (place, config) {
            if (config.selectors.lat && place.location) {
                aga.setFieldValue(config.selectors.lat, place.location.lat());
            }
            if (config.selectors.lng && place.location) {
                aga.setFieldValue(config.selectors.lng, place.location.lng());
            }
            if (config.selectors.place_id) {
                aga.setFieldValue(config.selectors.place_id, place.id);
            }

            if (config.mode === 'smart_mapping' && place.addressComponents) {
                var components = aga.parseAddressComponents(place.addressComponents);
                var countryCode = components.country_short || '';

                // Set country FIRST — frameworks like WooCommerce re-render
                // state/postcode fields when country changes.
                if (config.selectors.country) {
                    var fmt = config.formats || {};
                    var countryPrimary = (fmt.country === 'short') ? components.country_short : components.country_long;
                    var countryAlt = (fmt.country === 'short') ? components.country_long : components.country_short;
                    aga.setFieldValue(config.selectors.country, countryPrimary, countryAlt);
                }

                if (config.selectors.street) {
                    aga.setFieldValue(config.selectors.street, (components.street_number + ' ' + components.route).trim());
                }

                // Smart country-aware city mapping
                if (config.selectors.city) {
                    var smartCity = aga.getSmartCity(components, countryCode);
                    aga.setFieldValue(config.selectors.city, smartCity);
                }

                // Smart country-aware state mapping
                var smartState = aga.getSmartState(components, countryCode, (config.formats || {}).state);
                var zipValue = components.postal_code;

                // Delay state and postcode to allow framework re-render after country change.
                setTimeout(function () {
                    if (config.selectors.state) {
                        aga.setFieldValue(config.selectors.state, smartState.primary, smartState.alt);
                    }
                    if (config.selectors.zip) {
                        aga.setFieldValue(config.selectors.zip, zipValue);
                    }
                }, 500);
            }
        },

        _warnedSelectors: {},

        setFieldValue: function (selector, value, altValue) {
            if (!selector || value === undefined) return;
            var field = document.querySelector(selector);
            if (!field) {
                if (!aga._warnedSelectors[selector]) {
                    aga._warnedSelectors[selector] = true;
                    console.warn('Autocomplete Google Address: Field not found for selector:', selector, '— check your form config.');
                }
                return;
            }

            var finalValue = value;

            // Smart matching for <select> elements (country/state/district dropdowns).
            // Try multiple strategies to find the right option.
            if (field.tagName === 'SELECT') {
                finalValue = aga.findSelectMatch(field, value, altValue) || value;
            }

            // For React-controlled inputs (e.g. WooCommerce block checkout),
            // we must use the native setter to trigger React's change detection.
            var nativeInputValueSetter = Object.getOwnPropertyDescriptor(
                window.HTMLInputElement.prototype, 'value'
            );
            var nativeSelectValueSetter = Object.getOwnPropertyDescriptor(
                window.HTMLSelectElement.prototype, 'value'
            );
            var nativeTextareaValueSetter = Object.getOwnPropertyDescriptor(
                window.HTMLTextAreaElement.prototype, 'value'
            );

            if (field.tagName === 'SELECT' && nativeSelectValueSetter) {
                nativeSelectValueSetter.set.call(field, finalValue);
            } else if (field.tagName === 'TEXTAREA' && nativeTextareaValueSetter) {
                nativeTextareaValueSetter.set.call(field, finalValue);
            } else if (nativeInputValueSetter) {
                nativeInputValueSetter.set.call(field, finalValue);
            } else {
                field.value = finalValue;
            }

            // Dispatch events that both React and vanilla JS listeners pick up.
            field.dispatchEvent(new Event('input', { bubbles: true }));
            field.dispatchEvent(new Event('change', { bubbles: true }));
        },

        /**
         * Smart-match a value against <select> options.
         * Tries: exact value, exact text, case-insensitive, partial text match, alt value.
         * Works for all countries — handles state codes, district names, provinces, etc.
         */
        findSelectMatch: function (select, value, altValue) {
            if (!value && !altValue) return null;
            var options = select.options;
            var valueLower = value ? value.toLowerCase().trim() : '';
            var altLower = altValue ? altValue.toLowerCase().trim() : '';

            // 1. Exact match on option value
            for (var i = 0; i < options.length; i++) {
                if (options[i].value === value) return options[i].value;
            }

            // 2. Exact match on alt value (e.g. short code vs long name)
            if (altValue) {
                for (var i = 0; i < options.length; i++) {
                    if (options[i].value === altValue) return options[i].value;
                }
            }

            // 3. Case-insensitive match on option value
            for (var i = 0; i < options.length; i++) {
                if (options[i].value.toLowerCase() === valueLower) return options[i].value;
                if (altLower && options[i].value.toLowerCase() === altLower) return options[i].value;
            }

            // 4. Exact match on option text
            for (var i = 0; i < options.length; i++) {
                var optText = options[i].text.toLowerCase().trim();
                if (optText === valueLower) return options[i].value;
                if (altLower && optText === altLower) return options[i].value;
            }

            // 5. Partial match — option text contains value or vice versa
            //    e.g. "Dhaka" matches "Dhaka Division" or "Dhaka District"
            for (var i = 0; i < options.length; i++) {
                var optText = options[i].text.toLowerCase().trim();
                if (!optText || !options[i].value) continue;
                if (valueLower && (optText.indexOf(valueLower) !== -1 || valueLower.indexOf(optText) !== -1)) {
                    return options[i].value;
                }
                if (altLower && (optText.indexOf(altLower) !== -1 || altLower.indexOf(optText) !== -1)) {
                    return options[i].value;
                }
            }

            // 6. No match found — return null (the raw value will be used as fallback)
            return null;
        },

        // ---- Saved Addresses (Pro) ----

        /**
         * Cached saved addresses per form to avoid repeated AJAX calls.
         * Keyed by config.form_id.
         */
        _savedAddressesCache: {},

        setupSavedAddresses: function (mainInput, dropdown, config, state) {
            mainInput.addEventListener('focus', function () {
                if (mainInput.value.trim() !== '') return;
                if (state.isFetching) return;

                var formId = config.form_id || 'default';

                // If already cached, show immediately.
                if (aga._savedAddressesCache[formId]) {
                    aga.renderSavedAddresses(aga._savedAddressesCache[formId], dropdown, mainInput, config, state);
                    return;
                }

                // Fetch from server (once per page load per form).
                $.ajax({
                    url: aga_frontend_data.ajax_url,
                    type: 'POST',
                    data: {
                        action: 'aga_get_addresses',
                        nonce: aga_frontend_data.nonce
                    },
                    success: function (response) {
                        if (response.success && response.data && response.data.addresses && response.data.addresses.length) {
                            aga._savedAddressesCache[formId] = response.data.addresses;
                            // Only show if input is still focused and empty.
                            if (document.activeElement === mainInput && mainInput.value.trim() === '') {
                                aga.renderSavedAddresses(response.data.addresses, dropdown, mainInput, config, state);
                            }
                        }
                    }
                });
            });
        },

        renderSavedAddresses: function (addresses, dropdown, mainInput, config, state) {
            if (!addresses || !addresses.length) return;

            dropdown.innerHTML = '';
            state.activeIndex = -1;

            // Header
            var header = document.createElement('li');
            header.className = 'aga-saved-header';
            header.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="vertical-align:-1px;margin-right:4px;"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>Recent Addresses';
            dropdown.appendChild(header);

            addresses.forEach(function (entry, index) {
                var li = document.createElement('li');
                li.className = 'aga-autocomplete-item aga-saved-item';
                li.textContent = entry.address;
                li.style.cursor = 'pointer';

                li.addEventListener('mouseenter', function () {
                    var items = dropdown.querySelectorAll('.aga-autocomplete-item');
                    aga.highlightItem(items, index);
                });

                var savedSelectHandler = function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    state.isSelecting = true;
                    mainInput.value = entry.address;
                    mainInput.dispatchEvent(new Event('change', { bubbles: true }));

                    var location = null;
                    if (entry.lat && entry.lng) {
                        location = {
                            lat: function () { return parseFloat(entry.lat); },
                            lng: function () { return parseFloat(entry.lng); }
                        };
                    }

                    var placeObj = {
                        formattedAddress: entry.address,
                        location: location,
                        id: entry.place_id || '',
                        addressComponents: entry.components || []
                    };

                    aga.applyMapping(placeObj, config);

                    if (config.map_picker && location) {
                        aga.centerMapPicker(location, config);
                    }

                    if (config.address_validation && entry.address) {
                        aga.validateAddress(mainInput, entry.address, entry.place_id || '');
                    }

                    aga.hideDropdown(dropdown);
                    setTimeout(function () { state.isSelecting = false; }, 100);
                };
                li.addEventListener('click', savedSelectHandler);
                li.addEventListener('touchend', savedSelectHandler);

                dropdown.appendChild(li);
            });

            // Divider
            var divider = document.createElement('li');
            divider.className = 'aga-saved-divider';
            dropdown.appendChild(divider);

            aga.positionDropdown(dropdown, mainInput);
            dropdown.style.display = 'block';
        },

        saveAddress: function (address, location, placeId, addressComponents) {
            if (!address || typeof aga_frontend_data === 'undefined') return;

            var lat = 0, lng = 0;
            if (location) {
                lat = typeof location.lat === 'function' ? location.lat() : (location.lat || 0);
                lng = typeof location.lng === 'function' ? location.lng() : (location.lng || 0);
            }

            // Normalize components to simple objects for storage.
            var components = [];
            if (addressComponents && addressComponents.length) {
                components = addressComponents.map(function (c) {
                    return {
                        types: c.types || [],
                        longText: c.longText || c.long_name || '',
                        shortText: c.shortText || c.short_name || ''
                    };
                });
            }

            // Fire-and-forget AJAX.
            $.ajax({
                url: aga_frontend_data.ajax_url,
                type: 'POST',
                data: {
                    action: 'aga_save_address',
                    nonce: aga_frontend_data.nonce,
                    address: address,
                    lat: lat,
                    lng: lng,
                    place_id: placeId,
                    components: components
                }
            });

            // Invalidate cached addresses so the next focus fetches fresh data.
            aga._savedAddressesCache = {};
        },

        // ---- End Saved Addresses ----

        /**
         * Country-aware mapping rules.
         * Defines which Google address component types map to "city" and "state"
         * for each country. Falls back to a sensible default chain.
         *
         * Each entry has:
         *   city:  ordered array of component types to try for the "city" field
         *   state: ordered array of component types to try for the "state" field
         */
        countryMappingRules: {
            // Default fallback chain (used when country not in this list)
            '_default': {
                city:  ['locality', 'postal_town', 'sublocality_level_1', 'sublocality', 'administrative_area_level_2', 'administrative_area_level_3'],
                state: ['administrative_area_level_1']
            },
            // Bangladesh — city=district (admin_level_2), state=division (admin_level_1)
            'BD': {
                city:  ['administrative_area_level_2', 'locality', 'sublocality_level_1'],
                state: ['administrative_area_level_1']
            },
            // UK — city=postal_town, state=admin_level_2 (county)
            'GB': {
                city:  ['postal_town', 'locality', 'sublocality'],
                state: ['administrative_area_level_2', 'administrative_area_level_1']
            },
            // Brazil — city=admin_level_2, state=admin_level_1
            'BR': {
                city:  ['administrative_area_level_2', 'locality'],
                state: ['administrative_area_level_1']
            },
            // Japan — city=locality, state=admin_level_1 (prefecture)
            'JP': {
                city:  ['locality', 'sublocality_level_1', 'administrative_area_level_2'],
                state: ['administrative_area_level_1']
            },
            // South Korea — similar to Japan
            'KR': {
                city:  ['locality', 'sublocality_level_1', 'administrative_area_level_2'],
                state: ['administrative_area_level_1']
            },
            // India — city=locality, but fallback to admin_level_2 (district)
            'IN': {
                city:  ['locality', 'administrative_area_level_2', 'sublocality_level_1'],
                state: ['administrative_area_level_1']
            },
            // China — city=locality or admin_level_2 (prefecture-level city)
            'CN': {
                city:  ['locality', 'administrative_area_level_2', 'sublocality'],
                state: ['administrative_area_level_1']
            },
            // UAE — city=locality, state=admin_level_1 (emirate)
            'AE': {
                city:  ['locality', 'sublocality_level_1', 'administrative_area_level_2'],
                state: ['administrative_area_level_1']
            },
            // Saudi Arabia
            'SA': {
                city:  ['locality', 'administrative_area_level_2'],
                state: ['administrative_area_level_1']
            },
            // Nigeria — city=locality, state=admin_level_1
            'NG': {
                city:  ['locality', 'administrative_area_level_2', 'sublocality'],
                state: ['administrative_area_level_1']
            },
            // Indonesia
            'ID': {
                city:  ['administrative_area_level_2', 'locality'],
                state: ['administrative_area_level_1']
            },
            // Philippines
            'PH': {
                city:  ['locality', 'administrative_area_level_2'],
                state: ['administrative_area_level_1']
            },
            // Colombia
            'CO': {
                city:  ['locality', 'administrative_area_level_2'],
                state: ['administrative_area_level_1']
            },
            // Mexico
            'MX': {
                city:  ['locality', 'administrative_area_level_2', 'sublocality'],
                state: ['administrative_area_level_1']
            },
            // Pakistan — city=locality or admin_level_2 (district)
            'PK': {
                city:  ['locality', 'administrative_area_level_2', 'sublocality_level_1'],
                state: ['administrative_area_level_1']
            },
            // Sri Lanka
            'LK': {
                city:  ['locality', 'administrative_area_level_2'],
                state: ['administrative_area_level_1']
            },
            // Nepal
            'NP': {
                city:  ['locality', 'administrative_area_level_2'],
                state: ['administrative_area_level_1']
            },
            // Thailand
            'TH': {
                city:  ['sublocality_level_1', 'locality', 'administrative_area_level_2'],
                state: ['administrative_area_level_1']
            },
            // Vietnam
            'VN': {
                city:  ['administrative_area_level_2', 'locality'],
                state: ['administrative_area_level_1']
            },
            // Egypt
            'EG': {
                city:  ['locality', 'administrative_area_level_2'],
                state: ['administrative_area_level_1']
            },
            // Turkey
            'TR': {
                city:  ['administrative_area_level_2', 'locality'],
                state: ['administrative_area_level_1']
            },
            // Germany — city=locality, state=admin_level_1 (Bundesland)
            'DE': {
                city:  ['locality', 'sublocality'],
                state: ['administrative_area_level_1']
            },
            // France — city=locality, no states (use region)
            'FR': {
                city:  ['locality', 'sublocality'],
                state: ['administrative_area_level_1']
            },
            // Italy
            'IT': {
                city:  ['locality', 'administrative_area_level_3'],
                state: ['administrative_area_level_2', 'administrative_area_level_1']
            },
            // Spain
            'ES': {
                city:  ['locality', 'administrative_area_level_4', 'administrative_area_level_3'],
                state: ['administrative_area_level_2', 'administrative_area_level_1']
            },
            // Netherlands
            'NL': {
                city:  ['locality', 'sublocality'],
                state: ['administrative_area_level_1']
            },
            // Australia
            'AU': {
                city:  ['locality', 'sublocality'],
                state: ['administrative_area_level_1']
            },
            // Canada
            'CA': {
                city:  ['locality', 'sublocality'],
                state: ['administrative_area_level_1']
            },
            // US
            'US': {
                city:  ['locality', 'sublocality', 'administrative_area_level_3'],
                state: ['administrative_area_level_1']
            }
        },

        /**
         * Get the smart city value based on country-aware rules.
         */
        getSmartCity: function (parsed, countryCode) {
            var rules = aga.countryMappingRules[countryCode] || aga.countryMappingRules['_default'];
            for (var i = 0; i < rules.city.length; i++) {
                var key = rules.city[i];
                // Map the component type to parsed keys
                var val = aga._getParsedValue(parsed, key, 'long');
                if (val) return val;
            }
            return '';
        },

        /**
         * Get the smart state value based on country-aware rules.
         */
        getSmartState: function (parsed, countryCode, format) {
            var rules = aga.countryMappingRules[countryCode] || aga.countryMappingRules['_default'];
            var suffix = (format === 'short') ? 'short' : 'long';
            var altSuffix = (format === 'short') ? 'long' : 'short';

            for (var i = 0; i < rules.state.length; i++) {
                var key = rules.state[i];
                var val = aga._getParsedValue(parsed, key, suffix);
                if (val) return { primary: val, alt: aga._getParsedValue(parsed, key, altSuffix) || val };
            }
            return { primary: '', alt: '' };
        },

        /**
         * Get a parsed value by component type and format.
         */
        _getParsedValue: function (parsed, componentType, format) {
            // Map component types to parsed object keys
            var map = {
                'locality':                       { long: 'locality',                          short: 'locality' },
                'postal_town':                    { long: 'postal_town',                       short: 'postal_town' },
                'sublocality':                    { long: 'sublocality',                        short: 'sublocality' },
                'sublocality_level_1':            { long: 'sublocality',                        short: 'sublocality' },
                'administrative_area_level_1':    { long: 'administrative_area_level_1_long',   short: 'administrative_area_level_1_short' },
                'administrative_area_level_2':    { long: 'administrative_area_level_2_long',   short: 'administrative_area_level_2_short' },
                'administrative_area_level_3':    { long: 'administrative_area_level_3',        short: 'administrative_area_level_3' },
                'administrative_area_level_4':    { long: 'administrative_area_level_4',        short: 'administrative_area_level_4' }
            };
            var entry = map[componentType];
            if (!entry) return '';
            return parsed[entry[format]] || '';
        },

        parseAddressComponents: function (components) {
            var parsed = {
                street_number: '',
                route: '',
                locality: '',
                postal_town: '',
                sublocality: '',
                administrative_area_level_1_long: '',
                administrative_area_level_1_short: '',
                administrative_area_level_2_long: '',
                administrative_area_level_2_short: '',
                administrative_area_level_3: '',
                administrative_area_level_4: '',
                country_long: '',
                country_short: '',
                postal_code: ''
            };
            components.forEach(function (component) {
                var types = component.types || [];
                types.forEach(function (type) {
                    switch (type) {
                        case 'street_number':
                            parsed.street_number = component.longText || component.long_name || '';
                            break;
                        case 'route':
                            parsed.route = component.longText || component.long_name || '';
                            break;
                        case 'locality':
                            parsed.locality = component.longText || component.long_name || '';
                            break;
                        case 'postal_town':
                            parsed.postal_town = component.longText || component.long_name || '';
                            break;
                        case 'sublocality_level_1':
                        case 'sublocality':
                            parsed.sublocality = component.longText || component.long_name || '';
                            break;
                        case 'administrative_area_level_1':
                            parsed.administrative_area_level_1_long = component.longText || component.long_name || '';
                            parsed.administrative_area_level_1_short = component.shortText || component.short_name || '';
                            break;
                        case 'administrative_area_level_2':
                            parsed.administrative_area_level_2_long = component.longText || component.long_name || '';
                            parsed.administrative_area_level_2_short = component.shortText || component.short_name || '';
                            break;
                        case 'administrative_area_level_3':
                            parsed.administrative_area_level_3 = component.longText || component.long_name || '';
                            break;
                        case 'administrative_area_level_4':
                            parsed.administrative_area_level_4 = component.longText || component.long_name || '';
                            break;
                        case 'country':
                            parsed.country_long = component.longText || component.long_name || '';
                            parsed.country_short = component.shortText || component.short_name || '';
                            break;
                        case 'postal_code':
                            parsed.postal_code = component.longText || component.long_name || '';
                            break;
                    }
                });
            });
            return parsed;
        }
    };

    // Scan for data-aga-config attributes on inputs and script tags
    // (used by Elementor form fields and widgets) and merge into aga_form_configs.
    function scanDataConfigs() {
        var elements = document.querySelectorAll('[data-aga-config]');
        if (!elements.length) return;
        window.aga_form_configs = window.aga_form_configs || [];
        elements.forEach(function (el) {
            try {
                var cfg = JSON.parse(el.getAttribute('data-aga-config'));
                var exists = window.aga_form_configs.some(function (c) {
                    return c.form_id === cfg.form_id;
                });
                if (!exists) {
                    window.aga_form_configs.push(cfg);
                }
            } catch (e) {}
        });
    }

    // Expose a global reinit function for dynamic content (e.g. WooCommerce checkout).
    // This safely re-runs setup on all configs, skipping already-initialized inputs.
    window.aga_reinit = function () {
        scanDataConfigs();
        if (typeof window.aga_form_configs !== 'undefined' && aga._apiReady) {
            aga.run();
        }
    };

    // Track whether the Google Maps API is ready.
    aga._apiReady = false;
    var _originalRun = aga.run;
    aga.run = function () {
        aga._apiReady = true;
        _originalRun.call(aga);
    };

    $(function () {
        scanDataConfigs();
        aga.init();
    });

})(jQuery);
