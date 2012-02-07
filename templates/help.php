<?php
if ( !defined('P3_PATH') )
	die( 'Forbidden ');
?>
<script type="text/javascript">
	// Set up the tabs
	jQuery( document ).ready( function( $) {
		$( "#toggle-glossary" ).click( function() {
			$( "#glossary-terms" ).toggle();
			if ( "Hide Glossary" == $( "#toggle-glossary" ).html() ) {
				$( "#toggle-glossary" ).html( "Show Glossary" );
			} else {
				$( "#toggle-glossary" ).html( "Hide Glossary" );
			}
		});
		$( "#glossary-terms td.term" ).click( function() {
			var definition = $( "div.definition", $( this ) ).html();
			$( "#p3-glossary-term-display" ).html( definition );
			$( "#p3-glossary-table td.term.hover" ).removeClass( "hover" );
			$( this ).addClass( "hover" );
		});
		$( "#p3-glossary-table td.term:first" ).click();
		$( "#p3-hide-glossary" ).click( function() {
			if ( "Hide" == $( this ).html() ) {
				$( "#p3-glossary-table tbody" ).hide();
				$( "#p3-glossary-table tfoot" ).hide();
				$( this ).html( "Show" );
			} else {
				$( "#p3-glossary-table tbody" ).show();
				$( "#p3-glossary-table tfoot" ).show();
				$( this ).html( "Hide" );
			}
		});
		

		// Debug log
		$( "#p3-hide-debug-log" ).click( function() {
			if ( "Hide" == $( this ).html() ) {
				$( "#p3-debug-log-table thead" ).hide();
				$( "#p3-debug-log-table tbody" ).hide();
				$( "#p3-debug-log-table tfoot" ).hide();
				$( this ).html( "Show" );
			} else {
				$( "#p3-debug-log-table thead" ).show();
				$( "#p3-debug-log-table tbody" ).show();
				$( "#p3-debug-log-table tfoot" ).show();
				$( this ).html( "Hide" );
			}
		});
		$( "#p3-debug-log-container table tbody tr:even ").addClass( "even" );


		// Automatically create the table of contents
		var links = [];
		var i = 1;
		$( "h2.p3-help-question:not(:first )" ).each( function() {
			if ( $( this ).attr( "data-question-id" ) !== undefined ) {
				$( this ).before( '<a name="' + $( this ).attr( "data-question-id" ) + '">&nbsp;</a>' );
				links.push( '<li><a href="#' + $( this ).attr( "data-question-id" ) + '">' + $( this ).html() + '</a></li>' );
			} else {
				$( this ).before( '<a name="q' + i + '">&nbsp;</a>' );
				links.push( '<li><a href="#q' + i + '">' + $( this ).html() + '</a></li>' );
				i++;
			}
		});
		$( "div.p3-question blockquote:not(:first )" ).each( function() {
			$( this ).after( '<a href="#top">Back to top</a>' );
		});
		$( "#p3-help-toc" ).html( "<ul>" + links.join( "\n" ) + "</ul>" );
		
		$( "div.p3-question" ).corner( "round 8px" )
	});
</script>

<div class="p3-question">
	<a name="top">&nbsp;</a>
	<h2 class="p3-help-question">Contents</h2>
	<blockquote>
		<div id="p3-help-toc"></div>
	</blockquote>
</div>


<div class="p3-question">
	<h2 class="p3-help-question">What does the P3 plugin do?</h2>
	<blockquote>
		This plugin does just what its name says, it creates a profile of your WordPress site's plugins' performance
		by measuring their impact on your site's load time.
		<br /><br />
		Often times, WordPress sites load slowly because of poorly-configured plugins or because there are so many of
		them. This plugin can help you narrow down the cause of your site's slowness.
	</blockquote>
</div>

<div class="p3-question">
	<h2 class="p3-help-question">How do I use this?</h2>
	<blockquote>
		Simply click "Start Scan" to run an automated scan of your site. The scanner generates some traffic on your
		site and monitors your site's performance on the server, then shows you the results. With this information,
		you can decide what action to take.
	</blockquote>
</div>

