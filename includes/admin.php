<?PHP
/**
* THQ CONNECT ADMIN.PHP
* This contains everything for the admin settings page.
*/
class thqConnectSettingsPage {
	/* Hold options for callback */
    private $options;
	/* Hold current tab */
	private $activeTab;
	
	/* Construct */
    public function __construct() {
		/* Add menu item */
        add_action( 'admin_menu', array( $this, 'thq_connect_settings_page' ), 101 );
		/* initialise the page */
        add_action( 'admin_init', array( $this, 'thq_connect_settings_page_init' ) );
    } //end __construct
	
    /* Menu Item */
    public function thq_connect_settings_page() {
		add_submenu_page(
			'thq-connect-page',
			'THQ Connect Settings',
			'Settings',
			'thq_connect_user',
			'thq-connect-settings',
		array( $this, 'thq_connect_settings_create_page')
	 );
    } //end thq_connect_settings_page
	
	
    /* Create & Display Page */
    public function thq_connect_settings_create_page() {
        ?>
        <div class="wrap">
            <h2><img src="<?PHP echo plugins_url( '../img/tidyhq-icon.png', __FILE__ ); ?>" style="height: 20px; width: 20px;"> THQ Connect Settings</h2>  
        <?PHP
		/* Show messages */
		settings_errors();
		 /* Tabs */
        $this->activeTab = !empty($_GET['tab']) ? $_GET[ 'tab' ] : '';
        ?>
        <h2 class="nav-tab-wrapper">
            <a href="?page=thq-connect-settings" class="nav-tab <?php echo $this->activeTab == '' ? 'nav-tab-active' : ''; ?>">Plugin Options</a>
            <a href="?page=thq-connect-settings&tab=calendar" class="nav-tab <?php echo $this->activeTab == 'calendar' ? 'nav-tab-active' : ''; ?>">Calendar Options</a>
			<a href="?page=thq-connect-settings&tab=invitation" class="nav-tab <?php echo $this->activeTab == 'invitation' ? 'nav-tab-active' : ''; ?>">Invitation to WordPress</a>
			<!-- add more tabs here -->
        </h2>
            <form method="post" action="options.php">
            <?php
		/* Calendar Tab */
		if(!empty($this->activeTab) && $this->activeTab == 'calendar') {
		    $this->options = get_option( 'thq_connect_calendar_settings' );
			settings_fields('thq_connect_calendar_settings_group');
			do_settings_sections('thq-connect-calendar-settings');
		} //end if
		/* Invitation Tab */
		elseif(!empty($this->activeTab) && $this->activeTab == 'invitation') {
		    $this->options = get_option( 'thq_connect_invitation_settings' );
			settings_fields('thq_connect_invitation_settings_group');
			do_settings_sections('thq-connect-invitation-settings');
		} //end elseif
		/* Add more as elseif as required */
		/* No tab */
		else {
		    $this->options = get_option( 'thq_connect_settings' );
			settings_fields('thq_connect_settings_group'); //create fields
			do_settings_sections('thq-connect-settings'); //display fields
		} //end else
		/* Show submit button */
        submit_button();
            ?>
            </form> <!-- end form -->
        </div> <!-- end wrap -->
        <?php
    } //end thq_connect_settings_create_page
	
