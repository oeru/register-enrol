<?php

require ORE_PATH . 'includes/ore_base.php';

class OREMain extends OREBase {
    protected static $instance = NULL; // this instance
    private $errors = array();

    // ORE -> ORE variables
	public static  $current_user; // If logged in upon instantiation, it is a user object.
	public static $templates = array(); // List of templates available in the plugin dir and theme (populated in init())
	public static $template; // Name of selected template (if selected)
	public static $data; // ore_data option
	public static $footer_loc; // Location of footer file if one is found when generating a widget, for use in loading template footers.
	public static $url_login; // URL for the AJAX Login procedure in templates (including callback and template parameters)
	public static $url_remember; // URL for the AJAX Remember Password procedure in templates (including callback and template parameters)
	public static $url_register; // URL for the AJAX Registration procedure in templates (including callback and template parameters)

    // returns an instance of this class if called, instantiating if necessary
    public static function get_instance() {
        NULL === self::$instance and self::$instance = new self();
        return self::$instance;
    }

    // this starts everything...
/*    public function init() {
        $this->log('in init');

        $this->log('setting up scripts');
        // add the ajax handlers
        wp_enqueue_script('ore-script', ORE_URL.'js/ore_script.js', array(
            'jquery', 'jquery-form'));
        wp_localize_script('ore-script', 'ore_data', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce_submit' => wp_create_nonce('ore-submit-nonce')
        ));
        // our css
        wp_register_style('ore-style', ORE_URL.'css/ore_style.css');
        wp_enqueue_style('ore-style');
        // this enables the feedfinder service for authenticated users...
        add_action('wp_ajax_ore_submit', array($this, 'ajax_submit'));
        // this allows users who aren't authenticated to use the feedfinder
        add_action('wp_ajax_nopriv_ore_submit', array($this, 'ajax_submit'));
    } */

    // the function called after the ore-submit button is clicked in our form
/*    public function ajax_submit() {
       $this->log('in ajax_submit: '.print_r($_POST, true));
       // check if the submitted nonce matches the generated nonce created in the auth_init functionality
       if ( ! wp_verify_nonce(sanitize_text_field($_POST['nonce_submit']), 'ore-submit-nonce') ) {
           die ("Busted - someone's trying something funny in submit!");
       } else {
           $this->log('ore-submit-nonce all good.');
       }
       $this->log("processing submit form...");
       // generate the response
       header( "Content-Type: application/json" );
       $this->ajax_response(array('success' => $this->process()));
       $this->log('ajax_submit done, dying...');
       wp_die();
    } */

    // from here is code adapted from Login with Ajax/LWA

	// Actions to take upon initial action hook
	public static function init(){
        $this->log('in init');
		// Load ORE options
		self::$data = get_option('ore_data');
		// Remember the current user, in case there is a logout
		self::$current_user = wp_get_current_user();

		// Get Templates from theme and default by checking for folders - we assume a template works if a folder exists!
		// Note that duplicate template names are overwritten in this order of precedence (highest to lowest) - Child Theme > Parent Theme > Plugin Defaults
		// First are the defaults in the plugin directory
		self::find_templates(path_join(WP_PLUGIN_DIR , basename( dirname( __FILE__ ) ). "/widget/"));
		// Now, the parent theme (if exists)
		if (get_stylesheet_directory() != get_template_directory()) {
			self::find_templates( get_template_directory().'/plugins/'.);
		}
		// Finally, the child theme
		self::find_templates(get_stylesheet_directory().'/plugins/'.ORE_ID);

		// Generate URLs for login, remember, and register
		self::$url_login = self::template_link(site_url('wp-login.php', 'login_post'));
		self::$url_register = self::template_link(self::get_register_link());
		self::$url_remember = self::template_link(site_url('wp-login.php?action=lostpassword', 'login_post'));

		// Make decision on what to display
		if (!empty($_REQUEST["ore"])) { //AJAX Request
		    self::ajax();
		} elseif (isset($_REQUEST[OER_ID.'-widget'])) { // Widget Request via AJAX
			$instance = (!empty($_REQUEST["template"])) ? array('template' => $_REQUEST["template"]) : array();
			$instance['profile_link']  ( !empty($_REQUEST["ore_profile_link"])) ? $_REQUEST['ore_profile_link'] : 0;
			self::widget($instance);
			exit();
		} else {
			// Enqueue scripts - Only one script enqueued here.... theme JS takes priority, then default JS
			if (!is_admin()) {
			    $js_url = defined('WP_DEBUG') && WP_DEBUG ? 'ore_script.js':'ore_script.js'; // make the latter .min.js for production!
				wp_enqueue_script(OER_ID, self::locate_template_url($js_url), array('jquery'), ORE_VERSION);
				wp_enqueue_style(OER_ID, self::locate_template_url('widget.css'), array(), ORE_VERSION );
        		$schema = is_ssl() ? 'https':'http';
        		$js_vars = array('ajaxurl' => admin_url('admin-ajax.php', $schema));
        		//calendar translations
        		wp_localize_script(ORE_ID, 'ORE', apply_filters('ore_js_vars', $js_vars));
			}

			//Add logout/in redirection
			add_action('wp_logout', 'OREMain::logout_redirect');
			add_filter('logout_url', 'OREMain::logout_url');
			add_filter('login_redirect', 'OREMain::login_redirect', 1, 3);
			add_shortcode(ORE_ID, 'OREMain::shortcode');
			add_shortcode('ore', 'OREMain::shortcode');
		}
	}

