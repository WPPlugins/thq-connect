<?PHP
/**
* THQ Connect
* 
* @package		THQ Connect
* @author		Mark Belstead
* @copyright		2017 Mark Belstead
* @licence		GPL-2.0+
* @link			http://thqconnect.ga
*
* @wordpress-plugin
* Plugin Name:		THQ Connect
* Plugin URI:		http://thqconnect.ga
* Version: 		2.2.1
* GitHub Plugin URI: 	https://github.com/mbelstead/thq-connect
* GitHub Branch:    	trunk
* Requires WP:       	4.4
* Description:		TidyHQ is a cloud-based platform designed to help streamline the administration and management of organisations.  For more information about how TidyHQ can help your organisation visit [tidyhq.com](http://tidyhq.com).  For the plugin to work correctly, the WordPress user must have the SAME EMAIL ADDRESS as a corresponding TidyHQ user.  If they are not in WordPress, the plugin will create them as a user with basic WordPress access.  An admin will need to modify their access to allow more functionality. Data used by THQ Connect is read-only.  THQ Connect promises to never store confidential information from TidyHQ.
* Author:		Mark Belstead
* License:		GPL2
*  
* THQ Connect is free software: you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, either version 2 of the License, or any later version.
* 
* THQ Connect is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
*  
* You should have received a copy of the GNU General Public License along with THQ Connect. If not, see http://www.gnu.org/licenses/.
*/


/**
* MULTISITE
*/
if ( is_multisite() ) {
	add_action( 'admin_notices', function() {
		$class = 'notice notice-error';
		$message = __( 'Sorry, THQ Connect is not compatible with Multisite.', 'thq-connect-error' );
		printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
	} );
	die();
} //end if
else {
	add_action('init', 'thq_connect_init'); //all other initialisations
	require_once(dirname(__FILE__).'/includes/widgets.php'); //Widgets
} //end else
	
/** 
* INITIALISE PLUGIN
*/
function thq_connect_init(){ 
	/* NOT LOGGED IN */
	if( !is_user_logged_in() ){
			/* anything specific to do here? */
	} //end if
	/* LOGGED IN */
	else {
		require_once(dirname(__FILE__).'/includes/admin.php'); //Admin Settings Page
		require_once(dirname(__FILE__).'/includes/help.php');  //Help Page
		require_once(dirname(__FILE__).'/includes/adminbar.php'); //Admin bar
		require_once(dirname(__FILE__).'/includes/contacts.php'); //Contacts
		
		$thqSettings = new thqConnectSettings();
		$thq = $thqSettings->display();
		
		/* Check if THQ Connect has updated email from TidyHQ */
		if(!empty($_SESSION['thq-connect-email-updated']) && $_SESSION['thq-connect-email-updated'] == true) {
			add_action( 'admin_notices', function() {
				$class = 'notice notice-info';
				$message = __( 'Sorry, THQ Connect has updated WordPress with your new TidyHQ email address.', 'thq-connect-error' );
				printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
			} );
			unset($_SESSION['thq-connect-email-updated']);
		}//end if
		
		/* Check for required settings */
		if(!empty($thq['client_secret']) && !empty($thq['client_id']) && !empty($thq['domain_prefix'])) {
			add_action('admin_menu','thq_connect_admin_menu'); //Required for menu links
			add_action('admin_enqueue_scripts', 'thq_connect_styles');
			} //end if
		else {
			add_action('admin_notices', function() {
				$message = 'THQ Connect parameters will need to be set before plug in will work. ';
				$message .= is_admin() ? '<a href="'.admin_url('admin.php?page=thq-connect-settings').'">Click Here to update settings now.</a>' : 'Speak to your administrator.';
				thq_connect_error_notice('error',$message);
				write_log('client_secret, client_id or domain_prefix is not set.');
				} //end function
			);  //Display error if plugin not setup
		} //end else
		
		/* Do Profile Sync */
		if(!empty($thq['profile_sync']) && $thq['profile_sync'] == 'true'){

			require_once(dirname(__FILE__).'/includes/profiles.php');
		} //Profile Sync file
	}// end logged in
	wp_enqueue_style('thq-connect-general-styles', plugin_dir_url(__FILE__).'css/style.css','','2.2.1');
	require_once(dirname(__FILE__).'/includes/login.php'); //Login stuff
	require_once(dirname(__FILE__).'/includes/calendar.php'); //Include calendar file
	require_once(dirname(__FILE__).'/includes/organisations.php');  //Organisation functions
} //end function


