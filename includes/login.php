<?PHP
/**
* THQ-CONNECT LOGIN.PHP
* v2.1.1
* Allows single sign on
*/

$thqConnectLogin = new thqConnectLogin();

/* Not Logged in */
if(!is_user_logged_in()) {
  if(!empty($_GET['thq'])) { $thqConnectLogin->requestCode(); }
  elseif(!empty($_POST['action']) && $_POST['action'] == 'thqConnectLogin') { $thqConnectLogin->validatePost($_POST); }
  elseif(!empty($_GET['code'])) { $thqConnectLogin->validateCode($_GET['code']); }
  else { 
    /* not logged in but didn't do any above */
  } //end else
} //end else
else {
  /* User is logged in */
} //end else

class thqConnectLogin {
	/* Store settings */
	private $thqSettings;
	/* Store error messages */
	private $thqConnectError;
	/* Store domain prefix */
	private $domainPrefix;
	/* Store Token */
	private $thqToken;
	/* Store User */
	private $user;
	
	public function __construct() {
		$settings = new thqConnectSettings;
		$this->thqSettings = $settings->display();
    
		if(!empty($this->thqSettings['domain_prefix']) && !empty($this->thqSettings['client_secret']) && !empty($this->thqSettings['client_id'])) {
		 	add_action('login_form',array($this,'customLoginForm')); //changes standard login form
	    add_shortcode('thq_connect_login_form',array($this,'loginFormShortcode')); //Login Form shortcode
		}//end if
	} //end __construct
	
