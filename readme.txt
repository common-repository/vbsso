=== vBSSO ===
Contributors: xeagle
Donate link: http://www.vbsso.com/
Tags: single sign-on, sign on, authentication, vbulletin, wordpress, mediwiki, joomla, drupal, moodle, interprise shopping cart
Requires at least: 3.0
Tested up to: 3.0 - 3.5
Stable tag: 1.2.7

Provides universal Secure Single Sign-On between vBulletin and different popular platforms like WordPress.

== Description ==

= Support is available at http://www.vbsso.com only =

Provides universal Single Sign-On feature so that WordPress can use the vBulletin user database to manage authentication
and user profile data. There is documentation available so that it can be extended to other platforms.

This plugin is provided as is. Support and additional customizations are available at an hourly rate.

Plugin doesn't share any user related information with any third party side. It strongly synchronizes the information
between your own platforms connected personally to your vBulletin instance.

Plugin doesn't revert already sync data back to its original state if you decide to disable plugin later.

More details are available at http://www.vbsso.com

== Installation ==

= Support is available at http://www.vbsso.com only =

This section describes how to install the plugin and get it working.

= Installation =
1. Download WordPress vBSSO.
2. Unzip and upload folder "vbsso" to `/wp-content/plugins/` directory of your WordPress installation
3. Log in to WordPress as administrator.
4. Navigate to `Plugins` or `Network -> Plugins` (in case of enabled network) section and activate vBSSO plugin.

= Configuration =
1. Log in to your vBulletin control panel as administrator.
2. Navigate to `vBSSO` section.
3. Modify your default Platform Shared Key by setting it to more secure unreadable phrase to encrypt exchanged data.
4. Save Changes.

5. Log in to your vBulletin control panel as administrator.
6. Navigate to `vBSSO` section.
7. Expand section and click on the `Platforms` link.
8. Copy `Platform Url` link and `Shared Key` field from WordPress installation to vBulletin.
9. Click on `Connect` button to connect your new platform.
10. Back to WordPress vBSSO Settings page and verify that API Connections fields are filled out.

= SSL Configuration =
1. Modify wp-config.php
2. Add the following code "define('FORCE_SSL_ADMIN', true);" without quotes to top of the configuration file.
3. Add the following code "define('FORCE_SSL_LOGIN', true);" without quotes to top of the configuration file.
It's important to have SSL settings at the beginning of configuration file. For more details, please take a look at http://codex.wordpress.org/Administration_Over_SSL.

More details are available at http://www.vbsso.com/

== Frequently Asked Questions ==

= Support is available at http://www.vbsso.com only =

More details are available at http://www.vbsso.com/

== Screenshots ==

= Support is available at http://www.vbsso.com only =

1. vBSSO Settings.
2. Footer Link Settings.
3. Connect to vBulletin.

== Changelog ==

= Support is available at http://www.vbsso.com only =

= 1.2.7 =
* Fixed "Call to undefined method stdClass::add_role()" issue.
* Prepared the feature to handle broken user accounts between WordPress and vBulletin.
* Enhancements and bugs fixes.
* [Updated May 21, 2013]

= 1.2.6 =
* Improved vBSSO Login widget.
* Improved support of vBSSO WordPress SSL mode.
* Implemented vBSSO vBulletin usergroups API.
* Enhancements and bugs fixes.
* [Updated May 20, 2013]

= 1.2.5 =
* Fixed the issue with inability to log out.
* Enhancements and bugs fixes.
* [Updated February 17, 2013]

= 1.2.4 =
* Adjusted vBSSO Widget once WordPress is not connected to vBSSO.
* Enhancements and bugs fixes.
* [Updated September 23, 2012]

= 1.2.3 =
* Added customizable vBSSO "Login" widget.
* Added compatibility with WordPress 3.x.
* Added support of non-Latin characters in username (might be restricted by vBulletin username restriction rules).
* Enhancements and bugs fixes.
* [Updated September 3, 2012]

= 1.2.2 =
* Added support of vBulletin Profile Page connected to WordPress.
* Added support of vBulletin Author Page connected to WordPress.
* Added support of "Registration" url connected to vBulletin.
* Enhancements and bugs fixes.
* [Updated August 10, 2012]

= 1.2.1 =
* Fixed "Use of undefined constant IS_PROFILE_PAGE - assumed 'IS_PROFILE_PAGE'".
* Fixed an issue when sometimes avatars are not fetched from vBulletin and displayed.
* Enhancements and bugs fixes.
* [Updated June 22, 2012]

= 1.2 =
* Disabled E_DEPRECATED setting in error reporting (since PHP 5.3.0 only) to make PHP ignore "Deprecated: Function set_magic_quotes_runtime()" used in WordPress.
* Implemented fetching of blog avatars required for "Blogs Directory" (http://premium.wpmudev.org/project/blogs-directory) plugin from vBulletin. The blog avatar is an user avatar fetched of user who has a "Blogger" role in WordPress blog.
* Added an option to fetch or not avatars from vBulletin in Settings page.
* Improved installation process by adding a check for the required PHP extension before the plugin is completely activated.
* Fixed the issue with saving footer link option.
* Hid unnecessary options from vBSSO Settings page.
* [Updated September 28, 2011]

= 1.1 =
* Overrode lostpassword_url to let vBSSO take care of this functionally.
* Fixed the logging condition in case if we are unable to find user when try to sync user credentials.
* Added config.custom.default.php file as a config sample.
* Fixed css to display "SSO provided By" text correctly in different theme layouts.
* [Updated September 18, 2011]

= 1.0 =
* First version
* [Created September 10, 2011]

== Upgrade Notice ==

More details are available at http://www.vbsso.com/
