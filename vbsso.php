<?php
/*
Plugin Name: vBulletin Single Sign On (vBSSO)
Plugin URI: http://www.vbsso.com/platforms/wordpress
Description: Provides universal Secure Single Sign-On between vBulletin and different popular platforms like WordPress.
Author: www.vbsso.com
Version: 1.2.7
Author URI: http://www.vbsso.com
License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
Network: true
Compatibility: 3.x
*/

/**
 * -----------------------------------------------------------------------
 * vBSSO is a plugin that helps you connect to different software platforms
 * via secure Single Sign-On.
 *
 * Copyright (c) 2011-2013 vBSSO. All Rights Reserved.
 * This software is the proprietary information of vBSSO.
 *
 * Author URI: http://www.vbsso.com
 * License: GPL version 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * -----------------------------------------------------------------------
 * $Revision: 1140 $:
 * $Date: 2013-05-21 18:21:26 +0300 (Вт, 21 май 2013) $:
 */

require_once(dirname(__FILE__) . '/config.php');

//add_action( 'plugins_loaded', 'vbsso_update_db' );
//function vbsso_update_db() {
//    global $wpdb;
//
//    $vbsso_db_version = '1.0'; //upgradeable variable
//
//    $table_name = $wpdb->prefix . "verified_users";
//
//    if( get_site_option('vbsso_db_version') != $vbsso_db_version) {
//        //install
//        if ($wpdb->get_var("show tables like '$table_name'") != $table_name) {
//            $sql = "CREATE TABLE `$table_name` (
//                `id` BIGINT NOT NULL AUTO_INCREMENT ,
//                `userid` BIGINT NOT NULL ,
//                `localusername` VARCHAR(60) NOT NULL ,
//                `localemail` VARCHAR(100) NOT NULL ,
//                `vbusername` VARCHAR(60) NOT NULL ,
//                `vbemail` VARCHAR(100) NOT NULL ,
//                PRIMARY KEY ( `id` )
//                );";
//        }
//
//        //updates
//
//
//        //commit
//        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
//        dbDelta( $sql );
//
//        update_option('vbsso_db_version', $vbsso_db_version);
//    }
//}


/**
 * Register Activation Hook
 */
register_activation_hook(__FILE__, 'vbsso_register_activation_hook');
function vbsso_register_activation_hook() {
    $nloaded_extensions = vbsso_verify_loaded_extensions();

    if (count($nloaded_extensions)) {
        wp_die('Please install these PHP extensions `' . join(', ', $nloaded_extensions) . '` before installing or upgrading this product!');
    }
}

///**
// * Register Uninstall Hook
// */
//register_uninstall_hook(__FILE__, 'vbsso_register_uninstall_hook');
//function vbsso_register_uninstall_hook(){
//    global $wpdb;
//    $table_name = $wpdb->prefix . "verified_users";
//    $wpdb->query("DROP TABLE IF EXISTS $table_name");
//}

/**
 * Profile Url Redirection
 */
if (get_site_option(VBSSO_NAMED_EVENT_FIELD_SHOW_VBULLETIN_PROFILE, 1) && get_site_option(VBSSO_NAMED_EVENT_FIELD_PROFILE_URL, '') != '') {
    add_action( 'load-profile.php', 'vbsso_load_profile_hook' );
}
function vbsso_load_profile_hook() {

    /*if current user is an 'administrator' do nothing*/
    global $current_user;
    if (!in_array('administrator', $current_user->roles)) {
        wp_redirect(get_site_option(VBSSO_NAMED_EVENT_FIELD_PROFILE_URL));
    }
}

if (get_site_option(VBSSO_NAMED_EVENT_FIELD_SHOW_VBULLETIN_AUTHOR_PROFILE, 1) && get_site_option(VBSSO_NAMED_EVENT_FIELD_PROFILE_URL, '') != '') {
    add_filter( 'author_link', 'vbsso_author_link_hook', 10, 2 );
}
function vbsso_author_link_hook( $link, $author_id ) {
    $email = (get_userdata($author_id)) ? get_userdata($author_id)->user_email : 0;
    return  get_site_option(VBSSO_NAMED_EVENT_FIELD_PROFILE_URL, '') . md5(strtolower($email));
}

/**
 * Register Url Filter
 */
