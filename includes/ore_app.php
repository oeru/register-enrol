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
        // and, just as importantly, unauthenticated users...
        add_action('wp_ajax_nopriv_ore_submit', array($this, 'ajax_submit'));
        // allows us to add a class to our post
        add_filter('body_class', array($this, 'add_post_class'));
        add_filter('post_class', array($this, 'add_post_class'));
        // create a default page if it doesn't already exist...
        $this->log('create post: '.ORE_GETSTARTED_SLUG);
        $this->create_post(ORE_GETSTARTED_SLUG);
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
        switch ($form_action) {
            case 'login':
                $this->log('login');
                $user_data = array(
                    'user_login' => sanitize_text_field($_POST['credential']),
                    'user_password' => $_POST['password'],
                    'remember' => true
                );
                $login = wp_signon($user_data, false);
                $this->log('login: '.print_r($login, true));
                // if login fails
                if (is_wp_error($login)) {
                    $this->ajax_response(array(
                        'loggedin' => false,
                        'result' => $login->get_error_message()
                    ));
                } else { // or succeeds
                    $this->ajax_response(array(
                        'loggedin' => true,
                        'result' => 'login successful'
                    ));
                    // Todo - record this with the activity register!
                }
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
                break;
            case 'leave':
                $this->log('leave');
                break;
            default:
                $this->log('default action');
                break;
        }
        return true;
    }

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
                $markup .= '<div class="ore-form modal-body ore-body">'.$val['markup'].'</div>';
            }
            $button_classes = 'button ore-button';
            $both = (is_array($val['default']) && is_array($val['alternative'])) ? true : false;
            if (is_array($val['default'])) {
                $id = 'ore-'.$val['token'].'-default-action';
                $classes = $button_classes.' ore-default';
                $div_classes = ($both) ? ' ore-left' : '';
                $name = 'ore-default-'.$val['token'];
                $default = $val['default'];
                $markup .= '<div class="modal-footer"><div class="ore-default-wrapper'.$div_classes.'">';
                if (isset($default['label'])) {
                    if (isset($default['class'])) { $classes .= ' '.$default['class']; }
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
                    $id = 'ore-'.$val['token'].'-alternative-action';
                    $classes = $button_classes.' ore-alternative';
                    $div_classes = ($both) ? ' ore-right' : '';
                    $name = 'ore-alternatve-'.$val['token'];
                    $alt = $val['alternative'];
                    $markup .= '<div class="ore-alternative-wrapper'.$div_classes.'">';
                    if (isset($alt['label'])) {
                        if (isset($alt['class'])) { $classes .= ' '.$alt['class']; }
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

    // create a default post to hold our login form...
    public function create_post($slug) {
        global $wp_rewrite;
        // we might need to instantiate this
        if ($wp_rewrite === NULL) { $wp_rewrite = new wp_rewrite; }
        $post_id = -1; // this is non-post right now...
        if (!($post_id = $this->slug_exists($slug))) {
            $this->log('Creating a post at '.$slug.'...');
            $post = $this->get_post($slug);
            // check to see if this page title is already used...
            $blog_page_check = get_page_by_title($post['post_title']);
            if (!isset($blog_page_check->ID)) {
                if (!($post_id = wp_insert_post($post))) {
                    $this->log('Inserting post at '.$slug.' failed!');
                    return false;
                }
            } else {
                $this->log('Already have a page with this title - id: '.$blog_page_check->ID);
                return false;
            }
        } else {
            $this->log('Not creating the content again.');
        }
        $this->log('returing post id '.$post_id);
        return $post_id;
    }

    // check to see if a post with the given slug already exists...
    public function slug_exists($slug) {
        global $wpdb;
        $this->log('checking for a page at '.$slug);
        if ($wpdb->get_row("SELECT post_name FROM wp_posts WHERE post_name = '" . $slug . "'", 'ARRAY_A')) {
            return true;
        }
        return false;
    }

    public function add_post_class($classes) {
        global $post;
        $post_slug=$post->post_name;
        $this->log('running class filter - post_slug = '.$post_slug);
        if ($post_slug === ORE_GETSTARTED_SLUG) {
            $this->log('setting class on '.ORE_GETSTARTED_SLUG.' to '.ORE_CLASS);
            $classes[] = ORE_CLASS;
        }
        return $classes;
    }

    public function get_data() {
        return $this->data;
    }

    // provide the actual post itself
    private function get_post($slug) {
        $this->log('in get_post');
        // create the post array with boilerplate settings...
        $post = array(
            'comment_status' => 'closed',
            'ping_status' => 'closed',
            'post_status' => 'publish',
            'post_type' => 'page'
        );
        // Set the Author, Slug, title and content of the new post
        $post['post_author'] = 1;  // the default
        $post['post_name'] = $slug;
        $post['post_slug'] = $slug;
        $post['post_title']  = ORE_GETSTARTED;
        $post['post_content'] = "<!-- wp:paragraph -->
<p>If you are interested in higher education opportunities, optionally resulting in formal academic credits or even a qualification at a very reasonable cost, you've come to the right place! </p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>The OERu offers a range of courses in a variety of fields. The only cost to you will come <em>after</em> you've taken the course, and only if you would like your new found learning to be formally assessed by an <a href='https://oeru.org/oeru-partners'>OERu partner</a>! </p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>You don't need to log in to see OERu learning materials, but to engage more fully, to have us keep track of your progress, and to accumulate evidence of participation, you can Register an account on our Course system - there's no cost, no credit card or anything!</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>If you've already got an account you can log in using your credentials. If you're not sure, try logging in. </p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>If you've forgot your password, no problem! You can request a password reset link be sent to you by email. </p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>[".ORE_SHORTCODE."]</p>
<!-- /wp:paragraph -->

<!-- wp:paragraph -->
<p>If you're already logged in, your details will be displayed here as confirmation!<br/></p>
<!-- /wp:paragraph -->";
        return $post;
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
