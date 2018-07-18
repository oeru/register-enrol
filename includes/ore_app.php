<?php

require ORE_PATH . 'includes/ore_base.php';

class OREMain extends OREBase {
    public static $instance = NULL; // this instance

    // ORE -> ORE variables
	public static $current_user; // If logged in upon instantiation, it is a user object.
	public static $data; // ore_data option

    // returns an instance of this class if called, instantiating if necessary
    public static function get_instance() {
        NULL === self::$instance and self::$instance = new self();
        return self::$instance;
    }

    // this starts everything...
    public function init() {
        $this->log('in init');

        $this->log('setting up scripts');
        // add the ajax handlers
        $current_user = wp_get_current_user();
        // for security's sake, don't even show the password hash...
        unset($current_user->data->user_pass);
        $this->log('current user: '.print_r($current_user, true));
        wp_enqueue_script(ORE_SCRIPT, ORE_URL.'js/ore_script.js', array(
            'jquery', 'jquery-form'));
        wp_localize_script(ORE_SCRIPT, 'ore_data', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce_submit' => wp_create_nonce('ore-submit-nonce'),
            'user' => $current_user
        ));
        // our css
        wp_register_style(ORE_STYLE, ORE_URL.'css/ore_style.css');
        wp_enqueue_style(ORE_STYLE);
        // this enables the feedfinder service for authenticated users...
        add_action('wp_ajax_ore_submit', array($this, 'ajax_submit'));
        // this allows users who aren't authenticated to use the feedfinder
        add_action('wp_ajax_nopriv_ore_submit', array($this, 'ajax_submit'));
        // add the shortcode
        add_shortcode(ORE_ID, 'OREMain::shortcode');

                // allows us to add a class to our post
        add_filter('body_class', array($this, 'add_post_class'));
        add_filter('post_class', array($this, 'add_post_class'));
        // create a default page if it doesn't already exist...
        $this->log('create post: '.ORE_GETSTARTED_SLUG);
        $this->create_post(ORE_GETSTARTED_SLUG);
    }

    // the function called after the ore-submit button is clicked in our form
    public function ajax_submit() {
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
}
?>