if (vbsso_get_platform_config_property(SHAREDAPI_PLATFORM_WORDPRESS, VBSSO_CONFIG_PROPERTY_OVERRIDE_LINKS, true) && get_site_option(VBSSO_NAMED_EVENT_FIELD_REGISTER_URL, '') != '') {
    add_filter('register', 'vbsso_register_url_hook');
}
function vbsso_register_url_hook() {
    if ( ! is_user_logged_in() ) {
        if ( get_option('users_can_register') )
            $link = '<li><a href="'. sharedapi_url_add_destination(get_site_option(VBSSO_NAMED_EVENT_FIELD_REGISTER_URL, ''), 'server', '', get_site_option(VBSSO_NAMED_EVENT_FIELD_LID, '')) .'" rel="nofollow">' . __('Create your account') . '</a></li>';
        else
            $link = '';
    } else {
        $link = '<li><a href="' . admin_url() . '" rel="nofollow">' . __('Site Admin') . '</a></li>';
    }

    return $link;
}

/**
 * Login Url Filter
 */
if (vbsso_get_platform_config_property(SHAREDAPI_PLATFORM_WORDPRESS, VBSSO_CONFIG_PROPERTY_OVERRIDE_LINKS, true) && get_site_option(VBSSO_NAMED_EVENT_FIELD_LOGIN_URL, '') != '') {
    add_filter('login_url', 'vbsso_login_url_hook');
}
function vbsso_login_url_hook() {
    return sharedapi_url_add_destination(get_site_option(VBSSO_NAMED_EVENT_FIELD_LOGIN_URL, ''), 'server', '', get_site_option(VBSSO_NAMED_EVENT_FIELD_LID, ''));
}

/**
 * Logout Url Filter
 */
if (vbsso_get_platform_config_property(SHAREDAPI_PLATFORM_WORDPRESS, VBSSO_CONFIG_PROPERTY_OVERRIDE_LINKS, true) && get_site_option(VBSSO_NAMED_EVENT_FIELD_LOGOUT_URL, '') != '') {
    add_filter('logout_url', 'vbsso_logout_url_hook');
}
function vbsso_logout_url_hook() {
    return sharedapi_url_add_destination(get_site_option(VBSSO_NAMED_EVENT_FIELD_LOGOUT_URL, ''), 'server', '', get_site_option(VBSSO_NAMED_EVENT_FIELD_LID, ''));
}

/**
 * Lost Password Url Filter
 */
if (vbsso_get_platform_config_property(SHAREDAPI_PLATFORM_WORDPRESS, VBSSO_CONFIG_PROPERTY_OVERRIDE_LINKS, true) && get_site_option(VBSSO_NAMED_EVENT_FIELD_LOSTPASSWORD_URL, '') != '') {
    add_filter('lostpassword_url', 'vbsso_lostpassword_url_hook');
}
function vbsso_lostpassword_url_hook() {
    return sharedapi_url_add_destination(get_site_option(VBSSO_NAMED_EVENT_FIELD_LOSTPASSWORD_URL, ''), 'server', '', get_site_option(VBSSO_NAMED_EVENT_FIELD_LID, ''));
}

/**
 * Profile Personal Options Filter
 */
if (defined('IS_PROFILE_PAGE') && IS_PROFILE_PAGE) {
    add_action('profile_personal_options', 'vbsso_profile_personal_options_hook');
}
function vbsso_profile_personal_options_hook() {
    global $current_user;
    if (!in_array('administrator', $current_user->roles)) {
        echo '<link rel="stylesheet" type="text/css" href="' . VBSSO_WORDPRESS_PLUGIN_URL . 'assets/profile-overrides.css">';
    }
}

