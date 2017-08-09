<?PHP
/**
* THQ-CONNECT PROFILES.PHP
* Enables syncing of user data from TidyHQ to WordPress
*/
$profileUpdate = new thqConnectProfileSync();

/* PROFILE SYNC */
class thqConnectProfileSync {
	private $thqSettings;
	
	public function __construct(){
		$settings = new thqConnectSettings();
		$this->thqSettings = $settings->display();
		
		add_action('admin_init', array($this,'updateProfile'));
		add_filter( 'get_avatar' , array($this,'displayAvatar') , 1 , 5 );
		
	} //end __construct
	
	public function updateProfile() {
		$user = wp_get_current_user();
		
		$get = new thqConnectGet();
		$userdata = $get->request('contacts/me');
	
		/* TidyHQ Contact exists */
		if(!empty($userdata)) {
			/* No nickname, so use first name instead */
			$user_nickname = !empty($userdata['nick_name']) ? $userdata['nick_name'] : $userdata['first_name'];
			/* Update the user.  NOTE: we don't use password' */
			$user_update = wp_update_user(
				array(
					'ID' => $user->ID,
					'user_login' => $userdata['email_address'],
					'first_name' => $userdata['first_name'],
					'display_name' => $userdata['first_name'],
					'last_name' => $userdata['last_name'],
					'user_nicename' => $user_nickname,
					'user_email' => $userdata['email_address'],
					'description' => $userdata['details']
				)
			);
			/* TidyHQ fields to sync */
			$user_meta_keys = array('company','phone_number','address1','city','state','country','postcode','gender','birthday','facebook','twitter','subscribed','profile_image');
			/* Update each field */
			foreach($user_meta_keys as $user_meta_key) {
				update_user_meta( $user->ID, $user_meta_key, $userdata[$user_meta_key]);
			} //end of foreach
			update_user_meta( $user->ID, 'thq_connect_tidyhq_id', $userdata['id']); //update TidyHQ ID for future reference
		} //end if
		/* No TidyHQ contact */
		else {
			thq_connect_error_notice('notice-error','You have been logged in but you are not currently connected with this organisation. <a href="https://'.$this->thqSettings['domain_prefix'].$this->thqSettings['ts'].'tidyhq.com/public/connect/new">Connect Now</a>.  If you are already Connected, please log out and log back in again via TidyHQ.');
			write_log('User in TidyHQ, but no contact in organisation: '.$user->user_email);
		}//end else
	} //end function

	/* AVATAR */
	public function displayAvatar( $avatar, $id_or_email, $size, $default, $alt ) {
    /* Clear any already set user */
	$user = false;

	/* if id_or_email is a number it must be the id to match user */
    if ( is_numeric( $id_or_email ) ) {
        $id = (int) $id_or_email;
        $user = get_user_by( 'id' , $id );
    } //end if
	
	/* if it's an object its the ID too */
	elseif ( is_object( $id_or_email ) ) {
        if ( ! empty( $id_or_email->user_id ) ) { 
            $id = (int) $id_or_email->user_id;
            $user = get_user_by( 'id' , $id );
        } //end if
	} //end elseif
	
	/* Every other time it is an email */
	else {
        $user = get_user_by( 'email', $id_or_email );	
    } //end else

	/* The user now exists */
    if ( $user && is_object( $user ) ) {
		/* Find potential Gravitar user */
		$hash = md5(strtolower(trim($user->user_email)));
		/* Create Gravitar image URL */
		$uri = 'http://www.gravatar.com/avatar/' . $hash . '?d=404';
		/* Start creating the image */
		$headers = @get_headers($uri);
		/* If TidyHQ image is default or Gravitar doesnt exist */
		if ($this->thqSettings['profile_pic_default'] == "tidyhq" || (!preg_match("|200|", $headers[0]))) {
			$avatar = get_user_meta($user->data->ID,'profile_image',true);
			$avatar = "<img alt='{$alt}' src='{$avatar}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
			return $avatar;
		} //end if
		/* Either Gravitar is default or TidyHQ image doesnt exist */
		else {
			$avatar = $uri;
			$avatar = "<img alt='{$alt}' src='{$avatar}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";
			return $avatar;
		} //end else
		/* There is still no image available */
		if(!$avatar) {
			/* div for big image, span for rest */
			$htmlType = ($size == '64') ? 'div': 'span';
			/* user details */
			$last_name = get_user_meta($user->data->ID,'last_name',true);
			$first_name = get_user_meta($user->data->ID,'first_name',true);
			/* create initials from names */
			$initials = substr($first_name,0,1) . substr($last_name,0,1);
			/* set the avatar HTML */
			$avatar = "<".$htmlType." title='{$alt}' class='avatar avatar-{$size} photo' style='text-align: center; color: #000; height: {$size}; width: {$size}'>".$initials."</".$htmlType.">
<script type='text/javascript'>
var rgb = ['bd323f', '5629a1', '247495', '3fb55b', 'd1cd46', 'e2be31', 'ed473a', '66c1d2', '94a1b7', 'a52589', '84001f', '294ea1', '24955c', '8bce23', 'e1d61a', 'ea7e36', '0b88f1', '28adc7', '9758cc', '732e64'];

var selected_rgb = rgb[Math.floor(Math.random() * rgb.length)];

var avatars = document.getElementsByClassName('avatar');

for (var i = 0; i < avatars.length; ++i) {
	var item = avatars[i];
	item.style.backgroundColor = ('#'+selected_rgb);
}
</script>";

		} //end if
		/* There is an avatar */
		else {
			//do nothing
		} //end else
	} //end if
	/* There is no user? */
	else {
		//nothing to do
	} //end else
} //end function
	
	
} //end class


