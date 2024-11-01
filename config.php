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
 * $Revision: 1100 $:
 * $Date: 2013-05-10 15:01:11 +0300 (Пт, 10 май 2013) $:
 */

require_once(dirname(__FILE__) . '/includes/vbsso_shared.php');
if (file_exists(dirname(__FILE__) . '/config.custom.php')) {
    require_once(dirname(__FILE__) . '/config.custom.php');
}

if (!defined('ABSPATH')) {
    if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
        $previous_error_reporting = error_reporting(E_ALL);
        error_reporting($previous_error_reporting & ~E_DEPRECATED);
    }

    require_once(dirname(__FILE__) . '/../../../wp-config.php');
    require_once(dirname(__FILE__) . '/includes/api.php');
}

define ('VBSSO_WORDPRESS_PLUGIN_URL', WP_PLUGIN_URL . '/' . str_replace(basename(__FILE__), "", plugin_basename(__FILE__)));