	public static function widgets_init(){
		//Include and register widget
		include_once('includes/ore_widget.php');
		register_widget(ORE_WIDGET);
	}

	/*
	 * LOGIN OPERATIONS
	 */

	// Decides what action to take from the ajax request
	public static function ajax(){
		$return = array('result'=>false, 'error'=>'Unknown command requested');
		switch ( $_REQUEST[ORE_ACTION] ) {
			case 'login': // A login has been requested
			    $return = self::login();
				break;
			case 'remember': // Remember the password
				$return = self::remember();
				break;
			case 'register': // Remember the password
			default: // backwards-compatible with templates where ore = registration
			    $return = self::register();
			    break;
		}
		@header('Content-Type: application/javascript; charset=UTF-8', true); // add this for HTTP -> HTTPS requests which assume it's a cross-site request
		echo self::json_encode(apply_filters('ore_ajax_'.$_REQUEST[ORE_ACTION], $return));
		exit();
	}

	// Reads ajax login creds via POST, calls the login script, and interprets the result
	public static function login() {
		$return = array(); // What we send back
		if (!empty($_REQUEST['log']) && !empty($_REQUEST['pwd']) && trim($_REQUEST['log']) != '' && trim($_REQUEST['pwd'] != '')) {
			$credentials = array('user_login' => $_REQUEST['log'], 'user_password'=> $_REQUEST['pwd'], 'remember' => !empty($_REQUEST['rememberme']));
			$loginResult = wp_signon($credentials);
			$user_role = 'null';
			if (strtolower(get_class($loginResult)) == 'wp_user') {
				// User login successful
				self::$current_user = $loginResult;
				/* @var $loginResult WP_User */
				$return['result'] = true;
				$return['message'] = __("Login Successful, redirecting...", OER_ID);
				// Do a redirect if necessary
				$redirect = self::getLoginRedirect(self::$current_user);
				if (!empty($_REQUEST['redirect_to'])) {
                    $redirect= wp_sanitize_redirect($_REQUEST['redirect_to']);
                }
				if ($redirect != '') {
					$return['redirect'] = $redirect;
				}
				// If the widget should just update with ajax, then supply the URL here.
				if (!empty(self::$data['no_login_refresh']) && self::$data['no_login_refresh'] == 1) {
					// Is this coming from a template?
					$query_vars = (!empty($_REQUEST['template'])) ? "&template={$_REQUEST['template']}" : '';
					$query_vars .= (!empty($_REQUEST['ore_profile_link'])) ? "&ore_profile_link=1" : '';
					$return['widget'] = get_bloginfo('wpurl')."?login-with-ajax-widget=1$query_vars";
					$return['message'] = __("Login successful, updating...", OER_ID);
				}
			} elseif (strtolower(get_class($loginResult)) == 'wp_error') {
				// User login failed
				/* @var WP_Error $loginResult */
				$return['result'] = false;
				$return['error'] = $loginResult->get_error_message();
			} else {
				// Undefined Error
				$return['result'] = false;
				$return['error'] = __('An undefined error has ocurred', OER_ID);
			}
		} else {
			$return['result'] = false;
			$return['error'] = __('Please supply your username and password.', OER_ID);
		}
		$return['action'] = 'login';
		//Return the result array with errors etc.
		return $return;
	}

