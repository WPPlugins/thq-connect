/**
* THQ-CONNECT ADMIN.JS
* v2.2.6
* All admin page scripts
*/

/* Colour Picker code */
jQuery(document).ready(function($){
    $('.thq-connect-cf').wpColorPicker();
});

/* Alert for organisation dashboard link */
jQuery(document).ready(function(){
		jQuery('#toplevel_page_thq-connect-page ul li.wp-first-item a').on('click',function(e) {
      var usr = confirm('You will be redirected to TidyHQ and you may be required to log in again.');
      if(usr !== true) {
        e.preventDefault();  
      }      
		});
		jQuery('li#toplevel_page_thq-connect-page a.toplevel_page_thq-connect-page').on('click',function(e) {
      var usr = confirm('You will be redirected to TidyHQ and you may be required to log in again.');
      if(usr !== true) {
        e.preventDefault();  
      }      
		});
    /* alert for adminbar task link */
  	jQuery('#wp-admin-bar-thq-connect-outstanding-tasks a').on('click',function(e) {
      var usr = confirm('You will be redirected to TidyHQ and you may be required to log in again.');
      if(usr !== true) {
        e.preventDefault();  
      }      
		});
     /* alert for adminbar email link */
  	jQuery('#wp-admin-bar-thq-connect-unread-email a').on('click',function(e) {
      var usr = confirm('You will be redirected to TidyHQ and you may be required to log in again.');
      if(usr !== true) {
        e.preventDefault();  
      }      
		});
     /* alert for adminbar event link */
  	jQuery('#wp-admin-bar-thq-connect-upcoming-events a').on('click',function(e) {
      var usr = confirm('You will be redirected to TidyHQ and you may be required to log in again.');
      if(usr !== true) {
        e.preventDefault();  
      }      
		});
});
