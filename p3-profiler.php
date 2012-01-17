<?php
/*
Plugin Name: P3 (Plugin Performance Profiler)
Plugin URI: http://support.godaddy.com/godaddy/wordpress-p3-plugin/
Description: See which plugins are slowing down your site.  Create a profile of your WordPress site's plugins' performance by measuring their impact on your site's load time.
Author: GoDaddy.com
Version: 1.1.1
Author URI: http://www.godaddy.com/
*/

// Make sure it's wordpress
if ( !defined( 'ABSPATH') )
	die( 'Forbidden' );

/**************************************************************************/
/**        PACKAGE CONSTANTS                                             **/
/**************************************************************************/

// Shortcut for knowing our path
define( 'P3_PATH',  realpath( dirname( __FILE__ ) ) );

// Flag file for enabling profile mode
define( 'P3_FLAG_FILE', P3_PATH . DIRECTORY_SEPARATOR . '.profiling_enabled' );

// Directory for profiles
$uploads_dir = wp_upload_dir();
define( 'P3_PROFILES_PATH', $uploads_dir['basedir'] . DIRECTORY_SEPARATOR . 'profiles' );


/**************************************************************************/
/**        START PROFILING                                               **/
/**************************************************************************/

// Start profiling.  If it's already been started, this line won't do anything
require_once P3_PATH . '/start-profile.php';


/**************************************************************************/
/**        PLUGIN HOOKS                                                  **/
/**************************************************************************/

// Global plugin object
$p3_profiler_plugin = new P3_Profiler_Plugin();

// Admin hooks
if ( is_admin() ) {
	// Show the 'Profiler' option under the 'Plugins' menu
	add_action( 'admin_menu', array( $p3_profiler_plugin, 'settings_menu' ) );
	
	// Upgrade routine
	add_action( 'admin_init', array( $p3_profiler_plugin, 'upgrade' ) );

	// Ajax actions
	add_action( 'wp_ajax_p3_start_scan', array( $p3_profiler_plugin, 'ajax_start_scan' ) );
	add_action( 'wp_ajax_p3_stop_scan', array( $p3_profiler_plugin, 'ajax_stop_scan' ) );
	add_action( 'wp_ajax_p3_send_results', array( $p3_profiler_plugin, 'ajax_send_results' ) );
	add_action( 'wp_ajax_p3_save_settings', array( $p3_profiler_plugin, 'ajax_save_settings' ) );

	// Show any notices
	add_action( 'admin_notices', array( $p3_profiler_plugin, 'show_notices' ) );

	// Early init actions ( processing bulk table actions, loading libraries, etc.)
	add_action( 'admin_head', array( $p3_profiler_plugin, 'early_init' ) );
}

// Remove the admin bar when in profiling mode
if ( defined( 'WPP_PROFILING_STARTED' ) || isset( $_GET['P3_HIDE_ADMIN_BAR'] ) ) {
	add_action( 'plugins_loaded', array( $p3_profiler_plugin, 'remove_admin_bar' ) );
}

// Install / uninstall hooks
register_activation_hook( P3_PATH . DIRECTORY_SEPARATOR . 'p3-profiler.php', array( $p3_profiler_plugin, 'activate' ) );
register_deactivation_hook( P3_PATH . DIRECTORY_SEPARATOR . 'p3-profiler.php', array( $p3_profiler_plugin, 'deactivate' ) );
register_uninstall_hook( P3_PATH . DIRECTORY_SEPARATOR . 'p3-profiler.php', array( 'P3_Profiler_Plugin', 'uninstall' ) );
if ( function_exists( 'is_multisite' ) && is_multisite() ) {
	add_action( 'wpmu_add_blog', array( $p3_profiler_plugin, 'add_blog' ) );
	add_action( 'wpmu_delete_blog', array( $p3_profiler_plugin, 'delete_blog' ) );
}

/**
 * P3 Plugin Performance Profiler Plugin Controller
 *
 * @author GoDaddy.com
 * @version 1.0
 * @package P3_Profiler
 */
class P3_Profiler_Plugin {
	
	/**
	 * List table of the profile scans
	 * @var P3_Profile_Table
	 */
	public $scan_table = null;
	