///**
// * Edit User Profile Update Filter
// */
//add_action('profile_update', 'vbsso_profile_update_hook');
//function vbsso_profile_update_hook($user_id) {
//    global $userdata, $user_ID;
//
//    if ($user_id) {
//        $uid = $user_ID;
//        wp_set_current_user($user_id);
//        vbsso_insert_verification_info($userdata);
//        wp_set_current_user($uid);
//    }
//}
//
//add_action( 'delete_user', 'vbsso_delete_user_hook' );
//function vbsso_delete_user_hook($user_id) {
//    global $wpdb;
//
//    if ($user_id) {
//        $wpdb->query('DELETE FROM `' . $wpdb->prefix . 'verified_users` WHERE `userid` = ' . $user_id);
//    }
//}
//
//function vbsso_insert_verification_info ($userdata, $json = array()) {
//    global $wpdb;
//
//    $updateinfo = array();
//    $updateinfo['localusername'] = $userdata->user_login;
//    $updateinfo['localemail'] = $userdata->user_email;
//    if (!empty($json)) {
//        $vbsso_username = html_entity_decode($json[SHAREDAPI_EVENT_FIELD_USERNAME], NULL, get_option('blog_charset'));
//        $updateinfo['vbusername'] = $vbsso_username;
//        $updateinfo['vbemail'] = $json[SHAREDAPI_EVENT_FIELD_EMAIL];
//    }
//
//    $table_name = $wpdb->prefix . "verified_users";
//    if ($u = $wpdb->get_row("SELECT * FROM `$table_name` WHERE `userid` = " . $userdata->ID)) {
//        $wpdb->update(
//            $table_name, $updateinfo, array( 'userid' => $userdata->ID )
//        );
//    } else {
//        $wpdb->insert(
//            $table_name,
//            array(
//                'userid' => $userdata->ID,
//                'localusername' =>$userdata->user_login,
//                'localemail' =>$userdata->user_email,
//                'vbusername' =>$vbsso_username,
//                'vbemail' =>$json[SHAREDAPI_EVENT_FIELD_EMAIL]
//            )
//        );
//    }
//}

/**
 * Footer Filter
 */
add_filter('wp_footer', 'vbsso_wp_footer_hook');
function vbsso_wp_footer_hook() {
    global $current_user;

    $footer_link = get_site_option(VBSSO_PLATFORM_FOOTER_LINK_PROPERTY, VBSSO_PLATFORM_FOOTER_LINK_SHOW_EVERYWHERE);
    if ($footer_link == (VBSSO_PLATFORM_FOOTER_LINK_SHOW_EVERYWHERE || VBSSO_PLATFORM_FOOTER_LINK_SHOW_ADMIN)) {
        echo VBSSO_PLATFORM_FOOTER_LINK_HTML;
    }

    echo VBSSO_PLATFORM_FOOTER_GA_HTML(sharedapi_get_platforms(SHAREDAPI_PLATFORM_WORDPRESS));
}

/**
 * Admin Footer Filter
 */
add_filter('admin_footer', 'vbsso_admin_footer_hook');
function vbsso_admin_footer_hook() {
    if (in_array(get_site_option(VBSSO_PLATFORM_FOOTER_LINK_PROPERTY, VBSSO_PLATFORM_FOOTER_LINK_SHOW_ADMIN), array(VBSSO_PLATFORM_FOOTER_LINK_SHOW_EVERYWHERE, VBSSO_PLATFORM_FOOTER_LINK_SHOW_ADMIN))) {
        echo VBSSO_PLATFORM_FOOTER_LINK_HTML;
    }

    echo VBSSO_PLATFORM_FOOTER_GA_HTML(sharedapi_get_platforms(SHAREDAPI_PLATFORM_WORDPRESS));
}

/**
 * Auth Cookie Expiration Filter.
 */
add_filter('auth_cookie_expiration', 'vbsso_auth_cookie_expiration_hook', 10, 3);
function vbsso_auth_cookie_expiration_hook($timeout, $user_id, $remember) {
    $vbsso_timeout = sharedapi_gpc_variable(VBSSO_NAMED_EVENT_FIELD_TIMEOUT, '', 'c');
    return !empty($vbsso_timeout) && $vbsso_timeout > 0 ? $vbsso_timeout : $timeout;
}

/**
 * Get Blog Avatar
 */
if (get_site_option(VBSSO_NAMED_EVENT_FIELD_FETCH_AVATARS, 1) && !function_exists('get_blog_avatar')) {
    function get_blog_avatar($id, $size = '96', $default = '', $alt = false) {
        static $bloggers;

        if (!$bloggers) {
            $bloggers = array();
        }

        if (!isset($bloggers[$id])) {
            $bloggers[$id] = '';

            $users = get_users(array('blog_id' => $id, 'role' => 'blogger'));
            if (count($users)) {
                $bloggers[$id] = $users[0]->user_email;
            }
        }

        return vbsso_get_avatar_hook('', $bloggers[$id], $size, $default, $alt);
    }
}

