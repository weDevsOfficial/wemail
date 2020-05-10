<?php
/**
 * Plugin Name: weMail - Email Marketing Simplified With WordPress
 * Description: Send Beautiful Email Newsletters with WordPress
 * Plugin URI: https://wordpress.org/plugins/wemail/
 * Author: weDevs
 * Author URI: https://getwemail.io/?utm_source=wp-org&utm_medium=author-uri
 * Version: 0.15.0
 * License: GPL-3.0
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wemail
 * Domain Path: /i18n/languages
 *
 * Copyright (c) 2019 weMail (email: info@getwemail.io). All rights reserved.
 *
 * Released under the GPL license
 * http://www.opensource.org/licenses/gpl-license.php
 *
 * This is an add-on for WordPress
 * http://wordpress.org/
 *
 * **************************************************************************
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 * **************************************************************************
 */

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

class_exists( 'WeDevs\WeMail\WeMail' ) || require_once __DIR__ . '/vendor/autoload.php';

use WeDevs\WeMail\WeMail;

define( 'WEMAIL_FILE', __FILE__ );
define( 'WEMAIL_PATH', dirname( WEMAIL_FILE ) );

/**
 * Init the wemail plugin
 *
 * @since 1.0.0
 *
 * @return WeDevs_WeMail
 */
function wemail() {
    return WeMail::instance();
}

// kick it off
wemail();

/**
 * Add custom links in activation tab of wemail
 */
function wemail_plugin_action_links( $links ) {
    $links = array_merge( array(
        '<a href="https://getwemail.io/docs/wemail/get-started/?utm_source=orgplugin&utm_medium=dashboarddoc&utm_campaign=settinglink" target="_blank">' . __( 'Docs', 'wemail' ) . '</a>',
        '<a href="https://getwemail.io/contact?utm_source=orgplugin&utm_medium=dashboardcontact&utm_campaign=settinglink" target="_blank">' . __( 'Support', 'wemail' ) . '</a>',
        '<a href="https://getwemail.io/?utm_source=site-plugin-settings&utm_medium=website-url" target="_blank">' . __( 'Visit Site', 'wemail' ) . '</a>',
    ), $links );

    return $links;
}

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'wemail_plugin_action_links' );