    // Checks post data and registers user, then exits
	public static function register(){
	    $return = array();
	    if (get_option('users_can_register')) {
			$errors = register_new_user($_REQUEST['user_login'], $_REQUEST['user_email']);
			if (!is_wp_error($errors)) {
				// Success
				$return['result'] = true;
				$return['message'] = __('Registration complete. Please check your e-mail.', OER_ID);
				// add user to blog if multisite
				if (is_multisite()) {
				    add_user_to_blog(get_current_blog_id(), $errors, get_option('default_role'));
				}
			} else {
				// Something's wrong
				$return['result'] = false;
				$return['error'] = $errors->get_error_message();
			}
			$return['action'] = 'register';
	    }else{
	    	$return['result'] = false;
			$return['error'] = __('Registration has been disabled.', OER_ID);
	    }
		return $return;
	}

	// Reads ajax login creds via POST, calls the login script and interprets the result
	public static function remember(){
		$return = array(); //What we send back
		// if we're not on wp-login.php, we need to load it since retrieve_password() is located there
		if (!function_exists('retrieve_password')) {
		    ob_start();
		    include_once(ABSPATH.'wp-login.php');
		    ob_clean();
		}
		$result = retrieve_password();
		if ($result === true) {
			// Password correctly remembered
			$return['result'] = true;
			$return['message'] = __("We have sent you an email", OER_ID);
		} elseif (strtolower(get_class($result)) == 'wp_error') {
			// Something went wrong
			/* @var $result WP_Error */
			$return['result'] = false;
			$return['error'] = $result->get_error_message();
		} else {
			// Undefined Error
			$return['result'] = false;
			$return['error'] = __('An undefined error has ocurred', OER_ID);
		}
		$return['action'] = 'remember';
		// Return the result array with errors etc.
		return $return;
	}

	// Added fix for WPML
	public static function logout_url($logout_url) {
		// Add ICL if necessary
		if (defined('ICL_LANGUAGE_CODE')) {
			$logout_url .= (strstr($logout_url,'?') !== false) ? '&amp;' : '?';
			$logout_url .= 'lang='.ICL_LANGUAGE_CODE;
		}
		return $logout_url;
	}

	public static function get_register_link(){
	    $register_link = false;
	    if (function_exists('bp_get_signup_page') && (empty($_REQUEST['action']) || ($_REQUEST['action'] != 'deactivate' && $_REQUEST['action'] != 'deactivate-selected'))) { // Buddypress
	    	$register_link = bp_get_signup_page();
	    } elseif (is_multisite()) { // Multisite/WPMS
            $register_link = site_url('wp-signup.php', 'login');
	    } else {
	    	$register_link = site_url('wp-login.php?action=register', 'login');
	    }
	    return $register_link;
	}

	/*
	 * Redirect Functions
	 */

	public static function logout_redirect(){
		$redirect = self::get_logout_redirect();
		if ($redirect != '') {
			wp_redirect($redirect);
			exit();
		}
	}