 /* Shortcode form */
public function loginFormShortcode() {
	#TODO add shortcode parameter to enable/disable domain_prefix
	/* User is logged in, we don't need the form */
	if ( is_user_logged_in() ) {
		return "You are already logged in.<br><a class='btn cta-button' href='".get_dashboard_url()."'>Go to Dashboard</a> or <a class='btn cta-button' href='". wp_logout_url( home_url() )."'>Log Out</a>";
	} //end if
	/* User is not logged in */
	else {
		/* initialise output */
		$output = '';
		/* Check if error needs to be displayed */
    global $thqConnectError;
		if(is_wp_error( $thqConnectError ) && count( $thqConnectError->get_error_messages('regerror') ) < 1) { $output .= "<span style='color: red'>".$thqConnectError->get_error_data('regerror')."</span>"; }
		/* Start of Form */
		$output .= "<form name='thq_connect_login' method='POST' action=''>";
		/* Email address */
		$output .= sprintf(
			"<label for='user_login'>Email Address: </label><input type='email' name='log' %s><br>",
			!empty($_COOKIE['thq_connect_user']) ? " value='".$_COOKIE['thq_connect_user']."'" : ""
		);
		/* Password */
		$output .= "<label for='user_pass'>Password: </label><input type='password' name='pwd'><br>";
		/* Domain Prefix */
		if(!empty($this->thqSettings['allow_domain_change'])) {
			$output .= sprintf(
			"<label for='domain_prefix' %s>TidyHQ Organisation:</label> <input type='%s' id='domain_prefix' value='".$this->thqSettings['domain_prefix']."' placeholder='".$this->thqSettings['domain_prefix'].".tidyhq.com' name='domain_prefix'>",
			(!empty($this->thqSettings['allow_domain_change']) && $this->thqSettings['allow_domain_change'] == true) ? '' : ' style="display: none;"',
			(!empty($this->thqSettings['allow_domain_change']) && $this->thqSettings['allow_domain_change'] == true) ? 'text' : 'hidden'
		);
		} //end if
		/* Remember me */
		$output .= sprintf(
			"<input type='checkbox' name='rememberme' %s> <label for='remember me'>Remember me</label><br>",
			!empty($_COOKIE['thq_connect_user']) ? ' checked="checked"' : ""
		);
		/* Button */
		$output .= "<p><input type='submit' value='Log in'>";
		/* Action */
		$output .= "<input type='hidden' name='action' value='thqConnectLogin'>";
		/* End of form */
		$output .= "</form>";
		/* Link to direct to TidyHQ validation instead */
		$output .= "<p>Alternatively, <a href='?thq=login' title='TidyHQ Login'>click here</a> and you can be sent to TidyHQ to validate your credentials.";
		 return $output;
	} //end else
} //end function


/*  Change default login page */
public function customLoginForm(){
    if(!empty($_SESSION['thq_connect_error'])) {
        add_filter('login_errors',function(){ 
          return $_SESSION['thq_connect_error'];
      });
    }
	/* Radio buttons - Choose either TidyHQ or WordPress to login */
	$output = "<p><label for='action'>WordPress <input type='radio' name='action' value='wp_login_form' onClick=\"setRadioAction('wp');\"> <input type='radio' name='action' checked='checked' onClick=\"setRadioAction('thq');\" value='thqConnectLogin'> TidyHQ</label></p>";
	/* Domain Prefix */
	$output .= "<p id='domain_prefix_box'><label for='domain_prefix'";
	$output .= (!empty($this->thqSettings['allow_domain_change']) && $this->thqSettings['allow_domain_change'] == true) ? '' : ' style="display: none;"';
	$output .= ">TidyHQ Organisation: </label>";
	if(!empty($this->thqSettings['allow_domain_change'])) {
		$output .= "<input type='";
		$output .= (!empty($this->thqSettings['allow_domain_change']) && $this->thqSettings['allow_domain_change'] == true) ? 'text' : 'hidden';
		$output .= "' id='domain_prefix' value='".$this->thqSettings['domain_prefix']."' placeholder='".$this->thqSettings['domain_prefix'].".tidyhq.com' name='domain_prefix'></p>";
	} //end if
	/* Javascript for radio buttons to display domain_prefix or not.  IT will also change the form action. */
	$output .= "
		<script type='text/javascript'>
			function setRadioAction(control){
				if(control == 'wp'){
					document.loginform.action = 'wp-login.php';
					document.getElementById('domain_prefix_box').style.display = 'none';
				}
				else {
					document.loginform.action = 'index.php';
					document.getElementById('domain_prefix_box').style.display = 'block';
				}
			}
			document.onload = setRadioAction('thq'); 
		</script>";
	if(!empty($_COOKIE['thq_connect_user'])) {
		$output .= "
			<script type='text/javascript'>
				document.getElementById('user_login').value = '".$_COOKIE['thq_connect_user']."';
				document.getElementById('rememberme').checked = true;
			</script>";
	} //end if
	echo $output;  //or should this be return?
}//end function
  
  
	/* Validate information submitted via POST for login in TidyHQ (username/pass entry) */
	public function validatePost($post) {
		/* Set domain prefix, either specified or default */
		$this->domainPrefix = (!empty($post['domain_prefix'])) ? $post['domain_prefix'] : $this->thqSettings['domain_prefix'];
		/* Add cookie if box checked */
		if(!empty($post['rememberme']) && !empty($post['log'])) {
			$cookieDays = 30;
			setcookie('thq_connect_user', $post['log'], time() + (86400 * $cookieDays));
		} //end if
		/* Set cookie to 1s if box unchecked to remove saved cookie */
		if(!empty($_COOKIE['thq_connect_user']) && empty($post['rememberme'])) {
			setcookie('thq_connect_user', $post['log'], 1);
		} //end if
		/* incase people have added extra URL in domain location */
		$domainValidate = strpos($this->domainPrefix,".");
		if($domainValidate !== false) {
			$this->domainPrefix = strtok($this->domainPrefix, ".");
		}
		/* Missing a variable */
		if(!$post['log'] || !$post['pwd'] || !$this->domainPrefix) {
			/* Show Error and write to log */
			write_log('Unable to validate username: '.$post['log'].' with entered password or domain: '.$this->domainPrefix);
			$this->thqLoginError("Invalid username or password.");
		} //end if
		/* variables available */
	  else {
			/* Request Token */
			$content = array(
			"client_id"=>$this->thqSettings['client_id'],
			"client_secret"=>$this->thqSettings['client_secret'],
			"username"=>$post['log'],
			"password"=>$post['pwd'],
			"domain_prefix"=>$this->domainPrefix,
			"grant_type"=>"password"
			);  //end of content

			$request = array(
				'method' => 'POST',
				'headers' =>  array("Content-type: application/json"),
				'body'=>$content
			);

			$output = wp_remote_post($this->thqSettings['token_url'],$request);

			/* Error processing request */
			if ( is_wp_error( $output ) ) {
				$error_message = $output->get_error_message();
				write_log("Something went wrong attempting to retrieve token: $error_message");
				$this->thqLoginError();
			} //end if
			/* Token retrieved */
			else {
				$result = json_decode($output['body'],true);
				/* No token returned.  User details must be wrong */
				if(empty($result) || empty($result['access_token'])){
					write_log('Unable to log in.  Email/Password incorrect or no user access.');
					$this->thqLoginError("Unable to validate User data against TidyHQ.  Your email address or password may be in correct or you are not permitted to access this organisation.");
				} //end if
				/* User validated in TidyHQ successfully*/
				else {
					$this->thqToken = $result['access_token']; 
					$this->requestUser();
				} //end else
			} //end else
		} //end else
	} //end validatePost
	
	
	/* Redirect to TidyHQ to request a code (log in via) */
	public function requestCode() {
		$url = $this->thqSettings['auth_url']."?";
		/* Include default domain if specified */
		if(!empty($this->thqSettings['domain_prefix'])) { $url .= "domain_prefix=" . $this->thqSettings['domain_prefix'] . "&"; }
		$url .= "client_id=" . $this->thqSettings['client_id'] . "&redirect_uri=" . $this->thqSettings['redirect_uri'] . "&response_type=code";
		wp_redirect($url);
		exit;					
	} //end requestCode
	
	
	/* Validate code received from requestCode above and return token */
	public function validateCode($authorizationCode) {
		/* Required content to retrieve token */
		$content = array(
			"client_id"=>$this->thqSettings['client_id'],
			"client_secret"=>$this->thqSettings['client_secret'],
			"redirect_uri" =>$this->thqSettings['redirect_uri'],
			"code"=>$authorizationCode,
			"grant_type"=>"authorization_code"
		);  //end of content
	
		/* send request for token */
		$response = wp_remote_post( $this->thqSettings['token_url'], array(
			'method' => 'POST',
			'headers' =>  array("Content-type: application/json"),
			'body' => $content
  		 )
		); //end of response

		/* Make response readable as array */
		$newresponse = json_decode($response['body'], true);
	
		/* Use token for next step */
		if(!empty($newresponse['access_token'])) {
			$this->thqToken = $newresponse['access_token'];
			$this->requestUser();
		} //end if
		/* No token.  Code was bad */
		else {
			write_log('Unable to log in.  Code incorrect or expired.');
			$this->thqLoginError();
		} //end else
	} //end validateCode
	

/* Validate user is a TidyHQ contact.  If not, why are they trying to log in? */
 public function requestUser(){
	 /* Token should always be there */
		if(!empty($this->thqToken)) {
			/* Call get request */
			$get = new thqConnectGet();
			$this->user = $get->request('/contacts/me',array('access_token'=>$this->thqToken));
			/* validate the user */
			if(!empty($this->user['email_address'])) {
				$this->validateUser();
			} //end if
			/* no user */
			else { 
				write_log('Token was provided but email was returned from TidyHQ. Access token: '.$this->thqToken);
				$this->thqLoginError();
			} //end else
		} //end if
		else {
			write_log('A request for email was made but no token was set.');
			$this->thqLoginError();
		} //end else
	}//end requestToken
	
