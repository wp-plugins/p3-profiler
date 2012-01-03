<?php

// If profiling hasn't started, start it
if ( !isset( $GLOBALS['p3_profiler'] ) && basename( __FILE__ ) !=  basename( $_SERVER['SCRIPT_FILENAME'] ) ) {
	declare( ticks = 1 ); // Capture every user function call
	include_once realpath( dirname( __FILE__ ) ) . '/class.p3-profiler.php';
	$GLOBALS['p3_profiler'] = new P3_Profiler(); // Go
}
