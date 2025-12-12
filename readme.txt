=== Reviews for Google My Business ===
Contributors: @fanny8p
Tags: google reviews, testimonials, google my business, reviews, social proof
Requires at least: 6.8
Tested up to: 6.9
Stable tag: 1.0.6
Requires PHP: 8.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display Google My Business reviews on your website for free. Improve credibility with full customization, categories, and flexible shortcode.

== Description ==

**The only WordPress plugin that gives you unlimited access to 100 Google reviews - Completely free!**

Unlike other plugins that limit you to 5-10 reviews or hide features behind paywalls, Reviews for Google My Business is 100% free forever with no premium upsells.

**Before start**

To operate you will need to follow instructions on the tab "Documentation". There you'll find the steps to configure access to reviews for your business listing. You'll also need Google validation (which can take 1-3 business days) before you can use the API.

= Why choose this plugin? =

* **Display up to 100 reviews** - No artificial limits, no premium versions
* **100% Free Forever** - All features included, no hidden costs
* **Beautiful Modern Slider** - Responsive design with smooth animations
* **Category System** - Organize reviews by service type (Training, Coaching, Design, etc.)
* **Advanced Customization** - Colors, borders, stars, layouts - fully customizable
* **OAuth 2.0 Security** - Enterprise-grade secure authentication
* **SEO Optimized** - Schema.org markup ready for better search rankings
* **No External Dependencies** - Reviews stored locally on your server
* **Lightning Fast** - Optimized code with 1-hour smart caching

= Key features =

**Display & Layout:**

* Modern responsive slider with 3 reviews per view on desktop
* Beautiful card design with customizable colors
* Author photos (automatically downloaded and stored locally)
* "Read more" button for long reviews
* Average rating summary with total review count

**Organization:**

* Category system to organize reviews by topic
* Filter reviews by one or multiple categories
* Custom job titles for each reviewer
* Flexible shortcode with powerful parameters

**Customization:**

* Customize all colors (cards, stars, text, accents)
* Adjustable border radius
* Responsive design for all devices
* No coding required - visual customization panel

**Performance & Security:**

* OAuth 2.0 secure authentication
* 1-hour intelligent caching system
* Reviews stored in WordPress database
* Encrypted API credentials
* GDPR compliant (no visitor data sent to Google)
* Optimized database queries for large review sets

= Simple shortcode usage =

To display Google My Business reviews on your site, just copy the shortcode and paste the shortcode in your pages, posts or widgets directly in the text editor.
The shortcode will automatically transform into visible content on your site! You can place it anywhere: in a page, a post, or even in a widget area depending on your theme.

= Full customization =

Access the customization panel in **Google Reviews → Configuration → Customization** to personalize:

* Card background colors
* Star colors
* Text colors
* Accent colors (navigation, buttons)
* Border radius

= Enterprise-grade security =

* OAuth 2.0 authentication (industry standard)
* Encrypted credential storage
* WordPress nonce verification on all actions
* CSRF protection with state parameters
* Input sanitization and output escaping
* Prepared SQL statements
* Regular security audits

== External Services ==

This plugin connects to Google APIs to authenticate and retrieve your Google Business Profile reviews. **Important:** You maintain full control and no visitor data is sent to Google.

= Google OAuth 2.0 Authentication =

**Service:** Google OAuth 2.0 Authorization Server
**Domain:** accounts.google.com
**Purpose:** Secure authentication to access your Google Business Profile
**When used:** When you click "Connect with Google" in settings
**Data sent:** Client ID, redirect URI, requested permissions
**Privacy:** No personal or visitor data is transmitted

**Service:** Google OAuth 2.0 Token Server
**Domain:** oauth2.googleapis.com
**Purpose:** Exchange authorization codes for access tokens
**When used:** After authorization and for token refresh
**Data sent:** Authorization code, Client ID, Client Secret

Google Terms of Service: https://policies.google.com/terms
Google Privacy Policy: https://policies.google.com/privacy

= Google My Business APIs =

**Service:** Google My Business Account Management API
**Domain:** mybusinessaccountmanagement.googleapis.com
**Purpose:** Retrieve your Google Business Profile accounts and locations
**When used:** During setup and when clicking "Refresh Locations"
**Data sent:** Access token only

**Service:** Google My Business Business Information API
**Domain:** mybusinessbusinessinformation.googleapis.com
**Purpose:** Retrieve reviews from your business location
**When used:** During sync (manual or automatic via hourly cache refresh)
**Data sent:** Access token, Account ID, Location ID

**Scopes used:**

* `business.manage` - Permission to manage business information
* `plus.business.manage` - Legacy Google+ business pages

