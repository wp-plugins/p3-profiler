<?php
if ( !defined('P3_PATH') )
	die( 'Forbidden ');
?>
<script type="text/javascript">

	/*****************************************************************/
	/**  AUTO SCANNER HELPER OBJECT                                 **/
	/*****************************************************************/
	// This will load all of the pages in the list, then turn off
	// the profile mode and view the results when complete.
	var P3_Scan = {

		// List of pages to scan
		pages: <?php echo json_encode( $this->list_of_pages() ); ?>,

		// Current page
		current_page: 0,

		// Pause flag
		paused: false,

		// Create a random string
		random: function(length) {
			var ret = "";
			var alphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789";
			for ( var i = 0 ; i < length ; i++ ) {
				ret += alphabet.charAt( Math.floor( Math.random() * alphabet.length ) );
			}
			return ret;
		},

		// Start
		start: function() {
			
			// If cache busting is disabled, remove P3_NOCACHE from the pages
			if ( jQuery( '#p3-cache-buster' ).prop( 'checked' ) ) {
				for ( i = 0 ; i < P3_Scan.pages.length ; i++ ) {
					if ( P3_Scan.pages[i].indexOf('?') > -1 ) {
						P3_Scan.pages[i] += '&P3_NOCACHE=' + P3_Scan.random(8);
					} else {
						P3_Scan.pages[i] += '?P3_NOCACHE=' + P3_Scan.random(8);
					}
				}
			}

			// Form data
			data = {
				'p3_ip' : jQuery( '#p3-advanced-ip' ).val(),
				'p3_disable_opcode_cache' : jQuery( '#p3-disable-opcode-cache' ).prop( 'checked' ),
				'p3_cache_buster' : jQuery( '#p3-cache-buster' ).prop( 'checked' ),
				'p3_scan_name' : jQuery( "#p3-scan-name" ).val(),
				'action' : 'p3_start_scan',
				'p3_nonce' : jQuery( "#p3_nonce" ).val()
			}

			// Turn on the profiler
			jQuery.post( ajaxurl, data, function( response ) {
				if ( 1 != response ) {
					alert( "There was an error processing your request.  Please reload the page and try again. [" + response + "]");
				} else {

					// Start scanning pages
					jQuery( "#p3-scan-frame" ).attr( "onload", "P3_Scan.next_page();" );
					jQuery( "#p3-scan-frame" ).attr( "src", P3_Scan.pages[0] );
					P3_Scan.current_page = 0;
					P3_Scan.update_display();
					
				}
			});
		},
		
		// Pause
		pause: function() {
			
			// Turn off the profiler
			data = {
				'action' : 'p3_stop_scan',
				'p3_nonce' : '<?php echo wp_create_nonce( 'p3_ajax_stop_scan' ); ?>'
			}
			jQuery.post( ajaxurl, data, function( response ) {
				if ( response.indexOf( '.json' ) < 0 ) {
					alert( "There was an error processing your request.  Please reload the page and try again. [" + response + "]");
				}

				// Hide the cancel button
				jQuery( "#p3-cancel-scan-buttonset" ).hide();
				jQuery( "#p3-resume-scan-buttonset" ).show();
				jQuery( "#p3-view-results-buttonset" ).hide();
				
				// Show the view results button
				jQuery( "#p3-view-incomplete-results-submit" ).attr( "data-scan-name", response );
				
				// Pause
				P3_Scan.paused = true;
				
				// Update the caption
				jQuery( "#p3-scanning-caption" ).html( "Scanning is paused." ).css( "color", "black" );
			});
		},

		// Resume
		resume: function() {
			
			data = {
				'p3_ip' : jQuery( '#p3-advanced-ip' ).val(),
				'p3_disable_opcode_cache' : jQuery( '#p3-disable-opcode-cache' ).prop( 'checked' ),
				'p3_cache_buster' : jQuery( '#p3-cache-buster' ).prop( 'checked' ),
				'p3_scan_name' : jQuery( "#p3-scan-name" ).val(),
				'action' : 'p3_start_scan',
				'p3_nonce' : jQuery( "#p3_nonce" ).val()
			}

			// Turn on the profiler
			jQuery.post( ajaxurl, data, function( response ) {
				if ( 1 != response ) {
					alert( "There was an error processing your request.  Please reload the page and try again. [" + response + "]");
				} else {

					// Show the cancel button
					P3_Scan.paused = false;
					jQuery( "#p3-cancel-scan-buttonset" ).show();
					jQuery( "#p3-resume-scan-buttonset" ).hide();
					jQuery( "#p3-view-results-buttonset" ).hide();
					P3_Scan.update_display();
					P3_Scan.next_page();
				}
			});
		},

		// Stop
		stop: function() {
			
			// Turn off the profiler
			data = {
				'action' : 'p3_stop_scan',
				'p3_nonce' : '<?php echo wp_create_nonce( 'p3_ajax_stop_scan' ); ?>'
			}
			jQuery.post( ajaxurl, data, function( response ) {
				if ( response.indexOf( '.json' ) < 0 ) {
					alert( "There was an error processing your request.  Please reload the page and try again. [" + response + "]");
				}
				
				// Hide the cancel button
				jQuery( "#p3-cancel-scan-buttonset" ).hide();
				jQuery( "#p3-resume-scan-buttonset" ).hide();
				jQuery( "#p3-view-results-buttonset" ).show();
				
				// Show the view results button
				jQuery( "#p3-view-results-submit" ).attr( "data-scan-name", response );
				
				// Update the caption
				jQuery( "#p3-scanning-caption" ).html( "Scanning is complete." ).css( "color", "black" );
			});
		},

		// Update the display
		update_display : function() {
			jQuery( "#p3-scanning-caption" ).html( 'Scanning ' + P3_Scan.pages[P3_Scan.current_page] ).css( "color", "" );
			jQuery( "#p3-progress" ).progressbar( "value", ( P3_Scan.current_page / ( P3_Scan.pages.length - 1 ) ) * 100 );
		},

		// Look at the next page
		next_page : function() {

			// Paused?
			if ( P3_Scan.paused ) {
				return true;
			}

			// Is it time to stop?
			if ( P3_Scan.current_page >= P3_Scan.pages.length - 1 ) {
				P3_Scan.stop();
				return true;
			}

			// Next page
			jQuery( "#p3-scan-frame" ).attr( "src", P3_Scan.pages[++P3_Scan.current_page] );

			// Update the display
			P3_Scan.update_display();
		}
	};

	// Sync save settings
	function p3_sync_advanced_settings() {
		if ( jQuery( "#p3-use-current-ip" ).prop( "checked" ) ) {
			jQuery( "#p3-advanced-ip" ).val( "<?php echo esc_js( $GLOBALS['p3_profiler']->get_ip() ); ?>" );
			jQuery( "#p3-advanced-ip" ).prop( "disabled", true );
		} else {
			<?php $ip = get_option( 'p3-profiler_ip_address' ); if ( empty( $ip ) ) { $ip = $GLOBALS['p3_profiler']->get_ip(); } ?>
			jQuery( "#p3-advanced-ip" ).val( "<?php echo esc_js( $ip ); ?>" );
			jQuery( "#p3-advanced-ip" ).prop( "disabled", false );
		}
	}

	// Onload functionality
	jQuery( document ).ready( function( $) {

		/*****************************************************************/
		/**  DIALOGS                                                    **/
		/*****************************************************************/

		// IP settings
		$( "#p3-ip-dialog" ).dialog({
			'autoOpen' : false,
			'closeOnEscape' : true,
			'draggable' : false,
			'resizable' : false,
			'modal' : true,
			'width' : 450,
			'height' : 340,
			'title' : "Advanced Settings",
			'buttons' :
			[
				{
					text: 'OK',
					'class' : 'button-secondary',
					click: function() {
						
						// Save settings
						data = {
							'action' : 'p3_save_settings',
							'p3_disable_opcode_cache' : $( '#p3-disable-opcode-cache' ).prop( 'checked' ),
							'p3_use_current_ip' : $( '#p3-use-current-ip' ).prop( 'checked' ),
							'p3_ip_address' : $( '#p3-advanced-ip' ).val(),
							'p3_cache_buster' : $( '#p3-cache-buster' ).prop( 'checked' ),
							'p3_nonce' : '<?php echo wp_create_nonce( 'p3_save_settings' ); ?>'
						}
						$.post( ajaxurl, data, function( response ) {
							if ( 1 != response ) {
								alert( "There was an error saving your settings.  Please reload the page and try again. [" + response + "]");
							}
							$( "#p3-ip-dialog" ).dialog( "close" );
						});
					}
				},
				{
					text: 'Cancel',
					'class': 'p3-cancel-button',
					click: function() {
						$( this ).dialog( "close" );
					}
				}
			]
		});

		// Iframe scanner
		$( "#p3-scanner-dialog" ).dialog({
			'autoOpen' : false,
			'closeOnEscape' : true,
			'draggable' : false,
			'resizable' : false,
			'modal' : true,
			'width': 800,
			'height' : 600,
			'title' : "Performance Scan",
			'dialogClass' : 'noPadding'
		});

		// Auto scan or manual scan 
		$( "#p3-scan-name-dialog" ).dialog({
			'autoOpen' : false,
			'closeOnEscape' : true,
			'draggable' : false,
			'resizable' : false,
			'modal' : true,
			'width' : 425,
			'height' : 180,
			'title' : 'Scan Name'
			// 'dialogClass' : 'noTitle'
		});

		// Progress dialog
		$( "#p3-progress-dialog" ).dialog({
			'autoOpen' : false,
			'closeOnEscape' : false,
			'draggable' : false,
			'resizable' : false,
			'modal' : true,
			'width' : 450,
			'height' : 117,
			'dialogClass' : 'noTitle'
		});



		/*****************************************************************/
		/**  LINKS                                                      **/
		/*****************************************************************/
		
		// Advanced settings link
		$( "#p3-advanced-settings" ).click( function() {
			$( "#p3-ip-dialog" ).dialog( "open" );
		});



		/*****************************************************************/
		/**  BUTTONS                                                    **/
		/*****************************************************************/
		
		// Start scan button
		$( "#p3-start-scan-submit" ).click( function() {
			
			// Stay checked to keep the styling
			$( this ).prop( "checked", true );
			$( this ).button( "refresh" );

			url = $( "#p3-scan-frame" ).attr( "data-defaultsrc" );
			if ( url.indexOf('?') >= 0 || url.indexOf('&') >= 0 ) {
				url += '&P3_HIDE_ADMIN_BAR=1';
			} else if ( url.charAt(url.length - 1) != '/' ) {
				url += '/?P3_HIDE_ADMIN_BAR=1';
			} else {
				url += '?P3_HIDE_ADMIN_BAR=1';
			}

			$( "#p3-scan-frame" ).attr( "src", url );
			$( "#p3-scanner-dialog" ).dialog( "open" );
			$( "#p3-scan-name-dialog" ).dialog( "open" );
		});
		
		// Stop scan button
		$( "#p3-stop-scan-submit" ).click( function() {

			// Stay checked to keep the styling
			$( this ).prop( "checked", true );
			$( this ).button( "refresh" );

			// Turn off the profiler
			data = {
				'action' : 'p3_stop_scan',
				'p3_nonce' : '<?php echo wp_create_nonce( 'p3_ajax_stop_scan' ); ?>'
			}
			jQuery.post( ajaxurl, data, function( response ) {
				if ( response.indexOf( '.json' ) < 0 ) {
					alert( "There was an error processing your request.  Please reload the page and try again. [" + response + "]");
				}
				location.reload();
			});
		});

		// Auto scan button
		$( "#p3-auto-scan-submit" ).click( function() {
			
			// Stay checked to keep the styling
			$( this ).prop( "checked", true );
			$( this ).button( "refresh" );

			// Close the "auto or manual" dialog
			$( "#p3-scan-name-dialog" ).dialog( "close" );

			// Open the progress bar dialog
			$( "#p3-progress-dialog" ).dialog( "open" );

			// Initialize the progress bar to 0%
			$( "#p3-progress" ).progressbar({
				'value': 0
			});

			P3_Scan.start();
		});

		// Manual scan button
		$( "#p3-manual-scan-submit" ).click( function() {
			
			// Stay checked to keep the styling
			$( this ).prop( "checked", true );
			$( this ).button( "refresh" );

			
			// Form data
			data = {
				'p3_ip' : jQuery( '#p3-advanced-ip' ).val(),
				'p3_disable_opcode_cache' : jQuery( '#p3-disable-opcode-cache' ).prop( 'checked' ),
				'p3_cache_buster' : jQuery( '#p3-cache-buster' ).prop( 'checked' ),
				'p3_scan_name' : jQuery( "#p3-scan-name" ).val(),
				'action' : 'p3_start_scan',
				'p3_nonce' : jQuery( "#p3_nonce" ).val()
			}

			// Turn on the profiler
			jQuery.post( ajaxurl, data, function( response ) {
				if ( 1 != response ) {
					alert( "There was an error processing your request.  Please reload the page and try again. [" + response + "]");
				}
			});

			$( "#p3-scan-name-dialog" ).dialog( "close" );
			$( "#p3-scan-caption" ).hide();
			$( "#p3-manual-scan-caption" ).show();
		});
		
		// Manual scan "I'm done" button
		$( "#p3-manual-scan-done-submit" ).click( function() {
			data = {
				'action' : 'p3_stop_scan',
				'p3_nonce' : '<?php echo wp_create_nonce( 'p3_ajax_stop_scan' ); ?>'
			}
			jQuery.post( ajaxurl, data, function( response ) {
				if ( response.indexOf( '.json' ) < 0 ) {
					alert( "There was an error processing your request.  Please reload the page and try again. [" + response + "]");
				} else {
					location.href = "<?php echo add_query_arg( array( 'p3_action' => 'view-scan', 'current_scan' => '1', 'name' => null ) ); ?>&name=" + response;
				}
			})
			$( "#p3-scanner-dialog" ).dialog( "close" );
		});
		
		// Manual scan cancel link
		$( "#p3-manual-scan-cancel" ).click( function() {
			P3_Scan.pause();
			$( "#p3-scanner-dialog" ).dialog( "close" );
		});

		// Cancel scan button
		$( "#p3-cancel-scan-submit" ).click( function() {
			
			// Stay checked to keep the styling
			$( this ).prop( "checked", true );
			$( this ).button( "refresh" );

			P3_Scan.pause();
		});
		
		// Resume
		$( "#p3-resume-scan-submit" ).click( function() {
			
			// Stay checked to keep the styling
			$( this ).prop( "checked", true );
			$( this ).button( "refresh" );

			P3_Scan.resume();
		});
		
		// View results button
		$( "#p3-view-results-submit" ).click( function() {

			// Stay checked to keep the styling
			$( this ).prop( "checked", true );
			$( this ).button( "refresh" );

			// Close the dialogs
			jQuery( "#p3-scanner-dialog" ).dialog( "close" );
			jQuery( "#p3-progress-dialog" ).dialog( "close" );

			// View the scan
			location.href = "<?php echo add_query_arg( array( 'p3_action' => 'view-scan', 'current_scan' => '1', 'name' => null ) ); ?>&name=" + $( this ).attr( "data-scan-name" );
		});
		$( "#p3-view-incomplete-results-submit" ).click( function() {
			$( "#p3-view-results-submit" ).trigger( "click" );
		});


		/*****************************************************************/
		/**  OTHER                                                      **/
		/*****************************************************************/
		// Enable / disable buttons based on scan name input
		$( "#p3-scan-name" ).live( "keyup", function() {
			if ( $( this ).val().match(/^[a-zA-Z0-9_\.-]+$/) ) {
				$( "#p3-auto-scan-submit" ).button( "enable" )
				$( "#p3-manual-scan-submit" ).button( "enable" );
			} else {
				$( "#p3-auto-scan-submit" ).button( "disable" );
				$( "#p3-manual-scan-submit" ).button( "disable" );
			}
		});
		
		// Enable / disable the IP text based on the "use current ip" checkbox
		$( "#p3-use-current-ip").live( "click", p3_sync_advanced_settings );
		p3_sync_advanced_settings();

		// Callouts
		$( "div.p3-callout-inner-wrapper" )
		.corner( "round 8px" )
		.parent()
		.css( "padding", "4px" )
		.corner( "round 10px" );

		// Start / stop buttons
		$( "#p3-scan-form-wrapper" ).corner( "round 8px" );
		
		// Continue scan
		$( "a.p3-continue-scan" ).click( function() {
			$( "#p3-start-scan-submit" ).trigger( "click" );
			$( "#p3-scan-name" ).val( $( this ).attr( "data-name" ).replace(/\.json$/, '' ) );
		});
	});
