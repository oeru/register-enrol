<?php

// Class initialization
class OREAdmin extends OREBase {
    public static $instance = NULL; // this instance

    // returns an instance of this class if called, instantiating if necessary
    public static function get_instance() {
        NULL === self::$instance and self::$instance = new self();
        return self::$instance;
    }

	//function __construct() {
    public function init() {
		global $user_level;
		$ore = OREMain::$data;
        self::log('OREMain data '.print_r(OREMain::$instance, true));
		add_action ('admin_menu', array (&$this, 'menus'));
	}

	function menus(){
		$page = add_options_page(ORE_ADMIN_TITLE, ORE_ADMIN_MENU, 'manage_options', ORE_ADMIN_SLUG, array(&$this,'options'));
		add_action('admin_head-'.$page, array(&$this,'options_head'));
	}

	public function options_head(){
		?>
		<style type="text/css">
			.nwl-plugin table { width: 100%; }
			.nwl-plugin table .col { width: 100px; }
			.nwl-plugin table input.wide { width: 100%; padding: 2px; }
		</style>
		<?php
	}

	public function options() {
		global $oer_admin, $wp_version;
		add_option('ore_data');
		$ore_data = array();

		if (!empty($_POST['oresubmitted']) && current_user_can('list_users') && wp_verify_nonce($_POST['_nonce'], 'register-enrol-admin'.get_current_user_id())) {
			//Build the array of options here
			foreach ($_POST as $postKey => $postValue) {
				if ($postValue != '' && preg_match('/ore_role_log(in|out)_/', $postKey)) {
					//Custom role-based redirects
					if (preg_match('/ore_role_login/', $postKey)) {
						//Login
						$ore_data['role_login'][str_replace('ore_role_login_', '', $postKey)] = $postValue;
					} else {
						//Logout
						$ore_data['role_logout'][str_replace('ore_role_logout_', '', $postKey)] = $postValue;
					}
				} elseif (substr($postKey, 0, 4) == 'ore_') {
					//For now, no validation, since this is in admin area.
					if ($postValue != '') {
						$ore_data[substr($postKey, 4)] = $postValue;
					}
				}
			}
			update_option('ore_data', $ore_data);
			?>
			<div class="updated"><p><strong><?php _e('Changes saved.'); ?></strong></p></div>
			<?php
		} else {
			$ore_data = get_option('ore_data');
		}
		?>
		<div class="wrap nwl-plugin">
			<h1>OERu Register Enrol</h1>
			<div id="poststuff" class="metabox-holder has-right-sidebar">
				<div id="post-body">
					<div id="post-body-content">
						<form method="post" action="">
                            <p>Placeholder for Admin stuff</p>
                        </form>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

function ore_admin_init(){
	global $oer_admin;
	$oer_admin = new OREAdmin();
}

// Start this plugin once all other plugins are fully loaded
add_action('init', 'ore_admin_init');
?>
