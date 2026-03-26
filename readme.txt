=== Autocomplete Google Address ===
Contributors: nishatbd31, freemius
Tags: google, maps, places, autocomplete, address, form, woocommerce, contact form 7, wpforms, gravity forms, elementor, checkout, address autocomplete
Requires at least: 5.4
Tested up to: 6.9
Stable tag: 5.1.2
Requires PHP: 7.2
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The easiest Google Address Autocomplete for WordPress. Works with WooCommerce, Contact Form 7, WPForms, Gravity Forms, Elementor, and any form. Set up in 60 seconds.

== Description ==

**The #1 Google Address Autocomplete plugin for WordPress.** Add real-time address suggestions to any form on your site — checkout, contact, registration, booking, or custom forms. No coding required.

= Why Choose Autocomplete Google Address? =

Most address plugins force you to use their own form builder. **This plugin is different** — it works with your *existing* forms. WooCommerce checkout, Contact Form 7, WPForms, Gravity Forms, Elementor Pro Forms, or any custom HTML form. Just point it at your fields and it works.

= Set Up in 60 Seconds =

1. Enter your Google API key
2. Pick your form plugin (WooCommerce, CF7, WPForms, etc.)
3. Done. Your forms now have address autocomplete.

Our **Setup Wizard** guides you through the entire process. No documentation needed.

= Free Features =

* **Works with Any Form** — Add address autocomplete to checkout, contact, registration, or any form
* **Single Line Mode** — Full formatted address goes into one field
* **Unlimited Configurations** — Different setups for different forms on the same site
* **Keyboard Navigation** — Arrow keys, Enter to select, Escape to close
* **Smart Dropdown** — Loading indicator, "no results" message, auto-positioning
* **Google Powered** — Uses the latest Google Places API for accurate, worldwide results
* **Lightweight** — No bloat, no extra CSS frameworks, no jQuery UI dependencies
* **Conflict Prevention** — Option to skip Google Maps API loading if another plugin already loads it
* **Global or Per-Page Activation** — Control exactly where autocomplete appears
* **Shortcode Support** — Use `[aga_autocomplete]` to add autocomplete anywhere
* **Config Duplicate** — One-click duplicate existing form configurations
* **Import/Export** — Export all configs as JSON, import on another site
* **Diagnostic Health Check** — Auto-check API keys, enabled APIs, system info
* **Conflict Detection** — Warns when other plugins load Google Maps API
* **Works in Every Country** — International address support with smart country-aware mapping

= Pro Features =

* **Smart Mapping Mode** — One field triggers autocomplete, then automatically fills Street, City, State/District, Zip, and Country into separate fields
* **WooCommerce Auto-Integration** — Zero configuration needed. Automatically adds autocomplete to billing and shipping address fields on checkout. Works with both Classic and Block Checkout
* **One-Click Form Presets** — Pre-built selector templates for Contact Form 7, WPForms, Gravity Forms, and Elementor Pro Forms. Just click "Apply Preset" and customize your field IDs
* **Draggable Map Preview** — Shows a Google Map with a draggable pin after address selection. Drag the pin to fine-tune the location and all fields update automatically
* **Multiple Country Restrictions** — Limit autocomplete results to one or more countries
* **Per-Form Language Override** — Set different languages for different forms
* **Per-Page Activation** — Load autocomplete only on specific pages
* **Smart Select Matching** — Automatically matches state/district/province names to WooCommerce dropdown values for every country (US states, Bangladesh districts, Indian states, UK counties, etc.)
* **React-Compatible** — Works perfectly with WooCommerce Block Checkout and other React-based forms
* **Address Validation** — Verify addresses with green/yellow/red badges using Google Address Validation API
* **Geolocation Auto-Detect** — "Use My Location" GPS button on the address input
* **Saved Addresses** — Show recently used addresses for logged-in users (up to 5 per user)
* **Usage Analytics Dashboard** — Track searches, selections, conversion rate, top countries/cities
* **Place Type Filter** — Restrict results to addresses, cities, businesses, or regions
* **Custom Dropdown Styling** — Customize dropdown colors, fonts, border radius from admin
* **Elementor Widget** — Native drag-and-drop widget for Elementor page builder
* **Elementor Form Field** — Address Autocomplete field type for Elementor Pro Forms with smart mapping sub-fields
* **Country Restriction Dropdown** — Select2 multi-select with all countries (max 5)
* **Language Selector** — Select2 dropdown with 80+ languages
* **Smart Country-Aware Mapping** — Automatically maps city/state correctly for 30+ countries (Bangladesh districts, UK postal towns, Brazil admin areas, etc.)
* **Zero-Config WooCommerce** — Auto-detects classic vs block checkout, re-initializes on AJAX updates
* **Priority Support** — Direct WhatsApp support from the developer

= Works With =

* WooCommerce (Classic & Block Checkout)
* Contact Form 7
* WPForms
* Gravity Forms
* Elementor Pro Forms
* Formidable Forms
* Ninja Forms
* Any HTML form with standard inputs

= How It Works =

