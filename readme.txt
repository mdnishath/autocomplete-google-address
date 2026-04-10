=== Autocomplete Google Address ===
Contributors: nishatbd31, freemius
Tags: google address autocomplete, woocommerce address, address validation, map picker, checkout autocomplete
Requires at least: 5.4
Tested up to: 6.9
Stable tag: 5.3.2
Requires PHP: 7.2
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

The #1 Google Address Autocomplete for WordPress. Visual point-and-click setup -- no coding needed. Works with WooCommerce, CF7, WPForms, Gravity Forms, Elementor, and any form.

== Description ==

**The #1 Google Address Autocomplete plugin for WordPress.** Add real-time address suggestions to any form on your site -- checkout, contact, registration, booking, or custom forms.

**NEW in v5.3.0: Visual Selector Tool** -- Just click on a form field to set it up. No CSS knowledge, no DevTools, no code. The easiest address autocomplete setup ever made.

= No Code Required -- Point and Click Setup =

Other address plugins make you learn CSS selectors or inspect code. **Not this one.**

With the **Visual Selector Tool**, you:

1. Click the "Pick" button next to any field
2. Your website loads in a preview window
3. Click the form field you want (e.g., your address input)
4. Choose from multiple selector options -- ID, Name, Class, or Placeholder
5. Done. The field is mapped automatically.

**It's that simple.** No tutorials, no documentation, no developer needed. If you can click a button, you can set up address autocomplete.

= Works With ANY Form -- Any Selector Type =

This plugin doesn't just work with IDs. It works with **any valid CSS selector**:

* `#billing_address` -- ID selector
* `[name="address"]` -- Name attribute
* `.address-field` -- CSS class
* `[placeholder="Enter address"]` -- Placeholder text
* `[data-field="address"]` -- Data attributes
* `form .row input:nth-of-type(2)` -- Complex DOM paths

**You don't need to know any of this.** The Visual Selector Tool figures it out for you and shows you all options.

= Most In-Demand Features =

* **Visual Selector Tool (NEW)** -- Click to select form fields. Zero code required.
* **Map Picker** -- Interactive Google Map where users click or drag a pin to pick their exact address. Auto-fills all fields.
* **Smart Mapping** -- One address field auto-fills Street, City, State, Zip, and Country into separate fields.
* **WooCommerce Auto-Setup** -- Detects your checkout page automatically. Works with Classic and Block Checkout.
* **Address Validation** -- Green/yellow/red badges verify if an address is real.
* **GPS Geolocation** -- "Use My Location" button fills the address instantly.
* **8 Form Plugins Supported** -- WooCommerce, Contact Form 7, WPForms, Gravity Forms, Elementor Pro, Fluent Forms, Ninja Forms, plus any HTML form.
* **60-Second Setup Wizard** -- Enter API key, pick your form plugin, done.

= Why 10,000+ Sites Choose This Plugin =

Most address plugins force you to use their form builder. **This plugin is different** -- it works with your *existing* forms. Your form design stays exactly as you built it. Zero style override.

* Works with your existing forms -- no rebuilding
* Zero style override -- your CSS stays untouched
* Lightweight -- no jQuery UI, no CSS frameworks, no bloat
* Works in every country -- smart mapping for 30+ countries
* Programmatic API -- uses the latest Google Places API (New)

= How the Visual Selector Tool Works =

**Step 1: Click "Pick"**
Next to every field mapping input, you will see a blue "Pick" button with a crosshair icon. Click it.

**Step 2: Your Page Loads**
A full-screen preview window opens showing your actual website. You can switch between pages using the dropdown -- Homepage, Checkout, Contact page, or any page on your site.

**Step 3: Hover and Click**
Move your mouse over any form field. A blue highlight appears showing the field selector. Click the field you want.

**Step 4: Choose Your Selector**
The tool shows you ALL possible selector options in color-coded cards:

* **ID** (Blue) -- Like `#billing_address` -- Most reliable, recommended
* **Name** (Green) -- Like `[name="address"]` -- Great for form plugins
* **Class** (Yellow) -- Like `.form-control` -- Shows if it matches multiple elements
* **Placeholder** (Purple) -- Like `[placeholder="Street address"]`
* **Full Path** (Gray) -- DOM tree path -- Always works as a fallback

Each card tells you if the selector is unique or matches multiple elements, so you always pick the right one.

