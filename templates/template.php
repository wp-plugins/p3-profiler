<?php

$button_current_checked = '';
$button_history_checked = '';
$button_help_checked    = '';
if ( 'current-scan' == $this->action || !empty( $_REQUEST['current_scan'] ) ) {
	$button_current_checked = 'checked="checked"';
} elseif ( 'help' == $this->action ) {
	$button_help_checked = 'checked="checked"';
} else {
	$button_history_checked = 'checked="checked"';
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
		
		// Callouts
		$( "div#p3-reminder-wrapper" )
			.corner( "round 8px" )
			.parent()
			.css( "padding", "4px" )
			.corner( "round 10px" );
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
			<?php if ( !empty( $this->profile ) ) : ?>
				Scan name: <?php echo $this->profile->profile_name; ?>
			<?php endif; ?>
		</div>
	</div>

	<!-- Start / stop button and callouts -->
	<?php require_once P3_PATH . '/templates/callouts.php'; ?>

	<!-- View scan or show a list of scans -->
	<?php if ( ( 'current-scan' == $this->action && !empty( $this->scan ) ) || 'view-scan' == $this->action ) { ?>
		<?php include_once P3_PATH . '/templates/view-scan.php'; ?>
	<?php } elseif ( 'help' == $this->action ) { ?>
		<?php include_once P3_PATH . '/templates/help.php'; ?>
	<?php } else { ?>
		<?php include_once P3_PATH . '/templates/list-scans.php'; ?>
	<?php } ?>

</div>

<div id="p3-reminder">
	<div id="p3-reminder-wrapper">
		Do you like this plugin?
		<ul>
			<li><a href="http://twitter.com/home?status=<?php echo rawurlencode(htmlentities('I just optimized my WordPress site with #p3plugin http://wordpress.org/extend/plugins/p3-profiler/ ')); ?>" target="_blank">Tweet</a> about it</li>
			<li><a href="http://wordpress.org/extend/plugins/p3-profiler/" target="_blank">Rate</a> it on the repository</li>
		</ul>
	</div>
</div>

<div id="p3-copyright">
	<img src="<?php echo plugins_url() . '/p3-profiler/logo.gif'; ?>" alt="GoDaddy.com logo" title="GoDaddy.com logo" />
	<br />
	Copyright &copy; 2011-<?php echo date('Y'); ?> <a href="http://www.godaddy.com/" target="_blank">GoDaddy.com</a>.  All rights reserved.
</div>
