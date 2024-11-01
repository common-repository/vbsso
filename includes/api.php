<?php
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
 * $Revision: 772 $:
 * $Date: 2012-10-11 14:55:47 +0300 (Чт, 11 окт 2012) $:
 */

function vbsso_get_plugin_version() {
    if (!function_exists('get_plugins'))
        require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    $plugin_folder = get_plugins('/' . plugin_basename(dirname(__FILE__) . '/../'));
    $plugin_file = VBSSO_PRODUCT_ID . '.php';
    return $plugin_folder[$plugin_file]['Version'];
}

if (strcmp($wp_version, '3.0.6') <= 0)
    require_once(ABSPATH . 'wp-includes/registration.php');

function vbsso_listener_report_error($error) {
    if (vbsso_get_platform_config_property(SHAREDAPI_PLATFORM_WORDPRESS, VBSSO_CONFIG_PROPERTY_LOG, false)) {
        //        TODO: WordPress has no it's own logging. Available plugins are http://wordpress.org/extend/plugins/tags/logging. Connect to one of that plugins later.
    }

    $code = !is_string($error) ? $error->get_error_code() : '';
    $message = !is_string($error) ? $error->get_error_message($code) : $error;
    $data = !is_string($error) ? $error->get_error_data($code) : '';

    return array(SHAREDAPI_EVENT_FIELD_ERROR_CODE => $code, SHAREDAPI_EVENT_FIELD_ERROR_MESSAGE => $message, SHAREDAPI_EVENT_FIELD_ERROR_DATA => $data);
}

//function vbsso_listener_user_generate_username($username, $email) {
//    $new_username = $username;
//
//    $user = get_userdatabylogin($new_username);
//    if ($user) {
//        $new_username = $email;
//
//        $user = get_userdatabylogin($new_username);
//        if ($user) {
//            $new_username = $username . '_' . $email;
//
//            $user = get_userdatabylogin($new_username);
//            if ($user) {
//                $index = 2;
//
//                $new_username = $username . $index;
//                while (get_userdatabylogin($new_username)) {
//                    $index++;
//                    $new_username = $username . $index;
//                }
//            }
//        }
//    }
//
//    return $new_username;
//}

function vbsso_listener_user_load($json, $create_user = false) {
    $vbsso_username = html_entity_decode($json[SHAREDAPI_EVENT_FIELD_USERNAME], NULL, get_option('blog_charset'));

    $user_by_email = get_user_by('email', $json[SHAREDAPI_EVENT_FIELD_EMAIL]);
    $user_by_login = get_user_by('login', $vbsso_username);

    // WP 3.0.x support
    $user_by_email = ($user_by_email instanceof WP_User) ? $user_by_email : new WP_User($user_by_email->ID);
    $user_by_login = ($user_by_login instanceof WP_User) ? $user_by_login : new WP_User($user_by_login->ID);

//    if ($user_by_email->ID) {
//        vbsso_insert_verification_info($user_by_email->data, $json);
//    }
//    if ($user_by_login->ID AND $user_by_email != $user_by_login) {
//        vbsso_insert_verification_info($user_by_login->data, $json);
//    }

    if (!$user_by_email->ID && !$user_by_login->ID && $create_user) {
        //$username = vbsso_listener_user_generate_username($json[SHAREDAPI_EVENT_FIELD_USERNAME], $json[SHAREDAPI_EVENT_FIELD_EMAIL]);
        $user_id = wp_create_user($vbsso_username, '', $json[SHAREDAPI_EVENT_FIELD_EMAIL]);

        if (!is_wp_error($user_id)) {
            $user = new WP_User($user_id);

            //Roles managing
            $new_roles = explode(',', $json[SHAREDAPI_EVENT_FIELD_USERGROUPS]);
            if ($vbsso_usergroups_assoc = json_decode(get_site_option(VBSSO_NAMED_EVENT_FIELD_USERGROUPS_ASSOC, NULL))) {
                foreach ($user->roles as $role) {
                    $user->remove_role($role);
                }
                foreach ($new_roles as $new_role) {
                    $user->add_role( $vbsso_usergroups_assoc->$new_role );
                }
            }

            $data = array('user_login' => $vbsso_username, 'user_nicename' => $vbsso_username, );
            $data = stripslashes_deep($data);

            global $wpdb;
            $res = $wpdb->update($wpdb->users, $data, array('ID' => $user->ID));
            if ($res === false) {
                $user = vbsso_listener_report_error('Unable to update user login' . $json[SHAREDAPI_EVENT_FIELD_USERNAME]);
            }

            return $user;
        } else {
            return vbsso_listener_report_error($user_id);
        }
    }

    return ($user_by_email) ? $user_by_email : vbsso_listener_report_error('Unable to load user: ' . $json[SHAREDAPI_EVENT_FIELD_EMAIL]);
}

function vbsso_listener_verify($json) {
    $supported = vbsso_get_supported_api_properties();
    foreach ($supported as $key => $item) {
        if (get_site_option($key) != $json[$item['field']]) {
            update_site_option($key, $json[$item['field']]);
        }
    }

    return array('data' => array(SHAREDAPI_EVENT_FIELD_VERIFY => true));
}