/**
* PROFILE PAGE
* This adds TidyHQ fields onto WordPress profile page
*/
$thqProfilePage = new thqConnectProfilePage();

class thqConnectProfilePage{

	/* Construct */
	public function __construct(){
		add_action('show_user_profile', array($this,'thq_connect_profile'));  //Show fields
		add_action('edit_user_profile', array($this,'thq_connect_profile')); //Save fields if changed
		add_action('admin_init', array($this,'thq_connect_profile_fields_disable')); //Disable the fields  #TODO make option to allow/turn this off?
	} //end __construct

	/* Profile Fields */
	function thq_connect_profile($user) {
	?>
	<!-- Additional Personal Details -->
	<h2>Additional Personal Details</h2>
	<p class="description">The following details are populated through TidyHQ.</p>
	<table class="form-table">
		<!-- Company -->
		<tr>
			<th><label for="company"><?php _e('Company'); ?></label></th>
			<td>
				<input type="text" name="company" id="company" value="<?php echo esc_attr( get_the_author_meta( 'company', $user->ID ) ); ?>" class="regular-text" />
				<p class="description"><?php _e('Company (If applicable)', 'tidyhq'); ?></p>
			</td>
		</tr>
		<!-- Gender -->
		<tr>
			<th><label for="gender"><?php _e('Gender', 'tidyhq'); ?></label></th>
			<td>
				<select name="gender" id="gender">
					<?PHP $user_gender = esc_attr( get_the_author_meta( 'address1', $user->ID ) ); ?>
					<option value="M"<?PHP if($user_gender == "M") { echo " selected";} ?>>Male</option>
					<option value="F"<?PHP if($user_gender == "F") { echo " selected";} ?>>Female</option>
					<option value="U"<?PHP if($user_gender == "U") { echo " selected";} ?>>Undisclosed</option>
				</select>
				<p class="description"><?php _e('What is your gender?', 'tidyhq'); ?></p>
			</td>
		</tr>
		<!-- Date of Birth -->
		<tr>
			<th><label for="birthday"><?php _e('Date of Birth', 'tidyhq'); ?></label></th>
			<td>
				<input type="date" name="birthday" id="birthday" value="<?php echo esc_attr( get_the_author_meta( 'birthday', $user->ID ) ); ?>" class="regular-text" />
				<p class="description"><?php _e('What is your date of birth?', 'tidyhq'); ?></p>
			</td>
		</tr>
	</table>
	<!-- Additional Contact Details -->
	<h2>Additional Contact Details</h2>
	<p class="description">The following details are populated through TidyHQ.</p>
	<table class="form-table">
		<!-- Phone Number -->
		<tr>
			<th><label for="phone_number"><?php _e('Phone Number', 'tidyhq'); ?></label></th>
			<td>
				<input type="text" name="phone_number" id="phone_number" value="<?php echo esc_attr( get_the_author_meta( 'phone_number', $user->ID ) ); ?>" class="regular-text" />
				<p class="description"><?php _e('Your phone number.', 'tidyhq'); ?></p>
			</td>
		</tr>
		<!-- Street Address -->
		<tr>
			<th><label for="address1"><?php _e('Street Address', 'tidyhq'); ?></label></th>
			<td>
				<input type="text" name="address1" id="address1" value="<?php echo esc_attr( get_the_author_meta( 'address1', $user->ID ) ); ?>" class="regular-text" />
				<p class="description"><?php _e('What is your street address?', 'tidyhq'); ?></p>
			</td>
		</tr>
		<!-- City -->
		<tr>
			<th><label for="city"><?php _e('City/Suburb', 'tidyhq'); ?></label></th>
			<td>
				<input type="text" name="city" id="city" value="<?php echo esc_attr( get_the_author_meta( 'city', $user->ID ) ); ?>" class="regular-text" />
				<p class="description"><?php _e('What is your city or suburb?', 'tidyhq'); ?></p>
			</td>
		</tr>
		<!-- State -->
		<tr>
			<th><label for="state"><?php _e('State/Territory', 'tidyhq'); ?></label></th>
			<td>
			<?PHP
				$user_state = esc_attr( get_the_author_meta( 'state', $user->ID ) );
				$user_country = esc_attr( get_the_author_meta( 'country', $user->ID ) );
				if($user_country = 'Australia') {
			?>
				<select name="state" id="state">
					<option value="WA"<?PHP if($user_state == "WA" || !isset($user_state)) { echo " selected";} ?>>Western Australia</option>
					<option value="NSW"<?PHP if($user_state == "NSW") { echo " selected";} ?>>New South Wales</option>
					<option value="VIC"<?PHP if($user_state == "VIC") { echo " selected";} ?>>Victoria</option>
					<option value="SA"<?PHP if($user_state == "SA") { echo " selected";} ?>>South Australia</option>
					<option value="QLD"<?PHP if($user_state == "QLD") { echo " selected";} ?>>Queensland</option>
					<option value="NT"<?PHP if($user_state == "NT") { echo " selected";} ?>>Northern Territory</option>
					<option value="ACT"<?PHP if($user_state == "ACT") { echo " selected";} ?>>Australian Capital Territory</option>
				</select>
			<?PHP
				} //end if
				else {
			?>
				<input type="text" name="state" id="state" value="<?php echo esc_attr( get_the_author_meta( 'state', $user->ID ) ); ?>" class="regular-text" />
			<?PHP
				} //end else
			?>
				<p class="description"><?php _e('Select State.', 'tidyhq'); ?></p>
			</td>
		</tr>
		<!-- Postcode -->
		<tr>
			<th><label for="postcode"><?php _e('Postcode', 'tidyhq'); ?></label></th>
			<td>
				<input type="number" max="6999" min="6000" name="postcode" id="postcode" value="<?php echo esc_attr( get_the_author_meta( 'postcode', $user->ID ) ); ?>" class="regular-text" />
				<p class="description"><?php _e('What is your postcode?', 'tidyhq'); ?></p>
			</td>
		</tr>
	</table>
	<!-- Social Networking -->
	<h2>Social Networking</h2>
	<p class="description">The following details are populated through TidyHQ.</p>
	<table class="form-table">
		<!-- Facebook -->
		<tr>
			<th><label for="facebook"><?php _e('Facebook', 'tidyhq'); ?></label></th>
			<td>
				<input type="text" name="facebook" id="facebook" value="<?php echo esc_attr( get_the_author_meta( 'facebook', $user->ID ) ); ?>" class="regular-text" />
				<p class="description"><?php _e('What is your Facebook user?', 'tidyhq'); ?></p>
			</td>
		</tr>
		<!-- Twitter -->
		<tr>
			<th><label for="twitter"><?php _e('Twitter', 'tidyhq'); ?></label></th>
			<td>
				<input type="text" name="twitter" id="twitter" value="<?php echo esc_attr( get_the_author_meta( 'twitter', $user->ID ) ); ?>" class="regular-text" />
				<p class="description"><?php _e('What is your Twitter ID?', 'tidyhq'); ?></p>
			</td>
		</tr>
		<!-- Linked in -->
		<tr>
			<th><label for="linkedin"><?php _e('Linkedin', 'tidyhq'); ?></label></th>
			<td>
				<input type="text" name="linkedin" id="linkedin" value="<?php echo esc_attr( get_the_author_meta( 'linkedin', $user->ID ) ); ?>" class="regular-text" />
				<p class="description"><?php _e('What is your Linkedin user?', 'tidyhq'); ?></p>
			</td>
		</tr>
		<!-- Instagram -->
		<tr>
			<th><label for="instagram"><?php _e('Instagram', 'tidyhq'); ?></label></th>
			<td>
				<input type="text" name="instagram" id="instagram" value="<?php echo esc_attr( get_the_author_meta( 'instagram', $user->ID ) ); ?>" class="regular-text" />
				<p class="description"><?php _e('What is your Instagram user?', 'tidyhq'); ?></p>
			</td>
		</tr>
	</table>

	<!-- TidyHQ Populated -->
	<h2><img src="<?PHP echo plugins_url( '../img/tidyhq-icon.png', __FILE__ ); ?>" style="height: 20px; width: 20px;"> TidyHQ Account Details</h2>
	<p class="description">The following are other details are populated from TidyHQ.</span><br>
	<table class="form-table">
		<!-- Subscription -->
		<tr>
			<th><label for="subscribed"><?php _e('Subscription', 'tidyhq'); ?></label></th>
			<td>
				<input type="checkbox" name="subscribed" id="subscribed" value="1"
			<?PHP
				$user_subscribed = esc_attr( get_the_author_meta( 'subscribed', $user->ID ) );
				if($user_subscribed == "1") { echo " checked='checked'"; }

			?>
				>
				<p class="description"><?php _e('Tick to continue receiving important emails from us.', 'tidyhq'); ?></p>
			</td>
		</tr>
		<!-- Contact ID -->
		<tr>
			<th><label for="thq_connect_tidyhq_id"><?php _e('TidyHQ Contact ID', 'tidyhq'); ?></label></th>
			<td>
				<input type="text" name="thq_connect_tidyhq_id" disabled id="thq_connect_tidyhq_id" value="
			<?PHP
				echo esc_attr( get_the_author_meta( 'thq_connect_tidyhq_id', $user->ID ) );
			?>">
			<p class="description"><?php _e('Your contact ID.', 'tidyhq'); ?></p>
			</td>
		</tr>
	</table>
<?PHP
	} //end of tidyhq_profile	
 
