=== Reviews for Google My Business ===
Contributors: @fanny8p
Tags: reviews, google, testimonials
Requires at least: 6.8
Tested up to: 6.8
Stable tag: 1.0.0
Requires PHP: 8.0
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Display Google My Business reviews on your website for free. Improve credibility with full customization, categories, and flexible shortcode.

== Description ==
Key Features
- OAuth 2.0 authentication** - Secure connection to the Google My Business API
- Category system** - Organize your reviews by category (Training, Coaching, Design, etc.)
- Flexible shortcode** - Display all reviews or filter by category
- Advanced customization** - Customizable colors, borders, and stars
- Custom fields** - Add each reviewer's position

== External services ==

This plugin connects to Google APIs to authenticate and retrieve your Google Business Profile reviews. Below is a detailed explanation of all external services used:

= Google OAuth 2.0 Authentication =

**Service:** Google OAuth 2.0 Authorization Server
**Domain:** accounts.google.com
**Purpose:** To securely authenticate your Google account and obtain authorization to access your Google Business Profile data.
**When data is sent:** When you click "Authorize with Google" in the plugin configuration page.
**Data sent:**
- Your Google Cloud Client ID
- Redirect URI (your website URL)
- Requested scopes (permissions)
No personal data from your website is sent during this process. You are redirected to Google's servers where you grant permission.

**Service:** Google OAuth 2.0 Token Server
**Domain:** oauth2.googleapis.com
**Purpose:** To exchange authorization codes for access tokens and refresh expired tokens.
**When data is sent:**
- After you authorize the plugin (one-time exchange of authorization code)
- Automatically when access tokens expire (token refresh)
**Data sent:**
- Authorization code or refresh token
- Client ID and Client Secret
- Grant type

Google Terms of Service: https://policies.google.com/terms
Google Privacy Policy: https://policies.google.com/privacy

= Google My Business API =

**Service:** Google My Business Account Management API
**Domain:** mybusinessaccountmanagement.googleapis.com
**Purpose:** To retrieve the list of your Google Business Profile accounts and locations.
**When data is sent:** When you configure the plugin or click "Refresh Locations" in the settings page.
**Data sent:**
- Access token (for authentication)
No other data is sent. The API returns a list of your available business accounts and locations.

**Service:** Google My Business Business Information API
**Domain:** mybusinessbusinessinformation.googleapis.com
**Purpose:** To retrieve information about your business locations, including reviews.
**When data is sent:**
- During initial setup when selecting a location
- When you manually sync reviews using the "Sync Reviews from API" button
- Automatically when the plugin checks for new reviews
**Data sent:**
- Access token (for authentication)
- Account ID and Location ID (to identify which business location to fetch reviews from)
No customer data from your website is sent to Google.

**Service:** Google APIs Core Services
**Domain:** www.googleapis.com
**Purpose:** OAuth 2.0 scope definitions and core API authentication.
**Scopes used:**
- https://www.googleapis.com/auth/business.manage - Permission to manage your business information
- https://www.googleapis.com/auth/plus.business.manage - Permission to manage your Google+ business pages (legacy scope)
**When accessed:** During OAuth authentication flow.

Google My Business API Terms of Service: https://developers.google.com/my-business/content/terms-of-service
Google APIs Terms of Service: https://developers.google.com/terms
Google Privacy Policy: https://policies.google.com/privacy

= Important Notes =

- All communication with Google services is performed over secure HTTPS connections.
- This plugin does NOT send any of your website's customer data, user data, or visitor information to Google.
- Only authentication tokens and business identifiers are transmitted.
- Your Google Cloud API credentials (Client ID and Client Secret) are stored securely in your WordPress database.
- Reviews and profile photos are downloaded and stored locally on your server. After the initial download, no further external calls are made to display reviews to your visitors.
- You maintain full control and can revoke access at any time from the plugin settings or your Google account settings.

By using this plugin, you acknowledge that you have read and agree to comply with Google's Terms of Service and Privacy Policy.

== Installation ==
1. Install using the built-in WordPress plugin installer, or unzip the zip file and drop the contents into the wp-content/plugins/ directory of your WordPress installation.
2. Activate the plugin in the WordPress "Plugins" menu.
3. Go to Google Reviews and click "Configure Authentication".
4. Follow the instructions to configure the plugin. Documentation is available in Google Reviews → Configuration, "Documentation" tab.

