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

function vbsso_get_wordpress_custom_config() {
    return array(
	'log' => true,
        'override-links' => true,
    );
}