    /* Create Settings */
    public function thq_connect_settings_page_init() {        
	
		/* Settings for Plugin */
        register_setting('thq_connect_settings_group','thq_connect_settings',array( $this, 'sanitize' ) );
		/* Settings for Calendar */
		register_setting('thq_connect_calendar_settings_group','thq_connect_calendar_settings',array( $this, 'sanitize' ) );
		/* Settings for Invitation */
		register_setting('thq_connect_invitation_settings_group','thq_connect_invitation_settings',array( $this, 'sanitize' ) );
		
		/* Required section */
		add_settings_section('thq_connect_settings_section','Required Settings',array( $this, 'thq_connect_print_section_info' ),'thq-connect-settings');
		/* Optional section */
		add_settings_section('thq_connect_optional_section','Optional Settings',array( $this, 'thq_connect_print_optional_info' ),'thq-connect-settings'); 
		/* Calendar Section */
		add_settings_section('thq_connect_calendar_colours_section','Calendar Colours',array( $this, 'thq_connect_calendar_section_info' ),'thq-connect-calendar-settings');
		/* Invitation Section */
		add_settings_section('thq_connect_invitation_section','TidyHQ Contact WordPress Invitation',array( $this, 'thq_connect_invitation_section_info' ),'thq-connect-invitation-settings');
		
		/* Role access */
		add_settings_field('thq_connect_role_access','Role Access',array( $this, 'thq_connect_role_access_callback'),'thq-connect-settings','thq_connect_optional_section');
		/* Org Sync */
		add_settings_field('thq_connect_org_sync','Organisation Match',array( $this, 'thq_connect_org_sync_callback' ),'thq-connect-settings','thq_connect_optional_section');      
		/* Client ID */
		add_settings_field('thq_connect_client_id','Client ID',array( $this, 'thq_connect_client_id_callback' ),'thq-connect-settings','thq_connect_settings_section'); 
		/* Client Secret */
        add_settings_field('thq_connect_client_secret','Client Secret',array( $this, 'thq_connect_client_secret_callback' ),'thq-connect-settings','thq_connect_settings_section');
		/* Domain Prefix */
		add_settings_field('thq_connect_domain_prefix','Domain Prefix',array( $this, 'thq_connect_domain_prefix_callback' ),'thq-connect-settings','thq_connect_settings_section'); 
		/* Profile Sync */
		add_settings_field('thq_connect_profile_sync','User/Contact Match',array( $this, 'thq_connect_profile_sync_callback' ),'thq-connect-settings','thq_connect_optional_section');
		/* Profile Sync */
		add_settings_field('thq_connect_profile_pic_default','Avatar Match',array( $this, 'thq_connect_profile_pic_default_callback' ),'thq-connect-settings','thq_connect_optional_section');
 
		/* Event Colour */
		add_settings_field('thq_connect_calendar_event_colour','Event Colour',array( $this, 'thq_connect_calendar_event_callback'),'thq-connect-calendar-settings','thq_connect_calendar_colours_section');
		/* Meeting Colour */
		add_settings_field('thq_connect_calendar_meeting_colour','Meeting Colour',array( $this, 'thq_connect_calendar_meeting_callback'),'thq-connect-calendar-settings','thq_connect_calendar_colours_section');
		/* Session Colour - DISABLED */
		//add_settings_field('thq_connect_calendar_session_colour','Session Colour',array( $this, 'thq_connect_calendar_session_callback'),'thq-connect-calendar-settings','thq_connect_calendar_colours_section');
		/* Task Colour */
		add_settings_field('thq_connect_calendar_task_colour','Task Colour',array( $this, 'thq_connect_calendar_task_callback'),'thq-connect-calendar-settings','thq_connect_calendar_colours_section');			
		
		/* Invitation Subject */
		add_settings_field('thq_connect_invitation_subject','Email Subject',array( $this, 'thq_connect_invitation_subject_callback'),'thq-connect-invitation-settings','thq_connect_invitation_section');
		/* Invitation Content */
		add_settings_field('thq_connect_invitation_content','Email Message',array( $this, 'thq_connect_invitation_content_callback'),'thq-connect-invitation-settings','thq_connect_invitation_section');

		
    } //end public_page_init
	
