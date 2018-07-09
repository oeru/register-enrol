<?php
/*
Copyright (C) 2009 NetWebLogic LLC

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/

// Class initialization
class OREAdmin{
	// action function for above hook
	function __construct() {
		global $user_level;
		$ore = OREMain::$data;
		add_action ('admin_menu', array (&$this, 'menus'));
		if (!empty($_REQUEST['ore_dismiss_notice']) && wp_verify_nonce($_REQUEST['_nonce'], 'ore_notice_'.$_REQUEST['ore_dismiss_notice']) && current_user_can('manage_options')) {
			if (key_exists($_REQUEST['ore_dismiss_notice'], $ore['notices'])) {
			    unset($ore['notices'][$_REQUEST['ore_dismiss_notice']]);
			    if (empty($ore['notices'])) {
                    unset($ore['notices']);
                }
    			update_option('ore_data', $ore);
			}
		} elseif (!empty($ore['notices']) && is_array($ore['notices']) && count($ore['notices']) > 0 && current_user_can('manage_options')) {
			add_action('admin_notices', array(&$this, 'admin_notices'));
		}
	}

	function menus(){
		$page = add_options_page(ORE_ADMIN_TITLE, ORE_ADMIN_MENU, 'manage_options', ORE_ADMIN_SLUG, array(&$this,'options'));
		add_action('admin_head-'.$page, array(&$this,'options_head'));
	}

	function admin_notices() {
	    if (!empty(OREMain::$data['notices']['password_link'])){
    		?>
    		<div class="updated notice notice-success is-dismissible password_link">
                <p>
                    <?php echo esc_html_e("Since WordPress 4.3 passwords are not emailed to users anymore, they're replaced with a link to create a new password.", 'register-enrol'); ?>
                    <!-- could be this should use the ORE_ADMIN_SLUG instead -->
                    <a href="<?php echo admin_url('options-general.php?page=register-enrol'); ?>"><?php echo esc_html_e("Check your registration email template.", 'register-enrol'); ?></a>
                </p>
                <button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e('Dismiss','register-enrol') ?></span></button>
            </div>
    	    <script type="text/javascript">
    			jQuery('document').ready(function($){
    				$(document).on('click', '.updated.notice.password_link .notice-dismiss', function(event){
    					jQuery.post('<?php echo esc_url(admin_url('admin-ajax.php'))?>', {
							'ore_dismiss_notice':'password_link',
							'_nonce':'<?php echo wp_create_nonce('ore_notice_password_link'); ?>'
        				});
    				});
    			});
    	    </script>
    		<?php
	    }
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
			if (!empty($_POST['ore_notification_override'])) {
				update_option('ore_notification_override',$_POST['ore_notification_override']);
			}
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
				<div id="side-info-column" class="inner-sidebar">
					<div id="categorydiv" class="postbox ">
						<div class="handlediv" title="Click to toggle"></div>
						<h3 class="hndle" style="color: green;">** Support this plugin! **</h3>
						<div class="inside">
							<p>This plugin was developed by the <a href="https://oeru.org">OERu</a>, in part based on the work of <a href="http://msyk.es/" target="_blank">Marcus Sykes</a> (in turn, sponsored by proceeds from <a href="http://netweblogic.com" target="_blank">NetWebLogic</a>).</p>
						</div>
					</div>
					<div id="categorydiv" class="postbox ">
						<div class="handlediv" title="Click to toggle"></div>
						<h3 class="hndle">Getting Help</h3>
						<div class="inside">
							<p>Before asking for help, check the readme files or the plugin pages for answers to common issues.</p>
							<p>If you have any suggestions, come over to the forums and leave a comment. It may just happen!</p>
						</div>
					</div>
				</div>
				<div id="post-body">
					<div id="post-body-content">
						<form method="post" action="">
						<h2><?php _e("General Settings", 'register-enrol'); ?></h2>
						<table class="form-table">
							<?php if (count(OREMain::$templates) > 1) { ?>
							<tr valign="top">
								<th scope="row">
									<label><?php _e("Default Template", 'register-enrol'); ?></label>
								</th>
								<td>
									<select name="ore_template" style="margin: 0px; padding: 0px; width: auto;">
					            		<?php foreach (array_keys(OREMain::$templates) as $template) { ?>
					            		<option <?php echo (!empty($ore_data['template']) && $ore_data['template'] == $template) ? 'selected="selected"' : ""; ?>><?php echo $template; ?></option>
                                        <?php } //foreach ?>
					            	</select>
									<br />
									<em><?php _e("Choose the default theme you'd like to use. This can be overridden in the widget, shortcode, and template tags.", 'register-enrol'); ?></em>
									<em><?php _e("Further documentation for this feature coming soon...", 'register-enrol'); ?></em>
								</td>
							</tr>
                            <?php } ?>
							<tr valign="top">
								<th scope="row">
									<label><?php _e("Disable refresh upon login?", 'register-enrol'); ?></label>
								</th>
								<td>
									<input style="margin: 0px; padding: 0px; width: auto;" type="checkbox" name="ore_no_login_refresh" value='1' class='wide' <?php echo (!empty($ore_data['no_login_refresh']) && $ore_data['no_login_refresh'] == '1') ? 'checked="checked"' : ''; ?> />
									<br />
									<em><?php _e("If the user logs in and you check the button above, only the login widget will update itself without refreshing the page. Not a good idea if your site shows different content to users once logged in, as a refresh would be needed.", 'register-enrol'); ?></em>
								</td>
							</tr>
						</table>


						<h2><?php _e("Redirection Settings", 'register-enrol'); ?></h2>
						<table class="form-table">
							<tr valign="top">
								<th scope="row">
									<label><?php _e("Global Login Redirect", 'register-enrol'); ?></label>
								</th>
								<td>
									<input type="text" name="ore_login_redirect" value='<?php echo (!empty($ore_data['login_redirect'])) ? $ore_data['login_redirect']:''; ?>' class='wide' />
									<em><?php _e("If you'd like to send the user to a specific URL after login, enter it here (e.g. http://wordpress.org/)", 'register-enrol'); ?></em>
									<br/><em><?php _e("Use %USERNAME% and it will be replaced with the username of person logging in.", 'register-enrol'); ?></em>
									<?php
									//WMPL itegrations
									function ore_icl_inputs($name, $ore_data) {
										if (function_exists('icl_get_languages')) {
											$langs = icl_get_languages();
											if (count($langs) > 1) {
												?>
												<table id="ore_<?php echo $name; ?>_langs">
												<?php
												foreach ($langs as $lang) {
													if (substr(get_locale(),0,2) != $lang['language_code']) {
													?>
													<tr>
														<th style="width: 100px;"><?php echo $lang['translated_name']?>: </th>
														<td><input type="text" name="ore_<?php echo $name; ?>_<?php echo $lang['language_code']; ?>" value='<?php echo (!empty($ore_data[$name.'_'.$lang['language_code']])) ? $ore_data[$name.'_'.$lang['language_code']] : ''; ?>' class="wide" /></td>
													</tr>
													<?php
													}
												}
												?>
												</table>
												<em><?php _e('With WPML enabled you can provide different redirection destinations based on language too.','register-enrol'); ?></em>
												<?php
											}
										}
									}
									ore_icl_inputs('login_redirect', $ore_data);
									?>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label><?php _e("Global Logout Redirect", 'register-enrol'); ?></label>
								</th>
								<td>
									<input type="text" name="ore_logout_redirect" value='<?php echo (!empty($ore_data['logout_redirect'])) ? $ore_data['logout_redirect'] : ''; ?>' class='wide' />
									<em><?php _e("If you'd like to send the user to a specific URL after logout, enter it here (e.g. http://wordpress.org/)", 'register-enrol'); ?></em>
									<br /><em><?php _e("Enter %LASTURL% to send the user back to the page they were previously on.", 'register-enrol'); ?></em>
									<?php
									ore_icl_inputs('logout_redirect', $ore_data);
									?>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label><?php _e("Role-Based Custom Login Redirects", 'register-enrol'); ?></label>
								</th>
								<td>
									<em><?php _e("If you would like a specific user role to be redirected to a custom URL upon login, place it here (blank value will default to the global redirect)", 'register-enrol'); ?></em>
									<table>
									<?php
									//Taken from /wp-admin/includes/template.php Line 2715
									$editable_roles = get_editable_roles();
									//WMPL integration
									function ore_icl_inputs_roles($name, $ore_data, $role) {
										if (function_exists('icl_get_languages')) {
											$langs = icl_get_languages();
											if (count($langs) > 1) {
												?>
												<table id="ore_<?php echo $name; ?>_langs">
												<?php
												foreach($langs as $lang) {
													if (substr(get_locale(),0,2) != $lang['language_code']) {
													?>
													<tr>
														<th style="width:100px;"><?php echo $lang['translated_name']?>: </th>
														<td><input type="text" name="ore_<?php echo $name; ?>_<?php echo $role; ?>_<?php echo $lang['language_code']; ?>" value='<?php echo (!empty($ore_data[$name][$role.'_'.$lang['language_code']])) ? $ore_data[$name][$role.'_'.$lang['language_code']] : ''; ?>' class="wide" /></td>
													</tr>
													<?php
													}
												}
												?>
												</table>
												<em><?php _e('With WPML enabled you can provide different redirection destinations based on language too.','register-enrol'); ?></em>
												<?php
											}
										}
									}
									foreach ($editable_roles as $role => $details) {
										$role_login = (!empty($ore_data['role_login']) && is_array($ore_data['role_login']) && array_key_exists($role, $ore_data['role_login'])) ? $ore_data['role_login'][$role] : '';
										?>
										<tr>
											<th class="col"><?php echo translate_user_role($details['name']); ?></th>
											<td>
												<input type='text' class='wide' name='ore_role_login_<?php echo esc_attr($role); ?>' value="<?php echo $role_login; ?>" />
												<?php
													ore_icl_inputs_roles('role_login', $ore_data, esc_attr($role));
												?>
											</td>
										</tr>
										<?php
									}
									?>
									</table>
								</td>
							</tr>
							<tr valign="top">
								<th scope="row">
									<label><?php _e("Role-Based Custom Logout Redirects", 'register-enrol'); ?></label>
								</th>
								<td>
									<em><?php _e("If you would like a specific user role to be redirected to a custom URL upon logout, place it here (blank value will default to the global redirect)", 'register-enrol'); ?></em>
									<table>
									<?php
									//Taken from /wp-admin/includes/template.php Line 2715
									$editable_roles = get_editable_roles();
									foreach ($editable_roles as $role => $details) {
										$role_logout = (!empty($ore_data['role_logout']) && is_array($ore_data['role_logout']) && array_key_exists($role, $ore_data['role_logout'])) ? $ore_data['role_logout'][$role] : '';
										?>
										<tr>
											<th class='col'><?php echo translate_user_role($details['name']); ?></th>
											<td>
												<input type='text' class='wide' name='ore_role_logout_<?php echo esc_attr($role); ?>' value="<?php echo $role_logout; ?>" />
												<?php ore_icl_inputs_roles('role_logout', $ore_data, $role); ?>
											</td>
										</tr>
										<?php
									}
									?>
									</table>
								</td>
							</tr>
						</table>

						<h2><?php _e("Notification Settings", 'register-enrol'); ?></h2>
						<p>
							<em><?php _e("If you'd like to override the default Wordpress email users receive once registered, make sure you check the box below and enter a new email subject and message.", 'register-enrol'); ?></em><br />
							<em><?php _e("If this feature doesn't work, please make sure that you don't have another plugin installed which also manages user registrations (e.g. BuddyPress and MU).", 'register-enrol'); ?></em>
						</p>
						<table class="form-table">
							<tr valign="top">
								<th>
									<label><?php _e("Override Default Email?", 'register-enrol'); ?></label>
								</th>
								<td>
									<input style="margin: 0px; padding: 0px; width: auto;" type="checkbox" name="ore_notification_override" value='1' class='wide' <?php echo (!empty($ore_data['notification_override']) && $ore_data['notification_override'] == '1') ? 'checked="checked"' : ''; ?> />
								</td>
							</tr>
							<tr valign="top">
								<th>
									<label><?php _e("Subject", 'register-enrol'); ?></label>
								</th>
								<td>
									<?php
									if (empty($ore_data['notification_subject'])) {
									    $ore_data['notification_subject'] = __('Your registration at %BLOGNAME%', 'register-enrol');
									}
									?>
									<input type="text" name="ore_notification_subject" value='<?php echo (!empty($ore_data['notification_subject'])) ? $ore_data['notification_subject'] : ''; ?>' class='wide' />
									<em><?php _e("<code>%USERNAME%</code> will be replaced with a username.", 'register-enrol'); ?></em><br />
									<?php if (version_compare($wp_version, '4.3', '>=')) { ?>
									<em><strong><?php echo sprintf(esc_html__("%s will be replaced with a link to set the user password.", 'register-enrol'), '<code>%PASSWORD%</code>'); ?></strong></em><br />
                                    <?php } else { ?>
									<em><?php _e("<code>%PASSWORD%</code> will be replaced with the user's password.", 'register-enrol'); ?></em><br />
                                    <?php } ?>
									<em><?php _e("<code>%BLOGNAME%</code> will be replaced with the name of your blog.", 'register-enrol'); ?></em>
									<em><?php _e("<code>%BLOGURL%</code> will be replaced with the url of your blog.", 'register-enrol'); ?></em>
								</td>
							</tr>
							<tr valign="top">
								<th>
									<label><?php _e("Message", 'register-enrol'); ?></label>
								</th>
								<td>
									<?php
										if (empty($ore_data['notification_message'])) {
										    if (version_compare($wp_version, '4.3', '>=')) {
										        $ore_data['notification_message'] = __('Thanks for signing up to our blog.

You can login with the following credentials by visiting %BLOGURL%

Username: %USERNAME%
To set your password, visit the following address: %PASSWORD%

We look forward to your next visit!

The team at %BLOGNAME%', 'register-enrol');
										} else {
											$ore_data['notification_message'] = __('Thanks for signing up to our blog.

You can login with the following credentials by visiting %BLOGURL%

Username : %USERNAME%
Password : %PASSWORD%

We look forward to your next visit!

The team at %BLOGNAME%', 'register-enrol');
									    }
									}
										?>
									<textarea name="ore_notification_message" class='wide' style="width: 100%; height: 250px;"><?php echo $ore_data['notification_message']; ?></textarea>
									<em><?php _e("<code>%USERNAME%</code> will be replaced with a username.", 'register-enrol'); ?></em><br />
									<?php if (version_compare($wp_version, '4.3', '>=')) { ?>
									<em><strong><?php echo sprintf(esc_html__("%s will be replaced with a link to set the user password.", 'register-enrol'), '<code>%PASSWORD%</code>'); ?></strong></em><br />
                                    <?php } else { ?>
									<em><?php _e("<code>%PASSWORD%</code> will be replaced with the user's password.", 'register-enrol'); ?></em><br />
                                    <?php } ?>
									<em><?php _e("<code>%BLOGNAME%</code> will be replaced with the name of your blog.", 'register-enrol'); ?></em>
									<em><?php _e("<code>%BLOGURL%</code> will be replaced with the url of your blog.", 'register-enrol'); ?></em>
								</td>
							</tr>
						</table>
							<div>
								<input type="hidden" name="oresubmitted" value="1" />
								<input type="hidden" name="_nonce" value="<?php echo wp_create_nonce('register-enrol-admin'.get_current_user_id()); ?>" />
								<p class="submit">
									<input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
								</p>
							</div>
						</form>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}

public function ore_admin_init(){
	global $oer_admin;
	$oer_admin = new OREAdmin();
}

// Start this plugin once all other plugins are fully loaded
add_action('init', 'ore_admin_init');
?>
