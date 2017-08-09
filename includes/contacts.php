<?PHP

/**
* THQ CONNECT CONTACTS.PHP
* Importing of contacts and invite to WordPress.
*/

/* Show contacts page for those with access */
if(current_user_can('thq_connect_user')) {
  $contactsPage = new thqConnectContactsPage();
} //end if

class thqConnectContactsPage {
	private $groupid;
	private $contactid;
	private $doaction;
	
	/* Construct */
	public function __construct() {
		$this->groupid = !empty($_GET['groupid']) ? $_GET['groupid'] : '';
		$this->contactid = !empty($_GET['contactid']) ? $_GET['contactid'] : '';
		$this->doaction = !empty($_GET['doaction']) ? $_GET['doaction'] : '';
		
		add_action( 'admin_menu', array( $this, 'thq_connect_contacts_menu'), 99 );  //adds the menu item
		add_action( 'admin_footer', array($this,'thq_connect_contacts_javascript')); //adds the script
		add_action('wp_ajax_thq_connect_assign_user_action', array($this,'thq_connect_assign_user_action' )); //adds the action
		add_action('wp_ajax_thq_connect_invite_user_action', array($this,'thq_connect_invite_user_action' )); //adds the action
  	} //end __construct
  
	/* ajax script */
	public function thq_connect_contacts_javascript() {
	?> 
		<script type="text/javascript" >
			jQuery(document).ready(function($) {
				jQuery('#thq-connect-contacts-select').change(function() {
					var thisGroup = jQuery(this).val();
					window.location.replace("?page=thq-connect-contacts&doaction=invitegroup&groupid="+thisGroup);
				});
				});	
</script>
	<?php
  	} //end function

	/* Action from ajax */
  	public function thq_connect_contacts_action() {
//		global $wpdb; //not sure if this is needed?
	  	/* create the data */
		$contactsPage = new thqConnectContactsList($_POST['groupid']);
		/* create the table */
  		$contactsPage->prepare_items(); 
		/* display the table */
  		echo $contactsPage->display();
		/* terminate script */
		wp_die();
	} //end function
  
	public function thq_connect_assign_user_action() {
		$get = new thqConnectGet();
		$contact = $get->request('contacts/'.$_POST['contactid']);
		$updateUser = get_userdata($_POST['wpuserid']);  //Match the user with new created user
		update_user_meta($_POST['contactid'], 'thq_connect_tidyhq_id', $_POST['wpuserid']); //updates the user's THQ id	
		$this->syncContacts($contact,$updateUser);
		echo "User updated.";
		wp_die();	
	} //end function
	
		/* Action from ajax */
  	public function thq_connect_invite_user_action() {
			$get = new thqConnectGet();
			$contact = $get->request('contacts/'.$_POST['contactid']);
			$user_id = username_exists( $contact['email_address'] );
			if ( !$user_id and email_exists($contact['email_address']) == false ) {
				$random_password = wp_generate_password( $length=12, $include_standard_special_chars=false );
				$newUser = wp_create_user( $contact['email_address'], $random_password, $contact['email_address'] );
				$updateUser = get_user_by('email',$contact['email_address']);  //Match the user with new created user
				update_user_meta($_POST['contactid'], 'thq_connect_tidyhq_id', $updateUser->ID); //updates the user's THQ id
				$this->syncContacts($contact,$updateUser);
				$this->sendWelcomeEmail($updateUser);
				echo "User created.";
			} else {
				echo "Error creating user.";
			}
		wp_die();
	} //end function
	
	private function syncContacts($contact,$updateUser){
		/* TidyHQ Contact exists */
		if(!empty($contact) && !empty($updateUser)) {
			/* No nickname, so use first name instead */
			$user_nickname = !empty($contact['nick_name']) ? $contact['nick_name'] : $contact['first_name'];
			/* Update the user.  NOTE: we don't use password' */
			$user_update = wp_update_user(
				array(
					'ID' => $updateUser->ID,
					'user_login' => $contact['email_address'],
					'first_name' => $contact['first_name'],
					'display_name' => $contact['first_name'],
					'last_name' => $contact['last_name'],
					'user_nicename' => $user_nickname,
					'user_email' => $contact['email_address'],
					'description' => $contact['details']
				)
			);
			/* TidyHQ fields to sync */
			$user_meta_keys = array('company','phone_number','address1','city','state','country','postcode','gender','birthday','facebook','twitter','subscribed','profile_image');
			/* Update each field */
			foreach($user_meta_keys as $user_meta_key) {
				update_user_meta( $updateUser->ID, $user_meta_key, $contact[$user_meta_key]);
			} //end of foreach
			update_user_meta( $updateUser->ID, 'thq_connect_tidyhq_id', $contact['id']); //update TidyHQ ID for future reference
		} //end if	
		
	} //end function
	