== Usage ==

After configuration, display reviews using the shortcode:

`[wgmbr_reviews]`

**Shortcode Parameters:**
- `limit` - Number of reviews to display (e.g., `limit="10"`)
- `category` - Filter by category slug (e.g., `category="training"`)
- `show_summary` - Display summary stats (e.g., `show_summary="1"`)

**Examples:**
- `[wgmbr_reviews limit="5"]` - Display 5 most recent reviews
- `[wgmbr_reviews category="training"]` - Display reviews from "training" category
- `[wgmbr_reviews category="training,coaching" limit="10"]` - Display 10 reviews from multiple categories
- `[wgmbr_reviews show_summary="1"]` - Display reviews with rating summary

== Frequently Asked Questions ==
= Do I need technical skills to use this plugin? =
No! Although the initial setup requires creating a project on the Google Cloud Console (we provide a detailed step-by-step guide), once configured, using the plugin is very simple. Simply insert a shortcode on your page.

= Is the plugin really free? =
Yes, the plugin is 100% free. However, you must create a project on the Google Cloud Console to obtain your API credentials. Google offers a free quota that is more than sufficient for most websites.

= Why do I need to create a Google Cloud project? =
To display your Google My Business reviews, the plugin must connect to the Google API. This requires OAuth 2.0 credentials, which you obtain by creating a free project on the Google Cloud Console. This also ensures the security of your data.

= Are reviews updated automatically? =
Yes, the plugin automatically retrieves your new Google My Business reviews.

= Can I customize the appearance of reviews? =
Absolutely! The plugin offers many customization options: colors, layout, number of reviews displayed, category system, and more. You can adapt the display to your site's design.

== Security ==

This plugin implements multiple layers of security to protect your data:

= OAuth 2.0 Security =
- Uses industry-standard OAuth 2.0 authentication with Google
- Implements CSRF protection via OAuth state parameter (RFC 6749)
- State parameter is cryptographically random (64 hex characters)
- Single-use state tokens with 10-minute expiry
- All OAuth callbacks require admin authentication
- Secure token storage with encryption

= WordPress Security Best Practices =
- All AJAX requests use nonce verification
- All admin actions check user capabilities (manage_options)
- Input sanitization on all user inputs
- Output escaping on all outputs
- Prepared statements for database queries
- HTTPS required for OAuth token exchange

= Data Protection =
- Client secrets are encrypted before storage
- Access tokens are stored securely in WordPress database
- Profile photos are downloaded and stored locally (no external hotlinking)
- No customer or visitor data is sent to external services
- You can revoke access at any time from plugin settings

== For Developers ==

= Source Code =
This plugin uses Laravel Mix to compile JavaScript and CSS assets. All source code is available in the plugin directory:
- JavaScript source files: `src/js/`
- SCSS source files: `src/scss/`
- Compiled assets: `assets/js/` and `assets/css/`

= Build Instructions =
To compile the assets from source:

1. Install Node.js (https://nodejs.org/)
2. Navigate to the plugin directory
3. Install dependencies: `npm install`
4. Compile assets:
   - For development: `npm run dev`
   - For production (minified): `npm run prod`
   - Watch for changes: `npm run watch`

= Build Configuration =
The build process is configured in `webpack.mix.js` and uses Laravel Mix, which is a wrapper around webpack.

Dependencies:
- Laravel Mix: ^6.0.43
- Swiper: ^12.0.3 (for carousel functionality)

For more information about Laravel Mix, visit: https://laravel-mix.com/

== Upgrade Notice ==

= 1.0.0 =
IMPORTANT: This version includes WordPress.org compliance updates with prefix changes. The shortcode has been renamed from [gmb_reviews] to [wgmbr_reviews]. Admin page URLs have also changed. Please update your shortcodes after upgrading.

== Changelog ==

= 1.0.0 =
* Initial release
* WordPress.org compliance: Updated all prefixes to meet 4+ character requirement
* Changed shortcode from [gmb_reviews] to [wgmbr_reviews]
* Security improvements: Enhanced data sanitization, validation, and escaping
* Security improvements: Improved OAuth 2.0 implementation with state parameter
* External service documentation: Added comprehensive documentation of Google API usage
* Source code: Made all uncompiled JavaScript source files available
* Profile photos: Now downloaded and stored locally instead of hotlinking