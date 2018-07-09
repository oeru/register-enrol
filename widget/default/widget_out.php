<?php
/*
 * This is the page users will see logged out.
 * You can edit this, but for upgrade safety you should copy and modify this file into your template folder.
 * The location from within your template folder is plugins/login-with-ajax/ (create these directories if they don't exist)
*/
?>
<div class="ore ore-default"><?php //class must be here, and if this is a template, class name should be that of template directory ?>
    <form class="ore-form" action="<?php echo esc_attr(OREMain::$url_login); ?>" method="post">
    	<div>
    	<span class="ore-status"></span>
        <table>
            <tr class="ore-username">
                <td class="ore-username-label">
                    <label><?php esc_html_e('Username', ORE_ID); ?></label>
                </td>
                <td class="ore-username-input">
                    <input type="text" name="log" />
                </td>
            </tr>
            <tr class="ore-password">
                <td class="ore-password-label">
                    <label><?php esc_html_e('Password', ORE_ID); ?></label>
                </td>
                <td class="ore-password-input">
                    <input type="password" name="pwd" />
                </td>
            </tr>
            <tr><td colspan="2"><?php do_action('login_form'); ?></td></tr>
            <tr class="ore-submit">
                <td class="ore-submit-button">
                    <input type="submit" name="wp-submit" id="ore_wp-submit" value="<?php esc_attr_e('Log In', ORE_ID); ?>" tabindex="100" />
                    <input type="hidden" name="ore_profile_link" value="<?php echo esc_attr($ore_data['profile_link']); ?>" />
                    <input type="hidden" name="login-with-ajax" value="login" />
					<?php if (!empty($ore_data['redirect'])) { ?>
					<input type="hidden" name="redirect_to" value="<?php echo esc_url($ore_data['redirect']); ?>" />
                    <?php } ?>
                </td>
                <td class="ore-submit-links">
                    <input name="rememberme" type="checkbox" class="ore-rememberme" value="forever" /><label><?php esc_html_e('Remember Me', ORE_ID); ?></label>
                    <br />
					<?php if (!empty($ore_data['remember'])) { ?>
					<a class="ore-links-remember" href="<?php echo esc_attr(OREMain::$url_remember); ?>" title="<?php esc_attr_e('Password Lost and Found', ORE_ID); ?>"><?php esc_attr_e('Lost your password?', ORE_ID); ?></a>
                    <?php } ?>
                    <?php if (get_option('users_can_register') && !empty($ore_data['registration'])) { ?>
					<br />
					<a href="<?php echo esc_attr(OREMain::$url_register); ?>" class="ore-links-register ore-links-modal"><?php esc_html_e('Register', ORE_ID); ?></a>
                    <?php } ?>
                </td>
            </tr>
        </table>
        </div>
    </form>
    <?php if (!empty($ore_data['remember']) && $ore_data['remember'] == 1) { ?>
    <form class="ore-remember" action="<?php echo esc_attr(OREMain::$url_remember) ?>" method="post" style="display: none;">
    	<div>
    	<span class="ore-status"></span>
        <table>
            <tr>
                <td>
                    <strong><?php esc_html_e("Forgotten Password", ORE_ID); ?></strong>
                </td>
            </tr>
            <tr>
                <td class="ore-remember-email">
                    <?php $msg = __("Enter username or email", ORE_ID); ?>
                    <input type="text" name="user_login" class="ore-user-remember" value="<?php echo esc_attr($msg); ?>" onfocus="if (this.value == '<?php echo esc_attr($msg); ?>') { this.value = ''; }" onblur="if (this.value == '') { this.value = '<?php echo esc_attr($msg); ?>'}" />
                    <?php do_action('lostpassword_form'); ?>
                </td>
            </tr>
            <tr>
                <td class="ore-remember-buttons">
                    <input type="submit" value="<?php esc_attr_e("Get New Password", ORE_ID); ?>" class="ore-button-remember" />
                    <a href="#" class="ore-links-remember-cancel"><?php esc_html_e("Cancel", ORE_ID); ?></a>
                    <input type="hidden" name="login-with-ajax" value="remember" />
                </td>
            </tr>
        </table>
        </div>
    </form>
    <?php } ?>
	<?php if (get_option('users_can_register') && !empty($ore_data['registration']) && $ore_data['registration'] == 1) { ?>
	<div class="ore-register ore-register-default ore-modal" style="display:none;">
		<h4><?php esc_html_e('Register For This Site', ORE_ID); ?></h4>
		<p><em class="ore-register-tip"><?php esc_html_e('A password will be e-mailed to you.', ORE_ID); ?></em></p>
		<form class="ore-register-form" action="<?php echo esc_attr(OREMain::$url_register); ?>" method="post">
			<div>
			<span class="ore-status"></span>
			<p class="ore-username">
				<label><?php esc_html_e('Username', ORE_ID); ?><br />
				<input type="text" name="user_login" id="user_login" class="input" size="20" tabindex="10" /></label>
			</p>
			<p class="ore-email">
				<label><?php esc_html_e('E-mail', ORE_ID); ?><br />
				<input type="text" name="user_email" id="user_email" class="input" size="25" tabindex="20" /></label>
			</p>
			<?php do_action('register_form'); ?>
			<?php do_action('ore_register_form'); ?>
			<p class="submit">
				<input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="<?php esc_attr_e('Register', ORE_ID); ?>" tabindex="100" />
			</p>
	        <input type="hidden" name="login-with-ajax" value="register" />
	        </div>
		</form>
	</div>
    <?php } ?>
</div>