**Step 5: Confirm**
Click "Use This Selector" and the field is mapped. That is it.

= Free Features =

* Works with any form -- checkout, contact, registration, booking
* Single line mode -- full address in one field
* Unlimited configurations -- different setups for different forms
* Keyboard navigation -- arrow keys, Enter, Escape
* Smart dropdown -- loading spinner, no-results message, Google attribution
* Google Places API (New) -- latest, most accurate API
* Shortcode support -- `[aga_autocomplete]` anywhere
* Duplicate configs -- one-click clone
* Import/Export -- JSON backup and transfer
* Health Check -- auto-diagnose API issues
* Conflict detection -- warns about other Google Maps scripts
* Works in every country -- 200+ countries supported

= Pro Features =

* **Visual Selector Tool** -- Point-and-click field mapping, no code needed
* **Map Picker** -- Interactive map with draggable pin, GPS auto-center, reverse geocoding
* **Smart Mapping Mode** -- Auto-fill Street, City, State, Zip, Country into separate fields
* **WooCommerce Auto-Integration** -- Zero-config setup, auto-detects Classic vs Block Checkout
* **One-Click Form Presets** -- Pre-built configs for CF7, WPForms, Gravity Forms, Elementor, Fluent Forms, Ninja Forms
* **Address Validation** -- Green/yellow/red verification badges using Google Address Validation API
* **GPS Geolocation** -- "Use My Location" button with IP fallback
* **Saved Addresses** -- Quick-select from recent addresses for logged-in users
* **PO Box Detection** -- Automatic warning for PO Box, APO, FPO addresses
* **Multiple Country Restrictions** -- Limit results to up to 5 countries
* **Per-Form Language Override** -- Different languages for different forms
* **Per-Page Activation** -- Load only on specific pages
* **Address Verification Webhook** -- Send alerts to Slack, Zapier, or any URL
* **Checkout Abandonment Tracking** -- Track incomplete checkouts in Analytics
* **White Label Mode** -- Custom admin menu name for agencies
* **REST API** -- Headless endpoints for React/Next.js storefronts
* **Usage Analytics Dashboard** -- Track searches, selections, top countries
* **Place Type Filter** -- Addresses only, cities, businesses, or regions
* **Custom Dropdown Styling** -- Colors, fonts, border radius from admin
* **Elementor Widget + Form Field** -- Native drag-and-drop with all Pro features
* **Dark Mode** -- Adapts to user's dark mode preference
* **RTL Support** -- Arabic, Hebrew, and right-to-left languages
* **ARIA Accessibility** -- Screen reader support
* **Smart Country-Aware Mapping** -- Correct city/state mapping for 30+ countries
* **Priority WhatsApp Support** -- Direct support from the developer

= Works With =

* WooCommerce (Classic & Block Checkout)
* Contact Form 7
* WPForms
* Gravity Forms
* Elementor Pro Forms
* Fluent Forms
* Ninja Forms
* Formidable Forms
* Any HTML form with standard inputs

= How It Works =

**For WooCommerce users (Pro):**
Activate the plugin, enter your API key in the Setup Wizard, select "WooCommerce" -- done. The plugin automatically detects your checkout page and adds autocomplete to billing and shipping address fields. No form configuration needed.

**For form plugin users (Pro):**
Select your form plugin in the Setup Wizard. The plugin creates a configuration with pre-filled selectors matching your plugin's field pattern. Or use the Visual Selector Tool to click and select fields visually.

**For any other form (Free & Pro):**
Create a configuration, use the Visual Selector Tool to click your form fields (or manually enter CSS selectors), choose Single Line or Smart Mapping mode, and activate globally or on specific pages.

= Technical Details =

* Uses the latest **Google Places API (New)** -- future-proof and fully supported
* Programmatic API approach -- your form inputs keep their original styling
* Session tokens for optimized Google API billing
* Debounced search (300ms) to minimize API calls
* Server-side IP geolocation -- no CORS errors, cached for 24 hours
* Reverse geocoding for draggable map pin (requires Geocoding API)

== Installation ==

= Automatic Installation (Recommended) =

1. Go to **Plugins > Add New** in your WordPress admin
2. Search for **"Autocomplete Google Address"**
3. Click **Install Now**, then **Activate**
4. The **Setup Wizard** will launch automatically -- follow the 3 steps:
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
   * **Geocoding API** (optional -- needed for draggable map pin)
