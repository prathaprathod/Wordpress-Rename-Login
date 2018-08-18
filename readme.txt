=== Wordpress Rename Login ===

Contributors:      Prathap Rathod 
Tags:              login, login url, rename wp, wp login, rename login
Requires at least: 4.5
Tested up to:      4.9.8
Stable tag:        1.1.0
License:           GPL-3.0+

Change wp-login.php to anything you want.

== Description ==

**I don't offer support through the support forum.**

*Wordpress Rename Login* is a very light plugin that lets you easily and safely change wp-login.php to anything you want. You dont need any Extra Code Just Download Our Plugin and Change Your Wp-Login.php url to anything Start Enjoying.

This plugin is only maintained, which means we do not guarantee free support. Consider reporting a problem and be patient.

= Compatibility =

Requires WordPress 4.1 or higher. All login related things like the registration form, the lost password form, the login widget and the expired sessions just keep working.
All login related things such as the registration form, lost password form, login widget and expired sessions just keep working.
It’s also compatible with any plugin that hooks in the login form, including

* BuddyPress,
* bbPress,
* Limit Login Attempts,
* and User Switching.

Obviously it doesn’t work with plugins that *hardcoded* wp-login.php.

If you’re using a **page caching plugin** you should add the slug of the new login url to the list of pages not to cache.
For W3 Total Cache and WP Super Cache, this plugin will give you a message with a link to the field that you should update.

== Installation ==

1. Go to Plugins › Add New.
2. Search for *Wordpress Rename Login*.
3. Look for this plugin, download and activate it.
4. The page will redirect you to the settings. Wordpress Rename Login there.
5. You can change this option any time you want, just go back to Settings › Permalinks › Wordpress Rename Login.

== Frequently Asked Questions ==

= Forgot Login url  =

Go to your MySQL database and look for the value of `wrl_page` in the options table, or remove the `wordpress-rename-login` folder from your `plugins` folder, log in through wp-login.php and reinstall the plugin.

==Github==
https://github.com/prathaprathod/wordpress-rename-login

== Changelog ==
= 1.1.0 =

* Blocked access to wp-admin to prevent a redirect.

= 1.0 =

* Initial version.