	/**
	 * Remove the admin bar from the customer site when profiling is enabled
	 * to prevent skewing the numbers, as much as possible.  Also prevent ssl
	 * warnings by forcing content into ssl mode if the admin is in ssl mode
	 * @return void
	 */
	public function remove_admin_bar() {
		if ( !is_admin() && is_user_logged_in() ) {
			remove_action( 'wp_footer', 'wp_admin_bar_render', 1000 );
			if ( true === force_ssl_admin() ) {
				add_filter( 'site_url', array( $this, '_fix_url' ) );
				add_filter( 'admin_url', array( $this, '_fix_url' ) );
				add_filter( 'post_link', array( $this, '_fix_url' ) );
				add_filter( 'category_link', array( $this, '_fix_url' ) );
				add_filter( 'get_archives_link', array( $this, '_fix_url' ) );
				add_filter( 'tag_link', array( $this, '_fix_url' ) );
				add_filter( 'home_url', array( $this, '_fix_url' ) );
			}
		}
	}

	/**
	 * Replace http with https to avoid SSL warnings in the preview iframe if the admin is in SSL
	 * This will strip off any port numbers and will not replace URLs in off-site links
	 * @param string $url
	 * @return string
	 */
	public function _fix_url( $url ) {
		static $host = '';
		if ( empty( $host ) ) {
			$host = preg_replace( '/[:\d+$]/', '', $_SERVER['HTTP_HOST'] );
		}
		return str_ireplace( 'http://' . $host, 'https://' . $host, $url );
	}

	/**
	 * Add the 'Profiler' option under the 'Plugins' menu
	 * @return void
	 */
	public function settings_menu() {
		if ( function_exists( 'add_submenu_page' ) ) {
			$page = add_submenu_page(
				'tools.php',
				'P3 Plugin Profiler',
				'P3 Plugin Profiler',
				'manage_options',
				basename( __FILE__ ),
				array( $this, 'dispatcher' )
			);
			add_action( 'load-' . $page, array( $this, 'load_libraries' ) );
			add_action( 'admin_print_scripts-' . $page, array( $this, 'load_scripts' ) );
			add_action( 'admin_print_styles-' . $page, array( $this, 'load_styles' ) );
		}
	}

	/**
	 * Load the necessary resources
	 * @uses wp_enqueue_script
	 * @uses jquery, jquery-ui, jquery.corners
	 * @uses flot, flot.pie
	 * @return void
	 */
	public function load_libraries() {

		// Load php libraries libraries
		include_once P3_PATH . '/class.p3-profile-table-sorter.php';
		include_once P3_PATH . '/class.p3-profile-table.php';
		include_once P3_PATH . '/class.p3-profile-reader.php';
		
		// Load exceptions
		include_once P3_PATH . '/exceptions/class.p3-profiler-no-data-exception.php';
	}
	