/**
 * Get Avatar Filter
 */
if (get_site_option(VBSSO_NAMED_EVENT_FIELD_FETCH_AVATARS, 1) && get_site_option(VBSSO_NAMED_EVENT_FIELD_AVATAR_URL, '') != '') {
    add_action('get_avatar', 'vbsso_get_avatar_hook', 10, 5);
}
function vbsso_get_avatar_hook($avatar, $id_or_email, $size = '96', $default = '', $alt = false) {
    $avatar_url = get_site_option(VBSSO_NAMED_EVENT_FIELD_AVATAR_URL, '');

    $safe_alt = '';
    if (false !== $alt) {
        $safe_alt = esc_attr($alt);
    }

    if (!is_numeric($size)) {
        $size = '96';
    }

    $email = '';
    if (is_numeric($id_or_email)) {
        $id = (int)$id_or_email;
        $user = get_userdata($id);
        if ($user) {
            $email = $user->user_email;
        }
    } elseif (is_object($id_or_email)) {
        // No avatar for pingbacks or trackbacks
        $allowed_comment_types = apply_filters('get_avatar_comment_types', array('comment'));
        if (!empty($id_or_email->comment_type) && !in_array($id_or_email->comment_type, (array)$allowed_comment_types)) {
            return false;
        }

        if (!empty($id_or_email->user_id)) {
            $id = (int)$id_or_email->user_id;
            $user = get_userdata($id);
            if ($user) {
                $email = $user->user_email;
            }
        } elseif (!empty($id_or_email->comment_author_email)) {
            $email = $id_or_email->comment_author_email;
        }
    } else {
        $email = $id_or_email;
    }

    if (!empty($email)) {
        $email_hash = md5(strtolower($email));
    }

    $out = $avatar_url . $email_hash;

    $avatar = "<img alt='{$safe_alt}' src='{$out}' class='avatar avatar-{$size} photo' height='{$size}' width='{$size}' />";

    return $avatar;
}

/**
 * Mail Filter
 */
add_filter('wp_mail', 'vbsso_disable_registration_email_filter_hook');
function vbsso_disable_registration_email_filter_hook($result = '') {
    extract($result);
    if (preg_match('/New .+ User/', $subject)) {
        $to = '';
        $subject = '';
        $message = '';
        $headers = '';
        $attachments = array();
        return compact('to', 'subject', 'message', 'headers', 'attachments');
    }
    return $result;
}

/**
 * Admin Menu Filter
 */
if (is_admin()) {
    add_action('admin_menu', 'vbsso_admin_menu_hook');
}
function vbsso_admin_menu_hook() {
    global $wpdb;

    if (in_array($wpdb->blogid, array(0, 1))) {
        add_options_page(VBSSO_PRODUCT_NAME . ' Options', VBSSO_PRODUCT_NAME, 'manage_options', 'vbsso_options', 'vbsso_options');
    }
}

function vbsso_get_vb_usergroups() {
    $context = stream_context_create(array(
        'http' => array(
            'header'  => "Authorization: Basic " . base64_encode(
                sharedapi_decode_data(
                    get_site_option(VBSSO_NAMED_EVENT_FIELD_API_KEY, SHAREDAPI_DEFAULT_API_KEY), get_site_option(VBSSO_NAMED_EVENT_FIELD_BAA_USERNAME, null)
                ) . ':' . sharedapi_decode_data(
                    get_site_option(VBSSO_NAMED_EVENT_FIELD_API_KEY, SHAREDAPI_DEFAULT_API_KEY), get_site_option(VBSSO_NAMED_EVENT_FIELD_BAA_PASSWORD, null)
                )
            )
        )
    ));

    return json_decode(file_get_contents(get_site_option(VBSSO_NAMED_EVENT_FIELD_USERGROUPS_URL), false, $context));
}