/**
* STYLESHEETS
* Include custom JS & CSS. Remember to update version numbers!
*/
function thq_connect_styles() {
		//wp_enqueue_style('thq-connect-styles', plugin_dir_url(__FILE__).'css/style.css','','2.1.4');
		wp_enqueue_style( 'wp-color-picker');
		wp_enqueue_script('thq-connect-admin-script',plugin_dir_url(__FILE__).'js/admin.js',array('jquery', 'wp-color-picker' ), '2.2.6', true );
		/* Colour Picker - mainly for Calendar */
		wp_enqueue_script( 'wp-color-picker'); 
} //end function
/**
* MENU
* Menu link to TidyHQ and THQWP folder
*/
function thq_connect_admin_menu() {
	/* User has correct capability */
	if(current_user_can('thq_connect_user')) {
		$thqSettings = new thqConnectSettings();
		$thq = $thqSettings->display();
		
		add_menu_page(
			'TidyHQ',
			'TidyHQ',
			'thq_connect_user',
			'thq-connect-page',
			'https://'.$thq['domain_prefix'].'.tidyhq.com/dashboard',
			plugin_dir_url(__FILE__).'img/tidyhq-icon.png',
			99
		);
		global $submenu;
		$url = 'https://'.$thq['domain_prefix'].'.tidyhq.com/dashboard';
		$submenu['thq-connect-page'][] = array('Dashboard', 'thq_connect_user', $url);
	} //end if
	else {
	//nothing to see as user doesn't have capability
	} //end else
} //end function
/** 
* ERROR MESSAGING
* General error messaging on Admin pages.
*/
function thq_connect_error_notice($type,$message) {
	$class = 'notice is-dismissible ';
	$class .= $type;
	$message = __( $message, 'thq-connect-error-notice' );
	printf( '<div class="%1$s"><p>%2$s</p></div>', $class, $message ); 
} //end notice
/**
* INSTALL/UNINSTALL FUNCTION
*/
register_activation_hook( __FILE__, 'thq_connect_activate' );
register_deactivation_hook( __FILE__, 'thq_connect_deactivate' );
/* On install add the capabilities and redirect to settings page */
function thq_connect_activate() {
    $role = get_role( 'administrator' );
    $role->add_cap( 'thq_connect_user' ); 
	$url = admin_url('admin.php?page=thq-connect-settings');
	wp_redirect($url);
} //end function
/* On deactivate run the uninstall file */
function thq_connect_deactivate(){
	if(file_exists(dirname(__FILE__).'/uninstall.php')) { require_once(dirname(__FILE__).'/uninstall.php'); }
} //end function
/**
* PLUGIN ACTION LINKS
* Display under the plugin on the WordPress plugins page.
*/
add_filter( 'plugin_row_meta', 'thq_connect_plugin_row_meta', 10, 2 );
/* Support link */
function thq_connect_plugin_row_meta( $links, $file ) {
	if ( strpos( $file, 'thq-connect.php' ) !== false ) {
		$new_links = array(
				'settings' => '<a href="'.admin_url('admin.php?page=thq-connect-settings').'">Settings</a>',
				'support' => '<a href="mailto:support@thqconnect.ga" target="_blank">Email Support</a>'
				);
		$links = array_merge( $links, $new_links );
	} //end if
	return $links;
} //end function
/**
* DEBUG LOG
*/
if ( ! function_exists('write_log')) {
   function write_log ( $log )  {
      if ( is_array( $log ) || is_object( $log ) ) {
         error_log( print_r( '[THQ CONNECT ALERT] '.$log, true ) );
      } //end if
	  else {
         error_log( '[THQ CONNECT ALERT] '.$log );
      } //end else
   } //end function
} //end if
/**
* THQ CONNECT GET REQUEST
* $var = newThqConnectGet;
* $newvar = $var->request($type,$and = array());
*/
class thqConnectGet {
private $thqSettings;
public function __construct(){
	$settings = new thqConnectSettings();
	$this->thqSettings = $settings->display();
} // end __construct
public function request($type,$and = array()) {
	/* Set the Token */
	$wpUser = wp_get_current_user();
	/* There is a user so grab the one in DB */
	if(!empty($wpUser) && $wpUser->ID != 0) { $thq_token = get_user_meta($wpUser->ID,'thq_connect_token',true); }
	/* Check if a token has been specified, if not, use the one in DB */
	$newToken = (!empty($and['access_token'])) ? $and['access_token'] : '';
	if(!empty($newToken)) { $thq_token = $newToken; }
	/* Check if token exists */
	if(!empty($thq_token)) {
  	$requests = ''; //set variable for request parameters
		/* Each parameter comes from array and is broken down for GET requests */
		foreach($and as $key=>$value) {
			$requests .= ($key == 'access_token')  ? '' : $key."=".$value."&"; //always adds the & on the end.  Last & is reserved for Token at the end
		} //end foreach
		$url = $this->thqSettings['request_url'].$type."?"; //start creating GET url
		if(!empty($requests)) { $url .= $requests; } //if parameters are set, add them in
		$url .= "access_token=".$thq_token;  //throw in the token 
		$response = wp_remote_post( $url, array( 'method' => 'GET','headers' =>  array("Content-type: application/json; charset=utf-8")));	//Send request
		$userdata = json_decode($response['body'], true); //make response readable as array
		/* If the response isn't as expected */
		if(!empty($userdata['message'])) {
			$newresponse = $response['response'];
			//thq_connect_error_notice('notice-error','Error processing your request.  Speak to your administrator.');
			write_log($newresponse['code']." ".$newresponse['message'].': '.$userdata['message']." [thqConnectGet(".$type.")]");
			return $newresponse['code'];
		} //end if
		else {
			return $userdata; //return correct response
		} //end else
	} //end if
	/* no token */
	else {
		$message = 'Attempted GET request with no token: ';
		$message .= 'TYPE=<'.$type.'> AND=<'.print_r($and,true).'>';
		write_log($message);
	} //end else
}//end request
} //end thqConnectGet
/**
* THQ CONNECT SETTINGS
* $var = new thqConnectSettings;
* $newvar = $var->display();
*/
class thqConnectSettings {
	private $settings;
	public function __construct(){
		/* Plugin Options in WP database */
		$pluginOptions = get_option('thq_connect_settings') ? get_option('thq_connect_settings') : array();
		/* Calendar Options in WP database */
		$calendarOptions = get_option('thq_connect_calendar_settings') ? get_option('thq_connect_calendar_settings') : array();
		
		/* Find Current User */
		$user = wp_get_current_user();
		/* Get user's domain prefix */
		$domainPrefix = get_user_meta($user->ID,'thq_connect_domain_prefix',true);
		/* If no domainPrefix set, use default */
		if(!empty($domainPrefix)) { $pluginOptions['domain_prefix'] = $domainPrefix; }
			$thqOptions = array(
				'redirect_uri' => get_home_url(), //site URL
				'request_url' => 'https://api.tidyhq.com/v1/', //For general API requests
				'auth_url' => 'https://accounts.tidyhq.com/oauth/authorize', //For initial log in
				'token_url' => 'https://accounts.tidyhq.com/oauth/token', //To validate code and get token
			);
		/* Merge other settings in to return array */
		$this->settings = array_merge($thqOptions,$pluginOptions,$calendarOptions); //Merge all settings above in to one array	
	} //end __construct
	public function display($attr = '') {
		if(!empty($attr)) { return $this->settings[$attr]; }
		else {	return $this->settings; }
	} //end function
} //end class

?>
