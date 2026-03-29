<p align="center">
  <h1 align="center">🌍 Autocomplete Google Address</h1>
  <p align="center">
    <strong>The most powerful Google Places address autocomplete plugin for WordPress.</strong><br/>
    Works with WooCommerce, Contact Form 7, WPForms, Gravity Forms, Elementor, and <em>any</em> form.
  </p>
</p>

<p align="center">
  <a href="https://wordpress.org/plugins/autocomplete-google-address/"><img src="https://img.shields.io/badge/version-5.2.2-blue?style=for-the-badge&logo=wordpress&logoColor=white" alt="Version 5.2.2"></a>
  <a href="https://wordpress.org/plugins/autocomplete-google-address/"><img src="https://img.shields.io/badge/WordPress-6.0%2B-21759B?style=for-the-badge&logo=wordpress&logoColor=white" alt="WordPress 6.0+"></a>
  <a href="https://www.php.net/"><img src="https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=for-the-badge&logo=php&logoColor=white" alt="PHP 7.4+"></a>
  <a href="https://www.gnu.org/licenses/gpl-2.0.html"><img src="https://img.shields.io/badge/license-GPL--2.0--or--later-green?style=for-the-badge" alt="License GPL-2.0-or-later"></a>
  <a href="https://wordpress.org/plugins/autocomplete-google-address/"><img src="https://img.shields.io/badge/downloads-5k%2B-orange?style=for-the-badge&logo=wordpress&logoColor=white" alt="Downloads"></a>
</p>

---

## 🎬 How It Works

> **Type an address** &rarr; **See instant suggestions** &rarr; **All fields auto-fill**
>
> ```
> 📍 User types:  "123 Main St..."
> 📋 Dropdown:    ┌─────────────────────────────────┐
>                 │ 🔍 123 Main Street, Springfield  │
>                 │ 🔍 123 Main Ave, Portland        │
>                 │ 🔍 123 Main Blvd, Denver         │
>                 └─────────────────────────────────┘
> ✅ Auto-fills:  Street → City → State → Zip → Country
> ```
>
> **No coding required.** Just point the plugin at your existing form fields using CSS selectors — your form styles stay untouched.

---

## 💡 Why Choose This Plugin?

- 🎯 **Works with your existing forms** — No need to rebuild forms. Uses CSS selectors to map to any input field on any form plugin or custom HTML form.
- 🎨 **Zero style override** — Unlike other plugins, this one never injects its own input widget. Your form design stays exactly as you built it.
- ⚡ **60-second setup** — Install, enter your API key, pick your form fields. Done. A 3-step setup wizard guides you through it.
- 🌍 **Smart country mapping** — Correctly maps city/state/district for 30+ countries, because "locality" doesn't mean the same thing everywhere.

---

## ✨ Features

| | 🆓 Free | 👑 Pro |
|---|---|---|
| **Any Form Support** (CSS selector-based) | ✅ | ✅ |
| **Single Line Mode** | ✅ | ✅ |
| **Unlimited Configurations** | ✅ | ✅ |
| **Keyboard Navigation** (Arrow keys, Enter, Esc) | ✅ | ✅ |
| **Smart Dropdown** (loading spinner + no-results message) | ✅ | ✅ |
| **Google Places API (New)** — Latest API | ✅ | ✅ |
| **Shortcode** `[aga_autocomplete]` | ✅ | ✅ |
| **Duplicate Form Configs** | ✅ | ✅ |
| **Import / Export Configs** (JSON) | ✅ | ✅ |
| **Diagnostic Health Check Page** | ✅ | ✅ |
| **Conflict Detection** | ✅ | ✅ |
| **Lightweight, No Bloat** | ✅ | ✅ |
| **Smart Mapping Mode** (auto-fill street, city, state, zip, country) | ❌ | ✅ |
| **WooCommerce Auto-Integration** (zero config, classic + block checkout) | ❌ | ✅ |
| **Form Plugin Presets** (CF7, WPForms, Gravity, Elementor) | ❌ | ✅ |
| **Draggable Map Preview** with reverse geocoding | ❌ | ✅ |
| **Address Validation** (verified / warning / invalid badges) | ❌ | ✅ |
| **Geolocation** "Use My Location" GPS button | ❌ | ✅ |
| **Saved Addresses** (last 5 for logged-in users) | ❌ | ✅ |
| **Usage Analytics Dashboard** | ❌ | ✅ |
| **Place Type Filter** | ❌ | ✅ |
| **Custom Dropdown CSS Styling** | ❌ | ✅ |
| **Native Elementor Widget + Form Field** | ❌ | ✅ |
| **Country Restriction** (multi-select, max 5) | ❌ | ✅ |
| **Language Selector** (80+ languages) | ❌ | ✅ |
| **Smart Country-Aware Mapping** (30+ countries) | ❌ | ✅ |
| **WhatsApp Live Support** | ❌ | ✅ |
| **Priority Support** | ❌ | ✅ |

---

## 🔌 Compatibility

> Works seamlessly with all major WordPress form plugins — **and any HTML form**.

