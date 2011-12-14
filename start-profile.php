<?php

// If profiling hasn't started, start it
if ( !isset( $GLOBALS['p3_profiler'] ) ) {
	declare( ticks = 1 ); // Capture ever user function call
	include_once realpath( dirname( __FILE__ ) ) . '/class.p3-profiler.php';
	$GLOBALS['p3_profiler'] = new P3_Profiler(); // Go
}