<div class="p3-question">
	<h2 class="p3-help-question">What do I do with these results?</h2>
	<blockquote>
		If your site loads in an acceptable time (usually &lt; 0.5 seconds), you might consider other explanation for
		sluggish loading. For example, loading large images, large videos, or a lot of content can cause slowness.
		Tools like <a href="http://www.webpagetest.org/" target="_blank">webpagetest.org</a>, <a href="http://getfirebug.com/"
		target="_blank">Firebug</a>, <a href="http://tools.pingdom.com/" target="_blank">Pingdom tools</a>, or
		<a href="http://developer.apple.com/technologies/safari/developer-tools.html" target="_blank">Safari Developer Tools</a>
		or <a href="http://code.google.com/chrome/devtools/docs/overview.html" target="_blank">Chrome Developer Tools</a> can
		show you a connection breakdown of your site's content.
	</blockquote>
</div>

<div class="p3-question">
	<h2 class="p3-help-question" data-question-id="q-circumvent-cache">How do I fix "No visits recorded..." ?</h2>
	<blockquote>
		This error message means that after being disabled, the profiler did not record any traffic on your site.  There are several common
		causes for this:
		<ul>
			<li>
				<strong>Cause:</strong> Your site is using a caching plugin.  The pages that are being scanned aren't actually loading on
				the server because they're cached in your browser or on the server before WordPress can generate them.  The P3 plugin doesn't
				load and doesn't record any traffic.
				<br />
				<strong>Solution:</strong> Enable the "Attempt to circumvent browser cache" option in the advanced settings.
			</li>
			<li>
				<strong>Cause:</strong> The IP address you've entered in the advanced settings dialog doesn't match the IP address you're
				scanning from.
				<br />
				<strong>Solution:</strong> Check the IP address you've entered and try again.				
			</li>
			<li>
				<strong>Cause:</strong> You've selected a manual scan, but haven't generated any traffic.
				<br />
				<strong>Solution:</strong> Try the automated scan.
			</li>
		</ul>
	</blockquote>
</div>

<div class="p3-question">
	<h2 class="p3-help-question">Why did P3 only record 2 or 3 visits during the scan?</h2>
	<blockquote>
		If your site is using a caching plugin, some pages might be cached in your browser or on the server and are loading before before WordPress
		can generate them.  When this happens, the P3 plugin doesn't load and doesn't record any traffic.  Please enable the "Attempt to circumvent
		browser cache" option in the advanced settings.
	</blockquote>
</div>

<div class="p3-question">
	<h2 class="p3-help-question">How does this work?</h2>
	<blockquote>
		When you activate the plugin by clicking "Start Scan," it detects visits from your IP address, and actively monitors
		all <a href="http://php.net/functions" target="_blank">php user defined function calls</a> while the server generates
		your WordPress pages. It then records the information in a report file you can view later. When the scan is complete,
		or you click "Stop Scan," the plugin becomes dormant again.
	</blockquote>
</div>

<div class="p3-question">
	<h2 class="p3-help-question">How does my site load the plugin?</h2>
	<blockquote>
		The plugin should be active at the earliest point in the code execution. The plugin can be loaded through an
		auto_prepend_file configuration directive from a .htaccess file or a <a href="http://php.net/manual/en/configuration.file.per-user.php"
		target="_blank">.user.ini</a> file, but be careful. The .user.ini files are cached, so you must remove the entry from your
		.user.ini file before you remove this plugin.
		<br /><br />
		This plugin automatically creates a <a href="http://codex.wordpress.org/Must_Use_Plugins" target="_blank">must-use</a>
		plugin to load before other plugins.  If that doesn't work, it runs like a regular plugin.
		<br /><br />
		You are currently using: 
	<?php
	// must-use plugin file
	$mu_file = WPMU_PLUGIN_DIR . '/p3-profiler.php';
	?>
	<?php /* must-use plugin file is there and not-empty */ ?>
	<?php if ( file_exists( $mu_file ) && filesize( $mu_file ) > 0 ){ ?>
		<a href="http://codex.wordpress.org/Must_Use_Plugins" target="_blank">must-use plugin</a>
		- <code><?php echo realpath( $mu_file ); ?></code>
	<?php /* default, using this plugin file */ ?>
	<?php } else { ?>
		<a href="http://codex.wordpress.org/Plugins" target="_blank">plugin</a>
		- <code><?php echo realpath( P3_PATH . '/p3-profiler.php' ); ?></code>
	<?php } ?>
	</blockquote>
