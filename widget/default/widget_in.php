<?php
/*
 * This is the page users will see logged in.
 * You can edit this, but for upgrade safety you should copy and modify this file into your template folder.
 * The location from within your template folder is plugins/register-enrol/ (create these directories if they don't exist)
*/
?>
<div class="ore">
	<?php
		$user = wp_get_current_user();
	?>
	<span class="ore-title-sub" style="display: none"><?php echo __('Hi', ORE_ID).' '.$user->display_name;  ?></span>
	<table>
		<tr>
			<td class="avatar ore-avatar">
				<?php echo get_avatar($user->ID, $size = '50');  ?>
			</td>
			<td class="ore-info">
				<?php
					// Admin URL
					if ($ore_data['profile_link'] == '1') {
						if (function_exists('bp_loggedin_user_link')) {
							?>
							<a href="<?php bp_loggedin_user_link(); ?>"><?php esc_html_e('Profile','login-with-ajax'); ?></a><br/>
							<?php
						} else {
							?>
							<a href="<?php echo trailingslashit(get_admin_url()); ?>profile.php"><?php esc_html_e('Profile','login-with-ajax'); ?></a><br/>
							<?php
						}
					}
					// Logout URL
					?>
					<a id="wp-logout" href="<?php echo wp_logout_url() ?>"><?php esc_html_e( 'Log Out' ,'login-with-ajax') ?></a><br />
					<?php
					// Blog Admin
					if (current_user_can('list_users')) {
						?>
						<a href="<?php echo get_admin_url(); ?>"><?php esc_html_e("blog admin", 'login-with-ajax'); ?></a>
						<?php
					}
				?>
			</td>
		</tr>
	</table>
</div>