function vbsso_options() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    if (isset($_POST['submit_options']) && $_POST['submit_options'] == 1) {
        if (isset($_POST[VBSSO_NAMED_EVENT_FIELD_API_KEY])) update_site_option(VBSSO_NAMED_EVENT_FIELD_API_KEY, $_POST[VBSSO_NAMED_EVENT_FIELD_API_KEY]);
        update_site_option(VBSSO_NAMED_EVENT_FIELD_FETCH_AVATARS, $_POST[VBSSO_NAMED_EVENT_FIELD_FETCH_AVATARS]);
        update_site_option(VBSSO_NAMED_EVENT_FIELD_SHOW_VBULLETIN_PROFILE, $_POST[VBSSO_NAMED_EVENT_FIELD_SHOW_VBULLETIN_PROFILE]);
        update_site_option(VBSSO_NAMED_EVENT_FIELD_SHOW_VBULLETIN_AUTHOR_PROFILE, $_POST[VBSSO_NAMED_EVENT_FIELD_SHOW_VBULLETIN_AUTHOR_PROFILE]);
        update_site_option(VBSSO_PLATFORM_FOOTER_LINK_PROPERTY, $_POST[VBSSO_PLATFORM_FOOTER_LINK_PROPERTY]);

        if (get_site_option(VBSSO_NAMED_EVENT_FIELD_USERGROUPS_URL, null)) {
            $vb_usergroups = vbsso_get_vb_usergroups();
            $vbsso_usergroups_assoc = array();

            foreach ($vb_usergroups as $vb_usergroup) {
                $vbsso_usergroups_assoc[$vb_usergroup->usergroupid] = $_POST[VBSSO_NAMED_EVENT_FIELD_USERGROUPS_ASSOC . '_' . $vb_usergroup->usergroupid];
            }

            update_site_option(VBSSO_NAMED_EVENT_FIELD_USERGROUPS_ASSOC, json_encode($vbsso_usergroups_assoc));
        }
    }

    $sharedkey_name = VBSSO_NAMED_EVENT_FIELD_API_KEY;
    $sharedkey_value = get_site_option(VBSSO_NAMED_EVENT_FIELD_API_KEY, SHAREDAPI_DEFAULT_API_KEY);
    $sharedkey_title = VBSSO_NAMED_EVENT_FIELD_API_KEY_TITLE;

    $url = VBSSO_WORDPRESS_PLUGIN_URL . 'vbsso.php';
    $url_title = VBSSO_NAMED_EVENT_FIELD_LISTENER_URL_TITLE;

    $footer_link_description = VBSSO_PLATFORM_FOOTER_LINK_DESCRIPTION_HTML;

    if (in_array(get_site_option(VBSSO_PLATFORM_FOOTER_LINK_PROPERTY, VBSSO_PLATFORM_FOOTER_LINK_SHOW_ADMIN), array(VBSSO_PLATFORM_FOOTER_LINK_SHOW_EVERYWHERE, VBSSO_PLATFORM_FOOTER_LINK_SHOW_ADMIN))) {
        echo VBSSO_PLATFORM_FOOTER_LINK_HTML;
    }

    $fetch_avatars = get_site_option(VBSSO_NAMED_EVENT_FIELD_FETCH_AVATARS, 1) ? 'checked' : '';
    $fetch_avatars_name = VBSSO_NAMED_EVENT_FIELD_FETCH_AVATARS;
    $fetch_avatars_title = VBSSO_NAMED_EVENT_FIELD_FETCH_AVATARS_TITLE;

    $show_vb_authors_profiles = get_site_option(VBSSO_NAMED_EVENT_FIELD_SHOW_VBULLETIN_AUTHOR_PROFILE, 1) ? 'checked' : '';
    $show_vb_authors_profiles_name = VBSSO_NAMED_EVENT_FIELD_SHOW_VBULLETIN_AUTHOR_PROFILE;
    $show_vb_authors_profiles_title = VBSSO_NAMED_EVENT_FIELD_SHOW_VBULLETIN_AUTHOR_PROFILE_TITLE;

    $show_vb_profile = get_site_option(VBSSO_NAMED_EVENT_FIELD_SHOW_VBULLETIN_PROFILE, 1) ? 'checked' : '';
    $show_vb_profile_name = VBSSO_NAMED_EVENT_FIELD_SHOW_VBULLETIN_PROFILE;
    $show_vb_profile_title = VBSSO_NAMED_EVENT_FIELD_SHOW_VBULLETIN_PROFILE_TITLE;

    $disabled_field = (get_site_option(VBSSO_NAMED_EVENT_FIELD_LOGIN_URL, '')) ? 'disabled' : '';

    echo <<<EOD
