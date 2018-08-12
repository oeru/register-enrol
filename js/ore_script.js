/* created by Dave Lane, dave@oerfoundation.org, https://oeru.org */
var ORE_DEBUG = true; // set to false to disable debugging
function LOG() { if (ORE_DEBUG) { console.log.apply(this, arguments); }}
LOG('ORE DEBUG =', ORE_DEBUG); // only prints if DEBUG = true

//LOG('register-enrol: ', ore_data);
//LOG('before jquery!');
// jQuery selectors and related functions in that context
// initiated after page is "ready"
jQuery(document).ready(function() {
    LOG('register-enrol: ', ore_data);

    var $ = jQuery;
    var form = $(this);
    var auth = false;
    var form_container = '#ore-container';
    var form_parent = '#ore-login-status';
    var form_class = '.ore-form';

    /*
     * Check the URL for #hash
     */

    var current_uri = window.location.href;
    var current_hash = window.location.hash;
    // this is whatever modal is currently showing
    var current_modal = null;

    check_hash(current_hash);

    // do something based on the has we find...
    function check_hash(hash) {
        hash = hash.replace('#','');
        //LOG('checking if ore_data is visible: ', ore_data);
        if (hash != "") {
            LOG('Hash found: ('+hash+')');
            //show_modal(hash);
            //LOG('ore_data', ore_data);
            //LOG('ore_data.modals', ore_data.modals[hash]);
            if (ore_data.modals.hasOwnProperty(hash)) {
                LOG('showing '+hash+' modal...');
                show_modal(hash);
            }
        } else {
            LOG('no hash found...');
        }
    }

    /*
     * Customise the User Status info
     */
    function user_status() {
        LOG('in status load function');
        // with a null user, the user_id is set to 0
        user = ore_data.user;
        if (user.hasOwnProperty('user_id') && user.user_id != 0) {
            LOG('user is authenticated.');
            auth = true;
            // if the user's logged in, set useful status details
            if (user.hasOwnProperty('display_name')) {
                LOG('display_name', user.display_name);
                $('#ore-label').text(user.display_name);
                // update the hover text
            } else {
                LOG('using username instead ', user.username);
                $('#ore-label').text(user.username);
            }
            $('#ore-login-modal').attr('title', 'You\'re logged in as '+user.username);
            if (user.hasOwnProperty('avatar_url')) {
                LOG('avatar', user.avatar_url);
                var avatar = $('<img>').attr({src: user.avatar_url + '?s=26&r=g', class: 'ore-avatar'});
                $('#ore-icon').replaceWith(avatar);
            }
            if (user.hasOwnProperty('course') && user.course != null) {
                if (user.course.enrolled) {
                    msg = 'Enrolled';
                    msg += '<span class="ore-enrolled ore-course-status-indicator">&nbsp;</span>';
                } else {
                    msg = 'Not enrolled';
                    msg += '<span class="ore-unenrolled ore-course-status-indicator">&nbsp;</span>';
                }
                $('#ore-login-modal').append('&nbsp;&raquo;&nbsp;<span id="ore-course-info" class="course-info">'+msg+'</span>');
            }
            return true;
        } else {
            LOG('user not logged in');
            return false;
        }
    }

    /*
     * Menu for an unauthenticated (anonymous) user
     */
    function visitor_menu() {
        text = '<div class="ore-menu-block ore-left"><p>If you don\'t yet have an account, we invite you to</p><p style="text-align: center;"> <span id="ore-register-button" class="button ore-button" data-toggle="modal" data-target="#ore-modal">Register</span></p></div><div class="ore-menu-block ore-right"><p>If you have already registered, please</p><p style="text-align: center;"><span id="ore-login-button" class="button ore-button" data-toggle="modal" data-target="#ore-modal">Log In</span></p></div>';
        LOG('new text: '+text);
        // enable menus
        prepare_menu(text);
    }
    /*
     * Menu for an unauthenticated (anonymous) user
     */
    function authenticated_menu() {
        user = ore_data.user;
        text = '';
        login_text = '<p>You are logged in as '+user.display_name+' ('+user.username+').</p>';
        button_text = '<span id="ore-edit-profile-button" class="button ore-button" data-toggle="modal" data-target="#ore-modal">Edit Profile</span>&nbsp;<span id="ore-update-password-button" class="button ore-button">Update Password</span>&nbsp;&nbsp;&nbsp;<span id="ore-log-out-button" class="button ore-button">Log Out</span>';
        course = false;
        if (user.hasOwnProperty('course') && user.course != null) {
            course = true;
            if (user.course.enrolled) {
                cat = 'leave';
                label = 'Leave '+user.course.course_tag;
                course_text = '<p>You are <strong>enrolled</strong> in "'+user.course.course_title+'" ('+user.course.course_tag+')</p>';
            } else {
                cat = 'enrol';
                label = 'Enrol in '+user.course.course_tag;
                course_text = '<p>You are <strong>not enrolled</strong> in "'+user.course.course_title+'" ('+user.course.course_tag+')</p>';
            }
        }
        // if we've got a course context, we have 3 divs, no course, only 2
        extra_class = (course) ? 'ore-one' : 'ore-two';
        // login status info
        text += '<div class="ore-menu-block '+extra_class+' ore-first">'+login_text+button_text+'</div>';
        if (course) {
            // course enrollment status
            text += '<div class="ore-menu-block '+extra_class+' ore-second">'+course_text+'<span id="ore-'+cat+'-button" class="button ore-button" data-toggle="modal" data-target="#ore-modal">'+label+'</span></div>';
        }
        LOG('new text: '+text);
        // enable menus
        prepare_menu(text);
    }
    /*
     * initialise jquery tooltips/menus with custom functionality
     * credit for this: https://gist.github.com/csasbach/867744
     */
    var trigger = '.ore-trigger';  // trigger for menu content
    var menu = 'ore-menu';  // the actual menu class

    // on a click outside the menu, close the menu
    $(document).click(function(){
        LOG('closing menus!');
        $(".ore-menu").hide();
    });

    // replace country selector tag with country selector
    function add_countries(form, select) {
        //LOG('adding country selector', form);
        return form.replace('{country_picker}', select);
    }

    // set the country of the current user
    function set_country(form, country) {
        LOG('setting country to ', country);
        // Todo: this doesn't work after closing the dialogue the first time, and reopening.
        $(form_container).find('#ore-country').val(country);
        LOG('finished setting country');
    }

    // replace user tokens
    function replace_user_tokens(string, user) {
        for (var key in user) {
            if (key === 'course') {
                for (var course_key in user['course']) {
                    string = string.replace('{'+course_key+'}', user['course'][course_key]);
                }
            }
            string = string.replace('{'+key+'}', user[key]);
        }
        return string;
    }

    function value_in_object(obj, needle) {
        match = false;
        obj.forEach(function (value, key) {
            //LOG('key: '+key+' val: '+String(value)+' needle: '+String(needle));
            if (String(needle).trim() == String(value).trim()) {
                LOG('strings are equal!');
                match = true;
            }
        })
        return match;
    }
    // set up the menu infrastructure
    function prepare_menu(text) {
        // menu pause and fade times in milliseconds
        var pausetime = 3000;
        var postclicktime = 500;
        var fadetime = 1000;

        LOG('prepare menu');
        $(trigger).each(function() {
            // grab the content from the title attribute and remove the
            // title attrib to avoid normal menu-on-hover behaviour
            $(this).data('title', $(this).attr('title'));
            LOG('sorted trigger with text '+$(this).data('title'));
            $(this).removeAttr('title');
            // manage clicks, e.g. from touch devices
            $(trigger).click( function(e) {
                create_menu($(this), text);
                enable_menu();
                LOG('click');
                set_menu_fade();
                // if the user clicks within the box, fade shortly thereafter
                //
                // Process Menu Click Events
                $(this).next().click( function (e) {
                    LOG('click in menu - this should be passed on!')
                    //LOG('this = ', $(this));
                    //LOG('e = ', e);
                    if (!menu_events(e.target.id)) {
                        e.stopPropagation();
                    }
                });
                // don't send this click to the "hide menu" function below.
                e.stopPropagation();
            });
        });
        function set_menu_fade() {
            $(this).next().animate({opacity: 1.0}, {duration: pausetime, complete: function() {
                    $(this).fadeOut(fadetime);
                }
            });
        }
        function create_menu(trigger, text) {
            trigger.next('.'+menu).remove();
            // create the menu
            trigger.after('<div class="'+menu+'">'+text+'</div>');
            // manage positioning of the menus (voffset pixels above and hoffset left of trigger)
            var right = 0;
            var top = trigger.outerHeight();
            trigger.next().css('right',right);
            trigger.next().css('top',top);
        }
        function enable_menu() {
            LOG('enabling menu');
            $('.'+menu).each(function() {
                // if the user explicitly clicks on a menu button, wait, and then fade
                $('.'+menu).click(function() {
                    LOG('click on menu to start delayed fade out');
                    $(this).animate({opacity: 1.0}, {duration: postclicktime, complete: function() {
                        $(this).fadeOut(fadetime);
                    }});
                });
            });
        }
    }
    function close_menu() {
        LOG('close menu');
        $('.'+menu).each(function() {
            $(this).hide();
        });
    }
    // process button clicks and other events
    function menu_events(target) {
        if (target == 'ore-log-out-button') {
            LOG('Log Out!');
            var href = window.location.href;
            // fix the entity, returning to a pure &
            var goto = ore_data.user.log_out_url.replace('&amp;', '&')+'&redirect_to='+href;
            LOG('going to url', goto);
            window.location.href = goto;
            return true;
        } else {
            LOG('processing target: ', target);
            id = token_from_id(target);
            LOG('got id: ', id);
            return show_modal(id);
        }
    }
    /*
    * End menu stuff
    */

    /*
     * we're using hash values to trigger modals and make them bookmarkable
     */
    // set the hash of the current modal
    function set_hash(hash) {
        current = get_hash();
        if (hash != current) {
            if (current != "") {
                LOG('current hash is #'+current);
                LOG('changing hash to #'+hash);
            } else {
                LOG('setting hash to #'+hash);
            }
            window.location.hash = hash;
        }
    }
    // get the current hash, if any
    function get_hash() {
        hash = window.location.hash;
        hash = (hash == "") ? false : hash.replace('#', '');
        return hash;
    }
    // clear the current hash
    function clear_hash() {
        LOG('Clearing hash');
        current = window.location.hash;
        if (current != "") {
            LOG('current hash = '+current);
        }
        window.location.hash = "";
    }
    // reload the page, e.g. after a login
    function reload(hash = null) {
        current = window.location.href;
        LOG('forcing reload of '+current);
        window.location.reload();
        if (hash == null) {
            LOG('setting hash to '+hash);
            window.location.hash = hash;
        }
    }
    /*
    * End hash stuff
    */

    /*
    * Modal dialogue stuff
    */
    $(form_container).on('show.bs.modal', '.ore-modal', function(e) {
        LOG('showing modal! e ', e);
        $(this).modal('show');
    });
    // closing based on either clicking a "Cancel" button, or the
    // clock "X" on the form...
    $(form_container).on('click', '.ore-button', function(e) {
        id = action_from_id(e.target.id);
        if (current_modal == id) {
            LOG('click1! (button) id '+id);
            // if "cancel" button is clicked (regardless of the words on it, which could
            // be in a different language, so we consult the class list), close the modal
            if (value_in_object(e.target.classList, 'cancel')) {
                LOG('hide the modal!');
                close_modal();
                clear_hash();
            //} else if (modal_events(action_from_id(e.target.id), e.target.id, ore_data)) {
            } else if (modal_events(id)) {
                LOG('modal event handled');
                // we can hide the modal...
                close_modal();
                // Todo - show the appropriate response modal...
            } else {
                LOG('unknown modal event');
            }
        } else {
            LOG('mismatched click, modal: '+current_modal+', id: '+id+' - launching a new modal instead!');
            show_modal(id);
        }
    });
    // default close button behaviour (on each modal)
    $(form_container).on('click', '.close', function(e) {
        LOG('click2! (close) e.target ', e.target);
        if (value_in_object(e.target.classList, 'close')) {
            LOG('close the modal!');
            close_modal();
        }
    });
    // show a modal based on its id, the key in the modals array in ore_modals.php
    function show_modal(id ) {
        LOG('show_modal', id);
        modals = ore_data.modals;
        user = ore_data.user;
        if (modals.hasOwnProperty(id)) {
            // if a menu is showing, close it.
            close_menu();
            // close any currently showing modals
            close_modal();
            // clear any existing hashes
            clear_hash();
            // show a the new modal
            form = modals[id].markup;
            LOG('Launch ', id);
            form = replace_user_tokens(form, user);
            LOG('after replace_user_tokens');
            if (id == 'edit_profile' || id == 'register') {
                LOG('adding countries');
                form = add_countries(form, ore_data.country_select);
                set_country(form, user.country);
                LOG('finished adding and setting country');
            }
            LOG('appending form to parent');
            form = $(form_parent).append(form);
            // set the "current_modal" value...
            current_modal = id;
            LOG('modal '+current_modal+' should be visible...');
        } else {
            LOG('click within menu isn\'t on a known button');
            return false;
        }
    }
    // close any currently open modal
    function close_modal(hash = null) {
        LOG('closing currently open modal');
        $(form_container+' .ore-modal.modal').hide();
        LOG('setting current_modal to null');
        current_modal = null;
        if (hash != null) {
            clear_hash();
            set_hash(hash);
        }
    }
    // respond to modal events like button clicks on modal forms
    function modal_events(action){
        var special_data = {};
        // if a clicked button is a "cancel" button (regardless of the words on it, which could
        // be in a different language, so we consult the class list), close the modal
        if (current_modal != action) {
            LOG('we got an action not related to the current modal');
            LOG('Hide '+current_modal+' and show '+action);
            show_modal(action);
        } else {
            if (action == 'register') {
                LOG('Register!');
                special_data = get_form_values();
            } else if (action == 'login') {
                LOG('Login!');
                special_data = get_form_values();
            } else if (action == 'password_reset') {
                LOG('Reset Password!');
                special_data = get_form_values();
                special_data['user_id'] = ore_data.user.user_id;
            } else if (action == 'edit_profile') {
                LOG('Save Profile!');
                special_data = get_form_values();
                special_data['user_id'] = ore_data.user.user_id;
            } else if (action == 'update_password') {
                LOG('Update Password!');
                special_data = get_form_values();
                special_data['user_id'] = ore_data.user.user_id;
            } else if (action == 'enrol') {
                LOG('Enrol!');
                special_data = {
                    'user_id': ore_data.user.user_id,
                    'course_id': ore_data.user.course.course_id,
                    'course_tag': ore_data.user.course.course_tag
                };
            } else if (action == 'leave') {
                LOG('Leave!');
                special_data = {
                    'user_id': ore_data.user.user_id,
                    'course_id': ore_data.user.course.course_id,
                    'course_tag': ore_data.user.course.course_tag
                };
            // informational modals, if OK is clicked, just close them
            } else if (action == 'successful_login' ||
                action == 'successful_reset' ||
                action == 'successful_registration' ||
                action == 'profile_saved' ||
                action == 'profile_save_failed' ||
                action == 'successfully_enrolled' ||
                action == 'failed_to_enrol' ||
                action == 'successfully_unenrolled') {
                LOG('closing informational modal: '+action);
                close_modal();
            } else {
                // unless we get to a requested action that's not catered for here,
                // in which case, bail, and say something!
                LOG('Not a configured button');
                return false;
            }
            // process the ajax call...
            if (ajax_submit(action, special_data)) {
                    LOG('completed ajax call for', action);
                // check if a new hash has been specified by the submit, and act on it!
                check_hash(window.location.hash);
            } else {
                LOG('failed to complete ajax call for', action);
            }
        }
        return true;
    }
    // get the values from a form
    function get_form_values() {
        form = $(form_container).find('.ore-form');
        var value = {};
        LOG('found ', form);
        $(form).each(function() {
            inputs = $(this).find(':input');
            LOG('found '+inputs.length+' inputs');
            inputs.each(function(i, obj) {
                LOG(obj.id+': '+obj.value);
                value[obj.id] = obj.value;
            });
        });
        return value;
    }
    // process ajax requests returning data to the server
    // and displaying error/status messages where relevant
    function ajax_submit(action, special_data) {
        LOG('in ajax_submission for', action);
        LOG('url: ', ore_data.ajaxurl);
        default_data = {
            'action': 'ore_submit',
            'form_action': action,
            'nonce_submit': ore_data.nonce_submit
        }
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: ore_data.ajaxurl,
            data: concat_data(default_data, special_data),
            success: function(data) {
                LOG('completed submission without submission error, returning true', data);
                // handle post submission stuff
                // if it was successful, do other stuff
                if (data.hasOwnProperty('success')) {
                    if (action == 'register') {
                        LOG('Registered successfully!');
                        set_hash('successful_registration');
                    } else if (action == 'login') {
                        LOG('Logged in! Reloading page');
                        reload("successful_login");
                    } else if (action == 'password_reset') {
                        LOG('Resetting password');
                        set_hash("successful_reset");
                    } else if (action == 'edit_profile') {
                        LOG('Save Profile!');
                        set_hash('profile_saved');
                    } else if (action == 'update_password') {
                        LOG('Password Updated!');
                        set_hash('password_updated');
                    } else if (action == 'enrol') {
                        LOG('Successfully enrolled!');
                        set_hash('successfully_enrolled');
                    } else if (action == 'leave') {
                        LOG('Successfully enrolled!');
                        set_hash('successfully_unenrolled');
                    } else {
                        LOG('nothing to do for action: '+action);
                    }
                } else { // if it failed, show error info
                    if (action == 'register') {
                        LOG('Failed to Register!');
                        set_hash('failed_registration');
                    } else if (action == 'login') {
                        LOG('Log in failed!');
                        set_hash("failed_login");
                    } else if (action == 'password_reset') {
                        LOG('Failed to reset password');
                        set_hash('failed_reset');
                    } else if (action == 'update_password') {
                        LOG('Password Not Updated!');
                        set_hash('password_update_failed');
                    } else if (action == 'edit_profile') {
                        LOG('Profile Save Failed!');
                        set_hash('profile_save_failed');
                    } else if (action == 'password_updated') {
                        LOG('Password Update Failed!');
                        set_hash('password_update_failed');
                    } else if (action == 'enrol') {
                        LOG('Failed to enrol!');
                        set_hash('failed_to_enrol');
                    } else {
                        LOG('nothing to do for action: '+action);
                    }

                }
                check_hash(window.location.hash);
                return true;
            },
            error: function(jqXHR, textStatus, errorThrown) {
                LOG('failure: jqXHR ', jqXHR);
                LOG('failure: textStatus ', textStatus);
                LOG('failure: errorThrown ', errorThrown);
                return false;
            }
        });
    }
    // combine data arrays
    function concat_data(one, two) {
        LOG('one ', one);
        LOG('two ', one);
        for (var key in two) {
            one[key] = two[key];
        }
        LOG('resulting array: ', one);
        return one;
    }
    // we need to strip the default action out of ids in the form of
    // ore-[action]-[default|alternative]-action
    function action_from_id(id) {
        LOG('action_from_id, id = '+id);
        terms = id.split('-');
        action = terms.slice(1, terms.length-2).join('_');
        LOG('returning action ', action);
        return action;
    }
    // get a modal identifier (token) from the CSS id
    function token_from_id(id) {
        LOG('token_from_id, id = '+id);
        terms = id.split('-');
        id = terms.slice(1, terms.length-1).join('_');
        LOG('returning id ', id);
        return id;
    }
    /*
    * End Modal dialogue stuff
    */

    /*
    * Per page-load code
    */
    // run the user status when the page loads
    // if the user is logged in, create the authenticated menu
    // if not, the login/register menu for a visitor
    if (user_status()) {
        authenticated_menu();
    } else {
        visitor_menu();
    }

    /*
     * Standard events
     */
    // set this up to submit on 'enter'
    $('input').keypress( function (e) {
        c = e.which ? e.which : e.keyCode;
        LOG('input: ' + c);
        if (c == 13) {
            $('#ore-default').click();
            return false;
        }
    });

    /*
     * form validation, e.g. uniqueness of username and email
     */
    $(form_class).validate({
        validClass: 'valid',
        rules: {
            'email': true,
            remote: {
                url: ore_data.ajaxurl,
                type: 'POST',
                data: {
                    'email': function() {
                        LOG('in email validation!');
                        return $('#email').val();
                    },
                    'action': 'ore_username_check'
                }
            }
        },
        messages: {
            'email': {
                required: "You must enter a unique email"
            }
        }
    });


    // the end of the jQuery loop...
}); // });