	private function sendWelcomeEmail($user) {
		add_filter( 'wp_mail_content_type', array($this,'wpmail_content_type' ));
		$options = get_option( 'thq_connect_invitation_settings' );
		$siteUrl = get_site_url();
		$siteName = get_bloginfo('name');
		
		$autoCorrect = array(
			'{firstname}' => $user->first_name,
			'{lastname}' => $user->last_name,
			'{emailaddress}' => $user->user_email
		);
		
		$subject = $options['invitation_subject'];
		$headers = "From: ".$siteName."<".get_bloginfo('admin_email').">";
		
		/* To user */
		$message = $options['invitation_content'];
		
		foreach ($autoCorrect as $key => $value) {
			$message = str_replace($key,$value,$message);
		} //end foreach
		
		wp_mail($user->user_email,$subject,$message,$headers);
		write_log($user->user_email.$subject.$message.$headers);
		
		remove_filter ( 'wp_mail_content_type', array($this,'wpmail_content_type'));
		
	} //end function
	
public function wpmail_content_type() {
    return 'text/html';
}
	
  	/* Menu Option */
	public function thq_connect_contacts_menu() {
	        add_submenu_page(
				'thq-connect-page',
				'Contacts',
				'Contacts',
				'thq_connect_user',
				'thq-connect-contacts',
				array( $this,'thq_connect_contacts_page')
			);
	}  //end thq_connect_contacts_menu
  
	/* Display the page */
 	public function thq_connect_contacts_page() {
	?>
		<div class='wrap'>
			<h2><img src="<?PHP echo plugins_url( '../img/tidyhq-icon.png', __FILE__ ); ?>" style="height: 20px; width: 20px;"> <?PHP echo get_admin_page_title(); ?></h2>
			<p><b>Contacts</b> allows you assign a TidyHQ contact to an existing WordPress user or invite a TidyHQ contact to join your WordPress site to help your site reach more members.  Please note:  Assigning a contact to an existing WordPress user will overwrite any existing WordPress profile details.</p>
  			<table class='form-table'>
				<tbody>
					<tr><th scope='row'><label for='thq-connect-contacts-select'>Contact Group: </label></th>
					<td>
						<select id="thq-connect-contacts-select" name="contact-group">
    							<option disabled selected>Select a group</option>
  	<?PHP
    		$get = new thqConnectGet();
    		/* list the groups */
		$groups = $get->request('groups');
    		foreach($groups as $group) {
    			echo "<option value='".$group['id']."'";
			if(!empty($this->groupid) && $this->groupid == $group['id']) { echo " selected"; }
			echo ">".$group['label']."</option>";
    		} //end foreach
  	?>
						</select>
						<p class='description'>Please select a group</p>
						<!--end group select--></td></tr></tbody></table>
			<div id="thq-connect-contacts-wrap">
			<?PHP
		$activeGroup = $get->request('groups/'.$this->groupid);
		echo "<h1>".$activeGroup['label']."</h1>";
		if(!empty($this->doaction) && $this->doaction == 'invitegroup'){
			$contactsPage = new thqConnectContactsList($this->groupid);
			$contactsPage->prepare_items();
  			echo $contactsPage->display(); 
			echo "";
		} //end if
?></div><!-- end contacts-wrap-->
		</div><!--end wrap -->
 	<?PHP
	} //end thq_connect_contacts_page
} //end class