	public static function get_logout_redirect(){
		$data = self::$data;
		// Global redirect
		$redirect = '';
		if (!empty($data['logout_redirect'])) {
			$redirect = $data['logout_redirect'];
		}
		// WPML global redirect
		$lang = (!empty($_REQUEST['lang'])) ? $_REQUEST['lang'] : '';
		$lang = apply_filters('ore_lang', $lang);
		if (!empty($lang)) {
			if (isset($data["logout_redirect_".$lang])) {
				$redirect = $data["logout_redirect_".$lang];
			}
		}
		// Role based redirect
		if (strtolower(get_class(self::$current_user)) == "wp_user") {
			// Do a redirect if necessary
			$data = self::$data;
			$user_role = array_shift(self::$current_user->roles); // Checking for role-based redirects
			if (!empty($data["role_logout"]) && is_array($data["role_logout"]) && isset($data["role_logout"][$user_role])) {
				$redirect = $data["role_logout"][$user_role];
			}
			// Check for language redirects based on roles
			if (!empty($lang)) {
				if (isset($data["role_logout"][$user_role."_".$lang])) {
					$redirect = $data["role_logout"][$user_role."_".$lang];
				}
			}
		}
		// final replaces
		if (!empty($redirect)) {
			$redirect = str_replace("%LASTURL%", $_SERVER['HTTP_REFERER'], $redirect);
			if (!empty($lang)) {
				$redirect = str_replace("%LANG%", $lang.'/', $redirect);
			}
		}
		return esc_url_raw($redirect);
	}

	public static function login_redirect($redirect, $redirect_notsurewhatthisis, $user) {
		$data = self::$data;
		if (is_object($user)) {
			$ore_redirect = self::get_login_redirect($user);
			if ($ore_redirect != '') {
				$redirect = $ore_redirect;
			}
		}
		return $redirect;
	}

	public static function get_login_redirect($user) {
		$data = self::$data;
		// Global redirect
		$redirect = false;
		if (!empty($data['login_redirect'])) {
			$redirect = $data["login_redirect"];
		}
		// WPML global redirect
		$lang = (!empty($_REQUEST['lang'])) ? $_REQUEST['lang'] : '';
		$lang = apply_filters('ore_lang', $lang);
		if (!empty($lang) && isset($data["login_redirect_".$lang])) {
			$redirect = $data["login_redirect_".$lang];
		}
		// Role based redirects
		if (strtolower(get_class($user)) == "wp_user") {
			$user_role = array_shift($user->roles); // Checking for role-based redirects
			if (isset($data["role_login"][$user_role])) {
				$redirect = $data["role_login"][$user_role];
			}
			// Check for language redirects based on roles
			if (!empty($lang) && isset($data["role_login"][$user_role."_".$lang])) {
				$redirect = $data["role_login"][$user_role."_".$lang];
			}
			// Do user string replacements
			$redirect = str_replace('%USERNAME%', $user->user_login, $redirect);
		}
		// Do string replacements
		$redirect = str_replace("%LASTURL%", wp_get_referer(), $redirect);
		if (!empty($lang)) {
			$redirect = str_replace("%LANG%", $lang.'/', $redirect);
		}
		return esc_url_raw($redirect);
	}

	/*
	 * WIDGET OPERATIONS
	 */

	public static function widget($instance = array()) {
		// Extract widget arguments
		// Merge instance options with global default options
		$ore_data = wp_parse_args($instance, self::$data);
		// Deal with specific variables
		$is_widget = false; //backwards-compatibility for overriden themes, this is now done within the WP_Widget class
		$ore_data['profile_link'] = (!empty($ore_data['profile_link']) && $ore_data['profile_link'] != "false" );
		// Add template logic
		self::$template = (!empty($ore_data['template']) && array_key_exists($ore_data['template'], self::$templates)) ? $ore_data['template'] : 'default';
		// Choose the widget content to display.
		if (is_user_logged_in()) {
			// Check for custom templates or theme template default
			$template_loc = ($template_loc == '' && self::$template) ? self::$templates[self::$template].'/widget_in.php' : $template_loc;
			include ($template_loc != '' && file_exists($template_loc)) ? $template_loc : 'widget/default/widget_in.php';
		} else {
		    // quick/easy WPML fix, should eventually go into a separate file
		    if (defined('ICL_LANGUAGE_CODE')) {
		        if (!function_exists('ore_wpml_input_var')) {
                    function ore_wpml_input_var() {
                        echo '<input type="hidden" name="lang" id="lang" value="'.esc_attr(ICL_LANGUAGE_CODE).'" />';
                    }
		        }
		        foreach(array('login_form','ore_register_form', 'lostpassword_form') as $action) {
                    add_action($action, 'ore_wpml_input_var');
                }
		    }
			// First check for template in theme with no template folder (legacy)
			$template_loc = locate_template(array('plugins/login-with-ajax/widget_out.php'));
			// Then check for custom templates or theme template default
			$template_loc = ($template_loc == '' && self::$template) ? self::$templates[self::$template].'/widget_out.php' : $template_loc;
			include ($template_loc != '' && file_exists($template_loc)) ? $template_loc : 'widget/default/widget_out.php';
			//quick/easy WPML fix, should eventually go into a seperate file
			if (defined('ICL_LANGUAGE_CODE')) {
			    foreach(array('login_form','ore_register_form', 'lostpassword_form') as $action) {
                    remove_action($action, 'ore_wpml_input_var');
                }
			}
		}
	}

