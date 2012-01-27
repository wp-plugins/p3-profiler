=== P3 (Plugin Performance Profiler) ===
Contributors: Godaddy, StarfieldTech
Tags: debug, debugging, developer, development, performance, plugin, profiler, speed
Requires at least: 3.3
Tested up to: 3.3.1
Stable tag: 1.1.3

See which plugins are slowing down your site.  This plugin creates a performance report for your site.

== Description ==
This plugin creates a profile of your WordPress site's plugins' performance by measuring their impact on your site's load time.  Often times, WordPress sites load slowly because of poorly configured plugins or because there are so many of them. By using the P3 plugin, you can narrow down anything causing slowness on your site.

This plugin uses the canvas element for drawing charts and requires requires Firefox, Chrome, Opera, Safari, or IE9 or later.  This plugin will not work in IE8 or lower.

== Screenshots ==

1. First, profile your site.  The scanner generates some traffic on your site and monitors your site's performance on the server, then shows you the results. With this information, you can decide what action to take.
2. After profiling, you'll see a breakdown of relative runtime for each plugin.
3. Callouts at the top give you quick information like how much load time (in seconds) is dedicated to plugins and how many database queries your site is running per page.
4. The detailed timeline gives you timing information for every plugin, the theme, and the core for every page during the profile.  Find out exactly what's happening on slow loading pages.
5. You can toggle each series on and off to customize this timeline for your precise needs.
6. The query timeline gives you the number of database queries for every page during the profile.  Find out which pages generate the most database queries.
7. Keep a history of your performance scans, compare your current performance with your previous performance.
8. Full in-app help documentation
9. Send a summary of your performance profile via e-mail.  If you want to show your developer, site admin, hosting support, or a plugin developer what's going on with your site, this is good way to start the conversation.

== Installation ==
Automatic installation

1. Log into your WordPress admin
2. Click __Plugins__
3. Click __Add New__
4. Search for __P3__
5. Click __Install Now__ under "P3 (Plugin Performance Profiler)"
6. Activate the plugin

Manual installation:

1. Download the plugin
2. Extract the contents of the zip file
3. Upload the contents of the zip file to the wp-content/plugins/ folder of your WordPress installation
4. Then activate the Plugin from Plugins page.

== Upgrade Notice ==

= 1.1.3 =
Fixed a regression bug re-introduced in v 1.1.2.  Thanks to user adamf for finding this so quickly!

= 1.1.2 =
Fix a few bugs reported by users.  Upgrading is optional if this plugin is working well for you.

= 1.1.1 =
This release addresses a bug which which broke the UI on sites that used other plugins that contained an apostrophe in their name.  Upgrading is recommended if you were affected by this bug.

= 1.1.0 =
Several usability enhancements and bugfixes.

= 1.0.5 =
This version addresses a path disclosure issue.  Users are encouraged to upgrade.

== Frequently Asked Questions ==

= What if I get a warning about usort()? =

Warning messages like this: `Warning: usort() [function.usort]: Array was modified by the user comparison function` are due to a known php bug.  See [php bug #50688](https://bugs.php.net/bug.php?id=50688) for more information.  This warning does not affect the functionality of your site and it is not visible to your users.

= In the e-mail report, why is my theme detected as "unknown?" =

Previous version of the plugin (before 1.1.0) did not have theme name detection support.  If you performed a scan with a previous version, then upgraded to 1.1.0+ to view the scan, the theme name will show as "unknown."

= How do I get support for P3? =

We love to make P3 better.  When reporting a bug, please visit this page so we can get more information:  [http://x.co/p3support](http://x.co/p3support)

Thanks!

== Changelog ==

= 1.1.3 =
 * Bugfix - regression bug re-introduced in v 1.1.2.  Thanks to user adamf for finding this so quickly!

= 1.1.2 =
 * Don't show screen options if there is no table
 * Show a "rate us / tweet us" box
 * Add an option to circumvent browser cache
 * Bugfix - Properly work with encrypted plugins (eval based obfuscation)
 * Bugfix - Work with suhosin/safe mode where ini_set / set_time_limit are disabled
 * Bugfix - Remove "Options -Indexes" because it's causing 500 error in some apache setups
 * Bugfix - Fix a warning with theme name detection if the theme is no longer installed

= 1.1.1 =
 * Bugfix - Plugin names with apostrophes broke the UI
 * Bugfix - Fix a deprecated warning with callt-ime pass by reference

= 1.1.0 =
 * Including plugin usage percentage / seconds in e-mail report
 * Including theme name in e-mail report.  Profiles created in older versions will show "unknown"
 * Grammar / wording changes
 * Remembering "disable opcode cache" in options table
 * New option for "use my IP."  If this is set, the current user's IP address will be used, if not, the stored IP pattern will be used
 * IP patterns will be stored as an option
 * Fixed:  IP patterns were incorrectly escaped
 * Now displaying profile name in the top right
 * If the profile didn't record any visits (e.g. wrong IP pattern) then an error will be displayed
 * Fixing pagination on the history page
 * Made the legends on the charts a bit wider for sites with a lot of plugins and plugins with long names
 * Added the ability to toggle series on/off in the "detailed timeline" chart
 * Removed network wide activation code - each site will be "activated" when the admin logs in
 * Removed "sync all profile folders whenever a blog is added/deleted" code.  Profile folders will be added when admins log in, removed when blogs are removed
 * When uninstalling, all profile folders and options will be removed
 * Using get_plugin_data() to get plugin names.  If the plugin doesn't exist anymore, or there's a problem getting the plugin name, the old formatting code is used

= 1.0.5 =
 * Security - Fixed a path disclosure vulnerability
 * Security - sanitized user input before it gets back to the browser
 * Thanks to Julio Potier from [Boiteaweb.fr](http://www.boiteaweb.fr/)

= 1.0.4 =
 * Bugfix - uninstalling the plugin when it hasn't been activated can result in an error message

= 1.0.3 =
 * Enforcing WordPress 3.3 requirement during activation
 * Documented warning about usort() and php bug

= 1.0.2 =
 * Fixed an error message when clicking "stop scan" too fast
 * Brought plugin version from php file in line with version from readme.txt and tag

= 1.0.1 =
 * readme.txt changes

= 1.0 =
 * Automatic site profiling
 * Manual site profiling
 * Profile history
 * Continue a profile session
 * Clear opcode caches (if possible) to improve plugin function detection
 * Limit profiling by IP address (regex pattern)
 * Limit profiling by site URL (for MS compatibility)
 * Rewrite http URLs to https to avoid SSL warnings when using wp-admin over SSL
 * Hide the admin toolbar on the front-end when profiling to prevent extra plugin scripts/styles from loading
 * In-app help / glossary page
 * Activate / deactivate hooks to try different loader methods so the profiler runs as early as possible
 * Uninstall hooks to clean up profiles
 * Hooks add/delete blog to clean up profiles
 * Send profile summary via e-mail