    /* Sanitise and save */
    public function sanitize( $input ) {
 		$new_input = array();
		/* Min role access */
        if( isset( $input['role_access'] ) ) { 
			foreach( get_editable_roles() as $role_name => $role_info){
				$role = get_role($role_name);
				if($role_name != 'administrator' && isset($role_info['capabilities']['thq_connect_user']) && !isset($input['role_access'][$role_name])) {
					$role->remove_cap('thq_connect_user');
				}//end if
				elseif($role_name != 'administrator' && !isset($role_info['capabilities']['thq_connect_user']) && isset($input['role_access'][$role_name])) {
					$role->add_cap('thq_connect_user');
				} //end elseif
				else {
					//no changes made
				} //end else
			} //end foreach
			$new_input['role_access'] = $input['role_access'];
		}
		/* All other Settings */
		else {
			foreach ($input as $setting => $value) {
				$new_input[$setting] = sanitize_text_field( $input[$setting] );
			} //end foreach
		} //end else
		/* Show message */
		if(!empty($new_input)) { $this->thq_connect_message('updated'); }
		else { $this->thq_connect_message('none'); }
		
      	return $new_input;
    } //end sanitize
	
	/* Error Messages */
	public function thq_connect_message($type) {
		/* Calendar */
		if(!empty($_GET['tab']) && $_GET['tab'] == 'calendar') {
			if($type = 'updated') { $message = __( 'Changes updated successfully.', 'thq-connect-calendar-settings' ); }
			elseif($type = 'notice-warning') { $message = __( 'No changes were made.', 'thq-connect-calendar-settings' ); }
			else { $message = __('Unable to save settings','thq-connect-calendar-settings'); }
		} //end if
		/* Invitation */
		elseif(!empty($_GET['tab']) && $_GET['tab'] == 'invitation') {
			if($type = 'updated') { $message = __( 'Changes updated successfully.', 'thq-connect-invitation-settings' ); }
			elseif($type = 'notice-warning') { $message = __( 'No changes were made.', 'thq-connect-invitation-settings' ); }
			else { $message = __('Unable to save settings','thq-connect-invitation-settings'); }
		} //end elseif
		/* General */
		else {
			if($type = 'updated') { $message = __( 'Changes updated successfully.', 'thq-connect-settings' ); }
			elseif($type = 'notice-warning') { $message = __( 'No changes were made.', 'thq-connect-settings' ); }
			else { $message = __('Unable to save settings','thq-connect-settings'); }
		} //end else
		/* add the message */
		add_settings_error('thq_connect_settings_saved',esc_attr( 'settings_updated' ),$message,$type);
	} //end thq_message
	
	/* Required Section Text */
	public function thq_connect_print_section_info() {
        print 'Complete details below.  All fields are required.  Please do not change unless necessary as it may stop the plugin from working.<br>For assistance with this plugin, see our <a href="'.admin_url().'admin.php?page=thq-connect-help">Help section</a>.';
	} //end print_thq_section_info
	
	/* Optional Section text */
	public function thq_connect_print_optional_info() {
        print 'All the following functions are optional.  You can enable and disable as required.';
	} //end print_thq_optional_info

	/* Calendar Section text */
	public function thq_connect_calendar_section_info() {
		print 'You can change the colours for calendar events below.  Alternatively, you can disable those event types from displaying (this will effect all users).';
	} //end function
	
	/* Invitation Section text */
	public function thq_connect_invitation_section_info() {
		print 'You can invite TidyHQ contacts to join your WordPress site using the email below.';
	} //end function
	
	/* Form Fields */
	
	/* Role Access */
	public function thq_connect_role_access_callback() {
		foreach(get_editable_roles() as $role_name => $role_info){
			printf('<label><input type="checkbox" id="thq_connect_role_access_'.$role_name.'" name="thq_connect_settings[role_access]['.$role_name.']" value ="true" %s %s > '.$role_info['name'].'</label><br>',
			isset($this->options['role_access'][$role_name]) ? ' checked="checked"' : '',
			$role_name == 'administrator' ? ' checked="checked" disabled' : ''
        );
		}
		printf('<p class="description" id="thq_connect_role_access_description">Which roles would you like to give access to TidyHQ functions in WordPress?</p>');
	} //end function
	
