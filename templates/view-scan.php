<?php
if ( !defined('P3_PATH') )
	die( 'Forbidden ');
$url_stats = array();
$domain    = '';
if ( !empty( $this->profile ) ) {
	$url_stats = $this->profile->get_stats_by_url();
	$domain    = @parse_url( $this->profile->report_url, PHP_URL_HOST );
}
$pie_chart_id                 = substr( md5( uniqid() ), -8 );
$runtime_chart_id             = substr( md5( uniqid() ), -8 );
$query_chart_id               = substr( md5( uniqid() ), -8 );
$component_breakdown_chart_id = substr( md5( uniqid() ), -8 );
$component_runtime_chart_id   = substr( md5( uniqid() ), -8 );
?>
<script type="text/javascript">

	/**************************************************************/
	/**  Init                                                    **/
	/**************************************************************/

	// Raw json data ( used in the charts for tooltip data
	var _data = [];
	<?php if ( !empty( $this->scan ) && file_exists( $this->scan ) ) { ?>
		<?php foreach ( file( $this->scan, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES ) as $line ) { ?>
			_data.push(<?php echo $line; ?>);
		<?php } ?>
	<?php } ?>

	// Set up the tabs
	jQuery( document ).ready( function( $) {
		$( "#results-table tr:even" ).addClass( "even" );
		$( "#p3-email-sending-dialog" ).dialog({
			'autoOpen' : false,
			'closeOnEscape' : false,
			'draggable' : false,
			'resizable' : false,
			'modal' : true,
			'width' : 325,
			'height' : 120,
			'dialogClass' : 'noTitle'
		});
		$( "#p3-detailed-series-toggle" ).dialog({
			'autoOpen' : false,
			'closeOnEscape' : true,
			'draggable' : false,
			'resizable' : false,
			'modal' : true,
			'width' : 400,
			'height' : 'auto',
			'title' : "Toggle Series",
			'buttons' :
			[
				{
					text: 'Ok',
					'class' : 'button-secondary',
					click: function() {
						$(this).dialog( "close" );
					}
				}
			]
		});
		$( "#p3-email-results-dialog" ).dialog({
			'autoOpen' : false,
			'closeOnEscape' : true,
			'draggable' : false,
			'resizable' : false,
			'modal' : true,
			'width' : 500,
			'height' : 560,
			'title' : "Email Report",
			'buttons' :
			[
				{
					text: 'Send',
					'class' : 'button-secondary',
					click: function() {
						data = {
							'p3_to'      : jQuery( '#p3-email-results-to' ).val(),
							'p3_from'    : jQuery( '#p3-email-results-from' ).val(),
							'p3_subject' : jQuery( '#p3-email-results-subject' ).val(),
							'p3_results' : jQuery( "#p3-email-results-results" ).val(),
							'p3_message' : jQuery( "#p3-email-results-message" ).val(),
							'action'      : 'p3_send_results',
							'p3_nonce'   : '<?php echo wp_create_nonce( 'p3_ajax_send_results' ); ?>'
						}
						
						// Open the "loading" dialog
						$( "#p3-email-sending-success" ).hide();
						$( "#p3-email-sending-error" ).hide();
						$( "#p3-email-sending-loading" ).show();
						$( "#p3-email-sending-close" ).hide();
						$( "#p3-email-sending-dialog" ).dialog( "open" );

						// Send the data
						jQuery.post( ajaxurl, data, function( response ) {
                                                        response = response.trim();
							if ( "1" == response.substring( 0, 1 ) ) {
								$( "#p3-email-success-recipient" ).html( jQuery( '#p3-email-results-to' ).val() );
								$( "#p3-email-sending-success" ).show();
								$( "#p3-email-sending-error" ).hide();
								$( "#p3-email-sending-loading" ).hide();
								$( "#p3-email-sending-close" ).show();
							} else {
								if ( "-1" == response.substring( 0, 2 ) ) {
									$( "#p3-email-error" ).html( "nonce error" );
								} else if ( "0" == response.charAt( 0 ) ) {
									$( "#p3-email-error" ).html( response.substr( 2 ) );
								} else {
									$( "#p3-email-error" ).html( "unknown error" );
								}
								$( "#p3-email-sending-success" ).hide();
								$( "#p3-email-sending-error" ).show();
								$( "#p3-email-sending-loading" ).hide();
								$( "#p3-email-sending-close" ).show();
							}
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
		$( "#p3-email-sending-close-submit" ).click( function() {
			$( this ).prop( "checked", true );
			$( this ).button( "refresh" );
			$( "#p3-email-sending-dialog" ).dialog( "close" );
			$( "#p3-email-results-dialog" ).dialog( "close" );
		});
		$( "#p3-email-results" ).click( function() {
			$( "#p3-email-results-dialog" ).dialog( "open" );
		});
		$( "#p3-email-sending-close" ).buttonset();
	});



	/**************************************************************/
	/**  Hover function for charts                               **/
	/**************************************************************/
	var previousPoint = null;
	function showTooltip( x, y, contents ) {
		jQuery( '<div id="p3-tooltip">' + contents + '</div>' ).css(
			{
				position: 'absolute',
				display: 'none',
				top: y + 5,
				left: x + 5,
				border: '1px solid #fdd',
				padding: '2px',
				'background-color': '#fee',
				opacity: 0.80
			}
		).appendTo( "body" ).fadeIn( 200 );
	}



	/**************************************************************/
	/**  Plugin pie chart                                        **/
	/**************************************************************/
	var data_<?php echo $pie_chart_id; ?> = [
		<?php if ( !empty( $this->profile ) ){ ?>
			<?php foreach ( $this->profile->plugin_times as $k => $v ) { ?>
				{
					label: "<?php echo esc_js( $k ); ?>",
					data: <?php echo $v; ?>
				},
			<?php } ?>
		<?php } else { ?>
			{ label: 'No plugins', data: 1}
		<?php } ?>
	];
	jQuery( document ).ready( function( $) {
		$.plot( $(
			"#p3-holder_<?php echo $pie_chart_id; ?>" ),
			data_<?php echo $pie_chart_id; ?>,
		{
				series: {
					pie: { 
						show: true,
						combine: {
							threshold: .03 // 3% or less
						}
					}
				},
				grid: {
					hoverable: true,
					clickable: true
				},
				legend: {
					container: $( "#p3-legend_<?php echo $pie_chart_id; ?>" )
				}
		});

		$( "#p3-holder_<?php echo $pie_chart_id; ?>" ).bind( "plothover", function ( event, pos, item ) {
			if ( item ) {
				$( "#p3-tooltip" ).remove();
				showTooltip( pos.pageX, pos.pageY,
					item.series.label + "<br />" + Math.round( item.series.percent ) + "%<br />" +
					Math.round( item.datapoint[1][0][1] * Math.pow( 10, 4 ) ) / Math.pow( 10, 4 ) + " seconds"
				);
			} else {
				$( "#p3-tooltip" ).remove();
			}
		});
	});



	/**************************************************************/
	/**  Runtime line chart data                                 **/
	/**************************************************************/
	var chart_<?php echo $runtime_chart_id; ?> = null;
	var data_<?php echo $runtime_chart_id; ?> = [
		{
			label: "WP Core time",
			data: [
			<?php foreach ( array_values( $url_stats ) as $k => $v ) { ?>
				[
					<?php echo $k + 1; ?>,
					<?php echo $v['core']; ?>
				],
			<?php } ?>
			]
		},
		{
			label: "Theme time",
			data: [
			<?php foreach ( array_values( $url_stats ) as $k => $v ) { ?>
				[
					<?php echo $k + 1; ?>,
					<?php echo $v['theme']; ?>
				],
			<?php } ?>
			]
		},
		{
			label: "Plugin time",
			data: [
			<?php foreach ( array_values( $url_stats ) as $k => $v ) { ?>
				[
					<?php echo $k + 1; ?>,
					<?php echo $v['plugins']; ?>
				],
			<?php } ?>
			]
		}
	];
	jQuery( document ).ready( function( $) {
		chart_<?php echo $runtime_chart_id; ?> = $.plot( $(
			"#p3-holder_<?php echo $runtime_chart_id; ?>" ),
			data_<?php echo $runtime_chart_id; ?>,
		{
				series: {
					lines: { show: true },
					points: { show: true },
				},
				grid: {
					hoverable: true,
					clickable: true
				},
				legend : {
					container: $( "#p3-legend_<?php echo $runtime_chart_id; ?>" )
				},
				zoom: {
					interactive: true
				},
				pan: {
					interactive: true
				},
				xaxis: {
					show: false
				}
		});

		// zoom buttons
		$( '<div class="button" style="float: left; position: relative; left: 490px; top: -290px;">-</div>' )
			.appendTo( $( "#p3-holder_<?php echo $runtime_chart_id; ?>" ).parent() ).click( function ( e ) {
			e.preventDefault();
			chart_<?php echo $runtime_chart_id; ?>.zoomOut();
		});
		$( '<div class="button" style="float: left; position: relative; left: 490px; top: -290px;">+</div>' )
			.appendTo( $( "#p3-holder_<?php echo $runtime_chart_id; ?>" ).parent() ).click( function ( e ) {
			e.preventDefault();
			chart_<?php echo $runtime_chart_id; ?>.zoom();
		});

		$( "#p3-holder_<?php echo $runtime_chart_id; ?>" ).bind( "plothover", function ( event, pos, item ) {
			if ( item ) {
				if ( previousPoint != item.dataIndex ) {
					previousPoint = item.dataIndex;

					$( "#p3-tooltip" ).remove();
					var x = item.datapoint[0].toFixed( 2 ),
						y = item.datapoint[1].toFixed( 2 );

					url = _data[item["dataIndex"]]["url"];

					// Get rid of the domain
					url = url.replace(/http[s]?:\/\/<?php echo $domain; ?>(:\d+)?/, "" );

					showTooltip( item.pageX, item.pageY,
								item.series.label + "<br />" +
								url + "<br />" +
								y + " seconds" );
				}
			} else {
				$( "#p3-tooltip" ).remove();
				previousPoint = null;            
			}
		});
	});
	


	/**************************************************************/
	/**  Query line chart data                                   **/
	/**************************************************************/
	var chart_<?php echo $query_chart_id; ?> = null;
	var data_<?php echo $query_chart_id; ?> = [
		{
			label: "# of Queries",
			data: [
			<?php if ( !empty( $this->profile ) ){ ?>
				<?php foreach ( array_values( $url_stats ) as $k => $v ) { ?>
					[
						<?php echo $k + 1; ?>,
						<?php echo $v['queries']; ?>
					],
				<?php } ?>
			<?php } ?>
			]
		}
	];
	jQuery( document ).ready( function( $) {
		chart_<?php echo $query_chart_id; ?> = $.plot( $(
			"#p3-holder_<?php echo $query_chart_id; ?>" ),
			data_<?php echo $query_chart_id; ?>,
		{
				series: {
					lines: { show: true },
					points: { show: true }
				},
				grid: {
					hoverable: true,
					clickable: true
				},
				legend : {
					container: $( "#p3-legend_<?php echo $query_chart_id; ?>" )
				},
				zoom: {
					interactive: true
				},
				pan: {
					interactive: true
				},
				xaxis: {
					show: false
				}
		});

		// zoom buttons
		$( '<div class="button" style="float: left; position: relative; left: 490px; top: -290px;">-</div>' )
			.appendTo( $( "#p3-holder_<?php echo $query_chart_id; ?>" ).parent() ).click( function ( e ) {
			e.preventDefault();
			chart_<?php echo $query_chart_id; ?>.zoomOut();
		});
		$( '<div class="button" style="float: left; position: relative; left: 490px; top: -290px;">+</div>' )
			.appendTo( $( "#p3-holder_<?php echo $query_chart_id; ?>" ).parent() ).click( function ( e ) {
			e.preventDefault();
			chart_<?php echo $query_chart_id; ?>.zoom();
		});

		$( "#p3-holder_<?php echo $query_chart_id; ?>" ).bind( "plothover", function ( event, pos, item ) {
			if ( item ) {
				if ( previousPoint != item.dataIndex ) {
					previousPoint = item.dataIndex;

					$( "#p3-tooltip" ).remove();
					var x = item.datapoint[0].toFixed( 2 ),
						y = item.datapoint[1]; //.toFixed( 2 );

					url = _data[item["dataIndex"]]["url"];

					// Get rid of the domain
					url = url.replace(/http[s]?:\/\/<?php echo $domain; ?>(:\d+)?/, "" );

					qword = ( y == 1 ) ? "query" : "queries";
					showTooltip( item.pageX, item.pageY,
								item.series.label + "<br />" +
								url + "<br />" +
								y + " " + qword );
				}
			} else {
				$( "#p3-tooltip" ).remove();
				previousPoint = null;            
			}
		});
	});


	/**************************************************************/
	/**  Compnent bar chart data                                 **/
	/**************************************************************/
	var chart_<?php echo $component_breakdown_chart_id; ?> = null;
	var data_<?php echo $component_breakdown_chart_id; ?> = [
		{
			label: 'Site Load Time',
			bars: {show: false},
			points: {show: false},
			lines: {show: true, lineWidth: 3},
			shadowSize: 0,
			data: [
				<?php for ( $i = -999 ; $i < 999 + 2; $i++ ) { ?>
					[
						<?php echo $i; ?>,
						<?php echo $this->profile->averages['site']; ?>
					],
				<?php } ?>
			]
		},
		{
			label: 'WP Core Time',
			data: [[0, <?php echo $this->profile->averages['core']; ?>]]
		},
		{
			label: 'Theme',
			data: [[1, <?php echo $this->profile->averages['theme']; ?>]]
		},
		<?php $i = 2; $other = 0; ?>
		<?php foreach ( $this->profile->plugin_times as $k => $v ) { ?>
			{
				label: '<?php echo esc_js( $k ); ?>',
				data: [[
					<?php echo $i++; ?>,
					<?php echo $v; ?>
				]],
			},
		<?php } ?>
	];

	jQuery( document ).ready( function( $) {
		chart_<?php echo $component_breakdown_chart_id; ?> = $.plot( $(
			"#p3-holder_<?php echo $component_breakdown_chart_id; ?>" ),
			data_<?php echo $component_breakdown_chart_id; ?>,
		{
				series: {
					bars: {
						show: true,
						barWidth: 0.9,
						align: 'center'
					},
					stack: false,
					lines: {
						show: false,
						steps: false,
					}
				},
				grid: {
					hoverable: true,
					clickable: true,
				},
				xaxis: {
					show: false,
					ticks: [
						[0, 'Site Load Time'],
						[1, 'WP Core Time'],
						[2, 'Theme'],
						<?php $i = 3; ?>
						<?php foreach ( $this->profile->plugin_times as $k => $v ) { ?>
							[
								<?php echo $i++ ?>,
								'<?php echo esc_js( $k ); ?>'
							],
						<?php } ?>
					],
					min: 0,
					max: <?php echo $i; ?>,
				},
				legend : {
					container: $( "#p3-legend_<?php echo $component_breakdown_chart_id; ?>" )
				},
				zoom: {
					interactive: true
				},
				pan: {
					interactive: true
				}
		});

		$( "#p3-holder_<?php echo $component_breakdown_chart_id; ?>" ).bind( "plothover", function ( event, pos, item ) {
			if ( item ) {
				$( "#p3-tooltip" ).remove();
				showTooltip( pos.pageX, pos.pageY,
					item.series.label + "<br />" + Math.round( item.datapoint[1] * Math.pow( 10, 4 ) ) / Math.pow( 10, 4 ) + " seconds"
				);
			} else {
				$( "#p3-tooltip" ).remove();
			}
		});

		// zoom buttons
		$( '<div class="button" style="float: left; position: relative; left: 490px; top: -290px;">-</div>' )
			.appendTo( $( "#p3-holder_<?php echo $component_breakdown_chart_id; ?>" ).parent() ).click( function ( e ) {
			e.preventDefault();
			chart_<?php echo $component_breakdown_chart_id; ?>.zoomOut();
		});
		$( '<div class="button" style="float: left; position: relative; left: 490px; top: -290px;">+</div>' )
			.appendTo( $( "#p3-holder_<?php echo $component_breakdown_chart_id; ?>" ).parent() ).click( function ( e ) {
			e.preventDefault();
			chart_<?php echo $component_breakdown_chart_id; ?>.zoom();
		});
	});

	/**************************************************************/
	/**  Runtime by component line chart data                    **/
	/**************************************************************/
	var chart_<?php echo $component_runtime_chart_id; ?> = null;
	var data_<?php echo $component_runtime_chart_id; ?> = [
		{
			label: "WP Core Time",
			data: [
			<?php if ( !empty( $this->profile ) ){ ?>
				<?php foreach ( array_values( $url_stats ) as $k => $v ) { ?>
					[
						<?php echo $k + 1; ?>,
						<?php echo $v['core']; ?>
					],
				<?php } ?>
			<?php } ?>
			]
		},
		{
			label: "Theme",
			data: [
			<?php if ( !empty( $this->profile ) ){ ?>
				<?php foreach ( array_values( $url_stats ) as $k => $v ) { ?>
					[
						<?php echo $k + 1; ?>,
						<?php echo $v['theme']; ?>
					],
				<?php } ?>
			<?php } ?>
			]
		},
		<?php if ( !empty( $this->profile ) && !empty( $this->profile->detected_plugins ) ) { ?>
			<?php foreach ( $this->profile->detected_plugins as $plugin ) { ?>
				{
					label: "<?php echo esc_js( $plugin ); ?>",
					data: [
					<?php foreach ( array_values( $url_stats ) as $k => $v ) { ?>
						[
							<?php echo $k + 1; ?>,
							<?php if ( array_key_exists( $plugin, $v['breakdown'] ) ) : ?>
								<?php echo $v['breakdown'][$plugin]; ?>
							<?php else : ?>
								0
							<?php endif; ?>
						],
					<?php } ?>
					]
				},
			<?php } ?>
		<?php } ?>
	];
	
	var detailed_timeline_options = {};

	jQuery( document ).ready( function ( $ ) {
		<?php if ( !empty( $this->profile ) && !empty( $this->profile->detected_plugins ) ) { ?>
			jQuery( "#p3-detailed-series-toggle" ).append( '<div><label><input type="checkbox" checked="checked" class="p3-detailed-series-toggle" data-key="WP Core Time" />WP Core Time</label></div>' );
			jQuery( "#p3-detailed-series-toggle" ).append( '<div><label><input type="checkbox" checked="checked" class="p3-detailed-series-toggle" data-key="Theme" />Theme</label></div>' );
			<?php foreach ( $this->profile->detected_plugins as $plugin ) { ?>
				jQuery( "#p3-detailed-series-toggle" ).append( '<div><label><input type="checkbox" checked="checked" class="p3-detailed-series-toggle" data-key="<?php echo esc_html( $plugin ); ?>" /><?php echo esc_html( $plugin ); ?></label></div>' );
			<?php } ?>
		<?php } ?>
		jQuery( "input.p3-detailed-series-toggle" ).click( function() {
			data = [];
			keys = [];
			jQuery( "input.p3-detailed-series-toggle:checked" ).each(function() {
				keys.push( $( this ).attr( "data-key" ) );
			});
			for ( i = 0 ; i < keys.length ; i++ ) {
				tmp = [];
				for ( j = 0 ; j < data_<?php echo $component_runtime_chart_id; ?>.length ; j++ ) {
					if ( keys[i] == data_<?php echo $component_runtime_chart_id; ?>[j]['label'] ) {
						for ( k = 0 ; k < data_<?php echo $component_runtime_chart_id; ?>[j]['data'].length ; k++ ) {
							tmp.push( data_<?php echo $component_runtime_chart_id; ?>[j]['data'][k] );
						}
					}
				}
				data.push( {
					data: tmp,
					label: keys[i]
				} );
			}
			if ( data.length == 0 ) {
				data = [
					{
						data: [],
						label: 'No data'
					}
				]
			}
			chart_<?php echo $component_runtime_chart_id; ?> = $.plot(
				$( "#p3-holder_<?php echo $component_runtime_chart_id; ?>" ),
				data,
				detailed_timeline_options
			);
		});
	});
	jQuery( document ).ready( function( $ ) {
		detailed_timeline_options = {
			series: {
				lines: { show: true },
				points: { show: true }
			},
			grid: {
				hoverable: true,
				clickable: true
			},
			legend : {
				container: jQuery( "#p3-legend_<?php echo $component_runtime_chart_id; ?>" )
			},
			zoom: {
				interactive: true
			},
			pan: {
				interactive: true
			},
			xaxis: {
				show: false
			}
		}
		chart_<?php echo $component_runtime_chart_id; ?> = $.plot(
			$( "#p3-holder_<?php echo $component_runtime_chart_id; ?>" ),
			data_<?php echo $component_runtime_chart_id; ?>,
			detailed_timeline_options
		);

		$( "#p3-holder_<?php echo $component_runtime_chart_id; ?>" ).bind( "plothover", function ( event, pos, item ) {
			if ( item ) {
				if ( previousPoint != item.dataIndex ) {
					previousPoint = item.dataIndex;

					$( "#p3-tooltip" ).remove();
					var x = item.datapoint[0].toFixed( 2 ),
						y = item.datapoint[1]; //.toFixed( 2 );

					url = _data[item["dataIndex"]]["url"];

					// Get rid of the domain
					url = url.replace(/http[s]?:\/\/<?php echo $domain; ?>(:\d+)?/, "" );

					showTooltip( item.pageX, item.pageY,
								item.series.label + "<br />" +
								url + "<br />" +
								y + " seconds" );
				}
			} else {
				$( "#p3-tooltip" ).remove();
				previousPoint = null;            
			}
		});
		
		// zoom buttons
		$( '<div class="button" style="float: left; position: relative; left: 460px; top: -290px;">-</div>' )
			.appendTo( $( "#p3-holder_<?php echo $component_runtime_chart_id; ?>" ).parent() ).click( function ( e ) {
			e.preventDefault();
			chart_<?php echo $component_runtime_chart_id; ?>.zoomOut();
		});
		$( '<div class="button" style="float: left; position: relative; left: 460px; top: -290px;">+</div>' )
			.appendTo( $( "#p3-holder_<?php echo $component_runtime_chart_id; ?>" ).parent() ).click( function ( e ) {
			e.preventDefault();
			chart_<?php echo $component_runtime_chart_id; ?>.zoom();
		});
		$( '<div class="button" style="float: left; position: relative; left: 460px; top: -290px;"><input type="checkbox" checked="checked" style="padding: 0; margin: 0; width: 15px;" /></div>' )
			.appendTo( $( "#p3-holder_<?php echo $component_runtime_chart_id; ?>" ).parent() ).click( function ( e ) {
			e.preventDefault();
			$( "#p3-detailed-series-toggle" ).dialog( "open" );
		});

	});
	
	jQuery( document ).ready( function( $ ) {
		$( "#p3-tabs" ).tabs();
	});

</script>
<div id="p3-tabs">
	<ul>
		<li><a href="#p3-tabs-1">Runtime By Plugin</a></li>
		<li><a href="#p3-tabs-5">Detailed Breakdown</a></li>
		<li><a href="#p3-tabs-2">Simple Timeline</a></li>
		<li><a href="#p3-tabs-6">Detailed Timeline</a></li>
		<li><a href="#p3-tabs-3">Query Timeline</a></li>
		<li><a href="#p3-tabs-4">Advanced Metrics</a></li>
	</ul>

	<!-- Plugin bar chart -->
	<div id="p3-tabs-5">
		<h2>Detailed Breakdown</h2>
		<div class="p3-plugin-graph">
			<table>
				<tr>
					<td rowspan="2">
						<div class="p3-y-axis-label">
							<em class="p3-em">Seconds</em>
						</div>
					</td>
					<td rowspan="2">
						<div class="p3-graph-holder" id="p3-holder_<?php echo $component_breakdown_chart_id; ?>"></div>
					</td>
					<td>
						<h3>Legend</h3>
					</td>
				</tr>
				<tr>
					<td>
						<div class="p3-custom-legend" id="p3-legend_<?php echo $component_breakdown_chart_id; ?>"></div>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td colspan="2">
						<div class="p3-x-axis-label" style="top: -10px;">
							<em class="p3-em">Component</em>
						</div>
					</td>
				</tr>
			</table>
		</div>		
	</div>
	
	<!-- Plugin pie chart div -->
	<div id="p3-tabs-1">
		<h2>Runtime by Plugin</h2>
		<div class="p3-plugin-graph" style="width: 570px;">
			<table>
				<tr>
					<td rowspan="2">
						<div style="width: 370px;" class="p3-graph-holder" id="p3-holder_<?php echo $pie_chart_id; ?>"></div>
					</td>
					<td>
						<h3>Legend</h3>
					</td>
				</tr>
				<tr>
					<td>
						<div class="p3-custom-legend" id="p3-legend_<?php echo $pie_chart_id;?>"></div>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<!-- Runtime line chart div -->
	<div id="p3-tabs-2">
		<h2>Summary Timeline</h2>
		<div class="p3-plugin-graph">
			<table>
				<tr>
					<td rowspan="2">
						<div class="p3-y-axis-label">
							<em class="p3-em">Seconds</em>
						</div>
					</td>
					<td rowspan="2">
						<div class="p3-graph-holder" id="p3-holder_<?php echo $runtime_chart_id; ?>"></div>
					</td>
					<td>
						<h3>Legend</h3>
					</td>
				</tr>
				<tr>
					<td>
						<div class="p3-custom-legend" id="p3-legend_<?php echo $runtime_chart_id; ?>"></div>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td colspan="2">
						<div class="p3-x-axis-label">
							<!-- <em class="p3-em">Visit</em> -->
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<!-- Query line chart div -->
	<div id="p3-tabs-3">
		<h2>Query Timeline</h2>
		<div class="p3-plugin-graph">
			<table>
				<tr>
					<td rowspan="2">
						<div class="p3-y-axis-label">
							<em class="p3-em">Queries</em>
						</div>
					</td>
					<td rowspan="2">
						<div class="p3-graph-holder" id="p3-holder_<?php echo $query_chart_id; ?>"></div>
					</td>
					<td>
						<h3>Legend</h3>
					</td>
				</tr>
				<tr>
					<td>
						<div class="p3-custom-legend" id="p3-legend_<?php echo $query_chart_id; ?>"></div>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td colspan="2">
						<div class="p3-x-axis-label">
							<!-- <em class="p3-em">Visit</em> -->
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>

	<!-- Component runtime chart div -->
	<div id="p3-tabs-6">
		<h2>Detailed Timeline</h2>
		<div class="p3-plugin-graph">
			<table>
				<tr>
					<td rowspan="2">
						<div class="p3-y-axis-label">
							<em class="p3-em">Seconds</em>
						</div>
					</td>
					<td rowspan="2">
						<div class="p3-graph-holder" id="p3-holder_<?php echo $component_runtime_chart_id; ?>"></div>
					</td>
					<td>
						<h3>Legend</h3>
					</td>
				</tr>
				<tr>
					<td>
						<div class="p3-custom-legend" id="p3-legend_<?php echo $component_runtime_chart_id; ?>"></div>
					</td>
				</tr>
				<tr>
					<td>&nbsp;</td>
					<td colspan="2">
						<div class="p3-x-axis-label">
							<!-- <em class="p3-em">Visit</em> -->
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>
	
	<!-- Advanced data -->
	<div id="p3-tabs-4">
		<div id="p3-metrics-container">
			<div class="ui-widget-header" id="p3-metrics-header" style="padding: 8px;">
				<strong>Advanced Metrics</strong>
			</div>
			<div>
				<table class="p3-results-table" id="p3-results-table" cellpadding="0" cellspacing="0" border="0">
					<tbody>
						<tr class="advanced">
							<td class="qtip-tip" title="The time the site took to load. This is an observed measurement (start
											timing when the page was requested, stop timing when the page was delivered to the browser,
											calculate the difference). Lower is better.">
								<strong>Total Load Time: </strong>
							</td>
							<td>
								<?php printf( '%.4f', $this->profile->averages['total'] ); ?> seconds <em class="p3-em">avg.</em>
							</td>
						</tr>
						<tr>
							<td class="qtip-tip" title="The calculated total load time minus the profile overhead. This is closer to your
											site's real-life load time. Lower is better.">
								<strong>Site Load Time</small></em></strong>
							</td>
							<td>
								<?php printf( '%.4f', $this->profile->averages['site'] ); ?> seconds <em class="p3-em">avg.</em>
							</td>
						</tr>
						<tr class="advanced">
							<td class="qtip-tip" title="The load time spent profiling code. Because the profiler slows down your load time,
											it is important to know how much impact the profiler has. However, it doesn't impact your site's
											real-life load time.">
								<strong>Profile Overhead: </strong>
							</td>
							<td>
								<?php printf( '%.4f', $this->profile->averages['profile'] ); ?> seconds <em class="p3-em">avg.</em>
							</td>
						</tr>
						<tr>
							<td class="qtip-tip" title="The load time caused by plugins. Because of WordPress' construction, we can trace a
											function call  from a plugin through a theme through the core. The profiler prioritizes plugin calls
											first, theme calls second, and core calls last. Lower is better.">
								<strong>Plugin Load Time: </strong>
							</td>
							<td>
								<?php printf( '%.4f', $this->profile->averages['plugins'] ); ?> seconds <em class="p3-em">avg.</em>
							</td>
						</tr>
						<tr>
							<td class="qtip-tip" title="The load time spent applying the theme. Because of WordPress' construction, we can trace
											a function call from a plugin through a theme through the core. The profiler prioritizes plugin calls
											first, theme calls second, and core calls last. Lower is better.">
								<strong>Theme Load Time: </strong>
							</td>
							<td>
								<?php printf( '%.4f', $this->profile->averages['theme'] ); ?> seconds <em class="p3-em">avg.</em>
							</td>
						</tr>
						<tr>
							<td class="qtip-tip" title="The load time caused by the WordPress core. Because of WordPress' construction, we can
											trace a function call from a plugin through a theme through the core. The profiler prioritizes plugin
											calls first, theme calls second, and core calls last. This will probably be constant.">
								<strong>Core Load Time: </strong>
							</td>
							<td>
								<?php printf( '%.4f', $this->profile->averages['core'] ); ?> seconds <em class="p3-em">avg.</em>
							</td>
						</tr>
						<tr class="advanced">
							<td class="qtip-tip" title="This is the difference between the observed runtime (what actually happened) and expected
											runtime (adding the plugin runtime, theme runtime, core runtime, and profiler overhead).
											There are several reasons this margin of error can exist. Most likely, the profiler is
											missing microseconds while adding the runtime it observed. Using a network clock to set the
											time (NTP) can also cause minute timing changes.
											Ideally, this number should be zero, but there's nothing you can do to change it. It
											will give you an idea of how accurate the other results are.">
								<strong>Margin of Error: </strong>
							</td>
							<td>
								<?php printf( '%.4f', $this->profile->averages['drift'] ); ?> seconds <em class="p3-em">avg.</em>
								<br />
								<em class="p3-em">
									(<span class="qtip-tip" title="How long the site took to load. This is an observed measurement (start timing
											when the page was requested, stop timing when the page was delivered to the browser, calculate the
											difference)."><?php printf( '%.4f', $this->profile->averages['observed'] ); ?> observed<span>,
											<span class="qtip-tip" title="The expected site load time calculated by adding plugin load time, core
											load time, theme load time, and profiler overhead.">
											<?php printf( '%.4f', $this->profile->averages['expected'] ); ?> expected</span>)
								</em>
							</td>
						</tr>
						<tr class="advanced">
							<td class="qtip-tip" title="The number of visits registered during a profiling session.  More visits produce a more
											accurate summary.">
								<strong>Visits: </strong>
							</td>
							<td>
								<?php echo number_format( $this->profile->visits ); ?>
							</td>
						</tr>
						<tr class="advanced">
							<td class="qtip-tip" title="The number of PHP function calls generated by a plugin. Fewer is better.">
								<strong>Number of Plugin Function Calls: </strong>
							</td>
							<td>
								<?php echo number_format( $this->profile->averages['plugin_calls'] ); ?> calls <em class="p3-em">avg.</em>
							</td>
						</tr>
						<tr>
							<td class="qtip-tip" title="The amount of RAM usage observed.  This is reported by memory_get_peak_usage().
											Lower is better.">
								<strong>Memory Usage: </strong>
							</td>
							<td>
								<?php echo number_format( $this->profile->averages['memory'] / 1024 / 1024, 2 ); ?> MB <em class="p3-em">avg.</em>
							</td>
						</tr>
						<tr>
							<td class="qtip-tip" title="The count of queries sent to the database.  This is reported by the WordPress function
											get_num_queries(). Lower is better.">
								<strong>MySQL Queries: </strong>
							</td>
							<td>
								<?php echo round( $this->profile->averages['queries'] ); ?> queries <em class="p3-em">avg.</em>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
	</div>

	<!-- Email these results -->
	<div class="button" id="p3-email-results" style="width: 155px; padding: 5px;">
		<img src="<?php echo plugins_url(); ?>/p3-profiler/css/icon_mail.gif" height="22" width="22" align="center"
			alt="Email these results" title="Email these results" />
		<a href="javascript:;">Email these results</a>
	</div>
	
	<!-- Email results dialog -->
	<div id="p3-email-results-dialog" class="p3-dialog">
		<div>
			From:<br />
			<input type="text" id="p3-email-results-from" style="width:95%;" size="35"
				value="<?php $user = wp_get_current_user(); echo $user->user_email; ?>" title="Enter the e-mail address to send from" />
		</div>
		<br />
		<div>
			Recipient:<br />
			<input type="text" id="p3-email-results-to" style="width:95%;" size="35"
				value="<?php $user = wp_get_current_user(); echo $user->user_email; ?>"
				title="Enter the e-mail address where you would like to send these results" />
		</div>
		<br />
		<div>
			Subject:<br />
			<input type="text" id="p3-email-results-subject" style="width:95%;" size="35"
				value="Performance Profile Results - <?php bloginfo( 'name' ); ?>" title="Enter the e-mail subject" />
		</div>
		<br />
		<div>
			Message: <em class="p3-em">( optional )</em><br />
			<textarea id="p3-email-results-message" style="width: 95%; height: 100px;">Hello,

I profiled my WordPress site's performance using the Profile Plugin and I wanted
to share the results with you.  Please take a look at the information below:</textarea>
		</div>
		<br />
		<div>
			Results: <em class="p3-em">( system generated, do not edit )</em><br />
			<textarea disabled="disabled" id="p3-email-results-results" style="width: 95%; height: 120px;"><?php 
			echo "WordPress Plugin Profile Report\n";
			echo "===========================================\n";
			echo 'Report date: ' . date( 'D M j, Y', $this->profile->report_date ) . "\n";
			echo 'Theme name: ' . $this->profile->theme_name . "\n";
			echo 'Pages browsed: ' . $this->profile->visits . "\n";
			echo 'Avg. load time: ' . sprintf( '%.4f', $this->profile->averages['site'] ) . " sec\n";
			echo 'Number of plugins: ' . count( $this->profile->detected_plugins ) . " \n";
			echo 'Plugin impact: ' . sprintf( '%.2f%%', $this->profile->averages['plugin_impact'] ) . " % of load time\n";
			echo 'Avg. plugin time: ' . sprintf( '%.4f', $this->profile->averages['plugins'] ) . " sec\n";
			echo 'Avg. core time: ' . sprintf( '%.4f', $this->profile->averages['core'] ) . " sec\n";
			echo 'Avg. theme time: ' . sprintf( '%.4f', $this->profile->averages['theme'] ) . " sec\n";
			echo 'Avg. mem usage: ' . number_format( $this->profile->averages['memory'] / 1024 / 1024, 2 ) . " MB\n";
			echo 'Avg. plugin calls: ' . number_format( $this->profile->averages['plugin_calls'] ) . "\n";
			echo 'Avg. db queries : ' . sprintf( '%.2f', $this->profile->averages['queries'] ) . "\n";
			echo 'Margin of error : ' . sprintf( '%.4f', $this->profile->averages['drift'] ) . " sec\n";
			echo "\nPlugin list:\n";
			echo "===========================================\n";
			foreach ( $this->profile->plugin_times as $k => $v) {
				echo $k . ' - ' . sprintf('%.4f sec', $v) . ' - ' . sprintf( '%.2f%%', $v * 100 / array_sum( $this->profile->plugin_times ) ) . "\n";
			}
			?></textarea>
		</div>
		<input type="hidden" id="p3-email-results-scan" value="<?php echo basename( $this->scan ); ?>" />
	</div>
	
	<!-- Email sending dialog -->
	<div id="p3-email-sending-dialog" class="p3-dialog">
		<div id="p3-email-sending-loading">
			<img src="<?php echo get_site_url() . '/wp-admin/images/loading.gif' ?>" height="16" width="16" title="Loading" alt="Loading" />
		</div>
		<div id="p3-email-sending-error">
			There was a problem sending the e-mail: <span id="p3-email-error"></span>
		</div>
		<div id="p3-email-sending-success">
			Your report was sent successfully to <span id="p3-email-success-recipient"></span>
		</div>
		<div id="p3-email-sending-close">
			<input type="checkbox" id="p3-email-sending-close-submit" checked="checked" /><label for="p3-email-sending-close-submit">Done</label>
		</div>
	</div>

	<!-- Enable / disable series dialog -->
	<div id="p3-detailed-series-toggle" class="p3-dialog">
		
	</div>
</div>