<form name="form1" method="post" action="">
    <input type="hidden" name="submit_options" value="1">

    <h4>Footer Link</h4>
    {$footer_link_description}
    <p>
EOD;

    $footer_link_name = VBSSO_PLATFORM_FOOTER_LINK_PROPERTY;
    $footer_link = get_site_option(VBSSO_PLATFORM_FOOTER_LINK_PROPERTY, VBSSO_PLATFORM_FOOTER_LINK_SHOW_EVERYWHERE);
    if ($footer_link == VBSSO_PLATFORM_FOOTER_LINK_SHOW_ADMIN) {
        $footer_link = update_option(VBSSO_PLATFORM_FOOTER_LINK_PROPERTY, VBSSO_PLATFORM_FOOTER_LINK_SHOW_EVERYWHERE);
    }
    $options = vbsso_get_platform_footer_link_options();
    foreach ($options as $key => $title) {
        $checked = $key == $footer_link ? 'checked' : '';
        echo '<label for="footer_link_option_' . $key . '"><input type="radio" id="footer_link_option_' . $key . '" name="' . $footer_link_name . '" value="' . $key . '" ' . $checked . '> ' . $title . '</label><br/>';
    }

    echo <<<EOD
    </p>

    <h4>Platform</h4>
    <p>
        <input size="80" name="{$sharedkey_name}" value="{$sharedkey_value}" {$disabled_field} />
        <br/><span class="description">{$sharedkey_title}</span>
        <br/><span class="description">
EOD;
        echo VBSSO_NAMED_EVENT_FIELD_API_KEY_WARNING;
        echo <<<EOD
        </span><br/><br/>

        <input size="80" value="{$url}" readonly />
        <br/><span class="description">{$url_title}</span><br>
    </p>

    <h4>Settings</h4>
    <p>
        <input type="checkbox" id="{$fetch_avatars_name}" name="{$fetch_avatars_name}" value="1" {$fetch_avatars} />
        <label for="{$fetch_avatars_name}">{$fetch_avatars_title}</label> <br/>
    </p>

    <p>
        <input type="checkbox" id="{$show_vb_authors_profiles_name}" name="{$show_vb_authors_profiles_name}" value="1" {$show_vb_authors_profiles} />
        <label for="{$show_vb_authors_profiles_name}">{$show_vb_authors_profiles_title}</label> <br/>
    </p>

    <p>
        <input type="checkbox" id="{$show_vb_profile_name}" name="{$show_vb_profile_name}" value="1" {$show_vb_profile} />
        <label for="{$show_vb_profile_name}">{$show_vb_profile_title}</label> <br/>
    </p>

    <h4>User Groups Association (Default role is subscriber)</h4>
    <p>
EOD;
    if (get_site_option(VBSSO_NAMED_EVENT_FIELD_USERGROUPS_URL, null)) {
        $context = stream_context_create(array(
            'http' => array(
                'header'  => "Authorization: Basic " . base64_encode(
                    sharedapi_decode_data(
                        get_site_option(VBSSO_NAMED_EVENT_FIELD_API_KEY, SHAREDAPI_DEFAULT_API_KEY), get_site_option(VBSSO_NAMED_EVENT_FIELD_BAA_USERNAME, null)
                    )
                        . ':' .
                        sharedapi_decode_data(
                            get_site_option(VBSSO_NAMED_EVENT_FIELD_API_KEY, SHAREDAPI_DEFAULT_API_KEY), get_site_option(VBSSO_NAMED_EVENT_FIELD_BAA_PASSWORD, null))
                )
            )
        ));

        $vb_usergroups = vbsso_get_vb_usergroups();
        $vbsso_usergroups_assoc = json_decode(get_site_option(VBSSO_NAMED_EVENT_FIELD_USERGROUPS_ASSOC, null));

        echo '<table id="vbsso_usergroups">';
        echo '<tr style="text-align: center"><td>vBulletin Usergroups</td><td>Wordpress Roles</td></tr>';
        foreach ($vb_usergroups as $vb_usergroup) {
            $ugid = $vb_usergroup->usergroupid;
            echo '<tr><td>' . $vb_usergroup->title . '</td><td><select name="' . VBSSO_NAMED_EVENT_FIELD_USERGROUPS_ASSOC . '_' . $vb_usergroup->usergroupid .'">';
            wp_dropdown_roles(($vbsso_usergroups_assoc AND $vbsso_usergroups_assoc->$ugid) ? $vbsso_usergroups_assoc->$ugid : 'subscriber');
            echo '</select></td></tr>';
        }
        echo '</table>';
    } else {
        echo "This platform is not connected.";
    }
    echo <<<EOD
