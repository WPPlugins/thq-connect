=== THQ Connect ===

Contributors: mbelstead
Author URI: http://www.github.com/mbelstead
Donate link: http://thqconnect.belstead.net
Plugin URI: http://thqconnect.belstead.net
Tags: Organizations, TidyHQ, Administration, Associations, Management
Requires at least: 4.6
Tested up to: 4.8
Stable tag: 2.2.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

TidyHQ and Wordpress integration. THQ Connect not only allows your members to sign in to WordPress with their TidyHQ credentials but it has been enhanced to also share information such as contact details, events, meetings, tasks and more.  A TidyHQ organisation is required.  Head to <tidyhq.com> now and start making your organisation management better.


== Description ==
THQ Connect has been designed to reduce the need for different log in details for your members and admins.  THQ Connect gives your members and admins more tools, not only for your website, but opens the door for more plug ins while keeping all your main administrative functionality in TidyHQ.  To keep your website secure, if they do not have a WordPress account with your organisation, the plugin will allow them to log in but only create them as a user with basic WordPress access.  Visitors not "Connected" to your organisation will be asked to connect when they log in giving you more opportunities to gain new members!

**Key Features**
* Log in to WordPress using your TidyHQ email and password
* Display a widget in your sidebar to allow the visitor to log in with their TidyHQ credentials.
* Display a button in your sidebar to display if the visitor is "Connected" or direct them to "Connect" to gain new members.
* Display a calendar (similar to the TidyHQ Dashboard) on your website which includes all public events and meetings.  For those who are logged in, the calendar will also display private meetings, events and tasks.  Sessions coming soon.
* Allow your organisation name, logo and more be shared across your website and administration platforms.
* Allow your contact information and more be shared across both platforms.
* Invite your TidyHQ users to join your WordPress site.

**Coming Soon**
* Parent/Child organisation support
* Multisite support
* Language support
* (Got more ideas?  Let us know!)