5. Go to **APIs & Services > Credentials**
6. Click **Create Credentials > API Key**
7. Copy the key and paste it in the plugin settings

= Using the Visual Selector Tool (Pro) =

1. Go to **Google Address > All Configs** and edit (or create) a form configuration
2. Next to the "Trigger Field Selector" input, click the blue **Pick** button
3. A preview of your website opens in a popup window
4. Use the page dropdown to navigate to the page with your form (Checkout, Contact, etc.)
5. **Hover** over form fields -- they highlight in blue
6. **Click** the field you want -- it highlights in green
7. A panel appears at the bottom with multiple selector options (ID, Name, Class, Placeholder, Path)
8. Click the selector you prefer (the first one marked "Recommended" is usually best)
9. Click **"Use This Selector"** -- the field mapping is saved
10. Repeat for other fields (Street, City, State, Zip, Country) if using Smart Mapping mode

The Visual Selector Tool is available for **all selector fields** -- Trigger Field, Street, City, State, Zip, Country, Latitude, Longitude, and Place ID.

= WooCommerce Setup (Pro) =

No manual configuration needed:

1. Complete the Setup Wizard and select "WooCommerce"
2. The plugin automatically creates billing and shipping configurations
3. Go to your checkout page -- address autocomplete is already working
4. Works with both Classic Checkout and Block Checkout

== Frequently Asked Questions ==

= Does this work with WooCommerce checkout? =

Yes! With the Pro plan, WooCommerce integration is fully automatic -- no configuration needed. It works with both the Classic Checkout and the new Block Checkout. The plugin detects your checkout page and adds autocomplete to billing and shipping address fields automatically.

= I don't know CSS selectors. Can I still use this plugin? =

Absolutely! The **Visual Selector Tool** (Pro) lets you set up everything by clicking -- no CSS knowledge needed. Just click the "Pick" button, click your form field on the preview, and you are done. The tool generates the correct selector for you automatically.

= What types of selectors does the plugin support? =

The plugin uses standard CSS selectors with `document.querySelector()`, so it supports ALL types -- IDs (`#field`), classes (`.field`), name attributes (`[name="field"]`), placeholder attributes, data attributes, and complex nested paths. The Visual Selector Tool shows you all available options for any field.

= Does it work in my country? =

Yes. The plugin uses Google Places API which covers addresses worldwide. The smart select matching system automatically handles different address formats -- US states, Bangladesh districts, Indian states, Canadian provinces, UK counties, Australian states, and 30+ more countries.

= Do I need a Google Maps API Key? =