    // define the shortcode behaviour!
	public static function shortcode($atts) {
		ob_start();
		$defaults = array(
			'profile_link' => true,
			'template' => 'default',
			'registration' => true,
			'redirect' => false,
			'remember' => true
		);
		self::widget(shortcode_atts($defaults, $atts));
		return ob_get_clean();
	}

	public static function new_user_notification($user_login, $login_link, $user_email, $blogname) {
		// Copied out of /wp-includes/pluggable.php
		$message = self::$data['notification_message'];
		$message = str_replace('%USERNAME%', $user_login, $message);
		$message = str_replace('%PASSWORD%', $login_link, $message);
		$message = str_replace('%BLOGNAME%', $blogname, $message);
		$message = str_replace('%BLOGURL%', get_bloginfo('wpurl'), $message);

		$subject = self::$data['notification_subject'];
		$subject = str_replace('%BLOGNAME%', $blogname, $subject);
		$subject = str_replace('%BLOGURL%', get_bloginfo('wpurl'), $subject);

		wp_mail($user_email, $subject, $message);
	}

	/*
	 * Auxillary Functions
	 */

	/**
	 * Returns the URL for a relative filepath which would be located in either a child, parent or plugin folder in order of priority.
	 *
	 * This would search for $template_path within:
	 * /wp-content/themes/your-child-theme/plugins/login-with-ajax/...
	 * /wp-content/themes/your-parent-theme/plugins/login-with-ajax/...
	 * /wp-content/plugins/login-with-ajax/widget/...
	 *
	 * It is assumed that the file aoreys exists within the core plugin folder if the others aren't found.
	 *
	 * @param string $template_path
	 * @return string
	 */
	public static function locate_template_url($template_path) {
	    if (file_exists(get_stylesheet_directory().'/plugins/'.ORE_SLUG.'/'.$template_path) ) { // Child Theme (or just theme)
	    	return trailingslashit(get_stylesheet_directory_uri())."plugins/'.ORE_SLUG.'/$template_path";
	    } else if (file_exists(get_template_directory().'/plugins/login-with-ajax/'.$template_path) ){ // Parent Theme (if parent exists)
	    	return trailingslashit(get_template_directory_uri())."plugins/login-with-ajax/$template_path";
	    }
	    // Default file in plugin folder
	    return trailingslashit(plugin_dir_url(__FILE__))."widget/$template_path";
	}

	// Checks a directory for folders and populates the template file
	public static function find_templates($dir) {
		if (is_dir($dir)) {
		    if ($dh = opendir($dir)) {
		        while (($file = readdir($dh)) !== false) {
		            if (is_dir($dir . $file) && $file != '.' && $file != '..' && $file != '.svn' && $file != '.git') {
		            	// Template dir found, add it to the template array
		            	self::$templates[$file] = path_join($dir, $file);
		            }
		        }
		        closedir($dh);
		    }
		}
	}

	/**
	 * Add template link and JSON callback var to the URL
	 * @param string $content
	 * @return string
	 */
	public static function template_link($content) {
		return add_query_arg(array('template'=>self::$template), $content);
	}

	/**
	 * Returns a sanitized JSONP response from an array
	 * @param array $array
	 * @return string
	 */
	public static function json_encode($array) {
		$return = json_encode($array);
		if (isset($_REQUEST['callback']) && preg_match("/^jQuery[_a-zA-Z0-9]+$/", $_REQUEST['callback'])) {
			$return = $_REQUEST['callback']."($return)";
		}
		return $return;
	}
}
?>