THQ Connect is Open-Source and available on [GitHub](http://www.github.com/mbelstead/thq-connect/).  If you have any suggestions, ideas or would like to contribute to THQ Connect, email [support@thqconnect.ga]('mailto:support@thqconnect.ga')

For more information, head to THQ Connect -> Help in your WordPress admin menu.


== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/thq-connect` directory, or install the plugin through the WordPress plugins screen directly.
2. Activate the plugin through the 'Plugins' screen in WordPress
3. A TidyHQ Application is required.  You can add one [here](https://dev.tidyhq.com/oauth_applications).  Your application name should be THQ Connect and your Callback URL should be your site's WordPress URL (ie. http://yoursite.com).
4. Use the Settings->THQ Connect screen to add Client ID & Client Secret from step 3.
5. There are several other configurable options on the above screen.  It is recommended to use Profile Sync & Org Sync.
6. Log out of WordPress, then log in with your TidyHQ credentials. Note: You will need to have logged in via TidyHQ to enable additional TidyHQ functions.
7. Add the login widget, form shortcode [thq_connect_login_form] somewhere on your site, or alternatively use the wp-login.php and select "TidyHQ" as your log in option.


== Frequently Asked Questions ==
= What is TidyHQ =
TidyHQ is a cloud-based platform designed to help streamline the administration and management of organisations.  For more information about how TidyHQ can help your organisation visit <tidyhq.com>.

= Can my members log in to WordPress using TidyHQ credentials? =
Provided the TidyHQ credentials are correct, THQ Connect will allow them to log in, however they will not have any access to additional WordPress or TidyHQ functions unless granted through the WordPress users or TidyHQ Users & Roles.

= Can people not associated with my organisation log in? =
As above.  Provided the TidyHQ credentials are correct, THQ Connect will allow them to log in, however they will not have any access to additional WordPress or TidyHQ functions.

= Is my information secure? =
Data used by THQ Connect is read-only.  THQ Connect promises to never store confidential information from TidyHQ.  Login information is sent directly to TidyHQ for validation.

= I logged in, but my WordPress is telling me I am not connected.  Why? =
Double check your email address matches that of your contact profile in TidyHQ.  For the plug in to work correctly they must match.

= Can I still log in with my normal WordPress username/password? =
Yes.  Simply select the "WordPress" radio button on your log in form.  Do be aware that not all THQ Connect functions may be available when logging in this way.

= My WordPress site name changed when I logged in.  What happened? =
If "Organisation Sync" is enabled, THQ Connect will automatically update your site name with your TidyHQ organisation name.  If you do not want this to happen, you can disable it in THQ Connect Settings.

= Is WordPress multisite supported =
Not at this stage, however it will be.

= My organisation is a child organisation of an association. Will THQ Connect work for me? =
Again no.  It is on the list of enhancements to come.

= I have a query or suggestion.  Who do I contact? =
If you email [thqconnect@belstead.net]('mailto:thqconnect@belstead.net'), I will get back to you as soon as I can.


== Screenshots ==
1. Login screen.  Select WordPress for normal user access or TidyHQ to use TidyHQ credentials.  Alternatively add [thq_connect_login_form] or use the Login via TidyHQ widget to display a login form somewhere on your website.
2. THQ Connect settings; Menu option to direct user to TidyHQ; New email/task/event count in admin bar; Profile pics;


== Changelog ==

= 2.2.1 =
* Urgent bug fix - issue stopping plugin to load.
* Added missing file.
* Fixed broken image in THQ Connect dashboard widget.
* Now tested with WordPress 4.8
* Added "Remember Me" to log in forms.  This will remember your email address for next time.
* Ammended Help.
* If you experience any issues using THQ Connect, please email [thqconnect@belstead.net]('mailto:thqconnect@belstead.net')

= 2.2 =
* Contacts:  You can now select a contact from a group and provided they have an email address, you can invite them to your WordPress site.  You can also assign an existing WordPress user to a Contact enabling them to take advantage of Profile Match.  Customise your invitation email in your settings.
* Calendar: Meetings are fixed.  Both Public and Private meetings will now display on your calendar when required.
* Calendar: Buttons below the public calendar now toggle the event types (just like the TidyHQ dashboard).
* Settings: Fixed bug when attempting to save Calendar settings.
* Settings: Client Secret is no longer displayed as visible characters (unless you click the button to show).
* General: Profile Sync and Organisation Sync functions renamed to Profile Match and Organisation Match.
* General: THQ Connect menu renamed to TidyHQ.
* General: Bug fixes.
* If you experience any issues using THQ Connect, please email [support@thqconnect.ga]('mailto:support@thqconnect.ga')

= 2.1 =
* New widgets available!  The _TidyHQ Connect Button_ widget allows you to display a button (similiar to the one on your TidyHQ site) to allow visitors to connect to your organisation.  The _Login via TidyHQ_ widget allows you to add a login form to your sidebar.
* Calendar improvements: significant reduce in loading time.  Adjusted styles to suit majority of themes more.
* THQ Connect functionality will now be hidden until Client ID, Client Secret and Domain Prefix are entered.  Until these are entered you will need to use the "Settings" link from the Plugins page or from your notification.
* Updated help.
* You will now be notified about leaving WordPress for TidyHQ when clicking Task, Events or Emails in the admin bar.
* Minor bug fixes.
* Several non-visible, back-end changes in prepatation for new things :)
* If you experience any issues using THQ Connect, please email [thqconnect@mail.tidyhq.com]('mailto:thqconnect@mail.tidyhq.com')

= 2.0 =
* Org Sync:  You can now choose what information to copy from TidyHQ.
* Settings:  Settings page moved from General Settings to THQ Connect menu.
* Profile Pic: If you don't have a TidyHQ profile pic, WordPress will check for a Gravitar.
* Calendar:  Add the [thq_connect_calender] shortcode to display a TidyHQ calender with all your organisation's public meetings and events.  Check out THQ Connect Settings > Calendar for more options.
* Support for users who change their TidyHQ email address.
* Minor bug fixes & tweaks.
* If you experience any issues using THQ Connect, please email [thqconnect@mail.tidyhq.com]('mailto:thqconnect@mail.tidyhq.com')

= 1.6 =
* TidyHQ site selection fixed.
* Org Sync is also working correctly.

= 1.5 =
* TidyHQ site selection.  You can now opt to log in to a different site than the default (you will still need to have access to that site).  Don't worry, your site will only sync with your set default organisation.  You can also disable this option in THQ Connect Settings.
* Updated help.
* Bug fixes:
** Fixed "No changes were made" when changes were indeed made.
** Initials now display correctly in coloured avatar box
** Fixed broken image on Help & Settings pages
** Fixed broken Dashboard widget

= 1.4 =
* Updated readme including up to date installation instructions and screenshots.
* Minor bug fixes.

= 1.3 =
* Uninstaller will now delete THQ Connect capabilities and options
* Bug fixes:
** Certain options now will not be triggered unless THQ Connect settings are configured.

= 1.2 =
* Error messages now display when you do not have access.
* Additional logging for debug.
* Updated THQ Connect Settings page and removed some settings which stopped the plugin working correctly.
* Various additional code changes and bug fixes.

= 1.1 =
* Updated Help
* For those without Avatars a coloured box will now appear in it's place - similar to TidyHQ.

= 1.0 =
* First public version
* We have a name.  Say hello to THQ Connect!

== Upgrade Notice ==
* If updating from a version prior to 2.0, it is recommended completely removing the plugin and reinstalling.

