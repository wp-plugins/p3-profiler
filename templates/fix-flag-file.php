<?php
if ( !defined('P3_PATH') )
	die( 'Forbidden ');
?>
<?php if ( file_exists( P3_FLAG_FILE ) && is_writable( P3_FLAG_FILE ) ) { ?>
	<h3>Fixed!</h3>
	The profiling flag file has been created and is writable.
<?php } else { ?>
	<h3>Still broken!</h3>
	The profiling flag file needs to exist and be writable.
<?php } ?>
<br /><br />
<code><?php echo realpath( P3_FLAG_FILE ); ?></code>
<br /><br />
<input type="button" class="button" onclick="location.href='<?php echo add_query_arg( array( 'p3_action' => 'current-scan' ) ); ?>';" value="Go back" />