</p>

    <p class="submit">
        <input type="submit" name="Submit" class="button-primary" value="Save Changes" />
    </p>
</form>
EOD;
}

/**
 * Adds vBSSO Login Form Widget.
 */
class vbsso_login_form extends WP_Widget {

    public function __construct() {
        parent::__construct(
            'vbsso_login_form',
            'vBSSO Login Form',
            array( 'description' => __( 'vBSSO Login Form Widget' ), )
        );
    }

    public function widget( $args, $instance ) {
        global $user_ID;

        extract( $args );
        $title = apply_filters( 'widget_title', $instance['title'] );

        if (vbsso_get_platform_config_property(SHAREDAPI_PLATFORM_WORDPRESS, VBSSO_CONFIG_PROPERTY_OVERRIDE_LINKS, true) && get_site_option(VBSSO_NAMED_EVENT_FIELD_LOGIN_VBULLETIN_URL, '') != '') {
            echo $before_widget;
            if ( ! empty( $title ) ) echo $before_title . $title . $after_title;

            $metalinks = '';

            if (!is_user_logged_in()) {
                echo '<form action="' . get_site_option(VBSSO_NAMED_EVENT_FIELD_LOGIN_VBULLETIN_URL, '') . '" method="post">
                        <table cellpadding="0" cellspacing="0" border="0">
                            <tr>
                                <td><label for="username" style="margin-right:10px;">' . __('Login') . '</label></td>
                                <td><input class="input" type="text" name="vb_login_username" id="vb_username" style="width:100%; padding:3px;" accesskey="u" /></td>
                            </tr>
                            <tr>
                                <td><label for="password" style="margin-right:10px;">' . __('Password') . '</label></td>
                                <td><input class="input" type="password" name="vb_login_password" id="vb_password" style="width:100%; padding:3px;" /></td>
                            </tr>
                        </table>

                        <label for="vb_cookieuser"><input class="input" type="checkbox" name="cookieuser" value="1" id="vb_cookieuser" accesskey="c" />'.__('Remember me').'</label>
                        <input class="button-primary" type="submit" value="' . __('Login') . '" accesskey="s" />

                        <input type="hidden" name="do" value="login" />
                        </form>';

                $metalinks .= wp_register(null, null, false);
                $metalinks .= '<li><a href="' . wp_lostpassword_url() . '" rel="nofollow">' . __('Forgot your password?') . '</a></li>';
            } else {
                echo '<ul><li style="list-style-type: none;">' . sprintf( __('Howdy, %1$s'), wp_get_current_user()->display_name ) . '!</li></ul>';
                echo '<div id="vbsso_avatar" style="float:left; padding: 3px; border: 1px solid #ddd; border-radius:4px; -moz-border-radius: 4px; -webkit-border-radius: 4px; margin-right: 22px; margin-top: 5px; width:38px; height:38px;">'.get_avatar($user_ID, $size = '38').'</div>';
                $metalinks .= '<li><a href="' . admin_url() . '" rel="nofollow">' . __('Site Admin') . '</a></li>';
                $metalinks .= '<li><a href="' . site_url('wp-admin/profile.php') . '" rel="nofollow">' . __('Profile') . '</a></li>';
                $metalinks .= '<li><a href=" ' . wp_logout_url() . '" rel="nofollow">' . __('Logout') . '</a></li>';
            }

            echo '<ul>' . $metalinks . '</ul>';

            echo $after_widget;
        }
    }

    public function update( $new_instance, $old_instance ) {
        $instance = array();
        $instance['title'] = strip_tags( $new_instance['title'] );

        return $instance;
    }

    public function form( $instance ) {
        if ( isset( $instance[ 'title' ] ) ) {
            $title = $instance[ 'title' ];
        }
        else {
            $title = __( 'vBSSO Login Form' );
        }
        ?>
    <p>
        <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>"
               name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
    </p>
    <?php
    }

}

add_action( 'widgets_init', create_function( '', 'register_widget( "vbsso_login_form" );' ) );