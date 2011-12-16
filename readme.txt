=== P3 (Plugin Performance Profiler) ===
Contributors: Godaddy, StarfieldTech
Tags: debug, debugging, developer, development, performance, plugin, profiler, speed
Requires at least: 3.3
Tested up to: 3.3
Stable tag: 1.0.4

See which plugins are slowing down your site.  This plugin creates a performance report for your site.

== Description ==
This plugin creates a profile of your WordPress site's plugins' performance by measuring their impact on your site's load time.  Often times, WordPress sites load slowly because of poorly configured plugins or because there are so many of them. By using the P3 plugin, you can narrow down anything causing slowness on your site.

Requires Firefox, Chrome, Opera, Safari, or IE9 or later.

== Screenshots ==

1. First, profile your site.  The scanner generates some traffic on your site and monitors your site's performance on the server, then shows you the results. With this information, you can decide what action to take.
2. After profiling, you'll see a breakdown of relative runtime for each plugin.
3. Callouts at the top give you quick information like how much load time (in seconds) is dedicated to plugins and how many database queries your site is running per page.
4. The detailed timeline gives you timing information for every plugin, the theme, and the core for every page during the profile.  Find out exactly what's happening on slow loading pages.
5. The query timeline gives you the number of database queries for every page during the profile.  Find out which pages generate the most database queries.
6. Keep a history of your performance scans, compare your current performance with your previous performance.
7. Full in-app help documentation
8. Send a summary of your performance profile via e-mail.  If you want to show your developer, site admin, hosting support, or a plugin developer what's going on with your site, this is good way to start the conversation.

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

== Frequently Asked Questions ==

= What if I get a warning about usort()? =

Warning messages like this: `Warning: usort() [function.usort]: Array was modified by the user comparison function` are due to a known php bug.  See [php bug #50688](https://bugs.php.net/bug.php?id=50688) for more information.  This warning does not affect the functionality of your site and it is not visible to your users.

== Changelog ==

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