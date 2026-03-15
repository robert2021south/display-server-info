=== Display Server Info ===
Contributors: robert2021south
Tags: server info, php info, dashboard widget, system info, shortcode
Donate link: http://ko-fi.com/robertsouth
Requires at least: 5.6
Tested up to: 6.9
Requires PHP: 7.2
Stable tag: 2.2.0
License: GPLv3 or later
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Displays server, PHP, and database info in the dashboard, admin bar, and footer, with shortcode and multilingual support.

== Description ==

Full Description:
Display Server Info is a powerful WordPress plugin that provides detailed server information for administrators directly within the dashboard.
It adds server, PHP, and database information to the dashboard, top admin bar, and footer for quick access.
The settings menu includes a "Display Server Info" submenu with two tabs:

1. Server Information Tab:
   In this tab, you will see the information about:
    Operating System
    Hostname
    Server IP
    Protocol
    Server Software
    Web Port
    CGI Version

    PHP Version
    Memory Limit
    Max Execution Time
    Upload Max Filesize
    Max File Uploads

    Database Server version
    Database Client version
    Database host
    Database username
    Database name
    Table prefix
    Database charset
    Database collation

    Please rate the Plugin if you find it useful, thanks.

2. Settings Tab:
   Configure plugin behavior with four toggle switches to enable or disable server information display in the dashboard, admin bar, and footer.
   Additionally, users can activate shortcode functionality, allowing `[disi_server_info]` to display server information anywhere in posts or pages.

The plugin is fully translated into 7 international languages, making it accessible for a global audience.

= Privacy =
This plugin respects your privacy. Any data collected (such as anonymous usage stats or optional feedback) is handled transparently and securely.
All data, including any optional email address, is transmitted securely (via TLS/HTTPS) and retained only as long as necessary. For example, feedback data is retained for a maximum of 12 months to help us improve the plugin.
For complete details on what data is collected, how it is stored (including hashing and encryption for email addresses), the legal basis under GDPR/CCPA, and your rights, please read our full privacy policy:
https://robertwp.com/privacy-policy

== Frequently Asked Questions ==

= Does this plugin slow down my website? =
No. Display Server Info is lightweight and optimized. It performs simple server checks and does not impact your site’s performance.

= Where can I see the server information after installing the plugin? =
You can view it in multiple places:
 	Dashboard widget
 	Top admin bar
 	Admin footer
 	“More Info” page
 	PHP info page
    And via shortcode on posts or pages

= Does the plugin clean up after uninstalling? =
Yes. All plugin options are removed during uninstall to keep your database clean.Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.

= Is the plugin compatible with the latest WordPress version? =
Yes. The plugin is tested with the latest WordPress releases and maintained with ongoing updates.

= Can I display server information on the frontend? =
Yes. Use the shortcode <code data-start="1463" data-end="1483">[disi_server_info]</code> to display server details anywhere on your site.

== Installation ==

Installation from within WordPress
Visit Plugins > Add New.
Search for Display Server Info.
Install and activate the Display Server Info plugin.

Manual installation
Upload the entire display-server-info folder to the /wp-content/plugins/ directory.
Visit Plugins.
Activate the Display Server Info plugin.

If everything is all right, you will see the "Server Information" widget under the dashboard "At a Glance" widget.

== Screenshots ==
1. Dashboard Server Info
2. Admin Bar Server Info
3. Footer Server Info
4. More Page
5. Setting Page
6. Phpinfo
7. Shortcode Use Case

== Changelog ==

= 2.2.0 =
* Added feedback form in settings page
* Implemented star rating system with half-star support
* Added option to leave feedback with email (optional)
* Enhanced user experience with toast notifications

= 2.1.3 =
* Update author name

= 2.1.2 =
* Added Author URI to the plugin header: https://robertwp.com
* Replaced namespace DisplayServerInfoPlugin with RobertWP\DisplayServerInfo
* Removed `load_plugin_textdomain()` call since it's no longer needed for WordPress 4.6 and above.

= 2.1.1 =
* Added uninstall cleanup functionality — plugin options will now be removed when uninstalled.

= 2.1.0 =
* Added uninstall cleanup functionality — plugin options will now be removed when uninstalled.

= 2.0.0 =
* Code refactored to object-oriented
* Added new feature: Display server information in the upper right corner of the management bar
* Added new feature: Display server information in the footer
* Added more pages: Display more server information, PHP information and database information
* Added configuration page: Configure whether to display in the dashboard, top management bar and footer
* Added shortcode support: Server information can be called in posts and pages

= 1.0.1 =
* Initial version

== Upgrade Notice ==

= 2.2.0 =
* Added feedback form in settings page
* Implemented star rating system with half-star support
* Added option to leave feedback with email (optional)
* Enhanced user experience with toast notifications