(function ($) {
    'use strict';

    var VST = {
        activeInput: null,
        modal: null,
        iframe: null,
        selectedSelector: null,
        currentPageUrl: null,
        searchTimer: null,
        currentPage: 1,
        activeFilter: 'all',

        init: function () {
            this.createModal();
            this.bindPickButtons();
        },

        createModal: function () {
            var html =
                '<div id="aga-vst-modal" class="aga-vst-modal" style="display:none;">' +
                    '<div class="aga-vst-modal-content">' +
                        '<div class="aga-vst-header">' +
                            '<div class="aga-vst-header-left">' +
                                '<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="22" y1="12" x2="18" y2="12"/><line x1="6" y1="12" x2="2" y2="12"/><line x1="12" y1="6" x2="12" y2="2"/><line x1="12" y1="22" x2="12" y2="18"/></svg>' +
                                '<h3>Visual Selector Tool</h3>' +
                            '</div>' +
                            '<div class="aga-vst-header-center">' +
                                '<div class="aga-vst-filter-tabs">' +
                                    '<button type="button" class="aga-vst-filter active" data-filter="all">All</button>' +
                                    '<button type="button" class="aga-vst-filter" data-filter="forms">Forms Only</button>' +
                                '</div>' +
                                '<div class="aga-vst-page-search-wrap">' +
                                    '<svg class="aga-vst-search-icon" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>' +
                                    '<input type="text" id="aga-vst-page-search" placeholder="Search pages & posts..." autocomplete="off" />' +
                                    '<div id="aga-vst-page-dropdown" class="aga-vst-page-dropdown" style="display:none;"></div>' +
                                '</div>' +
                                '<button type="button" id="aga-vst-reload" class="button button-small" title="Reload current page">' +
                                    '<span class="dashicons dashicons-update"></span>' +
                                '</button>' +
                            '</div>' +
                            '<div class="aga-vst-header-right">' +
                                '<span id="aga-vst-page-info" class="aga-vst-page-info"></span>' +
                                '<button type="button" id="aga-vst-close" class="aga-vst-close" title="Close">&times;</button>' +
                            '</div>' +
                        '</div>' +
                        '<div class="aga-vst-instructions">' +
                            '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>' +
                            'Hover over any form field and click to select it. Then choose a selector format below.' +
                        '</div>' +
                        '<div class="aga-vst-body">' +
                            '<iframe id="aga-vst-iframe" sandbox="allow-same-origin allow-scripts allow-forms"></iframe>' +
                            '<div id="aga-vst-loading" class="aga-vst-loading">' +
                                '<span class="spinner is-active"></span>' +
                                '<p>Loading page...</p>' +
                            '</div>' +
                        '</div>' +
                        '<div class="aga-vst-selector-panel" id="aga-vst-selector-panel" style="display:none;">' +
                            '<div class="aga-vst-panel-header">' +
                                '<span class="aga-vst-panel-title">Choose a selector:</span>' +
                                '<span class="aga-vst-panel-element" id="aga-vst-element-info"></span>' +
                            '</div>' +
                            '<div class="aga-vst-options" id="aga-vst-options"></div>' +
                        '</div>' +
                        '<div class="aga-vst-footer">' +
                            '<div class="aga-vst-footer-left">' +
                                '<span id="aga-vst-status">Select a form field on the page</span>' +
                            '</div>' +
                            '<div class="aga-vst-footer-right">' +
                                '<button type="button" id="aga-vst-cancel" class="button">Cancel</button>' +
                                '<button type="button" id="aga-vst-confirm" class="button button-primary" disabled>Use This Selector</button>' +
                            '</div>' +
                        '</div>' +
                    '</div>' +
                '</div>';

            $('body').append(html);

            this.modal = $('#aga-vst-modal');
            this.iframe = document.getElementById('aga-vst-iframe');

            var self = this;

            $('#aga-vst-close, #aga-vst-cancel').on('click', function () {
                self.close();
            });

            $('#aga-vst-confirm').on('click', function () {
                self.confirmSelection();
            });

            $('#aga-vst-reload').on('click', function () {
                self.loadPage(self.currentPageUrl);
            });

            $(document).on('keydown.vst', function (e) {
                if (e.key === 'Escape' && self.modal.is(':visible')) {
                    var $dd = $('#aga-vst-page-dropdown');
                    if ($dd.is(':visible')) {
                        $dd.hide();
                    } else {
                        self.close();
                    }
                }
            });

            // Page search input
            var $search = $('#aga-vst-page-search');
            $search.on('input', function () {
                var q = $(this).val().trim();
                if (self.searchTimer) clearTimeout(self.searchTimer);
                self.searchTimer = setTimeout(function () {
                    self.currentPage = 1;
                    self.searchPages(q);
                }, 300);
            });

            $search.on('focus', function () {
                self.currentPage = 1;
                self.searchPages($(this).val().trim());
            });

            // Filter tabs (All / Forms Only)
            $(document).on('click', '.aga-vst-filter', function () {
                $('.aga-vst-filter').removeClass('active');
                $(this).addClass('active');
                self.activeFilter = $(this).data('filter');
                self.currentPage = 1;
                self.searchPages($('#aga-vst-page-search').val().trim());
            });

            // Close dropdown when clicking outside
            $(document).on('mousedown.vst', function (e) {
                if (!$(e.target).closest('.aga-vst-page-search-wrap').length && !$(e.target).closest('.aga-vst-filter-tabs').length) {
                    $('#aga-vst-page-dropdown').hide();
                }
            });

            window.addEventListener('message', function (e) {
                self.handleMessage(e);
            });
        },

        bindPickButtons: function () {
            var self = this;
            $(document).on('click', '.aga-vst-pick-btn', function (e) {
                e.preventDefault();
                var $input = $(this).closest('.aga-field-group').find('input[type="text"]');
                if ($input.length) {
                    self.open($input);
                }
            });
        },

        open: function ($input) {
            this.activeInput = $input;
            this.selectedSelector = null;
            this.currentPageUrl = null;

            $('#aga-vst-selector-panel').hide();
            $('#aga-vst-options').empty();
            $('#aga-vst-confirm').prop('disabled', true);
            $('#aga-vst-status').text('Select a form field on the page');
            $('#aga-vst-page-search').val('');
            $('#aga-vst-page-info').text('');
            $('#aga-vst-page-dropdown').hide();

            this.modal.fadeIn(200);
            $('body').addClass('aga-vst-open');

            // Load homepage by default
            this.selectPage(aga_vst_data.home_url, 'Homepage');
        },

        close: function () {
            this.modal.fadeOut(200);
            $('body').removeClass('aga-vst-open');
            this.activeInput = null;
            this.selectedSelector = null;

            if (this.iframe) {
                this.iframe.src = 'about:blank';
            }
        },

        confirmSelection: function () {
            if (this.activeInput && this.selectedSelector) {
                this.activeInput.val(this.selectedSelector);
                this.activeInput.trigger('change');

                this.activeInput.css('background-color', '#e6ffe6');
                var $inp = this.activeInput;
                setTimeout(function () {
                    $inp.css('background-color', '');
                }, 2000);

                this.close();
            }
        },

        // AJAX page search
        searchPages: function (query) {
            var self = this;
            var $dd = $('#aga-vst-page-dropdown');

            $dd.html('<div class="aga-vst-dd-loading">Searching...</div>').show();

            $.ajax({
                url: aga_vst_data.ajax_url,
                data: {
                    action: 'aga_vst_search_pages',
                    nonce: aga_vst_data.nonce,
                    search: query,
                    page: self.currentPage,
                    filter: self.activeFilter
                },
                success: function (res) {
                    if (!res.success) {
                        $dd.html('<div class="aga-vst-dd-empty">Error loading pages</div>');
                        return;
                    }

                    self.renderPageDropdown(res.data, query);
                },
                error: function () {
                    $dd.html('<div class="aga-vst-dd-empty">Failed to search pages</div>');
                }
            });
        },

        renderPageDropdown: function (data, query) {
            var self = this;
            var $dd = $('#aga-vst-page-dropdown');
            var items = data.items || [];

            $dd.empty();

            // Always show Homepage + WC Checkout at the top when no search query
            if (!query && self.currentPage === 1) {
                $dd.append(
                    '<div class="aga-vst-dd-item aga-vst-dd-pinned" data-url="' + aga_vst_data.home_url + '">' +
                        '<span class="aga-vst-dd-icon">&#127968;</span>' +
                        '<span class="aga-vst-dd-title">Homepage</span>' +
                        '<span class="aga-vst-dd-badge aga-vst-dd-badge--home">home</span>' +
                    '</div>'
                );

                if (aga_vst_data.wc_checkout_url) {
                    $dd.append(
                        '<div class="aga-vst-dd-item aga-vst-dd-pinned" data-url="' + aga_vst_data.wc_checkout_url + '">' +
                            '<span class="aga-vst-dd-icon">&#128722;</span>' +
                            '<span class="aga-vst-dd-title">WooCommerce Checkout</span>' +
                            '<span class="aga-vst-dd-badge aga-vst-dd-badge--woo">checkout</span>' +
                        '</div>'
                    );
                }

                if (items.length) {
                    $dd.append('<div class="aga-vst-dd-divider"></div>');
                }
            }

            if (!items.length && self.currentPage === 1) {
                if (query) {
                    $dd.append('<div class="aga-vst-dd-empty">No pages found for "' + $('<span>').text(query).html() + '"</div>');
                }
            } else {
                items.forEach(function (item) {
                    var badge = item.type === 'page' ? 'page' : 'post';
                    $dd.append(
                        '<div class="aga-vst-dd-item" data-url="' + item.url + '">' +
                            '<span class="aga-vst-dd-title">' + $('<span>').text(item.title).html() + '</span>' +
                            '<span class="aga-vst-dd-badge aga-vst-dd-badge--' + badge + '">' + badge + '</span>' +
                        '</div>'
                    );
                });
            }

            // Pagination info + load more
            if (data.has_more) {
                $dd.append(
                    '<div class="aga-vst-dd-load-more" id="aga-vst-load-more">' +
                        'Load more (' + data.page + ' / ' + data.pages + ' pages, ' + data.total + ' total)' +
                    '</div>'
                );
            } else if (data.total > 0) {
                $dd.append(
                    '<div class="aga-vst-dd-info">' +
                        'Showing ' + Math.min(data.total, data.page * 20) + ' of ' + data.total + ' results' +
                    '</div>'
                );
            }

            // Click handlers
            $dd.find('.aga-vst-dd-item').on('click', function () {
                var url = $(this).data('url');
                var title = $(this).find('.aga-vst-dd-title').text();
                self.selectPage(url, title);
                $dd.hide();
            });

            // Load more handler
            $('#aga-vst-load-more').on('click', function () {
                self.currentPage++;
                $(this).text('Loading...');
                $.ajax({
                    url: aga_vst_data.ajax_url,
                    data: {
                        action: 'aga_vst_search_pages',
                        nonce: aga_vst_data.nonce,
                        search: query,
                        page: self.currentPage
                    },
                    success: function (res) {
                        if (res.success) {
                            // Remove old load-more/info
                            $('#aga-vst-load-more').remove();
                            $dd.find('.aga-vst-dd-info').remove();

                            var newItems = res.data.items || [];
                            newItems.forEach(function (item) {
                                var badge = item.type === 'page' ? 'page' : 'post';
                                var $el = $(
                                    '<div class="aga-vst-dd-item" data-url="' + item.url + '">' +
                                        '<span class="aga-vst-dd-title">' + $('<span>').text(item.title).html() + '</span>' +
                                        '<span class="aga-vst-dd-badge aga-vst-dd-badge--' + badge + '">' + badge + '</span>' +
                                    '</div>'
                                );
                                $el.on('click', function () {
                                    self.selectPage(item.url, item.title);
                                    $dd.hide();
                                });
                                $dd.append($el);
                            });

                            if (res.data.has_more) {
                                $dd.append(
                                    '<div class="aga-vst-dd-load-more" id="aga-vst-load-more">' +
                                        'Load more (' + res.data.page + ' / ' + res.data.pages + ' pages, ' + res.data.total + ' total)' +
                                    '</div>'
                                );
                                // Re-bind
                                $('#aga-vst-load-more').on('click', arguments.callee);
                            } else {
                                $dd.append(
                                    '<div class="aga-vst-dd-info">All ' + res.data.total + ' results loaded</div>'
                                );
                            }
                        }
                    }
                });
            });

            $dd.show();
        },

        selectPage: function (url, title) {
            this.currentPageUrl = url;
            $('#aga-vst-page-search').val(title);
            $('#aga-vst-page-info').text(title);
            this.loadPage(url);
        },

        loadPage: function (url) {
            if (!url) return;

            var separator = url.indexOf('?') !== -1 ? '&' : '?';
            var iframeUrl = url + separator + 'aga_vst=1';

            $('#aga-vst-loading').show();
            $('#aga-vst-selector-panel').hide();
            this.iframe.src = iframeUrl;

            var self = this;
            this.iframe.onload = function () {
                $('#aga-vst-loading').hide();
                self.injectBridge();
            };
        },

        injectBridge: function () {
            try {
                var iframeDoc = this.iframe.contentDocument || this.iframe.contentWindow.document;
                var script = iframeDoc.createElement('script');
                script.textContent = this.getBridgeScript();
                iframeDoc.body.appendChild(script);
            } catch (e) {
                $('#aga-vst-status').text('Cannot access page — try a different page on your site.');
            }
        },

        getBridgeScript: function () {
            return '(' + (function () {
                var overlay = null;
                var label = null;

                var SELECTABLE = 'input, textarea, select';

                function createOverlay() {
                    overlay = document.createElement('div');
                    overlay.id = 'aga-vst-overlay';
                    overlay.style.cssText = 'position:absolute;pointer-events:none;border:3px solid #3858e9;background:rgba(56,88,233,0.08);border-radius:4px;z-index:999999;transition:all 0.15s ease;display:none;box-shadow:0 0 0 4px rgba(56,88,233,0.15);';
                    document.body.appendChild(overlay);

                    label = document.createElement('div');
                    label.id = 'aga-vst-label';
                    label.style.cssText = 'position:absolute;pointer-events:none;background:#3858e9;color:#fff;font-size:11px;padding:3px 10px;border-radius:4px;z-index:999999;white-space:nowrap;font-family:-apple-system,BlinkMacSystemFont,sans-serif;display:none;box-shadow:0 2px 8px rgba(0,0,0,0.15);';
                    document.body.appendChild(label);
                }

                function generateAllSelectors(el) {
                    var selectors = [];

                    if (el.id && /^[a-zA-Z]/.test(el.id)) {
                        selectors.push({
                            value: '#' + CSS.escape(el.id),
                            type: 'ID',
                            badge: 'id',
                            desc: 'Unique element ID'
                        });
                    }

                    if (el.name) {
                        var nameSelector = '[name="' + el.name + '"]';
                        var unique = document.querySelectorAll(nameSelector).length === 1;
                        selectors.push({
                            value: nameSelector,
                            type: 'Name',
                            badge: 'name',
                            desc: unique ? 'Name attribute (unique)' : 'Name attribute (not unique — may match multiple)'
                        });
                    }

                    if (el.classList.length) {
                        var classes = Array.from(el.classList)
                            .filter(function (c) { return !/^(aga-vst|hover|focus|active|selected|ui-)/.test(c); });
                        if (classes.length) {
                            var classSel = '.' + classes.map(function (c) { return CSS.escape(c); }).join('.');
                            var classUnique = document.querySelectorAll(classSel).length === 1;
                            selectors.push({
                                value: classSel,
                                type: 'Class',
                                badge: 'class',
                                desc: classUnique ? 'CSS class (unique)' : 'CSS class (not unique — may match ' + document.querySelectorAll(classSel).length + ' elements)'
                            });
                        }
                    }

                    if (el.getAttribute('placeholder')) {
                        var phSel = '[placeholder="' + el.getAttribute('placeholder') + '"]';
                        var phUnique = document.querySelectorAll(phSel).length === 1;
                        selectors.push({
                            value: phSel,
                            type: 'Placeholder',
                            badge: 'placeholder',
                            desc: phUnique ? 'Placeholder text (unique)' : 'Placeholder text (not unique)'
                        });
                    }

                    var attrs = el.attributes;
                    for (var i = 0; i < attrs.length; i++) {
                        if (attrs[i].name.indexOf('data-') === 0 && attrs[i].value) {
                            var attrVal = attrs[i].value;
                            if (attrVal.length > 80 || /[{}"']/.test(attrVal)) continue;
                            try {
                                var dataSel = '[' + attrs[i].name + '="' + attrVal + '"]';
                                var dataUnique = document.querySelectorAll(dataSel).length === 1;
                                if (dataUnique) {
                                    selectors.push({
                                        value: dataSel,
                                        type: 'Data Attr',
                                        badge: 'data',
                                        desc: attrs[i].name + ' attribute'
                                    });
                                    break;
                                }
                            } catch (e) { /* skip */ }
                        }
                    }

                    var path = [];
                    var current = el;
                    while (current && current !== document.body) {
                        var tag = current.tagName.toLowerCase();
                        if (current.id && /^[a-zA-Z]/.test(current.id)) {
                            path.unshift('#' + CSS.escape(current.id));
                            break;
                        }
                        var parent = current.parentElement;
                        if (parent) {
                            var siblings = Array.from(parent.children).filter(function (s) { return s.tagName === current.tagName; });
                            if (siblings.length > 1) {
                                var index = siblings.indexOf(current) + 1;
                                tag += ':nth-of-type(' + index + ')';
                            }
                        }
                        path.unshift(tag);
                        current = parent;
                    }
                    var pathSel = path.join(' > ');
                    var isDuplicate = selectors.some(function (s) { return s.value === pathSel; });
                    if (!isDuplicate) {
                        selectors.push({
                            value: pathSel,
                            type: 'Full Path',
                            badge: 'path',
                            desc: 'DOM tree path (always unique)'
                        });
                    }

                    return selectors;
                }

                function positionOverlay(el) {
                    var rect = el.getBoundingClientRect();
                    var scrollX = window.scrollX || window.pageXOffset;
                    var scrollY = window.scrollY || window.pageYOffset;

                    overlay.style.left = (rect.left + scrollX - 3) + 'px';
                    overlay.style.top = (rect.top + scrollY - 3) + 'px';
                    overlay.style.width = (rect.width + 6) + 'px';
                    overlay.style.height = (rect.height + 6) + 'px';
                    overlay.style.display = 'block';

                    var shortLabel = '';
                    if (el.id) shortLabel = '#' + el.id;
                    else if (el.name) shortLabel = '[name="' + el.name + '"]';
                    else if (el.className) shortLabel = '<' + el.tagName.toLowerCase() + '.' + el.className.split(' ')[0] + '>';
                    else shortLabel = '<' + el.tagName.toLowerCase() + '>';

                    label.textContent = shortLabel;
                    label.style.left = (rect.left + scrollX) + 'px';
                    label.style.top = Math.max(0, rect.top + scrollY - 28) + 'px';
                    label.style.display = 'block';
                }

                function init() {
                    createOverlay();

                    document.addEventListener('mousemove', function (e) {
                        var el = document.elementFromPoint(e.clientX, e.clientY);
                        if (!el) return;
                        var target = el.closest(SELECTABLE);
                        if (!target) {
                            overlay.style.display = 'none';
                            label.style.display = 'none';
                            return;
                        }
                        positionOverlay(target);
                    }, true);

                    document.addEventListener('click', function (e) {
                        var el = e.target.closest(SELECTABLE);
                        if (!el) return;

                        e.preventDefault();
                        e.stopPropagation();
                        e.stopImmediatePropagation();

                        overlay.style.borderColor = '#22c55e';
                        overlay.style.background = 'rgba(34,197,94,0.1)';
                        overlay.style.boxShadow = '0 0 0 4px rgba(34,197,94,0.2)';
                        label.style.background = '#22c55e';

                        positionOverlay(el);

                        var allSelectors = generateAllSelectors(el);

                        window.parent.postMessage({
                            type: 'aga-vst-select',
                            selectors: allSelectors,
                            tag: el.tagName.toLowerCase(),
                            type_attr: el.type || '',
                            id: el.id || '',
                            name: el.name || '',
                            placeholder: el.placeholder || ''
                        }, '*');
                    }, true);

                    document.addEventListener('submit', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                    }, true);

                    document.querySelectorAll('a').forEach(function (a) {
                        a.addEventListener('click', function (e) {
                            e.preventDefault();
                        });
                    });

                    window.parent.postMessage({ type: 'aga-vst-ready' }, '*');
                }

                if (document.readyState === 'complete') {
                    init();
                } else {
                    window.addEventListener('load', init);
                }
            }).toString() + ')();';
        },

        handleMessage: function (e) {
            if (!e.data || !e.data.type) return;

            if (e.data.type === 'aga-vst-ready') {
                $('#aga-vst-status').text('Ready — hover and click a form field');
            }

            if (e.data.type === 'aga-vst-select') {
                this.showSelectorOptions(e.data);
            }
        },

        showSelectorOptions: function (data) {
            var self = this;
            var $panel = $('#aga-vst-selector-panel');
            var $options = $('#aga-vst-options');
            var $info = $('#aga-vst-element-info');

            var elDesc = '&lt;' + data.tag + '&gt;';
            if (data.name) elDesc += ' name="' + $('<span>').text(data.name).html() + '"';
            else if (data.id) elDesc += ' id="' + $('<span>').text(data.id).html() + '"';
            else if (data.placeholder) elDesc += ' placeholder="' + $('<span>').text(data.placeholder).html() + '"';
            $info.html(elDesc);

            $options.empty();
            var selectors = data.selectors || [];

            if (!selectors.length) {
                $options.html('<div class="aga-vst-no-options">No selectors found for this element.</div>');
                $panel.show();
                return;
            }

            this.selectedSelector = selectors[0].value;
            $('#aga-vst-confirm').prop('disabled', false);

            selectors.forEach(function (sel, idx) {
                var isRecommended = idx === 0;
                var $option = $(
                    '<div class="aga-vst-option' + (isRecommended ? ' selected' : '') + '" data-selector="' + $('<span>').text(sel.value).html() + '">' +
                        '<div class="aga-vst-option-top">' +
                            '<span class="aga-vst-badge aga-vst-badge--' + sel.badge + '">' + sel.type + '</span>' +
                            (isRecommended ? '<span class="aga-vst-recommended">Recommended</span>' : '') +
                        '</div>' +
                        '<code class="aga-vst-option-code">' + $('<span>').text(sel.value).html() + '</code>' +
                        '<span class="aga-vst-option-desc">' + sel.desc + '</span>' +
                    '</div>'
                );

                $option.on('click', function () {
                    $options.find('.aga-vst-option').removeClass('selected');
                    $(this).addClass('selected');
                    self.selectedSelector = sel.value;
                    $('#aga-vst-confirm').prop('disabled', false);
                    $('#aga-vst-status').html('Selected: <strong>' + $('<span>').text(sel.value).html() + '</strong>');
                });

                $options.append($option);
            });

            $panel.slideDown(200);
            $('#aga-vst-status').html('Selected: <strong>' + $('<span>').text(selectors[0].value).html() + '</strong>');
        }
    };

    $(function () {
        if (typeof aga_vst_data !== 'undefined') {
            VST.init();
        }
    });

})(jQuery);