	/* Validate user against WP database */
	public function validateUser() {
	/* No email submitted.  This should never happen */
		if(!$this->user['email_address']) {
			write_log('Unable to validate email: '.$this->user['email_address']);
			thqLoginError();
		} //end if
		else {
			/* Find user with the email address */
			$wpUser = get_user_by('email',$this->user['email_address']);
			/* There is a WP user that matches that email address */
			if(!empty($wpUser)) {
				$newUser = $wpUser;
			} //end if
			/* No user found */
			else {
				/* Find all users with the same id */
				$wpUsers = get_users(array('meta_key' => 'thq_connect_tidyhq_id', 'meta_value' => $user['id'])); //Find their TidyHQ
				/* User with that ID found */
				if(!empty($wpUsers)) {
					$newUser = $wpUsers[0];
					wp_update_user(array('ID'=>$wpUser->ID,'user_email'=>$this->user['email_address']));
					global $wpdb;
					$wpdb->update($wpdb->users, array('user_login' => $this->user['email_address']), array('ID' => $wpUser->id));
					$_SESSION['thq-connect-email-updated'] = true; /* #TODO Can we take this out of session? */
				} //end if
				/* Still no user, so create them */
				else {
					$password = wp_generate_password(); //Make a random password
					$thq_user_id = wp_create_user($this->user['email_address'],$password,$this->user['email_address']); //Creates a new user from details entered
					$newUser = get_user_by('email',$this->user['email_address']);  //Match the user with new created user
					update_user_meta($id, 'thq_connect_tidyhq_id', $newUser->ID); //updates the user's THQ id
					wp_new_user_notification($newUser->ID,'','admin'); //email admin that a new user was created (if enabled)
				} //end else
			} //end else
				
			$id = $newUser->ID; //get the WP user ID
			update_user_meta( $id, 'thq_connect_token', $this->thqToken );   //Updates the user's token.
			update_user_meta( $id, 'thq_connect_domain_prefix', $this->domainPrefix );   //Updates the user's domain prefix
			wp_set_current_user( $id, $newUser ); //set the current user
			wp_set_auth_cookie( $id ); //set a cookie.  Yum.
			$url = admin_url(); //get the url to admin page
			wp_redirect($url); //go to admin page
			exit(); // finished
		} //end else
	} //end function
		