	/**
	 * Load javascripts
	 * @uses wp_enqueue_script
	 * @uses jquery, jquery-ui, jquery.corners
	 * @uses flot, flot.pie
	 * @return void
	 */
	public function load_scripts() {
		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'jquery-ui-tabs' );
		wp_enqueue_script( 'jquery-ui-progressbar' );
		wp_enqueue_script( 'flot', plugins_url() . '/p3-profiler/js/jquery.flot.min.js', array( 'jquery-ui-core' ) );
		wp_enqueue_script( 'flot.pie', plugins_url() . '/p3-profiler/js/jquery.flot.pie.min.js', array( 'flot' ) );
		wp_enqueue_script( 'flot.navigate', plugins_url() . '/p3-profiler/js/jquery.flot.navigate.js', array( 'flot' ) );
		wp_enqueue_script( 'p3_corners', plugins_url() . '/p3-profiler/js/jquery.corner.js', array( 'jquery-ui-core' ) );
		wp_enqueue_script( 'p3_qtip', plugins_url() . '/p3-profiler/js/jquery.qtip.min.js', array( 'jquery-ui-core' ) );
	}

	/**
	 * Load styles
	 * @uses wp_enqueue_style
	 * @uses jquery-ui
	 * @return void
	 */
	public function load_styles() {
		wp_enqueue_style( 'p3_jquery_ui_css', plugins_url() . '/p3-profiler/css/custom-theme/jquery-ui-1.8.16.custom.css' );
		wp_enqueue_style( 'p3_qtip_css', plugins_url() . '/p3-profiler/css/jquery.qtip.min.css' );
		wp_enqueue_style( 'p3_css', plugins_url() . '/p3-profiler/css/p3.css' );
	}
	
	/**
	 * Load the necessary resources
	 * @uses wp_enqueue_script
	 * @uses jquery, jquery-ui, jquery.corners
	 * @uses flot, flot.pie
	 * @return void
	 */
	public function early_init() {

		// Only for our page
		if ( isset( $_REQUEST['page'] ) && basename( __FILE__ ) == $_REQUEST['page'] ) {
			// Load the list table, let it handle any bulk actions
			$this->scan_table = new P3_Profile_Table();
			$this->scan_table->prepare_items();

			// Usability message
			if ( !defined( 'WPP_PROFILING_STARTED' ) ) {
				$this->add_notice( 'Click "Start Scan" to run a performance scan of your website.' );
			}
		}
	}

	/**
	 * Dispatcher function.  All requests enter through here
	 * and are routed based upon the p3_action request variable
	 * @uses $_REQUEST['p3_action']
	 * @return void
	 */
	public function dispatcher() {
		$p3_action = '';
		if ( ! empty ( $_REQUEST ['p3_action'] ) ) {
			$p3_action = $_REQUEST ['p3_action'];
		}
		switch ( $p3_action ) {
			case 'list-scans' :
				$this->list_scans();
				break;
			case 'view-scan' :
				$this->view_scan();
				break;
			case 'start-scan' :
				$this->start_scan();
				break;
			case 'fix-flag-file' :
				$this->fix_flag_file();
				break;
			case 'help' :
				$this->show_help();
				break;
			default :
				$this->scan_settings_page();
		}
	}

	/**
	 * Write .profiling_enabled file, uses request_filesystem_credentials, if
	 * necessary, to create the file and make it writable
	 * @return void
	 */
	public function fix_flag_file() {

		// Don't force a specific file system method
		$method = '';
		
		// Define any extra pass-thru fields (none)
		$form_fields = array();
		
		// Define the URL to post back to (this one)
		$url = wp_nonce_url( add_query_arg( array( 'p3_action' => 'fix-flag-file' ) ), 'p3-fix-flag-file' );

		// Ask for credentials, if necessary
		if ( false === ( $creds = request_filesystem_credentials( $url, $method, false, false, $form_fields ) ) ) {
			return true; 
		} elseif ( ! WP_Filesystem($creds) ) {
			// The credentials are bad, ask again
			request_filesystem_credentials( $url, $method, true, false, $form_fields );
			return true;
		} else {
			// Once we get here, we should have credentials, do the file system operations
			global $wp_filesystem;
			if ( $wp_filesystem->put_contents( $wp_filesystem->wp_plugins_dir() . '/p3-profiler/.profiling_enabled' , '[]', FS_CHMOD_FILE | 0222) ) {
				include_once P3_PATH . '/templates/template.php';
			} else {
				wp_die( 'Error saving file!' );
			}
		}
	}

	/**
	 * Get a list of pages for the auto-scanner
	 * @return array
	 */
	public function list_of_pages() {

		// Start off the scan with the home page
		$pages = array( get_home_url() ); // Home page

		// Get the default RSS feed
		$pages[] = get_feed_link();

		// Search for 'e'
		$pages[] = home_url( '?s=e' );

		// Get the latest 10 posts
		$tmp = preg_split( '/\s+/', wp_get_archives( 'type=postbypost&limit=10&echo=0' ) );
		if ( !empty( $tmp ) ) {
			foreach ( $tmp as $page ) {
				if ( preg_match( "/href='([^']+)'/", $page, $matches ) ) {
					$pages[] = $matches[1];
				}
			}
		}

		// Fix SSL
		if ( true === force_ssl_admin() ) {
			foreach ( $pages as $k => $v ) {
				$pages[$k] = str_replace( 'http://', 'https://', $v );
			}
		}

		// Done
		return $pages;
	}

	/**************************************************************/
	/** AJAX FUNCTIONS                                           **/
	/**************************************************************/

	/**
	 * Start scan
	 * @return void
	 */
	public function ajax_start_scan() {

		// Check nonce
		if ( !wp_verify_nonce( $_POST ['p3_nonce'], 'p3_ajax_start_scan' ) ) {
			wp_die( 'Invalid nonce' );
		}

		// Sanitize the file name
		$filename = sanitize_file_name( basename( $_POST['p3_scan_name'] ) );

		// Create flag file
		if ( file_exists( P3_FLAG_FILE ) ) {
			$json = json_decode( file_get_contents( P3_FLAG_FILE ) );
		} else {
			$json = array();
		}
		
		// Site url
		$site_url = parse_url( get_home_url(), PHP_URL_PATH );
		if ( null === $site_url ) {
			$site_url = '/';
		}

		// Add the entry ( multisite installs can run more than one concurrent profile )
		$json[] = array(
			'ip'                   => stripslashes( $_POST['p3_ip'] ),
			'disable_opcode_cache' => ( 'true' == $_POST['p3_disable_opcode_cache'] ),
			'site_url'             => $site_url,
			'name'                 => $filename,
		);

		$flag1 = file_put_contents( P3_FLAG_FILE, json_encode( $json ) );
		
		// Kick start the profile file
		if ( !file_exists( P3_PROFILES_PATH . "/$filename.json" ) ) {
			$flag2 = file_put_contents( P3_PROFILES_PATH . "/$filename.json", '' );
		} else {
			$flag2 = true;
		}

		// Check if either operation failed
		if ( false === $flag1 & $flag2 ) {
			wp_die( 0 );
		} else {
			echo 1;
			die();
		}
	}

	/**
	 * Stop scan
	 * @return void
	 */
	public function ajax_stop_scan() {

		// Check nonce
		if ( !wp_verify_nonce( $_POST ['p3_nonce'], 'p3_ajax_stop_scan' ) ) {
			wp_die( 'Invalid nonce' );
		}

		// If there's no file, return an error
		if ( !file_exists( P3_FLAG_FILE ) ) {
			wp_die( 0 );
		}

		// Get the file
		$json = json_decode( file_get_contents( P3_FLAG_FILE ) );
		
		// Stop all sites who match the current site's URL
		$site_url = parse_url( get_home_url(), PHP_URL_PATH );
		if ( null === $site_url ) {
			$site_url = '/';
		}
		foreach ( (array) $json as $k => $v ) {
			if ( $site_url == $v->site_url ) {
				unset( $json[$k] );
			}
		}

		// Rewrite the file
		$flag = file_put_contents( P3_FLAG_FILE, json_encode( $json ) );
		if ( !$flag ) {
			wp_die( 0 );
		}

		// Tell the user what happened
		$this->add_notice( 'Turned off performance scanning.' );

		// Return the last filename
		if ( !empty( $v ) && is_object( $v ) && property_exists( $v, 'name' ) ) {
			echo $v->name . '.json';
			die();
		} else {
			wp_die( 0 );
		}
	}

	/**
	 * Save advanced settings
	 * @return void
	 */
	public function ajax_save_settings() {
		
		// Check nonce
		if ( !wp_verify_nonce( $_POST ['p3_nonce'], 'p3_save_settings' ) ) {
			wp_die( 'Invalid nonce' );
		}

		// Save the new options
		update_option( 'p3-profiler_disable_opcode_cache', 'true' == $_POST['p3_disable_opcode_cache'] );
		update_option( 'p3-profiler_use_current_ip', 'true' == $_POST['p3_use_current_ip'] );
		update_option( 'p3-profiler_ip_address', $_POST['p3_ip_address'] );
	
		die( '1' );
	}
	

	/**************************************************************/
	/** EMAIL RESULTS                                            **/
	/**************************************************************/

	/**
	 * Send results ( presumably to admin or support )
	 * @return void
	 */
	public function ajax_send_results() {

		// Check nonce
		if ( !wp_verify_nonce( $_POST ['p3_nonce'], 'p3_ajax_send_results' ) ) {
			wp_die( 'Invalid nonce' );
		}

		// Check fields
		$to      = sanitize_email( $_POST['p3_to'] );
		$from    = sanitize_email( $_POST['p3_from'] );
		$subject = filter_var(
			$_POST['p3_subject'], FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_LOW
		);
		$message = strip_tags( $_POST['p3_message'] );
		$results = strip_tags( $_POST['p3_results'] );
		
		// Append the results to the message ( if a messge was specified )
		if ( empty( $message ) ) {
			$message = stripslashes( $results );
		} else {
			$message = stripslashes( $message . "\n\n" .$results );
		}

		// Check for errors and send message
		if ( !is_email( $to ) || !is_email( $from ) ) {
			echo '0|Invalid e-mail';
		} elseif ( empty( $subject ) ) {
			echo '0|Invalid subject';
		} elseif ( false === wp_mail( $to, $subject, $message, "From: $from" ) ) {
			echo '0|<a href="http://codex.wordpress.org/Function_Reference/wp_mail" target="_blank">wp_mail()</a> function returned false';
		} else {
			echo '1';
		}
		die();
	}


	/**************************************************************/
	/** CURRENT PAGE                                             **/
	/**************************************************************/

	/**
	 * Show the settings page.
	 * This is where the user can start/stop the scan
	 */
	public function scan_settings_page() {
		include_once P3_PATH . '/templates/template.php';
	}


	/**************************************************************/
	/** HELP PAGE                                                **/
	/**************************************************************/

	/**
	 * Show the help page.
	 */
	public function show_help() {
		include_once P3_PATH . '/templates/template.php';
	}


	/**************************************************************/
	/**  HISTORY PAGE                                            **/
	/**************************************************************/

	/**
	 * View the results of a scan
	 * @uses $_REQUEST['name']
	 * @return void
	 */
	public function view_scan() {
		include_once P3_PATH . '/templates/template.php';
	}

	/**
	 * Show a list of available scans.
	 * Uses WP List table to handle UI and sorting.
	 * Uses P3_Profile_Table to handle deleting
	 * @uses WP_List_Table
	 * @uses jquery
	 * @uses P3_Profile_Table
	 * @return void
	 */
	public function list_scans() {
		include_once P3_PATH . '/templates/template.php';	
	}

	/**
	 * Get the latest performance scan
	 * @return string|false
	 */
	public function get_latest_profile() {

		// Open the directory
		$dir = opendir( P3_PROFILES_PATH );
		if ( false === $dir ) {
			wp_die( 'Cannot read profiles directory' );
		}

		// Loop through the files, get the path and the last modified time
		$files = array();
		while ( false !== ( $file = readdir( $dir ) ) ) {
			if ( '.json' == substr( $file, -5 ) && filesize( P3_PROFILES_PATH . '/' . $file ) > 0 ) {
				$files[filemtime( P3_PROFILES_PATH . "/$file" )] = P3_PROFILES_PATH . "/$file";
			}
		}
		closedir( $dir );

		// If there are no files, return false
		if ( empty( $files ) ) {
			return false;
		}

		// Sort the files by the last modified time, return the latest
		ksort( $files );
		return array_pop( $files );
	}

	/**
	 * Add a notices
	 * @uses transients
	 * @param string $notice
	 * @param bool $error Default false.  If true, this is a red error.  If false, this is a yellow notice.
	 * @return void
	 */
	public function add_notice( $notice, $error = false ) {

		// Get any notices on the stack
		$notices = get_transient( 'p3_notices' );
		if ( empty( $notices ) ) {
			$notices = array();
		}

		// Add the notice to the stack
		$notices[] = array(
			'msg'   => $notice,
			'error' => $error,
		);

		// Save the stack
		set_transient( 'p3_notices', $notices );
	}

	/**
	 * Display notices
	 * @uses transients
	 * @return voide
	 */
	public function show_notices() {

		// Skip notices if we're fixing the flag file
		if ( isset( $_REQUEST['p3_action'] ) && 'fix-flag-file' == $_REQUEST['p3_action'] ) {
			return true;
		}
		
		$notices = get_transient( 'p3_notices' );
		if ( !empty( $notices ) ) {
			$notices = array_unique( $notices );
			foreach ( $notices as $notice ) {
				echo '<div class="' . ( ( $notice['error'] ) ? 'error' : 'updated' ) . '"><p>' . htmlentities( $notice['msg'] ) . '</p></div>';
			}
		}
		set_transient( 'p3_notices', array() );
		if ( false !== $this->scan_enabled() ) {
			echo '<div class="updated"><p>Performance scanning is enabled.</p></div>';
		}
		
		// Check that we can write .profiling_enabled
		if ( isset( $_REQUEST['page'] ) && basename( __FILE__ ) == $_REQUEST['page'] && ( !isset( $_REQUEST['p3_action'] ) || 'fix-flag-file' != $_REQUEST['p3_action'] ) ) {
			if ( !file_exists( P3_FLAG_FILE ) || !is_writable( P3_FLAG_FILE ) ) {
				@touch( P3_FLAG_FILE );
				if ( !file_exists( P3_FLAG_FILE ) || !is_writable( P3_FLAG_FILE ) ) {
					echo '<div class="error"><p>Cannot set profile flag file <input type="button" onclick="location.href=\'' . add_query_arg( array( 'p3_action' => 'fix-flag-file' ) ) . '\';" class="button" value="click here to fix" /></p></div>';
				}
			}
		}
	}

	/**
	 * Activation hook
	 * Install the profiler loader in the most optimal place
	 * @return void
	 */
	public function activate() {
		global $wp_version;
		
		// Version check, only 3.3+
		if ( ! version_compare( $wp_version, '3.3', '>=') ) {
			if ( function_exists('deactivate_plugins') )
				deactivate_plugins(__FILE__);
			die( '<strong>P3</strong> requires WordPress 3.3 or later' );
		}


		$sapi = strtolower( php_sapi_name() );

		// .htaccess for mod_php
		if ( 'apache2handler' == $sapi ) {
			insert_with_markers(
				ABSPATH . '/.htaccess',
				'p3-profiler',
				array( 'php_value auto_prepend_file "' . P3_PATH . DIRECTORY_SEPARATOR . 'start-profile.php"' )
			);
		}

		// Always try to create the mu-plugin loader in case either of the above methods fail

		// mu-plugins doesn't exist	
		if ( !file_exists( WPMU_PLUGIN_DIR ) && is_writable( WPMU_PLUGIN_DIR . '/../' ) ) {
			wp_mkdir_p( WPMU_PLUGIN_DIR );
		}
		if ( file_exists( WPMU_PLUGIN_DIR ) && is_writable( WPMU_PLUGIN_DIR ) ) {
			file_put_contents(
				WPMU_PLUGIN_DIR . '/p3-profiler.php',
				'<' . "?php // Start profiling\nrequire_once( realpath( dirname( __FILE__ ) ) . '/../plugins/p3-profiler/start-profile.php' ); ?" . '>'
			);
		}
	}

	/**
	 * Make the profiles folder
	 * @param string $path
	 * @return void
	 */
	private function _make_profiles_folder( $path ) {
		wp_mkdir_p( $path );
		if ( !file_exists( "$path/.htaccess" ) ) {
			file_put_contents( $path . DIRECTORY_SEPARATOR . '.htaccess', "Deny from all\n" );
		}
		if ( !file_exists( "$path/index.php" ) ) {
			file_put_contents( $path. DIRECTORY_SEPARATOR . 'index.php', '<' . "?php header( 'Status: 404 Not found' ); ?" . ">\nNot found" );
		}
	}
	
	/**
	 * Delete the profiles folder
	 * @param string $path
	 * @return void
	 */
	private function _delete_profiles_folder( $path ) {
		if ( !file_exists( $path ) )
			return;
		$dir = opendir( $path );
		while ( ( $file = readdir( $dir ) ) !== false ) {
			if ( $file != '.' && $file != '..' ) {
				unlink( $path . DIRECTORY_SEPARATOR . $file );
			}
		}
		closedir( $dir );
		rmdir( $path );
	}	

	/**
	 * Deactivation hook
	 * Remove the profiler loader
	 * @return void
	 */
	public function deactivate() {

		// Remove any .htaccess modifications
		$file = ABSPATH . '/.htaccess';
		if ( file_exists( $file ) && array() !== extract_from_markers( $file, 'p3-profiler' ) ) {
			insert_with_markers( $file, 'p3-profiler', array( '# removed during uninstall' ) );
		}

		// Remove mu-plugin
		if ( file_exists( WPMU_PLUGIN_DIR . '/p3-profiler.php' ) ) {
			if ( is_writable( WPMU_PLUGIN_DIR . '/p3-profiler.php' ) ) {
				// Some servers give write permission, but not delete permission.  Empty the file out, first, then try to delete it.
				file_put_contents( WPMU_PLUGIN_DIR . '/p3-profiler.php', '' );
				unlink( WPMU_PLUGIN_DIR . '/p3-profiler.php' );
			}
		}
	}
	
	/**
	 * Uninstall hook
	 * Remove profile data
	 * @return void
	 */
	public static function uninstall() {
		// This is a static function so it needs an instance
		// Since I'm myself, I can call my own private methods
		$class = __CLASS__;
		$me    = new $class();
		
		// Delete the profiles folder
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			$blogs = get_blog_list( 0, 'all' );
			foreach ( $blogs as $blog ) {
				switch_to_blog( $blog['blog_id'] );
				$uploads_dir = wp_upload_dir();
				$folder      = $uploads_dir['basedir'] . DIRECTORY_SEPARATOR . 'profiles' . DIRECTORY_SEPARATOR;
				$me->_delete_profiles_folder( $folder );

				// Remove any options
				delete_option( 'p3-profiler_disable_opcode_cache' );
				delete_option( 'p3-profiler_use_current_ip' );
				delete_option( 'p3-profiler_ip_address' );
				delete_option( 'p3-profiler_version' );
			}
			restore_current_blog();
		} else {
			$me->_delete_profiles_folder( P3_PROFILES_PATH );
			
			// Remove any options
			delete_option( 'p3-profiler_disable_opcode_cache' );
			delete_option( 'p3-profiler_use_current_ip' );
			delete_option( 'p3-profiler_ip_address' );
			delete_option( 'p3-profiler_version' );
		}
	}

	/**
	 * Check to see if a scan is enabled
	 * @return array|false
	 */
	public function scan_enabled() {
		if ( !file_exists( P3_FLAG_FILE ) ) {
			return false;
		}
		$site_url = parse_url( get_home_url(), PHP_URL_PATH );
		if ( null === $site_url ) {
			$site_url = '/';
		}
		$json = json_decode( file_get_contents( P3_FLAG_FILE ), true );
		foreach ( (array) $json as $v ) {
			if ( $site_url == $v['site_url'] ) {
				return $v;
			}
		}
		return false;
	}
	
	/**
	 * Convert a filesize ( in bytes ) to a human readable filesize
	 * @param int $size
	 * @return string
	 */
	public function readable_size( $size ) {
		$units = array( 'B', 'KB', 'MB', 'GB', 'TB' );
		$size  = max( $size, 0 );
		$pow   = floor( ( $size ? log( $size ) : 0 ) / log( 1024 ) );
		$pow   = min( $pow, count( $units ) - 1 );
		$size /= pow( 1024, $pow );
		return round( $size, 0 ) . ' ' . $units[$pow];
	}
	
	/**
	 * Actions to take when a multisite blog is added
	 * @return void
	 */
	public function add_blog() {
		// Reserved for future use
	}
	
	/**
	 * Actions to take when a multisite blog is removed
	 * @return void
	 */
	public function delete_blog() {
		$uploads_dir = wp_upload_dir();
		$folder      = $uploads_dir['basedir'] . DIRECTORY_SEPARATOR . 'profiles' . DIRECTORY_SEPARATOR;
		$this->_delete_profiles_folder( $folder );
		delete_option( 'p3-profiler_disable_opcode_cache' );
		delete_option( 'p3-profiler_use_current_ip' );
		delete_option( 'p3-profiler_ip_address' );
		delete_option( 'p3-profiler_version' );
	}

	/**
	 * Upgrade
	 * Check options, perform any necessary data conversions
	 * @return void
	 */
	public function upgrade() {

		// Get the current version
		$version = get_option( 'p3-profiler_version' );
		
		// Upgrading from < 1.1.0
		if ( empty( $version ) || version_compare( $version, '1.1.0') < 0 ) {
			update_option( 'p3-profiler_disable_opcode_cache', true );
			update_option( 'p3-profiler_use_current_ip', true );
			update_option( 'p3-profiler_ip_address', '' );
			update_option( 'p3-profiler_version', '1.1.0' );
		}

		// Ensure the profiles folder is there
		$uploads_dir = wp_upload_dir();
		$folder      = $uploads_dir['basedir'] . DIRECTORY_SEPARATOR . 'profiles';
		$this->_make_profiles_folder( $folder );
	}
}
