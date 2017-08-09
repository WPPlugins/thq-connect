<?PHP
/**
* THQ-CONNECT UNINSTALL.PHP
* Removes access capabilities and all THQ-Connect database settings
*/

#2.1.0 Uninstall stops when this is enabled.
//if(!defined('WP_UNINSTALL_PLUGIN')) {
//	die;
//} //end if

	/* remove options from DB */
	delete_option('thq_connect_settings');
	
	/* delete cap from roles */
	foreach( get_editable_roles() as $role_name => $role_info){
		$role = get_role($role_name);
		if(isset($role_info['capabilities']['thq_connect_user'])) {
			$role->remove_cap('thq_connect_user');
			} //end if
	} //end foreach

	/* delete meta */
    $users = get_users();

    foreach ($users as $user) {
        delete_user_meta($user->ID, 'thq_connect_tidyhq_id');
				delete_user_meta($user->ID, 'thq_connect_token');
				delete_user_meta($user->ID, 'thq_connect_domain_prefix');
    } //end foreach
?>