</div>

<div class="p3-question">
	<h2 class="p3-help-question">How accurate are these results?</h2>
	<blockquote>
		The results have an inherent margin of error because of the nature of the tool and its multi-layered design.
		The plugin changes the environment to measure it, and that makes it impossible to get completely accurate results.
		<br /><br />
		It gets really close, though! The "margin of error" on the Advanced Metrics page displays the discrepancy between
		the measured results (the time for your site's PHP code to completely run) and the expected results (sum of the plugins,
		core, theme, profile load times) to show you the plugin's accuracy.
		<br /><br />
		If you want more accurate results, you'll need to resort to a different profiler like <a href="http://xdebug.org/" target="_blank">xdebug</a>,
		but this will not break down results by plugin.
	</blockquote>
</div>

<div class="p3-question">
	<h2 class="p3-help-question">Why are some plugins slow?</h2>
	<blockquote>
		WordPress is a complex ecosystem of plugins and themes, and it lives on a complex ecosystem of software on your web server.
		<br /><br />
		If a plugin runs slowly just once, it's probably an anomaly, a transient hiccup, and you can safely ignore it.
		<br /><br />
		If a plugin shows slowness once on a reguarly basis (e.g. every time you run a scan, once a day, once an hour), a scheduled
		task might be causing it. Plugins that backup your site, monitor your site for changes, contact outside sources (e.g. RSS feeds),
		warm up caches, etc. can exhibit this kind of behavior.
		<br /><br />
		If a plugin shows as fast-slow-fast-slow-fast-slow, it could be caused as the plugin loads its main code, then a follow-up piece
		of code, like a piece of generated JavaScript.
		<br /><br />
		If a plugin consistently shows slowness, you might want to contact the plugin author or try deactivating the plugin temporarily
		to see if it makes a difference on your site.
	</blockquote>
</div>

<div class="p3-question">
	<h2 class="p3-help-question">How are these results different from YSlow / PageSpeed / Webpagetest.org / Pingdom Tools?</h2>
	<blockquote>
		This plugin measures how your site was generated on the server. Tools like <a href="http://developer.yahoo.com/yslow/"
		target="_blank">YSlow</a>, <a href="https://developers.google.com/pagespeed/" target="_blank">PageSpeed</a>,
		<a href="http://www.webpagetest.org/" target="_blank">Webpagetest.org</a>, and <a href="http://tools.pingdom.com/fpt/"
		target="_blank">Pingdom Tools</a> measure how your site looks to the browser.
	</blockquote>
</div>

<div class="p3-question">
	<h2 class="p3-help-question">What can interfere with testing?</h2>
	<blockquote>
		Opcode optimizers can interfere with PHP backtraces. Leaving opcode optimizers turned on will result in timing that more accurately
		reflects your site's real performance, but the function calls to plugins may be "optimized" out of the backtraces and some
		plugins (especially those with only one hook) might not show up. Disabling opcode caches results in slower times, but shows all plugins.
		<br /><br />
		By default, this plugin attempts to disable any detected opcode optimizers when it runs. You can change this setting by clicking "Advanced
		Settings" under "Start Scan." 
		<br /><br />
		Caching plugins that have an option to disable caches for logged in users will not give you the same performance profile that
		an anonymous users experience. To get around this, you should select a manual scan, then run an incognito browser window, or run
		another browser, and browse your site as a logged out user. When you're finished, click "I'm done," and your scan should show the
		performance of an anonymous user.
	</blockquote>
</div>