function vbsso_listener_authentication($json) {
    $user = wp_get_current_user(); // object exists for both guest and authenticated user always.

    // If current user is logged in and authentication event came from same user, we don't need to auth him again.
    $is_event_from_current_user = (
        is_user_logged_in() AND $user instanceof WP_User AND $user->data->user_email == $json[SHAREDAPI_EVENT_FIELD_EMAIL]
    ) ? true : false;

    if (!$is_event_from_current_user) {
        $u = vbsso_listener_user_load($json, true);
        if (!sharedapi_is_error_data_item($u)) {
            if ($user->ID != $u->ID) {
                vbsso_listener_logout($json);

                setcookie(VBSSO_NAMED_EVENT_FIELD_TIMEOUT, $json[SHAREDAPI_EVENT_FIELD_TIMEOUT], 0, SITECOOKIEPATH, SITECOOKIEPATH);
                setcookie(VBSSO_NAMED_EVENT_FIELD_MUID, $json[SHAREDAPI_EVENT_FIELD_USERID], 0, SITECOOKIEPATH, COOKIE_DOMAIN);

                wp_set_current_user($u->ID);
                wp_set_auth_cookie($u->ID, isset($json[SHAREDAPI_EVENT_FIELD_REMEMBERME]) && $json[SHAREDAPI_EVENT_FIELD_REMEMBERME]);
                do_action(VBSSO_NAMED_EVENT_FIELD_MUID, $u->data->user_login);
            }
        } else {
            return array('error' => $u);
        }
    }
}

function vbsso_listener_logout($json) {
    if (is_user_logged_in()) {
        wp_logout();
    }
}

function vbsso_listener_register($json) {
    $u = vbsso_listener_user_load($json, true);

    if (sharedapi_is_error_data_item($u)) {
        return array('error' => $u);
    }
}

function vbsso_listener_credentials($json) {
    $u = vbsso_listener_user_load($json);
    if (!sharedapi_is_error_data_item($u)) {
        $update = false;

        if (isset($json[SHAREDAPI_EVENT_FIELD_EMAIL2])) {
            $u->data->user_email = $json[SHAREDAPI_EVENT_FIELD_EMAIL2];
            $update = true;
        }

        if (isset($json[SHAREDAPI_EVENT_FIELD_USERNAME2])) {
//            $u->user_nicename = $u->user_login = vbsso_listener_user_generate_username($json[SHAREDAPI_EVENT_FIELD_USERNAME2], $u->user_email);
            $u->data->user_nicename = $u->data->user_login = html_entity_decode($json[SHAREDAPI_EVENT_FIELD_USERNAME2], NULL, get_option('blog_charset'));
            $update = true;
        }

        if (isset($json[SHAREDAPI_EVENT_FIELD_USERGROUPS2])) {
            $update = true;
        }

        if ($update) {
            if (isset($json[SHAREDAPI_EVENT_FIELD_USERGROUPS2]) AND $vbsso_usergroups_assoc = json_decode(get_site_option(VBSSO_NAMED_EVENT_FIELD_USERGROUPS_ASSOC, NULL))) {
                //Roles managing
                $new_roles = explode(',', $json[SHAREDAPI_EVENT_FIELD_USERGROUPS2]);
                if ($vbsso_usergroups_assoc = json_decode(get_site_option(VBSSO_NAMED_EVENT_FIELD_USERGROUPS_ASSOC, NULL))) {
                    foreach ($u->roles as $role) {
                        $u->remove_role($role);
                    }
                    foreach ($new_roles as $new_role) {
                        $u->add_role( $vbsso_usergroups_assoc->$new_role );
                    }
                }
            }

            if (!is_wp_error($user_id = wp_insert_user(get_object_vars($u->data)))) {
//                // Verify new userdata
//                if (isset($json[SHAREDAPI_EVENT_FIELD_EMAIL2])) $json[SHAREDAPI_EVENT_FIELD_EMAIL] = $json[SHAREDAPI_EVENT_FIELD_EMAIL2];
//                if (isset($json[SHAREDAPI_EVENT_FIELD_EMAIL2])) $json[SHAREDAPI_EVENT_FIELD_USERNAME] = $json[SHAREDAPI_EVENT_FIELD_USERNAME2];
//                vbsso_listener_user_load($json);

                $u = new WP_User($user_id);
                if ($u->data->user_login != $u->data->user_nicename) {
                    $data = array('user_login' => $u->data->user_nicename);
                    $data = stripslashes_deep($data);

                    global $wpdb;
                    $res = $wpdb->update($wpdb->users, $data, array('ID' => $u->ID));
                    if ($res === false) {
                        return array('error' => vbsso_listener_report_error('Unable to update user login: ' . $u->data->user_login));
                    }
                }
            } else {
                return array('error' => vbsso_listener_report_error($user_id));
            }
        }
    } else {
        return array('error' => $u);
    }
}

//function vbsso_listener_conflict_users() {
//    global $wpdb;
//
//    $list_to_return = array();
//    $unverified_users = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}verified_users` WHERE `localusername` != `vbusername` OR `localemail` != `vbemail`");
//
//    if ($unverified_users) {
//        foreach ($unverified_users as $unverified_user) {
//            $list_to_return[$unverified_user->localusername] = $unverified_user->localemail;
//        }
//    }
//
//    return array(SHAREDAPI_EVENT_FIELD_DATA => $list_to_return);
//}

sharedapi_data_handler(SHAREDAPI_PLATFORM_WORDPRESS, $wp_version,
    vbsso_get_plugin_version(),
    get_site_option(VBSSO_NAMED_EVENT_FIELD_API_KEY, SHAREDAPI_DEFAULT_API_KEY),
    array(
        SHAREDAPI_EVENT_VERIFY => 'vbsso_listener_verify',
        SHAREDAPI_EVENT_LOGIN => 'vbsso_listener_register',
        SHAREDAPI_EVENT_AUTHENTICATION => 'vbsso_listener_authentication',
        SHAREDAPI_EVENT_LOGOUT => 'vbsso_listener_logout',
        SHAREDAPI_EVENT_REGISTER => 'vbsso_listener_register',
        SHAREDAPI_EVENT_CREDENTIALS => 'vbsso_listener_credentials',
        SHAREDAPI_EVENT_CONFLICT_USERS => 'vbsso_listener_conflict_users'
    ));