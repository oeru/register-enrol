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

    public static function get_instance() {
        NULL === self::$instance and self::$instance = new self();
        return self::$instance;
    }

    // this starts everything...
    public function init() {
        $this->log('in init');

        $this->log('setting up scripts');
        // jsquery validate script
        wp_register_script(
            'jquery-validate',
            'https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.15.0/jquery.validate.js',
            array('jquery'), true
        );
        wp_enqueue_script(ORE_SCRIPT, ORE_URL.'js/ore_script.js', array(
            'jquery', 'jquery-form', 'jquery-validate'));
        $user_array = $this->get_user();
        $user_country = (isset($user_array['country'])) ? $user_array['country'] : "";
        wp_localize_script(ORE_SCRIPT, 'ore_data', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce_submit' => wp_create_nonce('ore-submit-nonce'),
            'container' => ORE_CONTAINER,
            'login_status' => ORE_LOGIN_STATUS,
            'user' => $user_array,
            'modals' => $this->get_modals(),
            'country_select' => $this->get_country_selector($user_country)
        ));
        // our css
        wp_register_style(ORE_STYLE, ORE_URL.'css/ore_style.css');
        wp_enqueue_style(ORE_STYLE);
        // add the ajax handlers
        // this enables the register-enrol service for authenticated users...
        add_action('wp_ajax_ore_submit', array($this, 'ajax_submit'));
        add_action('wp_ajax_ore_emailcheck', array($this, 'ajax_email_check'));
        add_action('wp_ajax_ore_usernamecheck', array($this, 'ajax_username_check'));
        // and, just as importantly, unauthenticated users...
        add_action('wp_ajax_nopriv_ore_submit', array($this, 'ajax_submit'));
        add_action('wp_ajax_nopriv_ore_email_check', array($this, 'ajax_email_check'));
        add_action('wp_ajax_nopriv_ore_username_check', array($this, 'ajax_username_check'));
        // allows us to add a class to our post
        //add_filter('body_class', array($this, 'add_post_class'));
        //add_filter('post_class', array($this, 'add_post_class'));
        // create a default page if it doesn't already exist...
        //$this->log('create post: '.ORE_GETSTARTED_SLUG);
        //$this->create_post(ORE_GETSTARTED_SLUG);
    }

    // give realtime info on whether or not an email is unique in the system
    public function ajax_email_check() {
        global $wpdb;
        if (email_exists($_POST['email'])) {
            echo json_encode('error.');
        } else{
            echo json_encode('true');
        }
        die();
    }

    // give realtime info on whether or not a username is unique in the system
    public function ajax_username_check() {
        global $wpdb;
        if (username_exists($_POST['username'])) {
            echo json_encode('error.');
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
       $this->ajax_response(array('success' => $this->process()));
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
            case 'password_reset':
                $this->log('password_reset');
                break;
            case 'register':
                $this->log('register');
                break;
            case 'edit_profile':
                $this->log('edit_profile');
                break;
            case 'session_expired':
                $this->log('session_expired');
                break;
            case 'enrol':
                $this->log('enrol');
                $response = $this->enrol($_POST['user_id'], $_POST['course_id']);
                break;
            case 'leave':
                $this->log('leave');
                $response = $this->leave($_POST['user_id'], $_POST['course_id']);
                break;
            default:
                $this->log('default action');
                break;
        }
        // if response isn't 'true'
        if ($response != true){
            $this->log('failed to complete '.$form_action);
            if (is_array($response)) {
                $this->log('response is '.print_r($response, true));
                $this->ajax_response(array('success' => array(
                    $form_action => false,
                    'error' => $response->get_error_message()
                )));
            }
        }
        return true;
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
            return $response;
        }
        return true;
    }
    // enrol a user in a course
    public function enrol($user_id, $course_id) {
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
    public function leave($user_id, $course_id) {
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
            $markup .= '<div class="modal-dialog"><div class="modal-content">';
            if (isset($val['title'])) {
                //$this->log('getting the modal: "'.$val['title'].'"');
                $markup .= '<div class="modal-header">';
                $markup .= '<button class="close" type="button" data-dismiss="modal" aria-label="Close"><span class="close" aria-hidden="true" title="This will close this window, and any changes will be lost...">x</span></button>';
                $markup .= '<h1 class="ore-title">'.$val['title'].'</h1>';
                $markup .= '</div><!-- modal-header -->';
            }
            if (isset($val['markup'])) {
                $markup .= '<form class="ore-form modal-body ore-body">'.$val['markup'].'</form>';
            }
            $button_classes = 'button ore-button';
            $both = (is_array($val['default']) && is_array($val['alternative'])) ? true : false;
            if (is_array($val['default'])) {
                $default = $val['default'];
                $classes = $button_classes.' ore-default';
                $div_classes = ($both) ? ' ore-left' : 'singleton';
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
                    $markup .= '<span id="'.$id.'" name="'.$name.'" class="'.$classes.'">'.
                        $default['label'].'</span>';
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
                        $markup .= '<span id="'.$id.'" name="'.$name.'" class="'.$classes.'">'.
                            $alt['label'].'</span>';
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
            $markup .= '</div><!-- modal-content --></div><!-- modal-dialog --></div><!-- ore-modal -->';
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
