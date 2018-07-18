<?php
/*
 * This is the page users will see logged in.
 * You can edit this, but for upgrade safety you should copy and modify this file into your template folder.
 * The location from within your template folder is plugins/register-enrol/ (create these directories if they don't exist)
*/
?>
<div id="ore-status" class="ore">
	<?php
		$user = wp_get_current_user();
	?>
	<span class="ore-title-sub" style="display: none"><?php echo __('Hi', ORE_ID).' '.$user->display_name;  ?></span>
	<div class="ore-row">
		<div class="avatar ore-avatar ore-cell">
			<?php echo get_avatar($user->ID, $size = '50');  ?>
		</div>
		<div class="ore-info ore-cell">
			<?php
				// Admin URL
				if ($ore_data['profile_link'] == '1') {
					?>
					<a href="<?php echo trailingslashit(get_admin_url()); ?>profile.php"><?php esc_html_e('Profile', ORE_ID); ?></a><br/>
					<?php
				}
				// Logout URL
				?>
				<a id="wp-logout" href="<?php echo wp_logout_url() ?>"><?php esc_html_e( 'Log Out' , ORE_ID) ?></a><br />
				<?php
				// Blog Admin
				if (current_user_can('list_users')) {
					?>
					<a href="<?php echo get_admin_url(); ?>"><?php esc_html_e("blog admin", ORE_ID); ?></a>
					<?php
				}
			?>
		</div>
	</div><!-- row -->
</div>
