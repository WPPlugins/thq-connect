<?PHP
/**
* THQ-CONNECT ADMINBAR.PHP
* Everything for the WP Admin Bar.  Both public and admin pages
*/
	
/* check if user is logged in and has the right capability */
if(is_user_logged_in() && current_user_can('thq_connect_user')) {
	add_action( 'wp_before_admin_bar_render', 'thq_connect_admin_bar' );
	wp_enqueue_style('thq-connect-adminbar-styles', plugin_dir_url(__FILE__).'../css/adminbar.css','','2.1.0');
} //end if
	// add links/menus to the admin bar
function thq_connect_admin_bar() {
	global $wp_admin_bar;
	/* get task/event/meeting counts */
	$counts = new thqConnectAdminBarCounts();
	$totals = $counts->total();
	/* get THQ settings */
	$settings = new thqConnectSettings();
	$thq = $settings->display();
	/* will display as domain_prefix is set */
	if(!empty($thq['domain_prefix'])) {
		/* start of link to TidyHQ.  All start the same */
		$href = "https://".$thq['domain_prefix'].".tidyhq.com";
		/* Email count has a value */
		if(!empty($totals['email_count'])) {
			$label = " unread email";
			$label .= $totals['email_count'] > 1 ? "s" : "";
			$label .= "!";
			$wp_admin_bar->add_menu( array(
				'id'     => 'thq-connect-unread-email',
				'title'  => '<span class="ab-icon"></span>'.__( $totals['email_count'], 'some-textdomain' ),
				'href'   => $href.'/communicate/inbox',
				'meta'   => array(
					'target'   => '_self',
					'title'    => __( $totals['email_count'].$label, 'some-textdomain' ),
					'html'     => '',
				),
				)
			);
		} //end if
		/* Event count has a value */
		if(!empty($totals['event_count'])) {
			$label = " upcoming event";
			$label .= $totals['event_count'] > 1 ? "s" : "";
			$label .= "!";
			$wp_admin_bar->add_menu( array(
				'id'     => 'thq-connect-upcoming-events',
				'title'  => '<span class="ab-icon"></span>'.__( $totals['event_count'], 'some-textdomain' ),
				'href'   => $href.'/member/events',
				'meta'   => array(
					'target'   => '_self',
					'title'    => __( $totals['event_count'].$label, 'some-textdomain' ),
					'html'     => '',
				),
			)
			);
		} //end if
		/* Task count has a value */
		if(!empty($totals['task_count'])) {
			$label = " outstanding task";
			$label .= $totals['task_count'] > 1 ? "s" : "";
			$label .= "!";
			$wp_admin_bar->add_menu( array(
				'id'     => 'thq-connect-outstanding-tasks',
				'title'  => '<span class="ab-icon"></span>'.__( $totals['task_count'], 'some-textdomain' ),
				'href'   => $href.'/member/tasks',
				'meta'   => array(
					'target'   => '_self',
					'title'    => __( $totals['task_count'].$label, 'some-textdomain' ),
					'html'     => '',
				),
			)
			);
		} //end if
	} //end if
} //end function

class thqConnectAdminBarCounts {
  private $me;
  
  public function __construct(){
    $request = new thqConnectGet();
    $this->me = $request->request('contacts/me');
  } //end construct
  
/* get the counts */
public function total(){
	$request = new thqConnectGet();
	
	/* get the emails */
	$emails = $request->request('emails',array(
		'read'=>'false',
		'deleted'=>'false',
		'junk'=>'false',
		'archived'=>'false',
		'way'=>'inbound',
		'type'=>'email',
		'limit'=>'100')); //Max 100 emails to make run faster
	
	/* Make totals match, if 100, it will show more than 100 */
	if(!empty($emails)) {
		$email_count = (count($emails) == 100) ? "100+" : count($emails);
	} //end if
	
	/* Get the events */
	$now = date('c'); //only looking for events after today
	$events = $request->request('events',array('start_at'=>$now,'limit'=>'100'));
	
	/* Make totals match, if 100, it will show more than 100 */
	if(!empty($events)) {
		$event_count = (count($events) == 100) ? '100+' : count($events);
	} //end if
	
	/* Get the Tasks */
	$tasks_get = 'contacts/'.$this->me['id'].'/tasks';
	$tasks = $request->request($tasks_get,array('completed'=>'false','limit'=>'100'));
	
	/* Make totals match, if 100, it will show more than 100 */
	if(!empty($tasks)) {
		$task_count = (count($tasks) == 100) ? '100+' : count($tasks);
	} //end if
	
	/* create the array to return */
	$counts = array();
	
	/* If the count is not empty, return the number */
	if(!empty($email_count)) { $counts['email_count'] = $email_count; }
	if(!empty($event_count)) { $counts['event_count'] = $event_count; }
	if(!empty($task_count)) { $counts['task_count'] = $task_count; }
	
	/* return the array */
	return $counts;
} //end function
} //end class

?>
