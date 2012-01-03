<?php
if ( !defined('P3_PATH') )
	die( 'Forbidden ');
$p3_action = '';
if ( !empty( $_REQUEST['p3_action'] ) ) {
	$p3_action = $_REQUEST['p3_action'];
}
if ( empty( $p3_action ) || 'current-scan' == $p3_action ) {
	$scan = $this->get_latest_profile();
	$p3_action = 'current-scan';
} elseif ( 'view-scan' == $p3_action && !empty( $_REQUEST['name'] ) ) {
	$scan = sanitize_file_name( basename( $_REQUEST['name'] ) );
	if ( !file_exists( P3_PROFILES_PATH . "/$scan" ) ) {
		wp_die( '<div id="message" class="error"><p>Scan does not exist</p></div>' );
	}
	$scan = P3_PROFILES_PATH . "/$scan";
}
$button_current_checked = '';
$button_history_checked = '';
$button_help_checked    = '';
if ( 'current-scan' == $p3_action || !empty( $_REQUEST['current_scan'] ) ) {
	$button_current_checked = 'checked="checked"';
} elseif ( 'help' == $p3_action || 'fix-flag-file' == $p3_action ) {
	$button_help_checked = 'checked="checked"';
} else {
	$button_history_checked = 'checked="checked"';
}

// If there's a scan, create a viewer object
if ( !empty( $scan ) ) {
	try {
		$profile = new P3_Profile_Reader( $scan );
	} catch ( P3_Profile_No_Data_Exception $e ) {
		echo '<div class="error"><p>' . $e->getMessage() . '</p></div>';
		$scan = null;
		$profile = null;
		$p3_action = 'list-scans';
	} catch ( Exception $e ) {
		wp_die( '<div id="message" class="error"><p>Error reading scan</p></div>' );
	}
} else {
	$profile = null;
}

?>
<script type="text/javascript">
	jQuery( document ).ready( function( $) {
		$( "#button-current-scan" ).click( function() {
			location.href = "<?php echo add_query_arg( array( 'p3_action' => 'current-scan', 'name' => null, 'current_scan' => null ) ); ?>";
		});
		$( "#button-history-scans" ).click( function() {
			location.href = "<?php echo add_query_arg( array( 'p3_action' => 'list-scans', 'name' => null, 'current_scan' => null ) ); ?>";
		});
		$( "#button-help" ).click( function() {
			location.href = "<?php echo add_query_arg( array( 'p3_action' => 'help', 'name' => null, 'current_scan' => null ) ); ?>";
		})
		$( ".p3-button" ).button();
		$( "#p3-navbar" ).buttonset();
		$( "#p3-navbar" ).corner( "round 8px" );
		$( ".p3-big-button" ).buttonset();
		$( "#p3-results-table tr:even" ).addClass( "even" );
		$( "td div.row-actions-visible" ).hide();
		$( "table.wp-list-table td" ).mouseover( function() {
			$( "div.row-actions-visible", $( this ) ).show();
		}).mouseout( function() {
			$( "div.row-actions-visible", $( this ) ).hide();
		});
		$( ".qtip-tip" ).each( function() {
			$( this ).qtip({
				content: $( this ).attr( "title" ),
				position: {
					my: 'top center',
					at: 'bottom center'
				},
				style: {
					classes: 'ui-tooltip-blue ui-tooltip-shadow'
				}
			});
		});
	});
</script>
<div class="wrap">

	<!-- Header icon / title -->
	<div id="icon-plugins" class="icon32"><br/></div>
	<h2>P3 - Plugin Performance Profiler</h2>

	<!-- Header navbar -->
	<div class="ui-widget-header" id="p3-navbar">
		<input type="radio" name="p3-nav" id="button-current-scan" <?php echo $button_current_checked; ?> />
		<label for="button-current-scan">Current</label>
		<input type="radio" name="p3-nav" id="button-history-scans" <?php echo $button_history_checked; ?> />
		<label for="button-history-scans">History</label>
		<input type="radio" name="p3-nav" id="button-help" <?php echo $button_help_checked; ?> /><label for="button-help">Help</label>
		
		<div id="p3-scan-label">
			<?php if ( !empty( $profile ) ) : ?>
				Scan name: <?php echo $profile->profile_name; ?>
			<?php endif; ?>
		</div>
	</div>

	<!-- Start / stop button and callouts -->
	<?php require_once P3_PATH . '/templates/callouts.php'; ?>

	<!-- View scan or show a list of scans -->
	<?php if ( ( 'current-scan' == $p3_action && !empty( $scan ) ) || 'view-scan' == $p3_action ) { ?>
		<?php include_once P3_PATH . '/templates/view-scan.php'; ?>
	<?php } elseif ( 'help' == $p3_action ) { ?>
		<?php include_once P3_PATH . '/templates/help.php'; ?>
	<?php } elseif ( 'fix-flag-file' == $p3_action ) { ?>
		<?php include_once P3_PATH . '/templates/fix-flag-file.php'; ?>
	<?php } else { ?>
		<?php include_once P3_PATH . '/templates/list-scans.php'; ?>
	<?php } ?>

</div>

<div id="p3-copyright">
	<img src="<?php echo plugins_url() . '/p3-profiler/logo.gif'; ?>" alt="GoDaddy.com logo" title="GoDaddy.com logo" />
	<br />
	<?php if (date('Y') > 2011) : ?>
		Copyright &copy; 2011-<?php echo date('Y'); ?> <a href="http://www.godaddy.com/" target="_blank">GoDaddy.com</a>.  All rights reserved.
	<?php else : ?>
		Copyright &copy; 2011 <a href="http://www.godaddy.com/" target="_blank">GoDaddy.com</a>.  All rights reserved.
	<?php endif; ?>
</div>