Google My Business API Terms: https://developers.google.com/my-business/content/terms-of-service
Google APIs Terms: https://developers.google.com/terms

= Privacy & Security Notes =

* All communication uses secure HTTPS
* Reviews are downloaded and stored locally (no hotlinking)
* NO visitor, customer, or user data is sent to Google
* Only authentication tokens and business identifiers are transmitted
* You can revoke access anytime from plugin settings
* API credentials encrypted in WordPress database

By using this plugin, you agree to comply with Google's Terms of Service and Privacy Policy.

== Installation ==

= Automatic Installation (Recommended) =

1. Go to **Plugins → Add New** in your WordPress admin
2. Search for "Reviews for Google My Business"
3. Click **Install Now** and then **Activate**
4. Go to **Google Reviews → Configuration**
5. Follow the setup wizard (takes approximately 5 minutes)

= Manual Installation =

1. Download the plugin zip file
2. Go to **Plugins → Add New → Upload Plugin**
3. Upload the zip file and click **Install Now**
4. Click **Activate Plugin**
5. Go to **Google Reviews → Configuration**

= Configuration (One-Time Setup) =

**Step 1: Create Google Cloud Project (Free)**

1. Go to https://console.cloud.google.com
2. Create a new project
3. Enable "Google My Business API"
4. Create OAuth 2.0 credentials (detailed guide in plugin)

**Step 2: Connect the Plugin**

1. Enter your Client ID and Client Secret
2. Click "Connect with Google"
3. Authorize the plugin
4. Select your business location

**Step 3: Display Reviews**

1. Add the shortcode `[wgmbr_reviews]` to any page or post
2. Customize colors in the Customization tab
3. Done! Your reviews are now live

**Setup Time:** Approximately 5 minutes | **Documentation:** Available in the plugin under "Documentation" tab

== Frequently Asked Questions ==

= Is this plugin really 100% free? =

**Yes!** Unlike other plugins, there is NO premium version, NO feature limitations, and NO hidden fees. All 100 reviews and all features are completely free forever. We believe in true open-source software.

= Why do you offer 100 reviews for free when competitors charge for this? =

Because we believe Google My Business reviews belong to YOU, not to plugin developers. We built this plugin to give small businesses the tools they need without artificial paywalls.

= How many reviews can I display? =

You can display up to 100 Google My Business reviews. This is the maximum the plugin supports, and it's completely free. You can use the `limit` parameter to show fewer (e.g., `limit="20"`).

= Do I need coding skills? =

No! The plugin includes a visual customization panel. However, initial setup requires creating a Google Cloud project (we provide a step-by-step guide with screenshots).

= Why do I need to create a Google Cloud project? =

Google requires OAuth 2.0 credentials to access their APIs. This is a security requirement from Google, not the plugin. The good news: Google Cloud is free for standard usage, and setup takes only 5 minutes with our guide.

= Are reviews updated automatically? =

Yes! Reviews are cached for 1 hour for performance. After that, the plugin automatically fetches new reviews from Google. You can also manually sync anytime from the admin panel.

= Can I filter reviews by category? =

Yes! You can create custom categories (e.g., "Training", "Coaching", "Design") and assign reviews to them. Then filter reviews in your shortcode: `[wgmbr_reviews category="training"]`

= Can I customize the design? =

Absolutely! Go to **Google Reviews → Configuration → Customization** to customize:

* Card background color
* Star color
* Text colors
* Accent colors
* Border radius

All changes apply instantly without coding.

= Will this work with my theme? =

Yes! The plugin uses clean, modern CSS that works with any WordPress theme. The design is fully responsive (mobile, tablet, desktop).

= Does this affect my site's loading speed? =

No! Reviews are cached for 1 hour and stored in your WordPress database. The slider uses optimized JavaScript (Swiper.js). Average load time impact: less than 100ms.

= What happens if I have less than 3 reviews? =

The plugin automatically adapts! With 1-2 reviews, it displays them in a static layout (no slider) with proper centering. With 3+ reviews, it shows a beautiful slider.

= Can I display reviews on multiple pages? =

Yes! Use the shortcode on as many pages as you want. Each page can have different settings (limit, category, etc.).

= Is this GDPR compliant? =

Yes! The plugin does NOT send any visitor, customer, or user data to Google. Only YOUR business reviews are fetched and stored locally on your server.

= Can I translate the plugin? =

Yes! The plugin is translation-ready. English and French are included. For other languages, use .po/.mo files or translation plugins like WPML or Polylang.

= What if I need help? =

Check the **Documentation** tab in the plugin for detailed guides. For issues, visit our GitHub repository or WordPress.org support forum.

= Can I contribute to the plugin? =

Absolutely! This is open-source software. Developers are welcome to contribute via our GitHub repository.