/* We need to ensure this is included for next class */
if( ! class_exists( 'WP_List_Table' ) ) { require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' ); }

class thqConnectContactsList extends WP_List_Table {
	/* store the group id */
	private $groupid;
	
	public function __construct($arr){
		$this->groupid = $arr;
		parent::__construct(
			array(
				'singular' => 'singular_form',
        'plural'   => 'plural_form',
				'ajax'=>false
			)
		);
	} //end __construct

	/* Columns */
	public function get_columns() {
		$columns = array(
			'edit'=>'',
			'firstname'=>'First Name',
			'lastname'=>'Last Name',
			'emailaddress'=>'Email Address',
			'wordpress'=>'WordPress Username'
		);
		return $columns;
	} //end get_columns

	/* Sorting */
	function usort_reorder( $a, $b ) {
  // If no sort, default to title
  $orderby = ( ! empty( $_GET['orderby'] ) ) ? $_GET['orderby'] : 'lastname';
  // If no order, default to asc
  $order = ( ! empty($_GET['order'] ) ) ? $_GET['order'] : 'asc';
  // Determine sort order
  $result = strcmp( $a[$orderby], $b[$orderby] );
  // Send final sort direction to usort
  return ( $order === 'asc' ) ? $result : -$result;
}
	
	/* Prepare the table */
	public function prepare_items() {

		$columns = $this->get_columns();
		$hidden = array(); 
		$sortable = $this->get_sortable_columns();
		$this->_column_headers = array($columns,$hidden,$sortable);
		$data = $this->get_group_contacts();
		usort( $data, array( &$this, 'usort_reorder' ) );
		/* Get data */
				
		$per_page = 10;
	  $current_page = $this->get_pagenum();
  	$total_items = count($data);
		
		//$search = ( isset( $_REQUEST['s'] ) ) ? $_REQUEST['s'] : false;
		//if(!empty($search)) { $data = array_filter($data, function($var) use ($search) { return preg_match("/\b$search\b/i", $var); }); }

  	// only ncessary because we have sample data
  	$newData = array_slice($data,(($current_page-1)*$per_page),$per_page);

  $this->set_pagination_args( array(
    'total_items' => $total_items,                  //WE have to calculate the total number of items
    'per_page'    => $per_page                     //WE have to determine how many items to show on a page
  ) );
  $this->items = $newData;
	} //end prepare_items
	
	/* Column Default */
	public function column_default($item, $column_name) {
    		return $item[$column_name];
	} //end column_default

	/* Sortable columns */
	public function get_sortable_columns(){
  		$sortable_columns = array(
    			'lastname'  => array('lastname',false),
    			'firstname' => array('firstname',false),
    			'emailaddress' => array('emailaddress',false),
					'wordpress'=>array('wordpress',false)
  		);
  		return $sortable_columns;
	} //end function
	
	/* Get the data */
	public function get_group_contacts(){
		$get = new thqConnectGet();
		$contacts = $get->request('groups/'.$this->groupid.'/contacts');
		$newcontacts = array();
		foreach($contacts as $contact) {
			$wpUser = get_user_by('email', $contact['email_address']);
			/* Store TidyHQ id if exists and if email exists */
			if(!empty($wpUser) && !empty($contact['email_address'])) {
				$wpcontactid = get_the_author_meta( 'thq_connect_tidyhq_id', $wpUser->ID );
				$wpcontactid = !empty($wpcontactid) ? $wpUser->user_login : 'Contact invited';
			}
			/* No TidyHQ ID or email does not exist */
			else {
				/* only offer invite if email is not empty */
if(!empty($contact['email_address'])) {
	$wpcontactid = '<span id="'.$contact['id'].'"><select id="contact'.$contact['id'].'" class="thq-connect-send-invite"><option selected disabled>Assign to...</option><optgroup label="New User"><option value="invite">New WordPress User</option></optgroup>';
						
				$roles = get_editable_roles();
				$currentuser = wp_get_current_user();
				foreach($roles as $role) {
					$blogusers = get_users(array('role'=>$role['name']));
					foreach ($blogusers as $user) {
						$newusers[$role['name']][] = array('first_name'=>$user->first_name,'last_name'=>$user->last_name,'user_email'=>$user->user_email,'ID'=>$user->ID);
					} //end foreach
				} //end foreach
				foreach ($newusers as $role => $roleusers) {
					$wpcontactid .= "<optgroup label='".$role."'>";
					foreach($roleusers as $roleuser) {
						$wpcontactid .= "<option value='assign".$roleuser['ID']."'";
						if($currentuser->ID == $roleuser['ID']) { $wpcontactid .= " disabled"; }
						$wpcontactid .= ">".$roleuser['first_name']." ".$roleuser['last_name']." (".$roleuser['user_email'].")</option>";
					} //end foreach
					$wpcontactid .= "</optgroup>";
				} //end foreach	
				
$wpcontactid .=	'</select></span>	
					<script type="text/javascript">
			jQuery(document).ready(function($) {
				jQuery("#contact'.$contact['id'].'").change(function() {
				var thisAction = jQuery(this).val();
				if(thisAction == "invite") {
					/* details for ajax request */
					var data = ({
						action: "thq_connect_invite_user_action",
						contactid: '.$contact['id'].'
					});
		
					jQuery.post(ajaxurl, data, function(response) {
						jQuery("#'.$contact['id'].'").html(response);
					});
					}
					if(thisAction.indexOf("assign") >= 0) {
						var r = confirm("This cannot be undone.  Are you sure you want to link TidyHQ contact <'.$contact["email_address"].'> with WordPress user "+wpUserId."?");
						if (r == true) {
							var wpUserId = thisAction.replace("assign","");
							/* details for ajax request */
							var data = ({
								action: "thq_connect_assign_user_action",
								contactid: '.$contact['id'].',
								wpuserid: wpUserId
							});
							jQuery.post(ajaxurl, data, function(response) {
								jQuery("#'.$contact['id'].'").html(response);
							});
	
						}
					}
				});
			});	
		</script>';
		}
		else {
			$wpcontactid = "An email address is required";
		}
			}
			/* Store 'not provided' if no email */
			$email = !empty($contact['email_address']) ? $contact['email_address'] : "---";
			$var = new thqConnectSettings;
			$thqSettings = $var->display();
			$newcontacts[] = array(
				'edit'=>'<a href="https://'.$thqSettings['domain_prefix'].'.tidyhq.com/contacts/'.$contact['id'].'" title="Edit TidyHQ Contact"><span class="dashicons dashicons-edit"></span></a>',
				'firstname'=>$contact['first_name'],
				'lastname'=>$contact['last_name'],
				'emailaddress'=>$email,
				'wordpress'=>$wpcontactid
			);
		} //end foreach
		return $newcontacts;
	} //end get_group_contacts
	
} //end class
?> 
