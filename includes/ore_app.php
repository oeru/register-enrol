<?php

require ORE_PATH . 'includes/ore_base.php';
// get the countries list...
require ORE_PATH . 'includes/ore_countries.php';
// get the modal dialogue definitions
require ORE_PATH . 'includes/ore_modals.php';

class OREMain extends OREBase {
    public static $instance = NULL; // this instance
    // ORE -> ORE variables
	public static $current_user; // If logged in upon instantiation, it is a user object.
    // returns an instance of this class if called, instantiating if necessary
    public $errors;

    public static function get_instance() {
        NULL === self::$instance and self::$instance = new self();
        return self::$instance;
    }

    public function get_errors() {
        if (!is_array($this->errors)) {
            $this->errors = new WP_Error();
        }
        return $this->errors;
    }

    // this starts everything...
    public function init() {
        $this->log('in init');

        $this->log('setting up scripts');
        // jsquery validate script
        wp_register_script(
            'jquery-validate',
            'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.17.0/jquery.validate.js',
            //ORE_URL.'js/jquery.validate.js',
            array('jquery'), true
        );
        wp_enqueue_script(ORE_SCRIPT, ORE_URL.'js/ore_script.js', array(
            'jquery', 'jquery-form', 'jquery-validate'));
        $user_array = $this->get_user();
        $user_country = (isset($user_array['country'])) ? $user_array['country'] : "";
        // info to send to jQuery...
        $ore_data = array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce_submit' => wp_create_nonce('ore-submit-nonce'),
            'container' => ORE_CONTAINER,
            'pw_min' => ORE_MIN_PASSWORD_LENGTH,
            'dn_min' => ORE_MIN_DISPLAY_NAME_LENGTH,
            'un_min' => ORE_MIN_USERNAME_LENGTH,
            'login_status' => ORE_LOGIN_STATUS,
            'user' => $user_array,
            'modals' => $this->get_modals(),
            'country_select' => $this->get_country_selector($user_country)
        );
        // if there're errors, include that.
        if (count($this->errors)) {
            $this->log('sending the errors array');
            $ore_data['errors'] = $this->errors;
        }
        wp_localize_script(ORE_SCRIPT, 'ore_data', $ore_data);
        // our css
        wp_register_style(ORE_STYLE, ORE_URL.'css/ore_style.css');
        wp_enqueue_style(ORE_STYLE);
        // add the ajax handlers
        // this enables the register-enrol service for authenticated users...
        add_action('wp_ajax_ore_submit', array($this, 'ajax_submit'));
        add_action('wp_ajax_ore_email_check', array($this, 'ajax_email_check'));
        add_action('wp_ajax_ore_username_check', array($this, 'ajax_username_check'));
        // and, just as importantly, unauthenticated users...
        add_action('wp_ajax_nopriv_ore_submit', array($this, 'ajax_submit'));
        add_action('wp_ajax_nopriv_ore_email_check', array($this, 'ajax_email_check'));
        add_action('wp_ajax_nopriv_ore_username_check', array($this, 'ajax_username_check'));
    }

    // give realtime info on whether or not an email is unique in the system
    public function ajax_email_check() {
        if ( ! wp_verify_nonce(sanitize_text_field($_POST['nonce_submit']), 'ore-submit-nonce') ) {
            die ("Busted - someone's trying something funny in submit!");
        } else {
            $this->log('ore-submit-nonce all good.');
        }
        $new = $_POST['email'];
        $current = (isset($_POST['current_email'])) ? $_POST['current_email'] : null;
        $this->log('in email_check - new: '.$new.', current: '.$current);
        if ($new != $current && email_exists($new)) {
            echo json_encode('This email is already in use - you must select a unique email. Or perhaps you already have an account? If so, please "Login" instead.');
        } else{
            echo json_encode('true');
        }
        die();
    }

    // give realtime info on whether or not a username is unique in the system
    public function ajax_username_check() {
        if ( ! wp_verify_nonce(sanitize_text_field($_POST['nonce_submit']), 'ore-submit-nonce') ) {
            die ("Busted - someone's trying something funny in submit!");
        } else {
            $this->log('ore-submit-nonce all good.');
        }
        $username = $_POST['username'];
        $this->log('in username_check: '.$username);
        if (username_exists($username)) {
            echo json_encode('this username is already in use - you must select a unique username. Or is it possible you already have an account? If so, please "Login" instead.');
        } else{
            echo json_encode('true');
        }
        die();
    }

    // the function called after the ore-submit button is clicked in our form
    public function ajax_submit() {
       //$this->log('in ajax_submit: '.print_r($_POST, true));
       // check if the submitted nonce matches the generated nonce created in the auth_init functionality
       if ( ! wp_verify_nonce(sanitize_text_field($_POST['nonce_submit']), 'ore-submit-nonce') ) {
           die ("Busted - someone's trying something funny in submit!");
       } else {
           $this->log('ore-submit-nonce all good.');
       }
       $this->log("processing submit form...");
       // generate the response
       header( "Content-Type: application/json" );
       $response = $this->process();
       $this->log('returned result of '.print_r($result, true));
       if (!is_wp_error($response)) {
           $this->log('successfully completed '.$_POST['form_action']);
           $this->ajax_response(array('success' => $response));
       } else {
           $this->log('We\'ve got an error!');
           $this->log('failed to complete '.$_POST['form_action'].' with errors '.print_r($response, true));
           $this->ajax_response(array('failed' => $response));
       }
       $this->log('ajax_submit done, dying...');
       wp_die();
    }

    public function process() {
        $this->log('in process: '.print_r($_POST, true));
        $form_action = $_POST['form_action'];
        $response = false;
        switch ($form_action) {
            case 'login':
                $this->log('login');
                $response = $this->login();
                break;
            case 'reset_password':
                $this->log('password_reset');
                $response = $this->reset_password();
                break;
            case 'update_password':
                $this->log('update_password');
                $response = $this->update_password();
                break;
            case 'register':
                $this->log('register');
                $response = $this->register();
                break;
            case 'edit_profile':
                $this->log('edit_profile');
                $response = $this->edit_profile();
                break;
            case 'session_expired':
                $this->log('session_expired');
                $response = $this->session_expired();
                break;
            case 'enrol':
                $this->log('enrol');
                $response = $this->enrol();
                break;
            case 'leave':
                $this->log('leave');
                $response = $this->leave();
                break;
            default:
                $this->log('default action');
                break;
        }
        // if response isn't 'true'
        if (is_wp_error($response)) {
            $this->log('failed to complete '.$form_action);
            $this->log('response is '.print_r($response, true));
            //$this->ajax_response(array('success' => $response));
        }
        return $response;
    }
    // login process
    public function login() {
        $user_data = array(
            'user_login' => sanitize_text_field($_POST['credential']),
            'user_password' => $_POST['password'],
            'remember' => true
        );
        $response = wp_signon($user_data, false);
        $this->log('login: '.print_r($login, true));
        // if login fails
        if (is_wp_error($response)) {
        } else {
            $user = $response; // just for clarity :)
            wp_set_auth_cookie($user->ID);
            // reusing response to send info back to the JS inferface
            $response = array(
                'user_id' => $user->ID,
                'username' => $user->data->user_login,
                'display_name' => $user->data->display_name
            );
        }
        return $response;
    }
    // reset password process - from "function retrieve_password()" in wp-login.php
    public function reset_password() {
        // get the error object
        $errors = $this->get_errors();
        // ensure we have valid credentials from with which to find the relevant user account
        if (empty($_POST['credential']) || !is_string($_POST['credential'])) {
            $this->log('missing the credential');
            $errors->add(ORE_ERROR_LABEL, 'You must enter a username or email address.');
        } elseif (strpos($_POST['credential'], '@')) {
            $this->log('got an email: '.$_POST['credential']);
            $user_data = get_user_by('email', trim(wp_unslash($_POST['credential'])));
            if (empty($user_data)) {
                $this->log('no user found');
                $errors->add(ORE_ERROR_LABEL, 'There is no user registered with that email address.');
            } else {
                $this->log('found user id: '.$user_data->ID);
            }
        } else {
            $login = trim($_POST['credential']);
            $user_data = get_user_by('login', $login);
        }
        // call relevant hook
        do_action('lostpassword_post', $errors);
        // deal with errors
        if ($errors->get_error_code()) {
            return $errors;
        }
        if (!$user_data) {
            $errors->add(ORE_ERROR_LABEL, 'You have entered an invalid username or email.');
            return $errors;
        }
        // Redefining user_login ensures we return the right case in the email.
        $user_login = $user_data->user_login;
        $user_email = $user_data->user_email;
        $key = get_password_reset_key($user_data);
        if (is_wp_error($key)) {
            return $key;
        }
        if (is_multisite()) {
            $site_name = get_network()->site_name;
        } else {
            // The blogname option is escaped with esc_html on the way into the database
            // in sanitize_option we want to reverse this for the plain text arena of emails.
            $site_name = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
        }
        $message = __( 'Someone has requested a password reset for the following account:' ) . "\r\n\r\n";
        /* translators: %s: site name */
        $message .= sprintf( __( 'Site Name: %s'), $site_name ) . "\r\n\r\n";
        /* translators: %s: user login */
        $message .= sprintf( __( 'Username: %s'), $user_login ) . "\r\n\r\n";
        $message .= __( 'If this was a mistake, just ignore this email and nothing will happen.' ) . "\r\n\r\n";
        $message .= __( 'To reset your password, visit the following address:' ) . "\r\n\r\n";
        $message .= '<' . network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . ">\r\n";
        /* translators: Password reset email subject. %s: Site name */
        $title = sprintf( __( '[%s] Password Reset' ), $site_name );
        // apply the filter for password retrieval
        $title = apply_filters( 'retrieve_password_title', $title, $user_login, $user_data );
        if ($message && !wp_mail($user_email, wp_specialchars_decode($title), $message)) {
            wp_die(__('The email could not be sent.')."<br />\n".__('Possible reason: your host may have disabled the mail() function.'));
        }
        return true;
    }
    // update password, requires current password.
    public function update_password() {
        $this->log('in update_password');
        $errors = $this->get_errors();
        $user_id = ($_POST['user_id'] == '') ? false : trim($_POST['user_id']);
        $current_password = (!isset($_POST['current-password']) || $_POST['current-password'] == '') ? false : $_POST['current-password'];
        $new_password = (!isset($_POST['new-password']) || $_POST['new-password'] == '') ? false : $_POST['new-password'];
        $confirm_password = (!isset($_POST['confirm-password']) || $_POST['confirm-password'] == '') ? false : $_POST['confirm-password'];
        // we don't want to log passwords under normal circumstances!!
        //$this->log('current_password = ('.$current_password.')');
        if (!$user_id ||
            !$current_password ||
            !$new_password ||
            !$confirm_password) {
            $this->log('found a problem with the values');
            if (!$user_id) {
                $errors->add(ORE_ERROR_LABEL, 'No user id was provided!');
            }
            if (!$current_password) {
                $errors->add(ORE_ERROR_LABEL, 'You must enter your current password.');
            }
            if (!$new_password) {
                $errors->add(ORE_ERROR_LABEL, 'You must enter your new password.');
            }
            if (!$confirm_password) {
                $errors->add(ORE_ERROR_LABEL, 'You must enter your new password again to guard against typos.');
            } else if ($confirm_password != $new_password) {
                $errors->add(ORE_ERROR_LABEL, 'Your confirmation password is not the same as your new password.');
            }
            $this->log('found errors, failing.');
            return $errors;
        } else {
            $this->log('no errors, proceeding.');
            // now check the current password
            // get the username based on the userid:
            $user = get_userdata($user_id);
            $this->log('returned user '.print_r($user, true));
            $username = $user->data->user_login;
            // check if that password and username are correct for that user
            $this->log('testing your password for user '.$username.' to see if it\'s correct: '.$current_password);
            $error_or_user = wp_authenticate_username_password(null, $username, $current_password);
            // if authentication failed (with an error)
            if (is_wp_error($error_or_user)) {
                $this->log('not correct');
                $errors->add(ORE_ERROR_LABEL, 'Your current password is not valid for your user.');
            // if it worked, let's change the password.
            } else {
                $this->log('ok, saving new password');
                // this is the user found previously
                $user->data->user_pass = $new_password;
                // this actually sets the new password, and potentially mails
                // the user and admin to alert them to the change.
                if (wp_update_user($user)) {
                    $this->log('update to password appears successful');
                    return true;
                } else {
                    $this->log('update to password resulted in an error...'.print_r($error, true));
                    $error->add(ORE_ERROR_LABEL, 'Failed to update user for some reason.');
                }
            }
            if ($errors->get_error_code()) {
                $this->log('Password update failed!');
                $this->errors = $errors;
                return $errors;
            } else {
                $this->log('Password updated!');
                return true;
            }
        }
    }
    // register a new WordPress user
    public function register() {
        $this->log('in register');
        $errors = $this->get_errors();
        $first = ($_POST['first-name'] == '') ? false : sanitize_text_field(trim($_POST['first-name']));
        $last = ($_POST['last-name'] == '') ? false : sanitize_text_field(trim($_POST['last-name']));
        $username = ($_POST['username'] == '') ? false : sanitize_text_field(trim($_POST['username']));
        $display = ($_POST['display-name'] == '') ? false : sanitize_text_field(trim($_POST['display-name']));
        $password = (!isset($_POST['password']) || $_POST['password'] == '') ? false : $_POST['password'];
        $confirm_password = (!isset($_POST['confirm-password']) || $_POST['confirm-password'] == '') ? false : $_POST['confirm-password'];
        $email = (!isset($_POST['email']) || $_POST['email'] == '') ? false : $_POST['email'];
        $country = (!isset($_POST['ore-country']) || $_POST['ore-country'] == '') ? false : $_POST['ore-country'];
        // if we're missing any of these fields, spit back an error...
        if (!$first || !$last || !$username || !$display || !$password ||
            !$confirm_password || !$email || !$country) {
            $this->log('found a problem with the submitted values');
            if (!$first) {
                $errors->add(ORE_ERROR_LABEL, 'No first name was provided!');
            }
            if (!$last) {
                $errors->add(ORE_ERROR_LABEL, 'No last name was provided!');
            }
            if (!$display) {
                $errors->add(ORE_ERROR_LABEL, 'No display name was provided!');
            }
            if (!$username) {
                $errors->add(ORE_ERROR_LABEL, 'No username was provided!');
            }
            if (!$password) {
                $errors->add(ORE_ERROR_LABEL, 'You must enter a suitable password.');
            }
            if (!$confirm_password) {
                $errors->add(ORE_ERROR_LABEL, 'You must enter your password again to guard against typos.');
            } else if ($confirm_password != $new_password) {
                $errors->add(ORE_ERROR_LABEL, 'Your confirmation password is not the same as your password.');
            }
            if (!$email) {
                $errors->add(ORE_ERROR_LABEL, 'You must enter a valid email address.');
            }
            if (!$country) {
                $errors->add(ORE_ERROR_LABEL, 'You must select a country to associate with.');
            }
            $this->errors = $errors;
            return $errors;
        } else {
            $this->log('all details were provided, proceeding with registration...');
            // we've got all the details, now check for problems with any of them:
            // like...
            // is the username poorly formed (we use the originally entered name, trimmed, here)
            if (!validate_username(trim($_POST['username']))) {
                $errors->add(ORE_ERROR_LABEL, 'This username is invalid because it contains illegal characters. Please select a username made up of lower case letters and numbers only.');
            }
            // is the username already taken?
            if (username_exists($username)) {
                $errors->add(ORE_ERROR_LABEL, 'This username is already taken by another user. Please select a different one. Or is it possible that you\'ve already got an account in this system? In that case, please try to "Login" instead.');
            }
            /**
             * Filters the email address of a user being registered.
             *
             * @since 2.1.0
             *
             * @param string $email The email address of the new user.
             */
            $email = apply_filters( 'user_registration_email', $email );

            // now check email for uniqueness
            if (!is_email(trim($_POST['email']))) {
                $errors->add(ORE_ERROR_LABEL, 'The email you specified is not valid.');
            }
            // is the email already taken?
            if (email_exists($email)) {
                $errors->add(ORE_ERROR_LABEL, 'The email you specified is already taken by another user - please select a different one. Or is it possible that you\'ve already got an account in this system? If so, please try to "Login" instead.');
            }
           /**
             * Fires when submitting registration form data, before the user is created.
             *
             * @since 2.1.0
             *
             * @param string   $username The submitted username after being sanitized.
             * @param string   $email The submitted email.
             * @param WP_Error $errors Contains any errors with submitted username and email,
             * e.g., an empty field, an invalid username or email, or an existing username or email.
             */
            do_action('register_post', $username, $email, $errors);
            /**
             * Filters the errors encountered when a new user is being registered.
             *
             * The filtered WP_Error object may, for example, contain errors for an invalid
             * or existing username or email address. A WP_Error object should always returned,
             * but may or may not contain errors.
             *
             * If any errors are present in $errors, this will abort the user's registration.
             *
             * @since 2.1.0
             *
             * @param WP_Error $errors               A WP_Error object containing any errors encountered
             *                                       during registration.
             * @param string   $sanitized_user_login User's username after it has been sanitized.
             * @param string   $user_email           User's email.
             */
            $errors = apply_filters('registration_errors', $errors, $username, $email);
            // if there're errors to this point return with them...
            if ($errors->get_error_code()) {
                $this->errors = $errors;
                return $errors;
            // if not, proceed with creating the user!
            } else {
                // build the userdata array used to create the user.
                // We'll deal with the user's country affiliation later,
                // as it's not part of the default user model.
                $userdata = array(
                    'first_name' => $first,
                    'last_name' => $last,
                    'user_login' => $username,
                    'display_name' => $display,
                    'user_pass' => $password,
                    'user_email' => $email
                );
                // actually try to create the user!
                $errors_or_id = wp_insert_user($userdata);
                if (is_object($errors_or_id) && $errors_or_id->get_error_code()) {
                    $this->log('Creating the new user failed. Array: '.print_r($userdata, true));
                    $this->errors = $errors_or_id;
                    return $errors_or_id;
                // if we didn't get an error, set the resulting user's country!
                } else {
                    $this->log('setting the user (id: '.$errors_or_id.') to '.$country.'.');
                    $this->set_country($errors_or_id, $country);
                    // this has to be in the right format to provide info
                    // to the JS modals...
                    $response = array(
                        'user_id' => $errors_or_id,
                        'username' => $username,
                        'display_name' => $display,
                        'email' => $email,
                        'first_name' => $first,
                        'last_name' => $last,
                    );
                    return $response;
                }
            }
            // no errors picked up...
            return true;
        }
    }
    // a user editing their profile $details
    public function edit_profile() {
        $this->log('in edit_profile');
        $errors = $this->get_errors();
        // $user_id needs to be an integer...
        $user_id = (is_int($_POST['user_id'])) ? false : $_POST['user_id'];
        $first = ($_POST['first-name'] == '') ? false : sanitize_text_field(trim($_POST['first-name']));
        $last = ($_POST['last-name'] == '') ? false : sanitize_text_field(trim($_POST['last-name']));
        $display = ($_POST['display-name'] == '') ? false : sanitize_text_field(trim($_POST['display-name']));
        $email = (!isset($_POST['email']) || $_POST['email'] == '') ? false : $_POST['email'];
        $country = (!isset($_POST['ore-country']) || $_POST['ore-country'] == '') ? false : $_POST['ore-country'];
        $user = null;
        $existing_email = null;
        // if we're missing any of these fields, spit back an error...
        if (!$user_id || !$first || !$last || !$display || !$email || !$country) {
            $this->log('found a problem with the submitted values');
            if (!$user_id) {
                $errors->add(ORE_ERROR_LABEL, 'No valid user ID was provided!');
            }
            if (!$first) {
                $errors->add(ORE_ERROR_LABEL, 'No first name was provided!');
            }
            if (!$last) {
                $errors->add(ORE_ERROR_LABEL, 'No last name was provided!');
            }
            if (!$display) {
                $errors->add(ORE_ERROR_LABEL, 'No display name was provided!');
            }
            if (!$email) {
                $errors->add(ORE_ERROR_LABEL, 'You must enter a valid email address.');
            }
            if (!$country) {
                $errors->add(ORE_ERROR_LABEL, 'You must select a country to associate with.');
            }
            $this->errors = $errors;
            return $errors;
        } else {
            $this->log('checking for a valid user_id: '.$user_id);
            if ($user = get_userdata($user_id)) {
                $this->log('found user with id '.$user_id.'! Grabbing details');
                $username = sanitize_text_field($_POST['username']);
                $existing_email = $_POST['existing_email'];
                $this->log('user: '.$username.', email: '.$existing_email.'.');
            } else {
                $errors->add(ORE_ERROR_LABEL, 'The supplied user ID, '.$user_id.' does not correspond to a valid user.');
                return errors;
            }
            $this->log('all details were provided, proceeding with saving your profile...');
            // now check email for uniqueness
            if (!is_email(trim($_POST['email']))) {
                $errors->add(ORE_ERROR_LABEL, 'Your email is not valid.');
            }
            // is the email already taken?
            // assuming it's the existing one, unchanged.
            $this->log('email is '.$email.', existing email is '.$existing_email.'.');
            if ($email != $existing_email && email_exists($email)) {
                $errors->add(ORE_ERROR_LABEL, 'Your email is already taken by another user - please select a different one. Or is it possible that you\'ve already got an account in this system? In that case, please try  to "Login" instead.');
            }
            // if there're errors to this point return with them...
            if ($errors->get_error_code()) {
                $this->errors = $errors;
                return $errors;
            // if not, proceed with creating the user!
            } else {
                // build the userdata array used to create the user.
                // We'll deal with the user's country affiliation later,
                // as it's not part of the default user model.
                $userdata = array(
                    'ID' => $user_id,
                    'first_name' => $first,
                    'last_name' => $last,
                    'user_login' => $username,
                    'display_name' => $display
                );
                // only save the email again if it's *different* from the user's
                // existing email address, as they might otherwise hit an
                // "email already in use" error.
                if ($email != $existing_email) {
                    $userdata['user_email'] = $email;
                }
                // actually try to create the user!
                $errors_or_id = wp_update_user($userdata);
                if (is_wp_error($errors_or_id)) {
                    $this->log('Creating the new user failed. Array: '.print_r($userdata, true));
                    $this->errors = $errors_or_id;
                    return $errors_or_id;
                // if we didn't get an error, set the resulting user's country!
                } else {
                    $this->log('setting the user (id: '.$errors_or_id.') to '.$country.'.');
                    $this->set_country($errors_or_id, $country);
                    // this has to be in the right format to provide info
                    // to the JS modals...
                    $response = array(
                        'user_id' => $errors_or_id,
                        'username' => $username,
                        'display_name' => $display,
                        'email' => $email,
                        'first_name' => $first,
                        'last_name' => $last,
                    );
                    return $response;
                }
            }
            // no errors picked up...
            return true;
        }
    }
    // enrol a user in a course
    public function enrol() {
        $errors = $this->get_errors();
        if (empty($_POST['user_id']) || empty($_POST['course_id'])) {
            if (empty($_POST['user_id'])) {
                $errors->add(ORE_ERROR_LABEL, 'No user id was provided!');
            }
            if (empty($_POST['course_id'])) {
                $errors->add(ORE_ERROR_LABEL, 'No course id was provided!');
            }
            return $errors;
        }
        $user_id = $_POST['user_id'];
        $course_id = $_POST['course_id'];
        $this->log('user '.$user_id.' enrolling in course '.$course_id);
        $response = add_user_to_blog($course_id, $user_id, ORE_COURSE_ROLE);
        $check = is_user_member_of_blog($user_id, $course_id);
        if ($check) {
            $this->log('user '.$user_id.' is a member of '.$course_id);
        } else {
            $this->log('user '.$user_id.' is not a member of '.$course_id);
        }
        if (is_wp_error($response)) {
            $this->log('enrolment failed');
            return $response;
        }
        $this->log('enrolment succeeded');
        return true;
    }
    // unenrol a user from a course
    public function leave() {
        $errors = $this->get_errors();
        if (empty($_POST['user_id']) || empty($_POST['course_id'])) {
            if (empty($_POST['user_id'])) {
                $errors->add(ORE_ERROR_LABEL, 'No user id was provided!');
            }
            if (empty($_POST['course_id'])) {
                $errors->add(ORE_ERROR_LABEL, 'No course id was provided!');
            }
            return $errors;
        }
        $user_id = $_POST['user_id'];
        $course_id = $_POST['course_id'];
        $this->log('user '.$user_id.' leaving course '.$course_id);
        $response = remove_user_from_blog($user_id, $course_id);
        $check = is_user_member_of_blog($user_id, $course_id);
        if ($check) {
            $this->log('user '.$user_id.' is still a member of '.$course_id);
        } else {
            $this->log('user '.$user_id.' is no longer a member of '.$course_id);
        }
        if (is_wp_error($response)) {
            $this->log('unenrolment failed');
            return $response;
        }
        $this->log('unenrolment succeeded');
        return true;
    }
    // get a user object suitable for passing to javascript
    public function get_user($user_id = null) {
        // get the current user
        if ($user_id === null) {
            $current = wp_get_current_user();
            $user_id = $current->data->ID;
            // for security's sake, don't even show the password hash...
            unset($current->data->user_pass);
        } else {
            if (!($current = get_userdata($user_id))){
                $this->log('There is no user associated with user_id: '.$user_id);
                return false;
            }
        }
        //$this->log('current user: '.print_r($current, true));
        // initialise this with the default values
        //$user = $this->data;
        $user = array();
        // set instance values from $current
        $user['user_id'] = $current->ID;
        $user['first_name'] = $current->first_name;
        $user['last_name'] = $current->last_name;
        $user['username'] = $current->data->user_login;
        $user['email'] = $current->data->user_email;
        $user['display_name'] = $current->data->display_name;
        $user['country'] = $this->get_country($user_id);
        $user['country_name'] = $this->get_country_name($user['country']);
        $user['profile_url'] = $this->get_profile_url($user_id);
        $user['avatar_url'] = explode('?', get_avatar_url($user_id,
                array('default'=>'identicon', 'processed_args'=>$avatar_args)))[0];
        $user['log_out_url'] = wp_logout_url(get_permalink());
        $this->log('avatar_args... '.print_r($avatar_args, true));
        // sort out the course context
        $user['course'] = null;
        //$site = get_current_site();
        $site_id = get_current_blog_id();
        $site = get_site($site_id);
        $this->log('got site: '.print_r($site, true));
        // if this is greater than 1, it's not the default site, meaning it's a course site.
        if ($site_id > 1) {
            $details = get_blog_details($site_id);
            //$site = get_site($site_id);
            $tag = $this->get_site_tag($site);
            $enrolled = is_user_member_of_blog($current->ID, $site_id);
            $course = array(
                'course_id' => $site_id,
                'course_tag' => $tag,
                'enrolled' => $enrolled,
                'course_title' => $details->blogname,
                'course_url' => $details->siteurl,
                'course_path' => $details->pathinfo,
                'course_domain' => $details->domain,
            );
            $this->log('course object: '.print_r($course, true));
            $user['course'] = $course;
        }
        // replace default user object
        //$this->data = $user;
        return $user;
    }

    // given a site object, return the site's name
    public function get_site_tag($site) {
        return strtolower(substr($site->path,1,-1));
    }

    // http://2.gravatar.com/avatar/88814fd4a3a14b6a85b56980744c87fd?s=26&r=g
    // get the user's country
    public function get_country($id) { return $this->get_meta($id, 'usercountry'); }
    public function set_country($id, $val) { return $this->set_meta($id, 'usercountry', $val); }
    public function get_country_name($country_code) {
        global $countries;
        if ($country_name = $countries[$country_code]) { return $country_name; }
        return false;
    }
    // get the profile edit link
    // see https://stackoverflow.com/questions/20724301/get-wordpress-user-profile-url-link-by-id#25179230
    public function get_profile_url($user_id) {
        if (get_current_user_id() == $user_id) { $url = admin_url('profile.php'); }
        else { $url = add_query_arg( 'user_id', $user_id, self_admin_url('user-edit.php')); }
        return $url;
    }
    // returns gets meta value if set, otherwise returns null
    public function get_meta($id, $key) {
        if ($val = get_user_meta($id, $key, true)) { return $val; }
        return null;
    }
    // returns true if value is set, false otherwise
    public function set_meta($id, $key, $val) {
        if (update_user_meta($id, $key, $val)) { return true; }
        return false;
    }

    // get modal dialogues in a form that can be injected by javascript
    public function get_modals() {
        global $modals;

        $dialogs = array();
        foreach($modals as $index => $val) {
            $markup = '<div id="ore-modal-'.$val['token'].'" class="ore-modal modal fade">';
            $markup .= '<div class="modal-dialog"><form class="ore-form modal-body ore-body"><fieldset class="modal-content">';
            if (isset($val['title'])) {
                //$this->log('getting the modal: "'.$val['title'].'"');
                $markup .= '<div class="modal-header">';
                $markup .= '<button class="close" type="button" data-dismiss="modal" aria-label="Close"><span class="close" aria-hidden="true" title="This will close this window, and any changes will be lost...">x</span></button>';
                $markup .= '<legend class="ore-title">'.$val['title'].'</legend>';
                $markup .= '</div><!-- modal-header -->';
            }
            if (isset($val['markup'])) {
                $markup .= $val['markup'];
            }
            $button_classes = 'button ore-button';
            $both = (is_array($val['default']) && is_array($val['alternative'])) ? true : false;
            if (is_array($val['default'])) {
                $default = $val['default'];
                $classes = $button_classes.' ore-default';
                $div_classes = ($both) ? ' ore-left' : ' singleton';
                $name = 'ore-default-'.$val['token'];
                $markup .= '<div class="modal-footer"><div class="ore-default-wrapper ore-modal-block'.$div_classes.'">';
                if (isset($default['label'])) {
                    if (isset($default['class'])) { $classes .= ' '.$default['class']; }
                    if (isset($default['destination'])) {
                        $this->log('setting default destination to '.$default['destination']);
                        $id = 'ore-'.$default['destination'].'-default-action';
                    } else {
                        $id = 'ore-'.$val['token'].'-default-action';
                    }
                    $markup .= '<button id="'.$id.'" name="'.$name.'" class="'.$classes.'" type="submit">'.
                        $default['label'].'</button>';
                    //unset($default['label']);
                    if (isset($default['detail'])) {
                        //$markup .= '<p class="ore-detail">'._e($default['detail']).'</p>';
                        $markup .= '<p class="ore-detail">'.$default['detail'].'</p>';
                        //unset($default['detail']);
                    }
                }
                $markup .= '</div><!-- ore-default-wrapper -->';
                $dialogs[$index]['default'] = $default;
                if (is_array($val['alternative'])) {
                    $alt = $val['alternative'];
                    $classes = $button_classes.' ore-alternative';
                    $div_classes = ($both) ? ' ore-right' : '';
                    $name = 'ore-alternatve-'.$val['token'];
                    $markup .= '<div class="ore-alternative-wrapper ore-modal-block'.$div_classes.'">';
                    if (isset($alt['label'])) {
                        if (isset($alt['class'])) { $classes .= ' '.$alt['class']; }
                        if (isset($alt['destination'])) {
                            $this->log('setting alternative destination to '.$alt['destination']);
                            $id = 'ore-'.$alt['destination'].'-alternative-action';
                        } else {
                            $id = 'ore-'.$val['token'].'-alternative-action';
                        }
                        $markup .= '<button id="'.$id.'" name="'.$name.'" class="'.$classes.'" type="button">'.
                            $alt['label'].'</button>';
                        //unset($alt['label']);
                        if (isset($alt['detail'])) {
                            //$markup .= '<p class="ore-detail">'._e($alt['detail']).'</p>';
                            $markup .= '<p class="ore-detail">'.$alt['detail'].'</p>';
                            //unset($alt['detail']);
                        }
                    }
                    $markup .= '</div><!-- ore-alt-wrapper -->';
                    $dialogs[$index]['alternative'] = $alt;
                }
                $markup .= '</div><!-- modal-footer -->';
            }
            $markup .= '</fieldset><!-- modal-content --></form><!--form--></div><!-- modal-dialog --></div><!-- ore-modal -->';
            $dialogs[$index]['markup'] = $markup;
            //$this->log('dialog['.$index.']: '.print_r($dialogs[$index], true));
        }
        return $dialogs;
    }

    // return a suitably formatted select widget with the countries in it to pass
    // to jquery to use
    public function get_country_selector($user_country = "") {
        global $countries;
        $this->log('country for user: '.$user_country);
        $user_country = "";
        $selector = '<select id="ore-country" name="country" class="ore-country form-control">';
        $selector .= '<option value=""></option>';
        foreach($countries as $abbr => $country) {
            $selected = "";
            if ($user_country == $abbr) {
                $this->log('selected country for user: '.$abbr);
                $selected = ' selected="true"';
            }
            $selector .= '<option value="' . $abbr . '"'.$selected.'>' . $country . '</option>';
        }
        $selector .= '</select><!-- ore-country -->';
        return $selector;
    }

}