	/* Disable fields on profile page */
 	public function thq_connect_profile_fields_disable() {
		global $pagenow;
		/* ignore if not profile edit page */
		if ($pagenow!=='profile.php' && $pagenow!=='user-edit.php') {
			return;
		} //end if
		add_action( 'admin_footer', array($this,'thq_connect_profile_fields_disable_js' ));
	} //end function

	/* The Javascript to disable fields */
	public function thq_connect_profile_fields_disable_js() {
?>
    <script>
        jQuery(document).ready( function($) {
            var fields_to_disable = ['first_name','last_name','display_name','description','url','company','phone_number','address1','city','state','country','postcode','gender','birthday','facebook','twitter','subscribed','profile_image','linkedin','instagram'];
            for(i=0; i<fields_to_disable.length; i++) {
                if ( $('#'+ fields_to_disable[i]).length ) {
                    $('#'+ fields_to_disable[i]).attr("disabled", "disabled");
					$('#'+ fields_to_disable[i]).attr("title", "Log in to TidyHQ to update.");
                }
            }
			jQuery('#password').css('display','none');
			jQuery('.user-profile-picture .description').css('display','none');
			jQuery('#your-profile').before('<h3>Your profile is controlled by TidyHQ and only Personal Options can be changed here.  For all other changes, head to TidyHQ.</h3>');
			jQuery('#email').after('<p class="description">Please note: By changing your email address, your profile may not work with TidyHQ correctly.</p>');
        });
    </script>
<?PHP
	} //end function
} //end thqConnectProfilePage
?>