	/* Profile Sync */
	public function thq_connect_profile_sync_callback() {
		printf('<label>Enable <input type="radio" id="thq_connect_profile_sync" name="thq_connect_settings[profile_sync]" value="true" %s /> Disable <input type="radio" id="thq_connect_profile_sync" name="thq_connect_settings[profile_sync]" value="false" %s /></label><p class="description" id="thq_connect_profile_sync_description">Synchronize TidyHQ & WordPress profiles. When enabled TidyHQ handles all profile details.  Disable if you would prefer WordPress to handle it\'s own.</p>',
            (isset( $this->options['profile_sync'] ) && ($this->options['profile_sync'] == 'true')) ? ' checked="checked"' : '',
			(isset( $this->options['profile_sync'] ) && ($this->options['profile_sync'] == 'false')) ? ' checked="checked"' : ''
        );
	} //end function
	
	/* Domain Prefix */
	public function thq_connect_domain_prefix_callback() {
		printf('<input type="text" id="thq_connect_domain_prefix" placeholder="demo" name="thq_connect_settings[domain_prefix]" value="%s" />.tidyhq.com <br>
	<input type="checkbox" name="thq_connect_settings[child_org]" value="true" id="thq_connect_child_org" %s disabled>
	Is this a <a href="http://support.tidyhq.com/apps/add-ons-and-apps/association-app" title="Read more" target="_blank">child Organisation of an Association</a>? <br>
	<input type="checkbox" name="thq_connect_settings[allow_domain_change]" value="true" id="thq_connect_allow_domain_change" %s>
	Allow users to enter their own domain prefix?<br><p class="description" id="thq_connect_domain_prefix_description">Change this prefix to use a different TidyHQ organisation.  Only this prefix may change your website name - not user-entered domain prefixes.</p>',
		isset( $this->options['domain_prefix'] ) ? esc_attr( $this->options['domain_prefix']) : '',
		isset($this->options['child_org'] ) ? ' checked="checked"' : '',
		isset($this->options['allow_domain_change'] ) ? ' checked="checked"' : ''
		);
	} //end function
	
	/* Org Sync */
	public function thq_connect_org_sync_callback() {
		printf('<label>Enable <input type="radio" id="thq_org_sync_enable" name="thq_connect_settings[org_sync]" onChange="orgSync()" value="true" %s /> Disable <input onchange="orgSync()" type="radio" id="thq_org_sync_disable" name="thq_connect_settings[org_sync]" value="false" %s /></label><br>
	<div id="org_sync_options">
	<input type="checkbox" name="thq_connect_settings[sync_org_logo]" value="true" %s> Logo<br>
	<input type="checkbox" name="thq_connect_settings[sync_org_name]" value="true" %s> Blog Name<br>
	<input type="checkbox" name="thq_connect_settings[sync_org_timezone]" value="true" %s> Timezone<br>
	</div>
	<p class="description" id="thq_connect_org_sync_description">Update WordPress details with Organisation from TidyHQ after each login?</p>
	<script type="text/javascript">
	function orgSync() {
		if(document.getElementById("thq_org_sync_enable").checked) {
			document.getElementById("org_sync_options").style.display = "block";
		}
		else {
			document.getElementById("org_sync_options").style.display = "none";
		}
	}
	window.onload = orgSync();
	</script>',
            (isset( $this->options['org_sync'] ) && ($this->options['org_sync'] == 'true'))  ? ' checked="checked"' : '',
			(isset( $this->options['org_sync'] ) && ($this->options['org_sync'] == 'false')) ? ' checked="checked"' : '',
			(isset( $this->options['sync_org_logo'] ) && ($this->options['sync_org_logo'] == 'true')) ? ' checked="checked"' : '',
			(isset( $this->options['sync_org_name'] ) && ($this->options['sync_org_name'] == 'true')) ? ' checked="checked"' : '',
			(isset( $this->options['sync_org_timezone'] ) && ($this->options['sync_org_timezone'] == 'true')) ? ' checked="checked"' : ''
        );
	} //end function
	