| Plugin | Status |
|--------|--------|
| 🟣 **WooCommerce** (Classic + Block Checkout) | ✅ Fully Supported |
| 📝 **Contact Form 7** | ✅ Fully Supported |
| 📋 **WPForms** | ✅ Fully Supported |
| 🔵 **Gravity Forms** | ✅ Fully Supported |
| 🔶 **Elementor Pro** (Forms + Native Widget) | ✅ Fully Supported |
| 📑 **Formidable Forms** | ✅ Fully Supported |
| 🥷 **Ninja Forms** | ✅ Fully Supported |
| 🌐 **Any HTML Form** | ✅ Fully Supported |

---

## 🚀 Quick Start

> **Get up and running in under 60 seconds.**

**1️⃣ Install the Plugin**
- Go to **Plugins → Add New** in your WordPress dashboard
- Search for **"Autocomplete Google Address"**
- Click **Install Now** → **Activate**

**2️⃣ Enter Your Google API Key**
- Navigate to **Settings → Autocomplete Google Address**
- Paste your Google Places API key
- The setup wizard walks you through it

**3️⃣ Map Your Form Fields**
- Create a new configuration
- Select your form fields using CSS selectors (or use a preset for WooCommerce, CF7, etc.)
- Save & you're done!

---

## 🔑 Google API Setup

<details>
<summary><strong>Click to expand Google Cloud Console setup guide</strong></summary>

### Steps to get your API key:

