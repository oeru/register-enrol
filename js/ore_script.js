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

    /*
     * Customise the User Status info
     */
    function user_status(data) {
        LOG('in status load function');
        // with a null user, the user_id is set to 0
        if (data.user.hasOwnProperty('user_id') && data.user.user_id != 0) {
            LOG('user is authenticated.');
            auth = true;
            user = data.user;
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
                //msg += ' in '+user.course.course_tag;
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
    function visitor_menu(data) {
        text = '<div class="ore-left"><p>If you don\'t yet have an account, we invite you to</p><p style="text-align: center;"> <span id="ore-register-button" class="button ore-button" data-toggle="modal" data-target="#ore-modal">Register</span></p></div><div class="ore-right"><p>If you have already registered, please</p><p style="text-align: center;"><span id="ore-login-button" class="button ore-button" data-toggle="modal" data-target="#ore-modal">Log In</span></p></div>';
        LOG('new text: '+text);
        // enable menus
        prepare_menu(text);
    }
    /*
     * Menu for an unauthenticated (anonymous) user
     */
    function authenticated_menu(data) {
        user = data.user;
        text = '';
        login_text = '<p>You are logged in as '+user.display_name+' ('+user.username+').</p>';
        /*button_text = '<span id="ore-edit-profile-button" class="button ore-button" data-toggle="modal" data-target="#ore-modal">Edit Your Profile</span>&nbsp;<a href="'+user.log_out_url+'"><span id="ore-log-out-button" class="button ore-button">Log Out</span></a>';*/
        button_text = '<span id="ore-edit-profile-button" class="button ore-button" data-toggle="modal" data-target="#ore-modal">Edit Your Profile</span>&nbsp;<span id="ore-log-out-button" class="button ore-button">Log Out</span>';
        if (user.hasOwnProperty('course') && user.course != null) {
            if (user.course.enrolled) {
                cat = 'leave';
                label = 'Leave '+user.course.course_tag;
                course_text = '<p>You are <strong>enrolled</strong> in "'+user.course.course_title+'" ('+user.course.course_tag+')</p>';
            } else {
                cat = 'enrol';
                label = 'Enrol in '+user.course.course_tag;
                course_text = '<p>You are <strong>not enrolled</strong> in "'+user.course.course_title+'" ('+user.course.course_tag+')</p>';
            }
            // login status info
            text += '<div class="ore-menu-block ore-left">'+login_text+button_text+'</div>';
            // course enrollment status
            text += '<div class="ore-menu-block ore-right">'+course_text+'<span id="ore-'+cat+'-button" class="button ore-button" data-toggle="modal" data-target="#ore-modal">'+label+'</span></div>';
        } else {
            text += '<div class="ore-menu-block">'+login_text+button_text+'</div>';
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
        LOG('adding country selector', form);
        return form.replace('{country_picker}', country_select);
    }

    // set the country of the current user
    function set_country(form, country) {
        LOG('setting country to ', country);
        // Todo: this doesn't work after closing the dialogue the first time, and reopening.
        form.find('#ore-country').val(country);
    }

    // replace user tokens
    function replace_user_tokens(string, user) {
        for (var key in user) {
            //LOG('key: '+key+' val: '+user[key]);
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

    function prepare_menu(text) {
        // menu pause and fade times in milliseconds
        var pausetime = 3000;
        var postclicktime = 500;
        var fadetime = 1000;
        var form_parent = '#ore-login-status';

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
                    //menu = $(this).next();
                    LOG('this = ', $(this));
                    LOG('e = ', e);
                    if (!menu_events(e.target.id, ore_data, form_parent)) {
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
            //LOG('top right:'+right+', '+top);
            trigger.next().css('right',right);
            trigger.next().css('top',top);
        }
        function enable_menu() {
            LOG('enabling menu');
            $('.'+menu).each(function() {
                // if the user explicitly clicks on a menu button, wait, and then fade
                $('.'+menu).click(function() {
                    LOG('click on menu to start delayed fade out');
                    //$(this).remove();
                    $(this).animate({opacity: 1.0}, {duration: postclicktime, complete: function() {
                        $(this).fadeOut(fadetime);
                    }});
                });
            });
        }
    }

    // process button clicks and other events
    function menu_events(target, js_data, form_parent) {
        modals = js_data.modals;
        user = js_data.user;
        country_select = js_data.country_select;
        if (target == 'ore-edit-profile-button') {
            LOG('Launch Edit Profile!');
            //form = modals.register.markup;
            form = modals.edit_profile.markup;
            form = replace_user_tokens(form, user);
            form = add_countries(form, country_select);
            form = $(form_parent).append(form);
            set_country(form, user.country);
        } else if (target == 'ore-register-button') {
            LOG('Launch Register!');
            form = modals.register.markup;
            LOG('form, with tokens: ', form);
            form = replace_user_tokens(form, user);
            form = add_countries(form, country_select);
            form = $(form_parent).append(form);
        } else if (target == 'ore-login-button') {
            LOG('Launch Login!');
            form = modals.login.markup;
            form = $(form_parent).append(form);
        } else if (target == 'ore-log-out-button') {
            LOG('Log Out!');
            LOG('log_out_url = ', js_data.user.log_out_url);
            var href = window.location.href;
            // fix the entity, returning to a pure &
            var goto = js_data.user.log_out_url.replace('&amp;', '&')+'&redirect_to='+href;
            LOG('going to url', goto);
            window.location.href = goto;
            //window.location.href = js_data.user.log_out_url;
        } else if (target == 'ore-enrol-button') {
            LOG('Launch Enrol!');
            form = modals.enrol.markup;
            form = replace_user_tokens(form, user);
            form = $(form_parent).append(form);
        } else if (target == 'ore-leave-button') {
            LOG('Launch Leave!');
            form = modals.leave.markup;
            form = replace_user_tokens(form, user);
            form = $(form_parent).append(form);
        } else {
            LOG('click within menu isn\'t on a known button');
            return false;
        }
        return true;
    }

    /*
    * End menu stuff
    */

    /*
    * Modal dialogue stuff
    */
    $('#ore-container').on('show.bs.modal', '.ore-modal', function(e) {
        LOG('showing modal! e ', e);
        $(this).modal('show');
    });
    // closing based on either clicking a "Cancel" button, or the
    // clock "X" on the form...
    $('#ore-container').on('click', '.ore-button', function(e) {
        LOG('click! (button) e.target ', e.target);
        // if "cancel" button is clicked (regardless of the words on it, which could
        // be in a different language, so we consult the class list), close the modal
        if (value_in_object(e.target.classList, 'cancel')) {
            LOG('hide the modal!');
            $('#ore-container .ore-modal.modal').hide();
        //} else if (modal_events(action_from_id(e.target.id), e.target.id, ore_data)) {
        } else if (modal_events(action_from_id(e.target.id), ore_data)) {
            LOG('modal event handled');
            // we can hide the modal...
            $('#ore-container .ore-modal.modal').hide();
            // Todo - show the appropriate response modal...
        } else {
            LOG('unknown modal event');
        }
    });
    //
    // default close button behaviour (on each modal)
    $('#ore-container').on('click', '.close', function(e) {
        LOG('click! (close) e.target ', e.target);
        if (value_in_object(e.target.classList, 'close')) {
            LOG('close the modal!');
            //$('#ore-container .ore-modal.modal').modal('hide');
            $('#ore-container .ore-modal.modal').hide();
        }
    });

    // respond to modal events like button clicks on modal forms
    function modal_events(action, js_data){
        var special_data = {};
        // if a clicked button is a "cancel" button (regardless of the words on it, which could
        // be in a different language, so we consult the class list), close the modal
        if (action == 'register') {
            LOG('Register!');
            special_data = get_form_values();
        } else if (action == 'login') {
            LOG('Login!');
            special_data = get_form_values();
        } else if (action == 'edit_profile') {
            LOG('Save Profile!');
            special_data = get_form_values();
            special_data['user_id'] = js_data.user.user_id;
        } else if (action == 'enrol') {
            LOG('Enrol!');
            special_data = {
                'user_id': js_data.user.user_id,
                'course_id': js_data.user.course.course_id,
                'course_tag': js_data.user.course.course_tag
            };
        } else if (action == 'leave') {
            LOG('Leave!');
            special_data = {
                'user_id': js_data.user.user_id,
                'course_id': js_data.user.course.course_id,
                'course_tag': js_data.user.course.course_tag
            };
        } else if (action == 'password_reset') {
            LOG('Reset Password!');
        } else {
            // unless we get to a requested action that's not catered for here,
            // in which case, bail, and say something!
            LOG('Not a configured button');
            return false;
        }
        // process the ajax call...
        if (ajax_submit(action, js_data, special_data)) {
            LOG('completed ajax call for', action);
        } else {
            LOG('failed to complete ajax call for', action);
        }
        return true;
    }

    function get_form_values() {
        form = $('#ore-container').find('.ore-form');
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
    function ajax_submit(action, js_data, special_data) {
        LOG('in ajax_submission for', action);
        //LOG('js_data: ', js_data);
        //js_data.ajaxurl = 'http://wpms.local/wp-admin/admin-ajax.php';
        LOG('url: ', js_data.ajaxurl);
        default_data = {
            'action': 'ore_submit',
            'form_action': action,
            'nonce_submit': js_data.nonce_submit
        }
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: js_data.ajaxurl,
            data: concat_data(default_data, special_data),
            success: function(data) {
                LOG('completed submission without submission error, returning true', data);
                return true;
            },
            error: function(jqXHR, textStatus, errorThrown) {
                LOG('failure: jqXHR ', jqXHR);
                LOG('failure: textStatus ', textStatus);
                LOG('failure: errorThrown ', errorThrown);
                return false;
            }
        });
        //LOG('completed submission with error... returning false');
        //return false;
        //return true;
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
        terms = id.split('-');
        action = terms.slice(1, terms.length-2).join('_');
        LOG('returning action ', action);
        return action;
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
    if (user_status(ore_data)) {
        authenticated_menu(ore_data);
    } else {
        visitor_menu(ore_data);
    }

    /*
     * Standard events
     */
    // set this up to submit on 'enter'
    $('input').keypress( function (e) {
        c = e.which ? e.which : e.keyCode;
        LOG('input: ' + c);
        if (c == 13) {
            $('#ore-submit').click();
            return false;
        }
    });

    /*$('#ore-submit').click(function() {
        $.ajax({
            type: 'POST',
            dataType: 'json',
            url: ore_data.ajaxurl,
            data: {
                'action': 'ore_submit',
                'nonce_submit' : ore_data.nonce_submit,
            },
            success: function(data) {
                var msgs = '';
                var types = data.types
                LOG('Success: data: ', data);
                if (data.hasOwnProperty('success')) {
                    // strip links out
                    msgs = data.messages;
                    LOG('Success msgs', msgs);
                    // initialise menus
                    prepare_menu();
                }
                LOG('returning true');
                return true;
            },
            failure: function(data) {
                LOG('Failure: data: ', data);
            }
        });
        // if nothing else returns this first, there was a problem...
        LOG('completed submit... returning false');
        return false;
    });*/

    // the end of the jQuery loop...
}); // });
//LOG('after jquery!');