	/* Client ID */
	public function thq_connect_client_id_callback() {
		printf('<input type="text" id="thq_connect_client_id" required="required" style="width: 550px;" name="thq_connect_settings[client_id]" value="%s" />',
            isset( $this->options['client_id'] ) ? esc_attr( $this->options['client_id']) : ''
        );
	} //end function
	/* Client Secret */
	public function thq_connect_client_secret_callback() {
				printf(
			'<input type="password" id="thq_connect_client_secret" required="required" style="width: 550px;" name="thq_connect_settings[client_secret]" value="%s" /><button id="clientsecrettoggle" class="show"><i></i> Show</button>
			<script type="text/javascript">
			jQuery(function () {
  jQuery("#thq_connect_client_secret").each(function (index, input) {
    var $input = jQuery(input);
    jQuery("button#clientsecrettoggle").click(function () {
      var change = "";
      if (jQuery(this).attr("class") === "show") {
        jQuery(this).attr("class", "hide").html("<i></i> Hide");
        change = "text";
      } else {
        jQuery(this).attr("class", "show").html("<i></i> Show");
        change = "password";
      }
      var rep = jQuery("<input type=\'" + change + "\' />")
        .attr("id", $input.attr("id"))
        .attr("name", $input.attr("name"))
        .attr("class", $input.attr("class"))
				.attr("style", $input.attr("style"))
        .val($input.val())
        .insertBefore($input);
      $input.remove();
      $input = rep;
			 event.preventDefault();
      event.stopPropagation();
    }).insertAfter($input);
  });
});
			</script>',isset( $this->options['client_secret'] ) ? esc_attr( $this->options['client_secret']) : ''
        );
	} //end function
	/* Profile Pic default */
	public function thq_connect_profile_pic_default_callback() {
		printf('<input type="radio" id="thq_connect_profile_pic_default" name="thq_connect_settings[profile_pic_default]" value="gravitar" %s /> Gravitar<br><input type="radio" id="thq_connect_profile_pic_default" name="thq_connect_settings[profile_pic_default]" value="tidyhq" %s /> TidyHQ<p class="description" id="profile_pic_default_desc">WordPress will use the above as a priority and the other as a fallback if one does not exist.',
			(!empty($this->options['profile_pic_default']) && $this->options['profile_pic_default'] == "gravitar") ? 'checked="checked"' : '',
			(!isset( $this->options['profile_pic_default']) || $this->options['profile_pic_default'] == "tidyhq" ) ? 'checked="checked"' : ''
        );
	} //end function
	
	
	/* Event Colour */
	public function thq_connect_calendar_event_callback() {
		printf('<input type="text" id="thq_connect_calendar_event" name="thq_connect_calendar_settings[event_colour]" value="%s" style="background: %s; color: #fff;" class="thq-connect-cf" data-default-color="#0b88f1" /> <label><input type="checkbox" value="disabled" name="thq_connect_calendar_settings[event_flag]" %s /> Disable?</label><p class="description" id="thq_connect_calendar_event_description">The colour which Events show in the calendar.</p>',
		isset($this->options['event_colour']) ? $this->options['event_colour'] : '#0b88f1',
		isset($this->options['event_colour']) ? $this->options['event_colour'] : '#0b88f1',
		!isset($this->options['event_flag']) || (!empty($this->options['event_flag'] ) && $this->options['event_flag'] == 'enabled') ? '' : ' checked="checked"'
		);
	} //end function
	
