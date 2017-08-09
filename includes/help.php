<?PHP
/**
* THQ-CONNECT HELP.PHP
* Help page information
*/

/* Show help page for those with access */
if( current_user_can('thq_connect_user')) {	$helpPage = new thqConnectHelpPage(); }

class thqConnectHelpPage {
	/* Construct */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'thq_connect_help_menu'), 103 );  //adds the menu item
	} //end __construct

	/* Menu Option */
	public function thq_connect_help_menu() {  //adds menu item
	        add_submenu_page(
				'thq-connect-page',
				'THQ Connect Help',
				'Help',
				'thq_connect_user',
				'thq-connect-help',
				array( $this,'thq_connect_help_page')
			);
	}  //end thq_help_menu

	/* Start of Page */
	public function thq_connect_help_page() {
	?>
<div class='wrap'>
	<h2><img src="<?PHP echo plugins_url( '../img/tidyhq-icon.png', __FILE__ ); ?>" style="height: 20px; width: 20px;"><?PHP echo get_admin_page_title(); ?></h2>
	<!-- Setting Up in TidyHQ -->
	<h3>Setting up in TidyHQ</h3>
	<ol>
		<li>For this plugin to work correctly, you will need to set up an application through TidyHQ.  <a href='https://dev.tidyhq.com/oauth_applications'>Click here to create application now</a>.  The application name should be "THQ Connect" and the redirect URL should be your website address.  ie <?PHP echo get_bloginfo('url'); ?> </li>
		<li>Once the application is created, take note of the <u>Client ID</u> and <u>Client Secret</u>.  These will need to be added into WordPress <a href='<?PHP echo admin_url('admin.php?page=thq-connect-settings'); ?>'>here</a>.</li>
		<li>You are required to also include your TidyHQ domain prefix.  This will allow functions such as Single Sign On and Profile Sync.  Without the domain prefix being entered, these functions will not work.</li>
		<li>When you have an application in TidyHQ and the details are entered into WordPress, you are ready to go.</li>
	</ol>
	
	<!-- Logging in via TidyHQ -->
	<h3>Logging in via TidyHQ</h3>
	<p>There are a few ways to take advantage of the TidyHQ single sign-on.</p>
	<ul>
		<li>Add the _THQ Connect Login Widget_ to a sidebar on your website.  This will add an easy to use log in form.</li>
		<li>Add [thq_connect_login_form] somewhere in your website to allow your members to log in via TidyHQ</li>
		<li>The standard WordPress login form now gives an option to Log in via TidyHQ.</li>
	</ul>
	
	<!-- Profile Sync -->
	<h3>Profile and Contact Match</h3>
	<p>This allows your information to be exactly the same as your contact profile in TidyHQ.  When this is enabled, all profile changes in WordPress are disabled.</p>

	<!-- Contacts -->
	<h3>Contacts</h3>
	<p><b>Contacts</b> allows administrators to invite TidyHQ Contacts to become WordPress users or "Link" a current WordPress user to a TidyHQ Contact to allow them to take advantage of Profile Match and Single Sign On.  Simply head to Contacts, select your group and choose the action.  The invitation for Contacts to become WordPress users can be edited in Settings.
	
	<!-- Organisation sync -->
	<h3>Organisation Match</h3>
	<p><b>Organisation Match</b> allows your TidyHQ organisation's details merge in to WordPress.  For this to work correctly, the default domain prefix must be your TidyHQ organisation's.  Your information (including site name) will be updated each time someone logs in to WordPress.</p>
	
	<!-- Calendar -->
	<h3>Calendar</h3>
	<p>Include <b>[thq_connect_calendar]</b> in one of your pages to display a calendar based off meetings, tasks &amp; events from TidyHQ! Options to customise your calendar can be found in <a href="<?PHP echo admin_url('admin.php?page=thq-connect-settings&tab=calendar'); ?>">THQ Connect Settings > Calendar</a>, alternatively you can use the shortcode (replace &lt;type&gt; with either event, task or meeting):<br>
	<ul>
		<li>[thq_connect_calender &lt;type&gt;_flag=enabled/disabled] - Override the default and show/hide on a specific calendar.</li>
		<li>[thq_connect_calender &lt;type&gt;_colour=red] - Change display colour on a specific calendar.  Can use colour name, Hex, RGB etc.</li>
	</ul>
	<i>Note: Sessions are not currently supported.</i>
	</p>
	
	<!-- About -->
	<h3>About THQ Connect</h3>
	<p>Version: <?PHP
	$plugin_data = get_plugin_data(plugin_dir_path( __FILE__ ).'../thq-connect.php');
	echo $plugin_data['Version'];
	?>
	<br>
	<?PHP
		echo $plugin_data['Description'];
		echo "<p style='text-align: center'><a href='http://www.tidyhq.com' title='Powered by TidyHQ'><img src='//cdn.tidyhq.com/assets/tc/powered_by-480ed8552269da66abc71511a0b9a7f1944b459d431e52cf5e891d927f2f0075.png' alt='Powered by TidyHQ'></a></p>";
	?>
	<!-- more help? -->
	<h3>Need more help?</h3>
	<p>You can send an email with any problems, questions or suggestions to <a href='mailto:thqconnect@belstead.net'>thqconnect@belstead.net</a>.  Note: As THQ Connect is not affiliated with TidyHQ all and only THQ Connect queries should go here.  TidyHQ support should be directed using the chat functionality on your TidyHQ site.</p>
</div><!-- end wrap -->
<?PHP
	} //end tidyhq_help_page
} //end class
?>