	public function thqLoginError($message = ''){
		$newMessage = (!empty($message)) ? $message : 'Unable to log in at this time.';
    global $thqConnectError;
    $thqConnectError = new WP_Error();
    $thqConnectError->add_data($newMessage,'regerror');
    //$_SESSION['thq_connect_error']  = $newMessage;
    //$url = home_url();
    //wp_redirect($url);
	} //end thqLoginError
} //end class


/* Login Widget */
class thq_connect_login_widget extends WP_Widget {
	private $thqSettings;
	
	public function __construct() {
		$settings = new thqConnectSettings();
		$this->thqSettings = $settings->display();
		
		parent::__construct(
			'thq_connect_login_widget',
			__( 'THQ Connect - Login via TidyHQ Widget', 'thq_connect_domain' ),
			array( 'description' => __( 'Give your members an option to log in to WordPress using their TidyHQ credentials.', 'thq_connect_domain' ), )
		);
	} //end __construct

	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		$output = !empty( $title ) ? $args['before_title'] . $title . $args['after_title'] : '';
		/* not logged in */
		if( !is_user_logged_in()) {
			global $thqConnectError;
			if(is_wp_error( $thqConnectError ) && count( $thqConnectError->get_error_messages('regerror') ) < 1) { $output .= "<span style='color: red'>".$thqConnectError->get_error_data('regerror')."</span>"; }
			/* Start of Form */
			$output .= "<p><form name='thq_connect_login' method='POST' action=''>";
			$output .= sprintf(
			"<label for='user_login'>Email Address: </label><input type='email' name='log' %s><br>",
			!empty($_COOKIE['thq_connect_user']) ? " value='".$_COOKIE['thq_connect_user']."'" : ""
		);
		/* Password */
		$output .= "<label for='user_pass'>Password: </label><input type='password' name='pwd'><br>";
		/* Domain Prefix */
		if(!empty($this->thqSettings['allow_domain_change'])) {
			$output .= sprintf(
			"<label for='domain_prefix' %s>TidyHQ Organisation:</label> <input type='%s' id='domain_prefix' value='".$this->thqSettings['domain_prefix']."' placeholder='".$this->thqSettings['domain_prefix'].".tidyhq.com' name='domain_prefix'>",
			(!empty($this->thqSettings['allow_domain_change']) && $this->thqSettings['allow_domain_change'] == true) ? '' : ' style="display: none;"',
			(!empty($this->thqSettings['allow_domain_change']) && $this->thqSettings['allow_domain_change'] == true) ? 'text' : 'hidden'
		);
		} //end if
		/* Remember me */
		$output .= sprintf(
			"<input type='checkbox' name='rememberme' %s> <label for='remember me'>Remember me</label><br>",
			!empty($_COOKIE['thq_connect_user']) ? ' checked="checked"' : ""
		);
			/* Button */
			$output .= "</p><p><input type='submit' value='Log in'>";
			/* Action */
			$output .= "<input type='hidden' name='action' value='thqConnectLogin'>";
			/* End of form */
			$output .= "</form>";
			/* Link to direct to TidyHQ validation instead */
			$output .= "<p>Alternatively, <a href='?thq=login' title='TidyHQ Login'>click here</a> and you can be sent to TidyHQ to validate your credentials.</p>";
		} //end if
		/* logged in */
		else {
			$output .= "You are already logged in.  <a title='WordPress dashboard' href='".get_dashboard_url()."'>Go to Dashboard</a> or <a title='Log out' href='". wp_logout_url( home_url() )."'>Log Out</a>";
		} //end else
		echo $args['before_widget'];
		echo __( $output, 'thq_connect_domain' );
		echo $args['after_widget'];
	} //end widget

	public function form( $instance ) {
		$title = isset($instance['title']) ? $instance['title'] : __( 'Log in via TidyHQ', 'thq_connect_domain' );
	?><p>
	<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title (optional):' ); ?></label>
	<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" placeholder="Optional Title" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
	<p class='description'>A form will display to allow users to log in via TidyHQ.</p>
	<?PHP
	} //end form

	
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		
		return $instance;
		} //end update
} //end class

?>
