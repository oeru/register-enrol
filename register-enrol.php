<?php
/**
 * @package OERu Register Enrol
 */
/*
Plugin Name: OERu Register Enrol
Plugin URI: https://github.com/oeru/register-enrol
Description: Provides a widget that helps a user figure out the valid URL for
    their personal course blog feed
Version: 0.0.1
Author: Dave Lane
Author URI: https://oeru.org, http://WikiEducator.org/User:Davelane
License: AGPLv3 or later
*/

/*
This program is free software; you can redistribute it and/or
modify it under the terms of the GNU Affero General Public License
as published by the Free Software Foundation; either version 3
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
Affero GNU General Public License for more details:
https://www.gnu.org/licenses/agpl-3.0.en.html

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/


define( 'ORE_VERSION', '0.0.1' );
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
define('ORE_SHORTCODE', 'ore_form');
define('ORE_WIDGET', 'ORE Widget');
define('ORE_TEMPLATES', 'register-enrol');
define('ORE_ID', 'register-enrol');
define('ORE_CLASS', 'ore-form');
// support link for users of this plugin...
define('ORE_SUPPORT_FORUM', 'https://forums.oeru.org/t/register-enrol');
define('ORE_SUPPORT_BLOG', 'https://course.oeru.org/support/studying-courses/register-enrol/');
// admin details
define('ORE_ADMIN_SLUG', 'ore-settings');
define('ORE_ADMIN_TITLE', 'Register Enrol Settings');
define('ORE_ADMIN_MENU', 'ORE Settings');
// turn on debugging with true, off with false
define('ORE_DEBUG', true);
define('LOG_STREAM', getenv('LOG_STREAM'));

// include the dependencies
require ORE_PATH . 'includes/ore_app.php';

if ( function_exists( 'add_action' ) ) {
    // this starts everything up!
    add_action('plugins_loaded', array(OREMain::get_instance(), 'init'));
} else {
	echo 'This only works as a WordPress plugin.';
	exit;
}

// Set when to init this class
add_action('init', 'OREMain::init');
add_action('widgets_init', 'OREMain::widgets_init');

// Installation and Updates
$ore_data = get_option('ore_data');
if ( version_compare( get_option('ore_version',0), ORE_VERSION, '<' ) ){
    include_once('ore_install.php');
}

// Add translation
function ore_load_plugin_textdomain(){
	load_plugin_textdomain(ORE_ID, false, ORE_PATH.'/langs');
}
add_action('plugins_loaded', 'ore_load_plugin_textdomain');

// Include admin file if needed
if (is_admin()){
	include_once('register-enrol_admin.php');
}

// Include pluggable functions file if user specifies in settings
if (!empty($ore_data['notification_override']) ){
	include_once('pluggable.php');
}