<div class="p3-question">
	<h2 class="p3-help-question" data-question-id="q-opcode-optimizer">Is my site using an opcode optimizer?</h2>
	<blockquote>
		<?php $detected = 0; if ( extension_loaded( 'xcache' ) ) { $detected++; ?>
			Your site is using XCache.  Although XCache reports that no opcode optimization won't be implemented until
			version 2.0, this has been known to cause problems with P3.<br />
		<?php } ?>	
		<?php if ( extension_loaded( 'apc' ) ) { $detected++; ?>
			Your site is using APC.  This has not been known to cause problems with P3.<br />
		<?php } ?>
		<?php if ( true or extension_loaded( 'eaccelerator' ) && ini_get( 'eaccelerator.optimizer' ) ) { $detected++; ?>
			Your site is using eaccelerator with optimization enabled.  This has been known to cause problems with P3.  To temporarily
			disable the optimizer
			<?php if ( false and 'apache2handler' == strtolower( php_sapi_name() ) ) { ?>
				you can add <code>php_flag eaccelerator.optimizer Off</code> to your site's .htaccess file.
			<?php } elseif ( version_compare( PHP_VERSION, '5.3.0' ) >= 0 ) { ?>
				you can add <code>eaccelerator.optimizer = 0</code> to your site's <a href="http://php.net/manual/en/configuration.file.per-user.php" target="_blank"><?php echo ini_get( 'user_ini.filename' ); ?> file</a>.
			<?php } else { ?>
				you can ask your hosting provider.
			<?php } ?>
			<br />
		<?php } ?>
		<?php if ( extension_loaded( 'Zend Optimizer+' ) && ini_get( 'zend_optimizerplus.optimization_level' ) > 0 ) { $detected++; ?>
			Your site is using Zend Optimizer+.  This has not been known to cause problems with P3.<br />
		<?php } ?>
		<?php if ( extension_loaded( 'IonCube Loader' ) ) { $detected++; ?>
			Your site is using the IonCube loader.  This has not been known to cause problems with P3. <br />
		<?php } ?>
		<?php if ( extension_loaded( 'wincache' ) ) { $detected++; ?>
			Your site is using wincache.  This has not been known to cause problems with P3. <br />
		<?php } ?>
		<?php if ( extension_loaded( 'Zend Guard Loader' ) ) { $detected++; ?>
			Your site is using the Zend Guard loader.  This has not been known to cause problems with P3. <br />
		<?php } ?>
		<?php if ( extension_loaded( 'Zend Optimizer' ) ) { $detected++; ?>
			Your site is using the Zend Optimizer.  This extension has not been tested with P3.  Please report any problems.<br />
		<?php } ?>
		<?php if ( !$detected ) { ?>
			Your site is not using any opcode optimizers that have been detected by P3.
		<?php } ?>
	</blockquote>
</div>


<div class="p3-question">
	<h2 class="p3-help-question">How much room do these profiles take up on my server</h2>
	<blockquote>
		<?php
		$total_size = 0;
		$dir        = opendir( P3_PROFILES_PATH );
		while ( false !== ( $file = readdir( $dir ) ) ) {
			if ( '.' != $file && '..' != $file && '.json' == substr( $file, -5 ) ) {
				$total_size += filesize( P3_PROFILES_PATH . "/$file" );
			}
		}
		closedir( $dir );

		?>
		The scans are stored in <code><?php echo realpath( P3_PROFILES_PATH ); ?></code> and
		take up <?php echo $this->readable_size( $total_size ); ?> of disk space.  Each time you
		run a scan, this storage requirement goes up, and each time you delete a scan, it
		goes down.
	</blockquote>
</div>

<div class="p3-question">
	<h2 class="p3-help-question">Is this plugin always running?</h2>
	<blockquote>
		The short answer is no. 
		<br /><br />
		The more detailed answer is the loader is always running, but checks very early in the page
		loading process to see if you've enabled profiling mode and if the user's IP address matches
		the IP address the plugin is monitoring. For multisite installations, it also matches the site URL.
		If all these match, the plugin becomes active and profiles. Otherwise, your site loads as normal
		with no other code overhead. 
		<br /><br />
		Deactivating the plugin ensures it's not running at all, and does not delete your scans. However,
		uninstalling the plugin does delete your scans.
	</blockquote>
</div>

<div class="p3-question">
	<h2 class="p3-help-question">How can I test specific pages on my site?</h2>
	<blockquote>
		When you start a scan, choose "Manual Scan" and then you can visit specific links on your site that
		you want to profile. If you want to profile the admin section, just click the "X" in the top right
		of the scan window and you'll be returned to your admin section. You can browse as normal, then come
		back to the profile page and click "Stop Scan" when you're ready to view the results.
		<br /><br />
		To scan your site as an anonymous user, select "Manual Mode" as above, but instead of clicking your
		site in the scan window, open a different browser (or an incognito window) and browse your site as a
		logged out user. When you're done, close that browser and return to your admin. Click "I'm done" and
		view your scan results.
	</blockquote>
