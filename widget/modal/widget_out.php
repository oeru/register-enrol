<?php
/*
 * This is the page users will see logged out.
 * You can edit this, but for upgrade safety you should copy and modify this file into your template folder.
 * The location from within your template folder is plugins/register-enrol/ (create these directories if they don't exist)
*/
?>
	<div class="ore ore-template-modal"><?php //class must be here, and if this is a template, class name should be that of template directory ?>
		<a href="<?php echo esc_attr(OREMain::$url_login); ?>" class="ore-links-modal"><?php esc_html_e('Log In', ORE_ID); ?></a>
		<?php
		// FOOTER - once the page loads, this will be moved automatically to the bottom of the document.
		?>
		<div class="ore-modal" style="display:none;">
	        <form name="ore-form" class="ore-form" action="<?php echo esc_attr(OREMain::$url_login); ?>" method="post">
	        	<span class="ore-status"></span>
	            <table>
	                <tr class="ore-username">
	                    <td class="username_label">
	                        <label><?php esc_html_e('Username', ORE_ID); ?></label>
	                    </td>
	                    <td class="username_input">
	                        <input type="text" name="log" id="ore_user_login" class="input" />
	                    </td>
	                </tr>
	                <tr class="ore-password">
	                    <td class="password_label">
	                        <label><?php esc_html_e('Password', ORE_ID); ?></label>
	                    </td>
	                    <td class="password_input">
	                        <input type="password" name="pwd" id="ore_user_pass" class="input" value="" />
	                    </td>
	                </tr>
                	<tr><td colspan="2"><?php do_action('login_form'); ?></td></tr>
	                <tr class="ore-submit">
	                    <td class="ore-submit-button">
	                        <input type="submit" name="wp-submit" class="ore-wp-submit" value="<?php esc_attr_e('Log In', ORE_ID); ?>" tabindex="100" />
	                        <input type="hidden" name="ore_profile_link" value="<?php echo (!empty($ore_data['profile_link'])) ? 1 : 0; ?>" />
                        	<input type="hidden" name="<?php echo ORE_ID; ?>" value="login" />
							<?php if (!empty($ore_data['redirect'])) { ?>
							<input type="hidden" name="redirect_to" value="<?php echo esc_url($ore_data['redirect']); ?>" />
                            <?php } ?>
	                    </td>
	                    <td class="ore-links">
	                        <input name="rememberme" type="checkbox" id="ore_rememberme" value="forever" /> <label><?php esc_html_e('Remember Me', ORE_ID); ?></label>
	                        <br />
				        	<?php if (!empty($ore_data['remember'])) { ?>
							<a class="ore-links-remember" href="<?php echo esc_attr(OREMain::$url_remember); ?>" title="<?php esc_attr_e('Password Lost and Found', ORE_ID); ?>"><?php esc_attr_e('Lost your password?', ORE_ID); ?></a>
                            <?php } ?>
							<?php if (get_option('users_can_register') && !empty($ore_data['registration'])) { ?>
							<br />
							<a href="<?php echo esc_attr(OREMain::$url_register); ?>" class="ore-links-register-inline"><?php esc_html_e('Register', ORE_ID); ?></a>
                            <?php } ?>
	                    </td>
	                </tr>
	            </table>
	        </form>
        	<?php if (!empty($ore_data['remember']) && $ore_data['remember'] == 1) { ?>
	        <form name="ore-remember" class="ore-remember" action="<?php echo esc_attr(OREMain::$url_remember); ?>" method="post" style="display: none;">
	        	<span class="ore-status"></span>
	            <table>
	                <tr>
	                    <td>
	                        <strong><?php esc_html_e("Forgotten Password", ORE_ID); ?></strong>
	                    </td>
	                </tr>
	                <tr class="ore-remember-email">
	                	<td>
	                		<label>
	                        <?php $msg = __("Enter username or email", ORE_ID); ?>
	                        <input type="text" name="user_login" id="ore_user_remember" value="<?php echo esc_attr($msg); ?>" onfocus="if (this.value == '<?php echo esc_attr($msg); ?>') { this.value = ''; }" onblur="if (this.value == '') { this.value = '<?php echo esc_attr($msg); ?>' }" />
	                        </label>
							<?php do_action('lostpassword_form'); ?>
	                    </td>
	                </tr>
	                <tr>
	                    <td>
	                        <input type="submit" value="<?php esc_attr_e("Get New Password", ORE_ID); ?>" />
	                        <a href="#" class="ore-links-remember-cancel"><?php esc_html_e("Cancel", ORE_ID); ?></a>
	                        <input type="hidden" name="<?php echo ORE_ID; ?>" value="remember" />
	                    </td>
	                </tr>
	            </table>
	        </form>
            <?php } ?>
		    <?php if (get_option('users_can_register') && !empty($ore_data['registration']) && $ore_data['registration'] == 1) { //Taken from wp-login.php ?>
		    <div class="ore-register" style="display:none;">
				<form name="ore-register"  action="<?php echo esc_attr(OREMain::$url_register); ?>" method="post">
	        		<span class="ore-status"></span>
					<table>
		                <tr>
		                    <td>
		                        <strong><?php esc_html_e('Register For This Site', ORE_ID); ?></strong>
		                    </td>
		                </tr>
		                <tr class="ore-username">
		                    <td>
		                    	<label>
		                        <?php $msg = __('Username', ORE_ID) ?>
		                        <input type="text" name="user_login" id="user_login" value="<?php echo esc_attr($msg); ?>" onfocus="if (this.value == '<?php echo esc_attr($msg); ?>') { this.value = ''; }" onblur="if (this.value == '') { this.value = '<?php echo esc_attr($msg); ?>' }" />
		                        </label>
		                    </td>
		                </tr>
		                <tr class="ore-email">
		                    <td>
		                    	<label>
		                        <?php $msg = __('E-mail', ORE_ID) ?>
		                        <input type="text" name="user_email" id="user_email" value="<?php echo esc_attr($msg); ?>" onfocus="if (this.value == '<?php echo esc_attr($msg); ?>') { this.value = ''; }" onblur="if (this.value == '') { this.value = '<?php echo esc_attr($msg); ?>' }"/>
		                        </label>
		                    </td>
		                </tr>
		                <tr>
		                    <td>
								<?php
								//If you want other plugins to play nice, you need this:
								do_action('register_form');
							?>
		                    </td>
		                </tr>
		                <tr>
		                    <td>
		                        <?php esc_html_e('A password will be e-mailed to you.', ORE_ID); ?><br />
								<input type="submit" value="<?php esc_attr_e('Register', ORE_ID); ?>" tabindex="100" />
								<a href="#" class="ore-links-register-inline-cancel"><?php esc_html_e("Cancel", ORE_ID); ?></a>
								<input type="hidden" name="<?php echo ORE_ID; ?>" value="register" />
		                    </td>
		                </tr>
		            </table>
				</form>
			</div>
            <?php } ?>
		</div>
	</div>
