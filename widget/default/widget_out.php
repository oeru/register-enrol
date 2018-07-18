<?php
/*
 * This is the page users will see logged out.
 * You can edit this, but for upgrade safety you should copy and modify this file into your template folder.
 * The location from within your template folder is plugins/login-with-ajax/ (create these directories if they don't exist)
*/
?>
<div id="ore-status" class="ore ore-divs-only">
	<span class="ore-status"></span>
	<form class="ore-form" action="<?php echo esc_attr(OREMain::$url_login); ?>" method="post">
		<div class="ore-username">
			<label><?php esc_html_e('Username', ORE_ID); ?></label>
			<input type="text" name="log" id="ore_user_login" class="input" />
		</div>

		<div class="ore-password">
			<label><?php esc_html_e('Password', ORE_ID); ?></label>
			<input type="password" name="pwd" id="ore_user_pass" class="input" />
		</div>

		<div class="ore-login_form">
			<?php do_action('login_form'); ?>
		</div>

		<div class="ore-submit-button">
			<input type="submit" name="wp-submit" id="ore_wp-submit" value="<?php esc_attr_e('Log In', ORE_ID); ?>" tabindex="100" />
			<input type="hidden" name="ore_profile_link" value="<?php echo esc_attr($ore_data['profile_link']); ?>" />
			<input type="hidden" name="login-with-ajax" value="login" />
			<?php if (!empty($ore_data['redirect'])) { ?>
			<input type="hidden" name="redirect_to" value="<?php echo esc_url($ore_data['redirect']); ?>" />
            <?php } ?>
		</div>

		<div class="ore-links">
			<input name="rememberme" type="checkbox" class="ore-rememberme" value="forever" /> <label><?php esc_html_e('Remember Me', ORE_ID); ?></label>
			<br />
        	<?php if( !empty($ore_data['remember']) ): ?>
			<a class="ore-links-remember" href="<?php echo esc_attr(OREMain::$url_remember); ?>" title="<?php esc_attr_e('Password Lost and Found', ORE_ID); ?>"><?php esc_attr_e('Lost your password?', ORE_ID); ?></a>
			<?php endif; ?>
			<?php if (get_option('users_can_register') && !empty($ore_data['registration'])) { ?>
			<br />
			<a href="<?php echo esc_attr(OREMain::$url_register); ?>" class="ore-links-register-inline"><?php esc_html_e('Register', ORE_ID); ?></a>
            <?php } ?>
		</div>
	</form>
	<?php if (!empty($ore_data['remember']) && $ore_data['remember'] == 1 ) { ?>
	<form class="ore-remember" action="<?php echo esc_attr(OREMain::$url_remember); ?>" method="post" style="display:none;">
		<p><strong><?php esc_html_e("Forgotten Password", ORE_ID); ?></strong></p>
		<div class="ore-remember-email">
			<?php $msg = __("Enter username or email", ORE_ID); ?>
			<input type="text" name="user_login" id="ore_user_remember" value="<?php echo esc_attr($msg); ?>" onfocus="if (this.value == '<?php echo esc_attr($msg); ?>') { this.value = ''; }" onblur="if (this.value == '') { this.value = '<?php echo esc_attr($msg); ?>' }" />
			<?php do_action('lostpassword_form'); ?>
		</div>
		<div class="ore-submit-button">
			<input type="submit" value="<?php esc_attr_e("Get New Password", ORE_ID); ?>" />
			<a href="#" class="ore-links-remember-cancel"><?php esc_attr_e("Cancel", ORE_ID); ?></a>
			<input type="hidden" name="login-with-ajax" value="remember" />
		</div>
	</form>
    <?php } ?>
	<?php if (get_option('users_can_register') && !empty($ore_data['registration']) && $ore_data['registration'] == 1) { ?>
	<div class="ore-register" style="display:none;" >
		<form class="registerform" action="<?php echo esc_attr(OREMain::$url_register); ?>" method="post">
			<p><strong><?php esc_html_e('Register For This Site', ORE_ID); ?></strong></p>
			<div class="ore-username">
				<?php $msg = __('Username', ORE_ID); ?>
				<input type="text" name="user_login" id="user_login" value="<?php echo esc_attr($msg); ?>" onfocus="if (this.value == '<?php echo esc_attr($msg); ?>') { this.value = ''; }" onblur="if (this.value == '') { this.value = '<?php echo esc_attr($msg); ?>' }" />
		  	</div>
		  	<div class="ore-email">
		  		<?php $msg = __('E-mail', ORE_ID); ?>
				<input type="text" name="user_email" id="user_email" value="<?php echo esc_attr($msg); ?>" onfocus="if (this.value == '<?php echo esc_attr($msg); ?>') {this.value = ''; }" onblur="if (this.value == '') { this.value = '<?php echo esc_attr($msg); ?>' }"/>
			</div>
			<?php
				// If you want other plugins to play nice, you need this:
				do_action('register_form');
			?>
			<p class="ore-submit-button">
				<?php esc_html_e('A password will be e-mailed to you.', ORE_ID); ?>
				<input type="submit" name="wp-submit" id="wp-submit" class="button-primary" value="<?php esc_attr_e('Register', ORE_ID); ?>" tabindex="100" />
				<a href="#" class="ore-links-register-inline-cancel"><?php esc_html_e("Cancel", ORE_ID); ?></a>
				<input type="hidden" name="login-with-ajax" value="register" />
			</p>
		</form>
	</div>
    <?php } ?>
</div>