1. Go to [Google Cloud Console](https://console.cloud.google.com/)
2. Create a new project (or select an existing one)
3. Navigate to **APIs & Services → Library**
4. Enable the following APIs:
   - **Places API (New)** — *Required*
   - **Maps JavaScript API** — *Required for map preview*
   - **Geocoding API** — *Required for reverse geocoding*
5. Go to **APIs & Services → Credentials**
6. Click **Create Credentials → API Key**
7. *(Recommended)* Restrict your API key:
   - **Application restrictions:** HTTP referrers → add your domain
   - **API restrictions:** Restrict to the three APIs above
8. Copy the API key and paste it into the plugin settings

> **💡 Tip:** The plugin's built-in Health Check page will verify your API key and tell you if any required APIs are missing.

</details>

---

## 🌍 Smart Country Mapping

> Different countries structure addresses differently. This plugin knows that.

Most autocomplete plugins blindly map `locality` to City and `administrative_area_level_1` to State. That works for the US — but breaks for dozens of other countries.

**Autocomplete Google Address** ships with intelligent mapping for 30+ countries:

| Country | 🏙️ City From | 🗺️ State From |
|---------|-------------|---------------|
| 🇺🇸 **United States** | `locality` | `admin_level_1` |
| 🇧🇩 **Bangladesh** | `admin_level_2` (district) | `admin_level_1` (division) |
| 🇬🇧 **United Kingdom** | `postal_town` | `admin_level_2` (county) |
| 🇧🇷 **Brazil** | `admin_level_2` | `admin_level_1` |
| 🇮🇹 **Italy** | `admin_level_3` | `admin_level_2` |
| 🇯🇵 **Japan** | `locality` | `admin_level_1` (prefecture) |
| 🇮🇳 **India** | `locality` | `admin_level_1` |

> **👑 Pro Feature:** Smart Country-Aware Mapping is available in the Pro version and supports 30+ country-specific mapping rules out of the box.

---

## 📝 Shortcode Usage

Use the `[aga_autocomplete]` shortcode to embed an autocomplete field anywhere:

```
[aga_autocomplete]
```

**With parameters:**

```
[aga_autocomplete placeholder="Start typing your address..." class="my-custom-class"]
```

**In a template (PHP):**

```php
<?php echo do_shortcode('[aga_autocomplete]'); ?>
```

> **💡 Tip:** Combine the shortcode with a form configuration to auto-fill other fields on the same page.

---

## 📋 Changelog

### v5.2.2 — Map Picker Bug Fixes (Latest)

- 🐛 **Fix:** Map Picker no longer shows wrong pin in Mali/Africa on initial load — marker hidden until real location found
- 🐛 **Fix:** Map Picker no longer appears on admin form edit page — only on frontend
- 🐛 **Fix:** Disabled select fields display cleanly for free users

### v5.2.1 — Map Picker GPS + Zoom Control

- 🗺️ **Map Picker GPS** — Map now centers on user's real GPS location (zoom 17) instead of country center
- 🌐 **IP Fallback** — If GPS denied, uses IP-based geolocation for approximate location (zoom 14)
- 🔍 **Map Zoom Control** — Configurable default zoom level (1-21) in Settings > Appearance
- 🐛 **Fix:** Disabled select fields display cleanly for free users
- 🐛 **Fix:** Freemius Pro detection corrected

### v5.2.0 — Major Feature Release

**New Features:**
- 🗺️ **Map Picker** — Interactive map below address input. Click or drag pin to pick address. No typing needed. Works with ALL form plugins.
- ⚠️ **PO Box Detection** — Visual warning when PO Box / APO / Military addresses are detected
- 🔗 **Fluent Forms** — New preset integration for Fluent Forms
- 🥷 **Ninja Forms** — New preset integration for Ninja Forms
- 🧙 **Enhanced Setup Wizard** — WooCommerce feature config step with live preview, country/types/language settings
- 🔔 **Address Verification Webhook** — Send alerts to Slack/Zapier on invalid addresses (Pro)
- 🏷️ **White Label Mode** — Custom admin menu name for agencies (Pro)
- 📊 **Checkout Abandonment Tracking** — Track users who type address but don't complete checkout (Pro)
- 🔌 **REST API** — `/aga/v1/config` and `/aga/v1/validate` endpoints for headless stores (Pro)
- 🌙 **Dark Mode** — Dropdown adapts to `prefers-color-scheme: dark`
- 🔄 **RTL Support** — Full right-to-left language support (Arabic, Hebrew)
- ♿ **ARIA Accessibility** — Screen reader support with listbox/combobox roles
- 📱 **Elementor** — Address Validation, Geolocation, Saved Addresses now in both Widget and Form Field

**Bug Fixes:**
- 🍎 **iOS/Safari** — Touch events, autocomplete suppression, smooth scrolling
- 🐛 Race condition in rapid address selection
- 🐛 Stale API responses discarded on fast typing
- 🐛 Map drag no longer re-triggers autocomplete dropdown
- 🐛 Null safety for config.formats
- 🐛 Google Maps polling timeout (20s max)
- 🐛 ABSPATH protection on 6 PHP files

**Performance:**
- ⚡ Combined 2 DB queries into 1 for frontend loading
- ⚡ Health check results cached (5 min)
- ⚡ Async Google Maps loading

### v5.1.2 — Bug Fix

- 🐛 **Fix:** Settings toggle not saving — unchecking toggles now properly saves as OFF
- 🐛 **Fix:** `pointer is not a function` JavaScript error on settings page
- 🐛 **Fix:** Active tab tracking prevents saving on one tab from resetting toggles on other tabs

### v5.1.1 — Bug Fix

- 🐛 **Fix:** Setup wizard "Sorry, you are not allowed to access this page" error on fresh activation
- 🐛 **Fix:** Wizard page now registers as standalone hidden page, accessible regardless of CPT timing
- 🐛 **Fix:** Added capability check and redirect loop guard for wizard

### v5.1.0 — Major Feature Update

<details>
<summary><strong>Click to expand changelog</strong></summary>

- ✨ **Address Validation** — Verified / Warning / Invalid badge system for submitted addresses
- 📍 **Geolocation** — "Use My Location" GPS button for instant address detection
- 💾 **Saved Addresses** — Quick-select from last 5 addresses (logged-in users)
- 📊 **Usage Analytics Dashboard** — Track searches, selections, and popular addresses
- 🎨 **Custom Dropdown CSS** — Style the suggestion dropdown from the admin panel
- 🧩 **Native Elementor Widget** — Drag-and-drop autocomplete widget for Elementor
- 🌐 **Smart Country-Aware Mapping** — Intelligent city/state mapping for 30+ countries
- 🔍 **Place Type Filter** — Restrict results to addresses, cities, establishments, etc.
- 🌍 **Language Selector** — 80+ language options for suggestion results
- 🛒 **WooCommerce Block Checkout** — Full support for the new block-based checkout
- 🩺 **Diagnostic Health Check** — Auto-detect API issues, JS conflicts, and misconfigurations
- 🔄 **Import/Export** — JSON-based config transfer for multi-site setups
- ⚡ **Conflict Detection** — Automatically detect other plugins loading Google Maps JS

</details>

---

## 🤝 Contributing

Contributions, issues, and feature requests are welcome!

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/amazing-feature`)
3. **Commit** your changes (`git commit -m 'Add amazing feature'`)
4. **Push** to the branch (`git push origin feature/amazing-feature`)
5. **Open** a Pull Request

> Please make sure your code follows WordPress coding standards.

---

## 📄 License

This project is licensed under the **GPL-2.0-or-later** license.

See the [LICENSE](https://www.gnu.org/licenses/gpl-2.0.html) file for details.

---

## 💬 Support

Need help? We've got you covered.

| Channel | Link |
|---------|------|
| 💬 **WhatsApp Live Support** | [Chat with us](https://wa.me/8801767591988) |
| 🏛️ **WordPress.org Support Forum** | [Open a ticket](https://wordpress.org/support/plugin/autocomplete-google-address/) |
| 📧 **Plugin Page** | [wordpress.org/plugins/autocomplete-google-address](https://wordpress.org/plugins/autocomplete-google-address/) |

> **👑 Pro users** get priority support with faster response times via WhatsApp.

---

<p align="center">
  <strong>Made with ❤️ for the WordPress community</strong><br/>
  <sub>If this plugin helps you, please consider leaving a ⭐ review on <a href="https://wordpress.org/plugins/autocomplete-google-address/#reviews">WordPress.org</a></sub>
</p>