== Changelog ==

= 1.0.6 - 2025-12-12 =

**Improvements:**

* Improved: Added CSS imports for Swiper Navigation and Pagination modules for proper dynamic bullets rendering
* Improved: Configured dynamic bullets with maximum 5 visible bullets (`dynamicMainBullets: 5`)
* Improved: Disabled loop and autoplay on slider for better user control

= 1.0.5 - 2025-11-27 =

**Bug Fixes:**

* Fixed: Slider not displaying due to missing Swiper CSS import
* Fixed: Improved original text extraction from Google translated reviews (now handles both translation formats)

**Improvements:**

* Improved: Disabled autoplay on slider for better user control
* Improved: Added Swiper CSS to frontend styles for proper slider rendering

= 1.0.4 - 2025-11-21 =

**New Features:**

* Added: French translation (fr_FR)

**Improvements:**

* Improved: Slider now works correctly with only 2 reviews (displays 1 slide at a time with loop enabled)
* Improved: Reviews table now has horizontal scroll on screens smaller than 1200px
* Improved: Documentation code follows WordPress i18n best practices (URLs moved out of translatable strings)
* Fixed: Navigation and grab functionality now work properly with 2 reviews on all devices

= 1.0.3 - 2025-01-19 =

**Documentation:**

* Improved: Simplified plugin title for better clarity
* Improved: More professional tone (reduced excessive capitalization)
* Added: "Before start" section with setup prerequisites and Google validation timeline
* Improved: Better user expectations about initial configuration requirements

= 1.0.2 - 2025-01-19 =

* Updated: README short description to comply with 150 character limit

= 1.0.1 - 2025-01-19 =

**Bug Fixes:**

* Fixed: Slider layout broken when displaying 1-2 reviews (now uses static layout with proper centering)
* Fixed: Shortcode generator showing incorrect prefix (`gmb_reviews` instead of `wgmbr_reviews`)
* Fixed: Missing frontend JavaScript file (app.js was not enqueued, preventing slider from working)
* Fixed: Review card width issues in slider mode

**Improvements:**

* Changed: Increased default review limit from 50 to 100
* Changed: Slider automatically adapts - static layout for 1-2 reviews, slider for 3+ reviews
* Improved: Better responsive design for small review counts

**Documentation:**

* Removed emojis from README for better WordPress.org compliance
* Updated all documentation to reflect 100 review limit
* Improved professional appearance and formatting

= 1.0.0 - 2025-01-19 =

**Initial Release - Built for the Community**

**New Features:**

* Display up to 100 Google My Business reviews
* Beautiful modern slider with smooth animations
* Category system for review organization
* Advanced color customization panel
* OAuth 2.0 secure authentication
* Flexible shortcode with multiple parameters
* Automatic review synchronization
* Local storage (no hotlinking)
* Responsive design for all devices
* SEO-optimized markup
* Translation-ready (EN, FR included)

**Security:**

* WordPress.org compliance: All functions prefixed with `wgmbr_`
* Enhanced data sanitization and validation
* OAuth 2.0 with CSRF protection (state parameter)
* Encrypted credential storage
* Prepared SQL statements
* Input/output escaping

**Performance:**

* Smart 1-hour caching system
* Optimized database queries for 100+ reviews
* Lazy loading for images
* Minified assets (CSS/JS)
* CDN-ready

**Developer Features:**

* Clean, documented code
* WordPress coding standards compliant
* Laravel Mix build system
* Source files included (src/)
* GitHub repository available

**Bug Fixes:**

* Fixed slider layout for 1-2 reviews (now uses static layout)
* Fixed shortcode prefix in generator (`wgmbr_reviews` instead of `gmb_reviews`)
* Fixed missing app.js enqueue (JavaScript now loads correctly)
* Fixed review card width in slider mode

== Upgrade Notice ==

= 1.0.6 =
Improved slider pagination with dynamic bullets (max 5 visible), proper CSS module imports, and disabled loop/autoplay for better user control.

= 1.0.4 =
Added French translation, improved slider behavior with 2 reviews, and added horizontal scroll for tables on small screens.

= 1.0.3 =
Documentation improvements: More professional tone and better user expectations. Added setup prerequisites information. No functional changes.

= 1.0.2 =
README update: Short description adjusted to meet WordPress.org 150 character limit. No functional changes.

= 1.0.1 =
Important bug fixes: Resolves slider layout issues with 1-2 reviews, fixes missing JavaScript enqueue, and corrects shortcode generator. Default limit increased to 100 reviews. Update recommended.

= 1.0.0 =
Initial release. Welcome to the most generous Google Reviews plugin for WordPress! Display up to 100 reviews, 100% free, forever.