</div>

<div class="p3-question">
	<h2 class="p3-help-question">My plugins don't seem to cause site slowness.  Why is my site still slow?</h2>
	<blockquote>
		Your site can be slow for a number of reasons. Your site could have a lot of traffic, other sites on
		your server could have a lot of traffic, you could be referencing content from other sites that are slow,
		your Internet connection could be slow, your server could be out of RAM, your site could be very image
		heavy, your site could require a lot of HTTP requests, etc. In short, a lot of factors can cause slowness
		on your site
		<br /><br />
		Your next stop should be to use <a href="http://tools.pingdom.com/" target="_blank">Pingdom Tools</a>,
		<a href="http://webpagetest.org/" target="_blank">Webpage Test</a>, <a href="http://developer.yahoo.com/yslow/"
		target="_blank">YSlow</a>, <a href="https://developers.google.com/pagespeed/" target="_blank">Google PageSpeed</a>,
		and your browser's development tools like <a href="http://getfirebug.com/" target="_blank">Firebug</a> for Firefox,
		<a href="http://code.google.com/chrome/devtools/docs/overview.html" target="_blank">Chrome Developer Tools</a> for
		Chrome, or <a href="http://developer.apple.com/technologies/safari/developer-tools.html" target="_blank">Safari
		Developer Tools</a> for Safari.
		<br /><br />
		After you've tuned your site up as much as possible, if you're still not happy with its performance, you should
		consult your site/server administrator or hosting support.
	</blockquote>
</div>

<div class="p3-question">
	<h2 class="p3-help-question" data-question-id="q-debug-log">Where can I view the debug log?</h2>
	<blockquote>
		Debug mode will record 100 visits to your site, then turn off automatically.  You can view the log below.  The entries
		are shown in reverse order with the latest visits appearing at the top of the list.  You can also
		<a href="<?php echo wp_nonce_url( add_query_arg( array( 'p3_action' => 'clear-debug-log' ) ), 'p3-clear-debug-log' ) ; ?>" class="button-secondary">Clear the log</a> or
		<a href="<?php echo wp_nonce_url( add_query_arg( array( 'p3_action' => 'download-debug-log' ) ), 'p3-download-debug-log' ) ; ?>" class="button-secondary">Download the log</a> as a CSV.
		<br /><br />
		<div id="p3-debug-log-container">
			<div class="ui-widget-header" id="p3-debug-log-header" style="padding: 8px;">
				<strong>Debug Log</strong>
				<div style="position: relative; top: 0px; right: 80px; float: right;">
					<a href="javascript:;" id="p3-hide-debug-log">Hide</a>
				</div>
			</div>
			<div>
				<table class="p3-results-table" id="p3-debug-log-table" cellpadding="0" cellspacing="0" border="0">
					<thead>
						<tr>
							<td><strong>#</strong></td>
							<td><strong>Profiling Enabled</strong></td>
							<td><strong>Recording IP</strong></td>
							<td><strong>Scan Name</strong></td>
							<td><strong>Recording</strong></td>
							<td><strong>Disable Optimizers</strong></td>
							<td><strong>URL</strong></td>
							<td><strong>Visitor IP</strong></td>
							<td><strong>Time</strong></td>
							<td><strong>PID</strong></td>
						</tr>
					</thead>
					<tbody>
						<?php $log = get_option( 'p3-profiler_debug_log' ); $c = count( $log ); foreach ( $log as $entry ) : ?>
							<tr>
								<td><?php echo $c--; ?></td>
								<td><?php echo $entry['profiling_enabled'] ? 'true' : 'false'; ?></td>
								<td><?php echo $entry['recording_ip']; ?></td>
								<td>
								<?php if ( file_exists(P3_PROFILES_PATH . '/' . $entry['scan_name'] . '.json' ) ) : ?>
									<a href="<?php echo add_query_arg( array(
										'p3_action'    => 'view-scan',
										'current-scan' => null,
										'name'         => $entry['scan_name'] . '.json'
									) ); ?>"><?php echo $entry['scan_name']; ?></a>
								<?php else : ?>
									<?php echo $entry['scan_name']; ?>
								<?php endif; ?>
								</td>
								<td><?php echo $entry['recording'] ? 'true' : 'false'; ?></td>
								<td><?php echo $entry['disable_optimizers'] ? 'true' : 'false'; ?></td>
								<td><a href="<?php echo $entry['url'];?>" target="_blank"><?php echo htmlentities( $entry['url'] ); ?></a></td>
								<td><?php echo $entry['visitor_ip']; ?></td>
								<td><?php echo human_time_diff( $entry['time'] ) . ' ' . __('ago'); ?></td>
								<td><?php echo $entry['pid']; ?></td>
							</tr>
						<?php endforeach ; ?>
					</tbody>
					<tfoot>
						<tr>
							<td><strong>#</strong></td>
							<td><strong>Profiling Enabled</strong></td>
							<td><strong>Recording IP</strong></td>
							<td><strong>Scan Name</strong></td>
							<td><strong>Recording</strong></td>
							<td><strong>Disable Optimizers</strong></td>
							<td><strong>URL</strong></td>
							<td><strong>Visitor IP</strong></td>
							<td><strong>Time</strong></td>
							<td><strong>PID</strong></td>
						</tr>
					</tfoot>
				</table>
			</div>
		</div>
	</blockquote>