**For WooCommerce users (Pro):**
Activate the plugin, enter your API key in the Setup Wizard, select "WooCommerce" — done. The plugin automatically detects your checkout page and adds autocomplete to billing and shipping address fields. No form configuration needed.

**For form plugin users (Pro):**
Select your form plugin in the Setup Wizard. The plugin creates a configuration with pre-filled selectors matching your plugin's field pattern. Just update the field IDs to match your specific form.

**For any other form (Free & Pro):**
Create a configuration, enter the CSS selector of your address field (right-click > Inspect to find it), choose Single Line or Smart Mapping mode, and activate globally or on specific pages.

= Technical Details =

* Uses the latest **Google Places API (New)** — future-proof and fully supported
* Programmatic API approach — your form inputs keep their original styling
* Session tokens for optimized Google API billing
* Debounced search (300ms) to minimize API calls
* Reverse geocoding for draggable map pin (requires Geocoding API)

== Installation ==

= Automatic Installation (Recommended) =

1. Go to **Plugins > Add New** in your WordPress admin
2. Search for **"Autocomplete Google Address"**
3. Click **Install Now**, then **Activate**
4. The **Setup Wizard** will launch automatically — follow the 3 steps:
   * **Step 1:** Enter your Google Maps API key
   * **Step 2:** Select your form plugin (WooCommerce, CF7, WPForms, Gravity Forms, Elementor, or Manual)
   * **Step 3:** Done!

= Manual Installation =

1. Download the plugin zip file
2. Go to **Plugins > Add New > Upload Plugin**
3. Upload the zip and click **Install Now**
4. Activate the plugin
5. Go to **Google Address > Settings** to enter your API key

= Getting a Google Maps API Key =

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project (or select an existing one)
3. Go to **APIs & Services > Library**
4. Enable these APIs:
   * **Places API (New)**
   * **Maps JavaScript API**
   * **Geocoding API** (optional — needed for draggable map pin)
5. Go to **APIs & Services > Credentials**
6. Click **Create Credentials > API Key**
7. Copy the key and paste it in the plugin settings

= WooCommerce Setup (Pro) =

No manual configuration needed:

1. Complete the Setup Wizard and select "WooCommerce"
2. The plugin automatically creates billing and shipping configurations
3. Go to your checkout page — address autocomplete is already working
4. Works with both Classic Checkout and Block Checkout

= Form Plugin Setup (Pro) =

1. Complete the Setup Wizard and select your form plugin
2. The plugin creates a configuration with pre-filled selectors
3. Click "Apply Preset" to fill all field selectors with the correct pattern
4. Update the placeholder field IDs to match your actual form
5. Save and test on your form page

= Manual Setup =

1. Go to **Google Address > Add New**
2. Enter the CSS selector of your address input field (e.g., `#billing_address`, `.my-address-field`, `[name="address"]`)
   * To find the selector: right-click the field on your page > click "Inspect" > look for the `id` or `class` attribute
3. Choose a mode:
   * **Single Line** — Full address in one field, optionally capture lat/lng/place ID
   * **Smart Mapping (Pro)** — Fill Street, City, State, Zip, Country into separate fields
4. Set activation: Global (all pages) or specific pages
5. Publish the configuration
6. If not using Global Activation, add the shortcode `[aga_form id="123"]` to your page

== Frequently Asked Questions ==

= Does this work with WooCommerce checkout? =

Yes! With the Pro plan, WooCommerce integration is fully automatic — no configuration needed. It works with both the Classic Checkout and the new Block Checkout. The plugin detects your checkout page and adds autocomplete to billing and shipping address fields automatically.

= Does it work in my country? =

Yes. The plugin uses Google Places API which covers addresses worldwide. The smart select matching system automatically handles different address formats — US states, Bangladesh districts, Indian states, Canadian provinces, UK counties, Australian states, and more.

= Do I need a Google Maps API Key? =