Yes, a Google Maps API key is required. You can get one for free from the [Google Cloud Console](https://console.cloud.google.com/). Google offers $200/month in free API credits which covers most small-to-medium sites.

= Which Google APIs do I need to enable? =

Required: **Places API (New)** and **Maps JavaScript API**. Optional: **Geocoding API** (only needed if you want the draggable map pin to update address fields when moved).

= Will this slow down my site? =

No. The Google Maps script loads asynchronously and only on pages where autocomplete is active. The plugin itself is lightweight with no jQuery UI, no CSS frameworks, and no unnecessary dependencies.

= Does it change my form's styling? =

No. Unlike other plugins that inject their own styled input, this plugin uses a programmatic API that keeps your existing form inputs completely untouched. The autocomplete dropdown uses minimal, clean CSS that adapts to any design.

= Can I use it on multiple forms? =

Yes. Create as many configurations as you need -- different setups for different forms on the same site. Each configuration can have its own selectors, mode, country restrictions, and activation rules.

= Does it work with page builders? =

Yes. It works with Elementor, Beaver Builder, Divi, and any page builder. The plugin uses CSS selectors to find form fields, so it works regardless of how the form was built.

= Can I restrict results to specific countries? =

Yes (Pro). You can restrict autocomplete results to up to 5 countries. The Map Picker auto-centers on the restricted country.

= What happens when I upgrade to Pro? =

Pro features unlock instantly -- no reinstall, no separate download. Just enter your license key and all Pro features become available immediately.

= Is there a free trial? =

Yes, we offer a 3-day free trial of the Pro plan so you can test all features before purchasing.

== Screenshots ==

1. Visual Selector Tool -- Click any form field to map it. No code needed.
2. Selector Options Panel -- Choose from ID, Name, Class, Placeholder, or Path.
3. Setup Wizard -- Get started in 60 seconds.
4. Form Configuration -- Selector-based mapping builder with Pick buttons.
5. Map Picker -- Interactive map with draggable pin and GPS auto-center.
6. Frontend Autocomplete -- Clean dropdown on any form.
7. Smart Mapping -- Auto-fills Street, City, State, Zip, Country.
8. WooCommerce Checkout -- Works on both Classic and Block Checkout.
9. Address Validation -- Green/yellow/red verification badges.
10. Analytics Dashboard -- Track searches, selections, and abandonment.

== Changelog ==

= 5.3.2 =
* FIX: Autocomplete now works on duplicate forms -- when the same form appears twice on a page, both instances get autocomplete.
* FIX: Field mapping (city, state, zip, etc.) now fills the correct form instance instead of always targeting the first one.
* FIX: Autocomplete now auto-initializes inside popups, modals, and overlays via MutationObserver.
* FIX: Dropdown no longer flips above the input inside popups/modals -- always shows below where expected.

= 5.3.1 =
* NEW: AJAX page search with pagination -- search across all pages and posts (supports 750+ pages). No more 50-page limit.
* NEW: "Forms Only" filter -- one-click toggle to show only pages containing forms (detects CF7, WPForms, Gravity Forms, Elementor, Ninja Forms, Fluent Forms, WooCommerce, and HTML forms).

= 5.3.0 =
* NEW: Visual Selector Tool -- click any form field on your site to generate its CSS selector. No DevTools, no coding needed. Shows multiple selector options (ID, Name, Class, Placeholder, Path) with color-coded cards. Works for all selector fields. (Pro)
* NEW: Server-side IP geolocation -- eliminates CORS console errors from client-side fetch. Results cached for 24 hours per visitor.
* IMPROVED: Pick buttons with crosshair icon next to every selector input field for instant visual selection.
* IMPROVED: Selector options panel shows uniqueness info -- tells you if a selector matches 1 or multiple elements.
* FIX: Data attribute selectors with JSON values no longer crash the selector generator.

= 5.2.2 =
* FIX: Map Picker no longer shows a wrong pin in Mali/Africa on initial load. Marker is hidden until real location is found.
* FIX: Map Picker no longer appears on the admin form edit page -- only shows on the frontend where customers interact.
* FIX: Disabled select/input fields (non-Pro) now display cleanly without broken checkmark pattern.

= 5.2.1 =
* IMPROVED: Map Picker now uses real GPS geolocation to center on user's exact location (zoom 17) instead of country center.
* IMPROVED: If GPS is denied/unavailable, Map Picker falls back to IP-based geolocation (zoom 14) for approximate location.
* NEW: Configurable Map Zoom Level in Settings > Appearance (range 1-21, default 17).
* FIX: Disabled Select2/select fields now display cleanly for free users.
* FIX: Freemius is_premium flag corrected.

= 5.2.0 =
* NEW: Map Picker -- interactive map below address input. Click or drag pin to pick address (Pro).
* NEW: PO Box / APO / Military address detection with visual warning.
* NEW: Fluent Forms and Ninja Forms integrations.
* NEW: Enhanced Setup Wizard with WooCommerce feature config and live preview.
* NEW: Address Verification Webhook, White Label Mode, Abandonment Tracking, REST API (Pro).
* NEW: Dark Mode, RTL Support, ARIA Accessibility.
* FIX: iOS/Safari touch events, race conditions, stale responses, null safety.
* PERF: Combined DB queries, cached health checks, async Maps loading.

= 5.1.0 =
* NEW: Address Validation, Geolocation, Saved Addresses, Analytics Dashboard.
* NEW: Elementor Widget and Form Field, Shortcode, Import/Export, Health Check.
* NEW: Smart Country-Aware Mapping for 30+ countries.

= 5.0.0 =
* Major update: Single plugin architecture, new Google Places API, Setup Wizard, WooCommerce auto-integration.

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 5.3.1 =
AJAX page search with pagination for large sites (750+ pages). "Forms Only" filter to quickly find pages with forms. Upgrade recommended.

= 5.3.0 =
New: Visual Selector Tool -- set up address autocomplete by clicking form fields. No code required. Server-side IP geolocation fix. Upgrade recommended.
