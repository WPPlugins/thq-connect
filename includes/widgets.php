<?PHP
/**
* THQ-CONNECT WIDGETS.PHP
* Admin Dashboard widget and sidebar widgets
*/

/* Can't use settings class as it isnt loaded yet */
$thqSettings = get_option('thq_connect_settings');

/* Load public page widgets if required fields set */
if(!empty($thqSettings['domain_prefix']) && !empty($thqSettings['client_id']) && !empty($thqSettings['client_secret'])) {
	add_action( 'widgets_init', 'thq_connect_load_widgets' );
} //end if
function thq_connect_load_widgets() {
	register_widget( 'thq_connect_connect_widget' ); //connect/connected button
	if(file_exists(dirname(__FILE__).'/login.php')) {
		require_once(dirname(__FILE__).'/login.php'); //Login stuff
		register_widget( 'thq_connect_login_widget'); //login widget
	} //end if
} //end function

/* Load dashboard widgets */
add_action('wp_dashboard_setup','thq_connect_load_dashboard');
function thq_connect_load_dashboard() {
	$widget = new thqConnectDashboardWidget();
	$widget->display();
	/* add more here */
} //end function


/* Connect/Connected Button */
class thq_connect_connect_widget extends WP_Widget {
	private $thqSettings;
	
	public function __construct() {
		$settings = new thqConnectSettings();
		$this->thqSettings = $settings->display();
		
		parent::__construct(
			'thq_connect_connect_widget',
			__( 'THQ Connect - TidyHQ Connect Button', 'thq_connect_domain' ),
			array( 'description' => __( 'Display a button for visitors to be able to connect with your organisation.', 'thq_connect_domain' ), )
		);
	} //end __construct

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		$output = !empty( $title ) ? $args['before_title'] . $title . $args['after_title'] : '';
		$output .= "<a id='thq-connect-connect-button' ";
		/* Check if contact exists - will only work if logged in */
		if(!is_user_logged_in()){
			$me = '';
		} //end if
		else {
			$request = new thqConnectGet();
			$me = $request->request('/contacts/me');
		} //end else
		/* already connected */
		if(!empty($me)) {
			$output .= "class='thq-connect-connected' title='You are connected to ".get_bloginfo('name')."' href='https://".$this->thqSettings['domain_prefix'].".tidyhq.com'><img style='width: 14px;' src='//cdn.tidyhq.com/assets/tc/tc-header-logo-c14359d5bf3acf3eedda3145789b32b193edb6be87e0b94d32e2dfb9a1aa483d.png' alt='Tc header logo'> Connected";
		}
		else {
			$output .= "class='thq-connect-connect' title='Connect to ".get_bloginfo('name')."' href='https://".$this->thqSettings['domain_prefix'].".tidyhq.com/public/connect/new'><img style='width: 14px' src='//cdn.tidyhq.com/assets/tc/thq-white-icon-8ae5f714ead5cf59ed54a705940c5dbfe991dca574659295eea3a8cc2121f813.png' alt='Thq white icon'> Connect";
		}
		$output .= "</a>";
		echo $args['before_widget'];
		echo __( $output, 'thq_connect_domain' );
		echo $args['after_widget'];
	} //end widget

	public function form( $instance ) {
		$title = isset($instance['title']) ? $instance['title'] : __( '', 'thq_connect_domain' );
	?><p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title (optional):' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" placeholder="Optional Title" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
	<p class='description'>A button will display to allow visitors to connect to your organisation via TidyHQ</p>
	<?PHP
	} //end form

	
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		
		return $instance;
		} //end update
} //end class

/* Dashboard Widget */
class thqConnectDashboardWidget {
	private $thqSettings;
	
	/* Construct */
	public function __construct() {
		$settings = new thqConnectSettings();
		$this->thqSettings = $settings->display();
	} // end __construct
	
	/* Display */
	public function display() {
		wp_add_dashboard_widget(
                 'thq_connect_dashboard_widget',         // Widget slug.
                 'THQ Connect',         // Title.
                 array($this,'create') // Display function.
        );	
	} //end display
	
	/* Create */
	public function create() {
		$output = "<p style='text-align: center'>";
		$domain = !empty($this->thqSettings['domain_prefix']) ? $this->thqSettings['domain_prefix'].".tidyhq.com" : '';
		$plugin = get_plugin_data(dirname(__FILE__)."/../thq-connect.php");
		$output .= "<a href='http://www.tidyhq.com' title='Powered by TidyHQ'><img src='//cdn.tidyhq.com/assets/tc/powered_by-480ed8552269da66abc71511a0b9a7f1944b459d431e52cf5e891d927f2f0075.png' alt='Powered by TidyHQ'></a><br>";
		$output .= get_bloginfo('name')." uses TidyHQ for organisation administration.";
		if(!empty($domain)) { $output .= "<br><a href='https://".$domain."'>Visit ".get_bloginfo('name')." on TidyHQ</a>"; }
		$output .= "<br>Thank you for using THQ Connect version ".$plugin['Version']."</p>";
		
		echo $output; //should this be echo or return?
	} //end create

} //end theConnectDashboardWidget

?>