</div>

<div class="p3-question">
	<h2 class="p3-help-question">What if I get a warning about usort()?</h2>
	<blockquote>
		Warning messages like this:
		<code>Warning: usort() [function.usort]: Array was modified by the user comparison function</code> are due
		to a known php bug.  See <a href="https://bugs.php.net/bug.php?id=50688" target="_blank">php bug #50688</a>
		for more information.  This warning does not affect the functionality of your site and it is not visible
		to your users.
	</blockquote>
</div>

<div class="p3-question">
	<h2 class="p3-help-question">Does this plugin increase memory usage on my site?</h2>
	<blockquote>
		When you run a performance scan on your site, the memory requirements go up during the scan.  Accordingly, P3 sets your
		<a href="http://www.php.net/manual/en/ini.core.php#ini.memory-limit" target="_blank">memory limit</a> to 128
		MB and <a href="http://php.net/set_time_limit" target="_blank">request timeout</a> to 90 seconds during a
		performance scan.  These changes are not permanent and are only in effect when a performance scan is actively running.
	</blockquote>
</div>

<div class="p3-question">
	<h2 class="p3-help-question" style="border-bottom-width: 0px !important;">Glossary</h2>
	<blockquote>
		<div>
			<div id="p3-glossary-container">
				<div class="ui-widget-header" id="p3-glossary-header" style="padding: 8px;">
					<strong>Glossary</strong>
					<div style="position: relative; top: 0px; right: 80px; float: right;">
						<a href="javascript:;" id="p3-hide-glossary">Hide</a>
					</div>
				</div>
				<div>
					<table class="p3-results-table" id="p3-glossary-table" cellpadding="0" cellspacing="0" border="0">
						<tbody>
							<tr>
								<td colspan="2" style="border-left-width: 1px !important;">
									<div id="glossary">
										<table width="100%" cellpadding="0" cellspacing="0" border="0" id="glossary-terms">
											<tr>
												<td width="200" class="term"><strong>Total Load Time</strong>
													<div id="total-load-time-definition" style="display: none;" class="definition">
														The length of time the site took to load. This is an observed measurement (start timing when
														the page was requested, stop timing when the page was delivered to the browser, calculate the
														difference). Lower is better.
													</div>
												</td>
												<td width="400" rowspan="12" id="p3-glossary-term-display">&nbsp;</td>
											</tr>
											<tr>
												<td class="term"><strong>Site Load Time</strong>
													<div id="site-load-time-definition" style="display: none;" class="definition">
														The calculated total load time minus the profile overhead. This is closer to your site's
														real-life load time. Lower is better.
													</div>
												</td>
											</tr>
											<tr>
												<td class="term"><strong>Profile Overhead</strong>
													<div id="profile-overhead-definition" style="display: none;" class="definition">
														The load time spent profiling code. Because the profiler slows down your load time, it is
														important to know how much impact the profiler has. However, it doesn't impact your site's
														real-life load time.
													</div>
												</td>
											</tr>
											<tr>
												<td class="term"><strong>Plugin Load Time</strong>
													<div id="plugin-load-time-definition" style="display: none;" class="definition">
														The load time caused by plugins. Because of WordPress' construction, we can trace a
														function call from a plugin through a theme through the core. The profiler prioritizes
														plugin calls first, theme calls second, and core calls last. Lower is better.
													</div>
												</td>
											</tr>
											<tr>
												<td class="term"><strong>Theme Load Time</strong>
													<div id="theme-load-time-definition" style="display: none;" class="definition">
														The load time spent applying the theme. Because of WordPress' construction, we can trace a
														function call from a plugin through a theme through the core. The profiler prioritizes
														plugin calls first, theme calls second, and core calls last. Lower is better.
													</div>
												</td>
											</tr>
											<tr>
												<td class="term"><strong>Core Load Time</strong>
													<div id="core-load-time-definition" style="display: none;" class="definition">
														The load time caused by the WordPress core. Because of WordPress' construction, we can trace
														a function call from a plugin through a theme through the core. The profiler prioritizes
														plugin calls first, theme calls second, and core calls last. This will probably be constant.
													</div>
												</td>
											</tr>
											<tr>
												<td class="term"><strong>Margin of Error</strong>
													<div id="drift-definition" style="display: none;" class="definition">
														This is the difference between the observed runtime (what actually happened) and expected
														runtime (adding the plugin runtime, theme runtime, core runtime, and profiler overhead).
														<br /><br />
														There are several reasons this margin of error can exist. Most likely, the profiler is
														missing microseconds while adding the runtime it observed. Using a network clock to set the
														time (NTP) can also cause minute timing changes.
														<br /><br />
														Ideally, this number should be zero, but there's nothing you can do to change it. It
														will give you an idea of how accurate the other results are.
													</div>
												</td>
											</tr>
											<tr>
												<td class="term"><strong>Observed</strong>
													<div id="observed-definition" style="display: none;" class="definition">
														The time the site took to load. This is an observed measurement (start timing when the
														page was requested, stop timing when the page was delivered to the browser, calculate the
														difference).
													</div>
												</td>
											</tr>
											<tr>
												<td class="term"><strong>Expected</strong>
													<div id="expected-definition" style="display: none;" class="definition">
														The expected site load time calculated by adding plugin load time, core load time, theme
														load time, and profiler overhead.
													</div>
												</td>
											</tr>
											<tr>
												<td class="term"><strong>Plugin Function Calls</strong>
													<div id="plugin-funciton-calls-definition" style="display: none;" class="definition">
														The number of PHP function calls generated by a plugin. Fewer is better.
													</div>
												</td>
											</tr>
											<tr>
												<td class="term"><strong>Memory Usage</strong>
													<div id="memory-usage-definition" style="display: none;" class="definition">
														The amount of RAM usage observed. This is reported by
														<a href="http://php.net/memory_get_peak_usage"
														target="_blank">memory_get_peak_usage()</a>.  Lower is better.
													</div>
												</td>
											</tr>
											<tr>
												<td class="term"><strong>MySQL Queries</strong>
													<div id="mysql-queries-definition" style="display: none;" class="definition">
														The number of queries sent to the database. This is reported by the WordPress function
														<a href="http://codex.wordpress.org/Function_Reference/get_num_queries"
														target="_new">get_num_queries()</a>.  Fewer is better.
													</div>
												</td>
											</tr>
										</table>
									</div>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>
		</div>
	</blockquote>
</div>

<div class="p3-question">
	<h2 class="p3-help-question">License</h2>
	<blockquote>
		<strong>P3 (Plugin Performance Profiler)</strong>
		<br />
		Copyright &copy; 2011-<?php echo date('Y'); ?> <a href="http://www.godaddy.com/" target="_blank">GoDaddy.com</a>.  All rights reserved.
		<br /><br />
		This program is offered under the terms of the GNU General Public License Version 2 as published by the Free Software Foundation.
		<br /><br />
		This program offered WITHOUT WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
		See the GNU General Public License Version 2 for the specific terms.
		<br /><br />
		A copy of the GNU General Public License has been provided with this program.  Alternatively, you may find a copy of the license here:
		<a href="http://www.gnu.org/licenses/gpl-2.0.html" target="_blank">http://www.gnu.org/licenses/gpl-2.0.html</a>.
	</blockquote>
</div>