Yes, a Google Maps API key is required. You can get one for free from the [Google Cloud Console](https://console.cloud.google.com/). Google offers $200/month in free API credits which covers most small-to-medium sites.

= Which Google APIs do I need to enable? =

Required: **Places API (New)** and **Maps JavaScript API**. Optional: **Geocoding API** (only needed if you want the draggable map pin to update address fields when moved).

= Will this slow down my site? =

No. The Google Maps script loads asynchronously and only on pages where autocomplete is active. The plugin itself is lightweight with no jQuery UI, no CSS frameworks, and no unnecessary dependencies.

= Does it change my form's styling? =

No. Unlike other plugins that inject their own styled input, this plugin uses a programmatic API that keeps your existing form inputs completely untouched. The autocomplete dropdown uses minimal, clean CSS that adapts to any design.

= Can I use it on multiple forms? =

Yes. Create as many configurations as you need — different setups for different forms on the same site. Each configuration can have its own selectors, mode, country restrictions, and activation rules.

= Does it work with page builders? =

Yes. It works with Elementor, Beaver Builder, Divi, and any page builder. The plugin uses CSS selectors to find form fields, so it works regardless of how the form was built.

= Can I restrict results to specific countries? =

Yes (Pro). You can restrict autocomplete results to one or more countries using two-letter country codes. For example, enter "US, CA" to show only US and Canadian addresses.

= What happens when I upgrade to Pro? =

Pro features unlock instantly — no reinstall, no separate download. Just enter your license key in the plugin and all Pro features become available immediately.

= Is there a free trial? =

Yes, we offer a 3-day free trial of the Pro plan so you can test all features before purchasing.

== Screenshots ==

1. Setup Wizard — Get started in 60 seconds
2. Form Configuration — Selector-based mapping builder
3. Settings Page — Tabbed interface with API key validation
4. Frontend Autocomplete — Clean dropdown on any form
5. Smart Mapping — Automatically fills Street, City, State, Zip, Country
6. WooCommerce Checkout — Works on both Classic and Block checkout
7. Map Preview — Draggable pin with reverse geocoding
8. Form Presets — One-click setup for popular form plugins

== Changelog ==

= 5.1.2 =
* FIX: Settings toggle not saving — unchecking a toggle (e.g., "Do not load Google Maps JS API") now properly saves as OFF.
* FIX: "pointer is not a function" JavaScript error on settings page caused by missing wp-pointer dependency.
* FIX: Added active tab tracking so saving on one tab doesn't reset toggles on other tabs.

= 5.1.1 =
* FIX: Setup wizard "Sorry, you are not allowed to access this page" error on fresh activation.
* FIX: Wizard page now registers as standalone hidden page, accessible regardless of CPT timing.
* FIX: Added capability check and redirect loop guard for wizard.

= 5.1.0 =
* NEW: Address Validation with verified/warning/invalid badges (Pro).
* NEW: Geolocation "Use My Location" GPS button (Pro).
* NEW: Saved Addresses — show last 5 used addresses for logged-in users (Pro).
* NEW: Usage Analytics Dashboard — track searches, selections, top cities/countries (Pro).
* NEW: Place Type Filter — restrict to addresses, cities, businesses, regions (Pro).
* NEW: Custom dropdown CSS styling from Appearance settings tab (Pro).
* NEW: Native Elementor Widget — drag-and-drop address autocomplete widget.
* NEW: Elementor Pro Form Field — Address Autocomplete field type with smart mapping sub-fields and layout control.
* NEW: Shortcode `[aga_autocomplete]` — add autocomplete to any page/post.
* NEW: Form Config Duplicate — one-click duplicate button on configs list.
* NEW: Import/Export — JSON export/import for all form configurations.
* NEW: Diagnostic Health Check page — auto-tests API keys, enabled APIs, system info.
* NEW: Conflict Detection — warns when other plugins load Google Maps API.
* NEW: WhatsApp Chat Widget — 24/7 support button on all plugin admin pages.
* NEW: Review Request Banner — non-intrusive prompt after 14 days of use.
* NEW: Free-to-Pro Upgrade Banner — feature showcase after 7 days for free users.
* NEW: Smart Country-Aware Address Mapping — correct city/state mapping for 30+ countries.
* NEW: Country Restriction Select2 multi-select dropdown with all countries (max 5).
* NEW: Language Select2 dropdown with 80+ languages.
* NEW: Live Preview — test autocomplete from the form config admin page.
* IMPROVED: Zero-Config WooCommerce — auto-detects classic vs block checkout.
* IMPROVED: WooCommerce re-initialization on AJAX checkout updates and shipping toggle.
* IMPROVED: Modern admin UI with design tokens, unified component library.
* IMPROVED: All inline styles extracted to admin.css.
* IMPROVED: Saved addresses trigger map preview and reverse geocode.
* IMPROVED: Country restriction now properly supports multiple countries as array.
* FIXED: Masked API key (bullet characters) being saved to database.
* FIXED: Dropdown reappearing after address selection.
* FIXED: Geolocation shows friendly error on non-HTTPS sites.

= 5.0.0 =
* Major update: Single plugin architecture — no separate Pro download needed.
* Migrated to new Google Places API (programmatic approach, no style overrides).
* Added 3-step setup wizard for first-time configuration.
* Added WooCommerce auto-integration (Pro) — works with both classic and block checkout.
* Added form plugin presets for Contact Form 7, WPForms, Gravity Forms, and Elementor (Pro).
* Added draggable map preview with reverse geocoding (Pro).
* Added smart select matching for international address fields (works with all countries).
* Added React-compatible field updates for WooCommerce block checkout.
* Redesigned settings page with tabbed interface.
* Improved dropdown UX with loading spinner, no-results message, and Google attribution.
* Added API key validation button in settings.
* Multiple country restriction support (Pro).
* Per-form language override (Pro).

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 5.1.0 =
Massive feature update: Address Validation, Geolocation, Saved Addresses, Analytics Dashboard, Elementor Widget, Shortcode support, 30+ country smart mapping, modern admin UI, and much more. Upgrade recommended.

= 5.0.0 =
Major update: New Google API, setup wizard, WooCommerce auto-integration, form presets, and single-plugin Pro architecture. Upgrade recommended for all users.
