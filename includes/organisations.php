<?PHP
/**
* THQ-CONNECT ORGANISATIONS.PHP
* Allows Organisation data from TidyHQ to input into WordPress.
*/

/* Load Styles */
#v2.1.1 as unsure if required since adding adminbar.php
//wp_enqueue_style('thq-connect-adminbar-style', plugin_dir_url(__FILE__).'../css/adminbar.css','','2.1.0');

/* Do Organisation Sync */
$organisations = new thqConnectOrganisations();

class thqConnectOrganisations {
  
  private $thqSettings;
  private $org;
  
  public function __construct() {
		$settings = new thqConnectSettings;
		$this->thqSettings = $settings->display();
     
    /* not logged in */
    if(!is_user_logged_in()) {
      //do nothing
    } //end if
    /* logged in */
    else {
      $get = new thqConnectGet();
      $this->org = $get->request('organization');
      
      /* If org sync is enabled */
      if(!empty($this->thqSettings['org_sync']) && $this->thqSettings['org_sync'] == 'true') {
        add_action('admin_init', array($this,'sync'));
        /* if logo sync enabled */
        if(!empty($this->thqSettings['sync_org_logo'])) { add_action('admin_head',array($this,'customLogo')); }
      } //end if
    } //end else
	} //end __construct
  
  /* display custom logo can't be added to css file due to toggle */
  public function customLogo(){
    ?>
    <style type='text/css'>
      .wp-admin #wp
	    #wp-admin-bar-site-name > .ab-item:before {
	      background-image: url('<?PHP echo $this->org['logo_url']; ?>') !important;
        background-size: contain;
	      background-repeat: no-repeat;
	      background-position: 0 0;
	      color:rgba(0, 0, 0, 0);
      }
      .wp-admin #wpadminbar #wp-admin-bar-site-name > a {
	      content: '';
       }
      #wpadminbar #wp-admin-bar-wp-logo.hover > .ab-item .ab-icon {
	      background-position: 0 0;
      }
      #adminmenu .wp-has-current-submenu .wp-submenu a, #adminmenu .wp-has-current-submenu.opensub .wp-submenu a, #adminmenu .wp-submenu a, #adminmenu a.wp-has-current-submenu:focus+.wp-submenu a, .folded #adminmenu .wp-has-current-submenu .wp-submenu a, #collapse-menu:hover, #wpadminbar .ab-item, #wpadminbar a.ab-item, #wpadminbar>#wp-toolbar span.ab-label, #wpadminbar>#wp-toolbar span.noticon, #wpadminbar #wp-admin-bar-user-info .display-name  {
	      color: #c0c0c0 !important;
      }
  </style>
  <?PHP
  }
  
  public function sync() {
    $updated = '';
    
    if(!empty($this->thqSettings['sync_org_name']) && $this->thqSettings['sync_org_name'] == true) {
		/* Get current site name */
		$blogname = get_option('blogname');
		/* Check if site name is not blank, TidyHQ name is not blank and site name does not match TidyHQ org name already */
		if(!empty($blogname) && !empty($this->org['name']) && $blogname != $this->org['name']) {
			/* update the name */
			update_option('blogname',$this->org['name']);
			/* set variable for notification */
			$updated = 'true';
			/* clear name so it is not repeated */
			unset($this->org['name']);
		} //end if
		/* either site name is blank, TidyHQ organisation name is blank or both names match */
		else {
			//Nothing to do
		} //end else
	} //end if
	
	/* Check if Sync Timezone option is set */
	if(!empty($this->thqSettings['sync_org_timezone']) && $this->thqSettings['sync_org_timezone'] == true) {
		/* get current timezone */
		$timezone = get_option('timezone_string');
		/* confirm no blanks and timezones don't already match */
		if(!empty($timezone) && !empty($this->org['time_zone']) && $timezone != $this->org['time_zone']) {
			/* update timezone */
			update_option('timezone_string',$this->org['time_zone']);
			/* variable for notification */
			$updated = 'true';
			/* clear timezone so it is not repeated */
			unset($this->org['time_zone']);
		} //end if
		/* If blanks or they match already */
		else {
			//nothing to do here
		} //end else
	} //end if
	/* Check if organisation is found */
	if(!empty($this->org)) {
		/* Update all other TidyHQ organisation details */
		foreach($this->org as $option => $value) {
			/* get the current value */
			$wp_option = get_option($option);
			/* confirm no blanks and values don't already match */
			if(!empty($wp_option) && !empty($value) && $wp_option != $value) {
				/* update the option */
				update_option($option,$value);
				/* variable for notification */
				$updated = 'true';
			} //end if
			/* There are blanks or they match already */
			else {
				//nothing to do here
			} //end else
		} //end foreach
	} //end if	
	/* Display notification if a change has been made */
	if(!empty($updated)) {
		thq_connect_error_notice('notice-success','The latest site details have been updated from TidyHQ.');
	} //end if
    
  } //end function
  
} //end class



?>