</script>
<table id="p3-quick-report" cellpadding="0" cellspacing="0">
	<tr>

		<td>
			<div class="ui-widget-header" id="p3-scan-form-wrapper">
				<?php if ( false !== ( $info = $this->scan_enabled() ) ) { ?>
					<!-- Stop scan button -->

					<strong>IP:</strong><?php echo htmlentities( $info['ip'] ); ?>
					<div class="p3-big-button"><input type="checkbox" checked="checked" id="p3-stop-scan-submit" />
					<label for="p3-stop-scan-submit">Stop Scan</label></div>
					<?php echo htmlentities( $info['name'] ); ?>

				<?php } else { ?>

					<!-- Start scan button -->
					<?php echo wp_nonce_field( 'p3_ajax_start_scan', 'p3_nonce' ); ?>
					<strong>My IP:</strong><?php echo htmlentities( $GLOBALS['p3_profiler']->get_ip() ); ?>
					<div class="p3-big-button"><input type="checkbox" checked="checked" id="p3-start-scan-submit" />
					<label for="p3-start-scan-submit">Start Scan</label></div>
					<a href="javascript:;" id="p3-advanced-settings">Advanced Settings</a>

				<?php } ?>
			</div>
		</td>

		<!-- First callout cell -->
		<td class="p3-callout">
			<div class="p3-callout-outer-wrapper qtip-tip" title="Total number of active plugins, including must-use plugins, on your site.">
				<div class="p3-callout-inner-wrapper">
					<div class="p3-callout-caption">Total Plugins:</div>
					<div class="p3-callout-data">
						<?php
						// Get the total number of plugins
						$active_plugins = count( get_mu_plugins() );
						foreach ( get_plugins() as $plugin => $junk ) {
							if ( is_plugin_active( $plugin ) ) {
								$active_plugins++;
							}
						}
						echo $active_plugins;
						?>
					</div>
					<div class="p3-callout-caption">( currently active )</div>
				</div>
			</div>
		</td>

		<!-- Second callout cell -->
		<td class="p3-callout">
			<div class="p3-callout-outer-wrapper qtip-tip" title="Total number of seconds dedicated to plugin code per visit on your site."
				<?php if ( !empty( $this->scan ) ) { ?>title="From <?php echo basename( $this->scan ); ?><?php } ?>">
				<div class="p3-callout-inner-wrapper">
					<div class="p3-callout-caption">Plugin Load Time</div>
					<div class="p3-callout-data">
						<?php if ( null === $this->profile ) { ?>
							<span class="p3-faded-grey">n/a</span>
						<?php } else { ?>
							<?php printf( '%.3f', $this->profile->averages['plugins'] ); ?>
						<?php } ?>
					</div>
					<div class="p3-callout-caption">( sec. per visit )</div>
				</div>
			</div>
		</td>

		<!-- Third callout cell -->
		<td class="p3-callout">
			<div class="p3-callout-outer-wrapper qtip-tip" title="Percent of load time on your site dedicated to plugin code."
				<?php if ( !empty( $this->scan ) ) { ?>title="From <?php echo basename( $this->scan ); ?><?php } ?>">
				<div class="p3-callout-inner-wrapper">
					<div class="p3-callout-caption">Plugin Impact</div>
					<div class="p3-callout-data">
						<?php if ( null === $this->profile ) { ?>
							<span class="p3-faded-grey">n/a</span>
						<?php } else { ?>
							<?php printf( '%.1f%%', $this->profile->averages['plugin_impact'] ); ?>
						<?php } ?>
					</div>
					<div class="p3-callout-caption">( of page load time )</div>
				</div>
			</div>
		</td>

		<!-- Fourth callout cell -->
		<td class="p3-callout">
			<div class="p3-callout-outer-wrapper qtip-tip" title="Total number of database queries per visit."
				<?php if ( !empty( $this->scan ) ) { ?>title="From <?php echo basename( $this->scan ); ?><?php } ?>">
				<div class="p3-callout-inner-wrapper">
					<div class="p3-callout-caption">MySQL Queries</div>
					<div class="p3-callout-data">
						<?php if ( null === $this->profile ) { ?>
							<span class="p3-faded-grey">n/a</span>
						<?php } else { ?>
							<?php echo round( $this->profile->averages['queries'] ); ?>
						<?php } ?>
					</div>
					<div class="p3-callout-caption">per visit</div>
				</div>
			</div>
		</td>

	</tr>
</table>

<!-- Dialog for IP settings -->
<div id="p3-ip-dialog" class="p3-dialog">
	<div>
		IP address or pattern:<br /><br />
		<input type="checkbox" id="p3-use-current-ip" <?php if ( true == get_option( 'p3-profiler_use_current_ip' ) ) : ?>checked="checked"<?php endif; ?> />
		<label for="p3-use-current-ip">Use my IP address</label>
		<br />
		<input type="text" id="p3-advanced-ip" style="width:90%;" size="35" value="" title="Enter IP address or regular expression pattern" />
		<br />
		<em class="p3-em">Example: 1.2.3.4 or ( 1.2.3.4|4.5.6.7 )</em>
	</div>
	<br />
	<div>
		<input type="checkbox" id="p3-disable-opcode-cache" <?php if ( true == get_option( 'p3-profiler_disable_opcode_cache' ) ) : ?>checked="checked"<?php endif; ?> />
		<label for="p3-disable-opcode-cache">Attempt to disable opcode caches <em>( recommended )</em></label>
		<br />
		<em class="p3-em">This can increase accuracy in plugin detection, but decrease accuracy in timing</em>
	</div>
	<br />
	<div>
		<input type="checkbox" id="p3-cache-buster" <?php if ( true == get_option( 'p3-profiler_cache_buster' ) ) : ?>checked="checked"<?php endif; ?> />
		<label for="p3-cache-buster">Attempt to circumvent browser cache</label>
		<br />
		<em class="p3-em">This may help fix a "No visits recorded" error message.  See the <a href="<?php echo add_query_arg( array( 'p3_action' => 'help', 'current_scan' => null ) ); ?>#q-circumvent-cache">help</a> page for details.</em>
	</div>
</div>

<!-- Dialog for iframe scanner -->
<div id="p3-scanner-dialog" class="p3-dialog">
	<iframe id="p3-scan-frame" frameborder="0"
		data-defaultsrc="<?php echo ( true === force_ssl_admin() ?  str_replace( 'http://', 'https://', home_url() ) :  home_url() ); ?>">
	</iframe>
	<div id="p3-scan-caption">
		The scanner will analyze the speed and resource usage of all active plugins on your website.
		It may take several minutes, and this window must remain open for the scan to finish successfully. 
	</div>
	<div id="p3-manual-scan-caption" style="display: none;">
		<table>
			<tr>
				<td>
					Click the links and pages of your site, and the scanner will
					analyze the speed and resource usage of all of your active
					plugins.
				</td>
				<td width="220">
					<a href="javascript:;" id="p3-manual-scan-cancel">Cancel</a>
					&nbsp;&nbsp;&nbsp;
					<span class="p3-big-button">
						<input type="checkbox" id="p3-manual-scan-done-submit" checked="checked" />
						<label for="p3-manual-scan-done-submit">I'm Done</label>
					</span>
				</td>
			</tr>
		</table>
	</div>
</div>

<!-- Dialog for choose manual or auto scan  -->
<div id="p3-scan-name-dialog" class="p3-dialog">
	<div style="padding-top: 10px;">Scan name:
		<input type="text" name="p3_scan_name" id="p3-scan-name" title="Enter scan name here"
			value="scan_<?php echo date( 'Y-m-d' ); ?>_<?php echo substr( md5( uniqid() ), -8 );?>" size="35" maxlength="100" />
	</div>
	<div style="padding-top: 10px;"><em class="p3-em">Enter the name of a previous scan to continue scanning</em></div>
	<br />
	<div class="p3-big-button">
		<input type="checkbox" id="p3-auto-scan-submit" checked="checked" /><label for="p3-auto-scan-submit">Auto Scan</label>
		<input type="checkbox" id="p3-manual-scan-submit" checked="checked" /><label for="p3-manual-scan-submit">Manual Scan</label>
	</div>
</div>

<!-- Dialog for progress bar -->
<div id="p3-progress-dialog" class="p3-dialog">
	<div id="p3-scanning-caption">
		Scanning ...
	</div>
	<div id="p3-progress"></div>
	
	<!-- Cancel button -->
	<div class="p3-big-button" id="p3-cancel-scan-buttonset">
		<input type="checkbox" id="p3-cancel-scan-submit" checked="checked" /><label for="p3-cancel-scan-submit">Stop Scan</label>
	</div>

	<!-- View / resume buttons -->
	<div class="p3-big-button" id="p3-resume-scan-buttonset" style="display: none;">
		<input type="checkbox" id="p3-resume-scan-submit" checked="checked" /><label for="p3-resume-scan-submit">Resume</label>
		<input type="checkbox" id="p3-view-incomplete-results-submit" checked="checked" data-scan-name="" />
		<label for="p3-view-incomplete-results-submit">View Results</label>
	</div>
	
	<!-- View results button -->
	<div class="p3-big-button" id="p3-view-results-buttonset" style="display: none;">
		<input type="checkbox" id="p3-view-results-submit" checked="checked" data-scan-name="" />
		<label for="p3-view-results-submit">View Results</label>
	</div>	
</div>