	/* Task Colour */
	public function thq_connect_calendar_task_callback() {
		printf('<input type="text" id="thq_connect_calendar_task" name="thq_connect_calendar_settings[task_colour]" value="%s" style="background: %s; color: #fff;" class="thq-connect-cf" data-default-color="#66c1d2" /> <label><input type="checkbox" value="disabled" name="thq_connect_calendar_settings[task_flag]" %s /> Disable?</label><p class="description" id="thq_connect_calendar_task_description">The colour which Tasks show in the calendar.</p>',
		isset($this->options['task_colour']) ? $this->options['task_colour'] : '#66c1d2',
		isset($this->options['task_colour']) ? $this->options['task_colour'] : '#66c1d2',
		!isset($this->options['task_flag']) ||(!empty($this->options['task_flag'] ) && $ $this->options['task_flag'] == 'enabled') ? '' : ' checked="checked"'
		);
	} //end function
	
	/* Session Colour */
	public function thq_connect_calendar_session_callback() {
		printf('<input type="text" id="thq_connect_calendar_session" name="thq_connect_calendar_settings[session_colour]" value="%s" style="background: %s; color: #fff;" class="thq-connect-cf" data-default-color="#7266D2" /> <label><input type="checkbox" value="disabled" name="thq_connect_calendar_settings[session_flag]" %s /> Disable?</label><p class="description" id="thq_connect_calendar_session_description">The colour which Sessions show in the calendar.</p>',
		isset($this->options['session_colour']) ? $this->options['session_colour'] : '#7266D2',
		isset($this->options['session_colour']) ? $this->options['session_colour'] : '#7266D2',
		!isset($this->options['session_flag']) || (!empty($this->options['session_flag'] ) && $$this->options['session_flag'] == 'enabled' ) ? '' : ' checked="checked"'
		);
	} //end function
	
	/* Meeting Colour */
	public function thq_connect_calendar_meeting_callback() {
		printf('<input type="text" id="thq_connect_calendar_meeting" name="thq_connect_calendar_settings[meeting_colour]" value="%s" style="background: %s; color: #fff;" class="thq-connect-cf" data-default-color="#732e64" /> <label><input type="checkbox" value="disabled" name="thq_connect_calendar_settings[meeting_flag]" %s /> Disable?</label><p class="description" id="thq_connect_calendar_meeting_description">The colour which Meetings show in the calendar.</p>',
		isset($this->options['meeting_colour']) ? $this->options['meeting_colour'] : '#732e64',
		isset($this->options['meeting_colour']) ? $this->options['meeting_colour'] : '#732e64',
		!isset($this->options['meeting_flag']) ||(!empty($this->options['meeting_flag'] ) && $ $this->options['meeting_flag'] == 'enabled' ) ? '' : ' checked="checked"'
		);
	} //end function
	
	/* Invitation Subject */
	public function thq_connect_invitation_subject_callback() {
		printf(
			'<input type="text" id="thq_connect_invitation_subject" style="width: 550px" name="thq_connect_invitation_settings[invitation_subject]" value="%s" /> <p class="description" id="thq_connect_invitation_subject_description">The email subject.</p>',
			isset($this->options['invitation_subject']) ? $this->options['invitation_subject'] : 'Welcome to '.get_bloginfo('name')
			);
	} //end function
	
	/* Invitation Content */
	public function thq_connect_invitation_content_callback() {
		$content = !empty($this->options['invitation_content']) ? $this->options['invitation_content'] : 'Hi {firstname},<br>You have been added as a member of '.get_bloginfo('name').' and you can now log in. Your login email address and password are the same as what you would use to log in to TidyHQ.';
		
		$args = array(
			'textarea_name'=>'thq_connect_invitation_settings[invitation_content]'
		);
		wp_editor($content , 'thq-connect-invitation-content',$args );
		printf('<p class="description">Enter {firstname} {lastname} or {emailaddress} to automatically populate these fields.');
	} //end function
	
} //END CLASS

/* Create the page */
if( current_user_can('thq_connect_user') )
    $my_settings_page = new thqConnectSettingsPage();
?>
