<?php
/**
 * @package OERu Register Enrol
 */
/*
Plugin Name: OERu Register Enrol
Plugin URI: https://github.com/oeru/register-enrol
Description: Provides a widget that helps a user figure out the valid URL for
    their personal course blog feed. Parts are adapted from Login with Ajax by
    Markus Sykes - http://wordpress.org/extend/plugins/login-with-ajax/
Version: 0.0.1
Author: Dave Lane
Author URI: https://oeru.org, http://WikiEducator.org/User:Davelane
License: GPLv3 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 3
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details:
https://www.gnu.org/licenses/gpl-3.0.en.html

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/


define( 'ORE_VERSION', '0.0.3' );
// plugin computer name
define('ORE_NAME', 'ORE');
// current version
// the path to this file
define('ORE_FILE', __FILE__);
// absolute URL for this plugin, including site name, e.g.
// https://sitename.nz/wp-content/plugins/
define('ORE_URL', plugins_url("/", __FILE__));
// absolute server path to this plugin
define('ORE_PATH', plugin_dir_path(__FILE__));
// module details
define('ORE_SLUG', 'register-enrol');
define('ORE_ACTION', 'register-enrol');
define('ORE_ID', 'register-enrol');
define('ORE_TITLE', 'Register Enrol');
define('ORE_MENU', 'Register Enrol');
define('ORE_TEMPLATES', 'register-enrol');
define('ORE_ID', 'register-enrol');
define('ORE_STYLE', 'ore-style');
define('ORE_SCRIPT', 'ore-script');
define('ORE_CLASS', 'ore-form');
define('ORE_COURSE_ROLE', 'subscriber');
define('ORE_ERROR_LABEL', 'ore_error');
// these two nodes must be defined in the theme as HTML id attributes
define('ORE_CONTAINER', 'ore-container');
define('ORE_LOGIN_STATUS', 'ore-login-status');
// support link for users of this plugin...
define('ORE_SUPPORT_FORUM', 'https://forums.oeru.org/t/register-enrol');
define('ORE_SUPPORT_BLOG', 'https://course.oeru.org/support/studying-courses/register-enrol/');
define('ORE_SUPPORT_CONTACT', 'https://oeru.org/contact-us/');
// admin details
/*define('ORE_ADMIN_SLUG', 'ore-settings');
define('ORE_ADMIN_TITLE', 'Register Enrol Settings');
define('ORE_ADMIN_MENU', 'ORE Settings');*/
// turn on debugging with true, off with false
define('ORE_DEBUG', true);
define('LOG_STREAM', getenv('LOG_STREAM'));
define('ORE_MIN_PASSWORD_LENGTH', 8);
define('ORE_MIN_USERNAME_LENGTH', 6);
define('ORE_MIN_DISPLAY_NAME_LENGTH', 6);

// include the dependencies
require ORE_PATH . 'includes/ore_app.php';

if ( function_exists( 'add_action' ) ) {
    // this starts everything up!
    add_action('plugins_loaded', array(OREMain::get_instance(), 'init'));
    /*if (is_admin()) {
        include_once(ORE_PATH.'includes/ore_admin.php');
        add_action('plugins_loaded', array(OREAdmin::get_instance(), 'init'));
    }*/
} else {
	echo 'This only works as a WordPress plugin.';
	exit;
}
