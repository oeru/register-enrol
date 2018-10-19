/* created by Dave Lane, dave@oerfoundation.org, https://oeru.org */
var ORE_DEBUG = true; // set to false to disable debugging
function LOG() { if (ORE_DEBUG) { console.log.apply(this, arguments); }}
LOG('ORE DEBUG =', ORE_DEBUG); // only prints if DEBUG = true
// jQuery selectors and related functions in that context
// initiated after page is "ready"
jQuery(document).ready(function() {
    LOG('register-enrol: ', ore_data);
    /*
     * Setting default values
     */
    var $ = jQuery;
    //var form = $(this);
    var authenticated = false;
    var form_container = '#'+ore_data.container;
    var form_parent = '#'+ore_data.login_status;
    var form_class = '.ore-form';
    /*
     * Check the URL for #hash
     */
    var current_uri = window.location.href;
    var current_hash = window.location.hash;
    // this is whatever modal is currently showing
    var current_modal = null;
    var current_form = null;
    /*
     * initialise jquery tooltips/menus with custom functionality
     * credit for this: https://gist.github.com/csasbach/867744
     */
    var trigger = '.ore-trigger';  // trigger for menu content
    var menu = 'ore-menu';  // the actual menu class


    /*
     * general functions, not dependent on specific data objects
     */
    /*
    * Customise the User Status info
    */
    function user_status() {
        LOG('in status load function');
         // with a null user, the user_id is set to 0
         user = ore_data.user;
         if (user.hasOwnProperty('user_id') && user.user_id != 0) {
             LOG('user is authenticated.');
             authenticated = true;
             // if the user's logged in, set useful status details
             if (user.hasOwnProperty('display_name')) {
                 LOG('display_name', user.display_name);
                 $('#ore-label').text(user.display_name);
                 // update the hover text
             } else {
                 LOG('using username instead ', user.username);
                 $('#ore-label').text(user.username);
             }
             /*$('#ore-login-modal').attr('title', 'You\'re logged in as '+user.username);*/
             $('#ore-login-modal').prop('title', 'You\'re logged in as '+user.username);
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
                 $('#ore-login-modal').append('<span class="ore-spacer">&nbsp;&raquo;&nbsp;</span><span id="ore-course-info" class="course-info">'+msg+'</span>');
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
     // replace country selector tag with country selector
     function add_countries(markup, select) {
         //LOG('adding country selector', form);
         return markup.replace('{country_picker}', select);
     }

     // set the country of the current user
     function set_country(form, country) {
         LOG('setting country to ', country);
         // Todo: this doesn't work after closing the dialogue the first time, and reopening.
         LOG('Form = ', form);
         form.find('#ore-country').val(country);
         LOG('value: ', form.find('#ore-country').val());
         LOG('finished setting country', form.find('#ore-country').length);
     }

     // replace user tokens
     function replace_user_tokens(string, user) {
         for (var key in user) {
             if (key === 'course') {
                 for (var course_key in user['course']) {
                     replacement = (user['course'][course_key] == null) ? "No value" : user['course'][course_key];
                     string = string.replace('{'+course_key+'}', replacement);
                 }
             } else {
                 replacement = (user[key] == null) ? "No value" : user[key];
                 string = string.replace('{'+key+'}', user[key]);
             }
         }
         return string;
     }

     function value_in_object(obj, needle) {
         match = false;
         obj.forEach(function(value, key) {
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
                         LOG('menu_event '+e.target.id+' was false - stopping propagation');
                         e.stopPropagation();
                     } else {
                         LOG('menu_event '+e.target.id+' was true... eventually stopping propagation');
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
             //var right = 0;
             var top = trigger.outerHeight()+10; // offset of 10 required
             //trigger.next().css('right',right);
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
            // $(this).hide();
             $(this).remove();
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
     * form validation, e.g. uniqueness of username and email
     */
    // content entry helpers - these only apply to the registration form
    $(form_container).on('focus', '#ore-modal-register #username', function() {
        LOG('in username helper');
        var first = $('#first-name').val();
        var last = $('#last-name').val();
        if (first && last && !this.value) {
            this.value = first.toLowerCase()+last.toLowerCase();
        }
    });
    $(form_container).on('focus', '#ore-modal-register #display-name', function() {
        LOG('in display name helper');
        var first = $('#first-name').val();
        var last = $('#last-name').val();
        if (first && last && !this.value) {
            this.value = first+' '+last;
        }
    });
    // custom validators
    $.validator.addMethod('wp_username', function(value, element) {
        LOG('in wp_username validator');
        if (this.optional(element)) { return true; }
        // we trim the value here, as we'll trim it elsewhere. No point in being
        // too fussy :)
        return /^[a-z0-9]+$/i.test(value.trim());
    }, "Your username must be a combination of lowercase letters (a-z) and/or digits (0-9).");
    // default values
    var validation_defaults = {
        debug: true
    }
    // per-action details.
    var validation = {
        login: {
            rules: {
                'password': {
                    required: true
                }
            }
        },
        'password_reset': {
            rules: {
                'password': {
                    required: true
                }
            }
        },
        'session_expired': {
             rules: {
                 'password': {
                     required: true
                 }
             }
        },
        register: {
            rules: {
                'first-name': {
                    required: true
                },
                'last-name': {
                    required: true
                },
                username: {
                    required: true,
                    minlength: ore_data.un_min,
                    wp_username: true,
                    remote: {
                        url: ore_data.ajaxurl,
                        type: 'POST',
                        data: {
                            username: function() {
                                LOG('in username validation!');
                                return $('#username').val();
                            },
                            'action': 'ore_username_check',
                            'nonce_submit': ore_data.nonce_submit
                        }
                    }
                },
                'display-name': {
                    required: true,
                    minlength: ore_data.dn_min
                },
                password: {
                    required: true,
                    minlength: ore_data.pw_min
                },
                'confirm-password': {
                    required: true,
                    minlength: ore_data.pw_min,
                    equalTo: '#password'
                },
                email: {
                    required: true,
                    email: true,
                    remote: {
                        url: ore_data.ajaxurl,
                        type: 'POST',
                        data: {
                            email: function() {
                                LOG('in email validation!');
                                return $('#email').val();
                            },
                            'action': 'ore_email_check',
                            'nonce_submit': ore_data.nonce_submit
                        }
                    }
                },
                country: {
                   required: true
                }
            },
            messages: {
                'first-name': {
                    required: 'We require you specify your first or given name.'
                },
                'last-name': {
                    required: 'We require you specify your last or family name.'
                },
                username: {
                    required: 'You must enter a username. It must be at least '+ore_data.un_min+' characters long.'
                },
                'display-name': {
                    required: 'You must supply a display name - it can be a nickname - this will be shown publicly alongside any of your posts. Your Display Name must be at least '+ore_data.dn_min+' characters long.'
                },
                password: {
                    required: 'You must enter a password',
                    minlength: 'Your password must be at least '+ore_data.pw_min+' characters long.'
                },
                'confirm-password': {
                    required: 'You must enter a password confirmation',
                    minlength: 'Your password must be at least '+ore_data.pw_min+' characters long',
                    equalTo: 'Your confirmation is different from your password. They must be the same. Please try re-entering one or both.'
                },
                email: {
                    required: 'You must enter a valid email',
                    unique: 'Each user email must be unique. No two accounts can share the same email address.'
                },
                country: {
                    required: 'You must select the country with which you most closely associate.'
                }
            }
          },
          'edit_profile': {
              rules: {
                  'first-name': {
                      required: true
                  },
                  'last-name': {
                      required: true
                  },
                  'display-name': {
                      required: true,
                      minlength: 6
                  },
                  email: {
                      required: true,
                      email: true,
                      remote: {
                          url: ore_data.ajaxurl,
                          type: 'POST',
                          data: {
                              email: function() {
                                  LOG('in email validation!');
                                  return $('#email').val();
                              },
                              'current_email': ore_data.user.email,
                              'action': 'ore_email_check',
                              'nonce_submit': ore_data.nonce_submit
                          }
                      }
                  },
                  country: {
                     required: true
                  }
              },
              messages: {
                  'first-name': {
                      required: 'We require you specify your first or given name.'
                  },
                  'last-name': {
                      required: 'We require you specify your last or family name.'
                  },
                  'display-name': {
                      required: 'You must supply a display name - it can be a nickname - this will be shown publicly alongside any of your posts. Your Display Name must be at least '+ore_data.dn_min+' characters long.'
                  },
                  email: {
                      required: 'You must enter a valid email',
                      unique: 'Each user email must be unique. No two accounts can share the same email address.'
                  },
                  country: {
                      required: 'You must select the country with which you most closely associate.'
                  }
              }
          },
          'update_password': {
              rules: {
                  'current-password': {
                      required: true
                  },
                  'new-password': {
                      required: true,
                      minlength: ore_data.pw_min
                  },
                  'confirm-password': {
                      required: true,
                      minlength: ore_data.pw_min,
                      equalTo: '#new-password'
                  },
              },
              messages: {
                  'current-password': {
                      required: "You must enter your current password - this is a security precaution. If you don't know it, please do a <button id='ore-password-reset-auxillary-button' class='link ore-button'>Password Reset</button> instead."
                  },
                  'new-password': {
                      required: 'You must enter a password',
                      minlength: 'Your password must be at least '+ore_data.pw_min+' characters long'
                  },
                  'confirm-password': {
                      required: 'You must enter a password confirmation',
                      minlength: 'Your password must be at least '+ore_data.pw_min+' characters long',
                      equalTo: 'Your confirmation is different from your password. They must be the same. Please try re-entering one or both.'
                  }
              }
          }
     };
     // apply a set of rules to each form
     function set_validation_details(form, details) {
         //form = get_current_form();
         LOG('validating rules and messages for form: ', form);
         //form.validationEngine();
         form.validate(details);
     }
   /*
    * End of validation
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
                 LOG('modal event handled: ', id);
                 // we can hide the modal...
                 //close_modal();
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
             clear_hash();
         }
     });
     // show a modal based on its id, the key in the modals array in ore_modals.php
     function show_modal(id) {
         LOG('show_modal', id);
         modals = ore_data.modals;
         user = ore_data.user;
         if (modals.hasOwnProperty(id)) {
             // if a menu is showing, close it.
             close_menu();
             // close any currently showing modals
             if (id != current_modal) {
                 LOG('got a new id: '+id+' vs. '+current_modal);
                 LOG('closing existing modals');
                 close_modal();
                 // clear any existing hashes
                 clear_hash();
                 // set the hash for the current modal
                 set_hash(id);
                 // show a the new modal
                 form_markup = modals[id].markup;
                 LOG('Launching ', id);
                 LOG('Replacing user tokens with contents of ', user);
                 form_markup = replace_user_tokens(form_markup, user);
                 LOG('after replace_user_tokens');
                 if (id == 'edit_profile' || id == 'register') {
                     LOG('adding countries');
                     form_markup = add_countries(form_markup, ore_data.country_select);
                 }
                 LOG('appending form to parent');
                 // at this point, the form should appear!
                 // here form is a DOM node...
                 //form = $(form_parent).append(form_markup);
                 form = $('#ore-container').append(form_markup);
                 // set the "current_modal" value...
                 current_modal = id;

             } else {
                 LOG('do nothing: we\'re already showing the modal: ', id);
             }
             if (id == 'edit_profile' || id == 'register') {
                 LOG('Setting the country');
                 set_country(form, user.country);
             }
             // enable validation
             forms = form.find(form_class);
             LOG('forms found: '+forms.length);
             if (forms.length == 1) {
                 forms.each(function() {
                     LOG('setting rules and messages', validation[id]);
                     //LOG('we have validation settings for '+ id);
                    // LOG('applying to this form: ', $(this));
                     var validation_details = Object.assign({}, validation_defaults, validation[id]);
                     LOG('setting rules and messaging to ', validation_details);
                     $(this).validate(validation_details);
                     LOG('turn off submit button until valid...');
                 });
             } else if (forms.length > 1) {
                 LOG('found too many forms!');
             } else {
                 LOG('found no forms');
             }
             LOG('modal '+current_modal+' should be visible...');
             return true;
         } else {
             LOG('click within menu isn\'t on a known button');
             return false;
         }
     }
     // close any currently open modal
     function close_modal(hash = null) {
         LOG('closing currently open modal');
         // temp: disable this for testing.
         $(form_container+' .ore-modal.modal').remove();
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
             special_data = get_form_values();
             LOG('processing requested action: ', action);
             if (action == 'password_reset' || action == 'update_password' || action == 'edit_profile') {
                 LOG('in modal_events, setting special data for password_reset, or update_password, or edit_profile');
                 special_data['user_id'] = ore_data.user.user_id;
                 special_data['username'] = ore_data.user.username;
                 special_data['existing_email'] = ore_data.user.email;
                 LOG('updating special_data: ', special_data);
             } else if (action == 'enrol' || action == 'leave') {
                 special_data = {
                     'user_id': ore_data.user.user_id,
                     'course_id': ore_data.user.course.course_id,
                     'course_tag': ore_data.user.course.course_tag
                 };
             // informational modals, if OK is clicked, just close them
             } else if (get_purpose(action, 'confirmation')) {
                 close_modal();
                 return true;
             } else {
                 // unless we get to a requested action that's not catered for here,
                 // in which case, bail, and say something!
                 //LOG('Not a configured button, but closing any open modals anyway...');
                 //close_modal();
                 LOG('Not a configured button, continuing...');
                 //LOG('Not a configured button, returning false...');
                 //return false;
             }
             LOG('submitting form', action);
             // if we've got a valid form... proceed.
             if (form.valid()) {
                 LOG('form is valid');
                 // process the ajax call...
                 if (ajax_submit(action, special_data)) {
                     LOG('completed ajax call for', action);
                     // check if a new hash has been specified by the submit, and act on it!
                     close_modal();
                     check_hash(window.location.hash);
                 } else {
                     LOG('failed to complete ajax call for', action);
                 }
             } else {
                 LOG('form is not valid!');
             }
         }
         LOG('Modal_events returning "true"');
         return true;
     }
     // get the values from a form
     function get_form_values() {
         form = $(form_container).find('.ore-form');
         var values = {};
         LOG('get_form_values - found ', form);
         $(form).each(function() {
             inputs = $(this).find(':input');
             LOG('found '+inputs.length+' inputs');
             inputs.each(function(index, value) {
                 LOG(index+': ', value);
                 if (value.id) {
                     values[value.id] = value.value;
                 }
             });
             LOG('got values: ', values);
         });
         return values;
     }
     function get_purpose(action, purpose) {
         if (ore_data.modals[action].hasOwnProperty('purpose') &&
            ore_data.modals[action].purpose == purpose) {
                return true;
         } else {
             return false;
         }
     }
     // process ajax requests returning data to the server
     // and displaying error/status messages where relevant
     function ajax_submit(action, special_data) {
         LOG('in ajax_submission for', action);
        // LOG('url: ', ore_data.ajaxurl);
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
                     LOG('Succeeded: ', action);
                     // set details for registration feedback before showing new modal
                     if (action == 'register' || action == 'edit_profile' || action == 'login') {
                         LOG('adding details to the ore_data object to make it possible to provide user feedback.', ore_data.user);
                         ore_data.user = data.success;
                         LOG('reassigned data.success to ore_data.user: ', ore_data.user);
                     }
                     // setting hash to the "failed" result from the action
                     if (ore_data.modals[action].default.success != null) {
                         LOG('setting hash to #'+ore_data.modals[action].default.success);
                         set_hash(ore_data.modals[action].default.success);
                         show_modal(ore_data.modals[action].default.success);
                     }
                     // do special stuff for changes that have an impact on what
                     // the user is allowed to see on the site...
                     if (action == 'login' || action == 'edit_profile' || action == 'enrol' || action == 'leave') {
                         LOG('Reloading page for completed action: ', action);
                         reload(ore_data.modals[action].default.success);
                     } else {
                         LOG('nothing to do for action: '+action);
                     }
                 } else if (data.hasOwnProperty('failed')) { // if it failed, show error info
                     //LOG('wrapper html: ', wrapper.val());
                     LOG('Failed: ', action);
                     // setting hash to the "failed" result from the action
                     if (ore_data.modals[action].default.failed != null) {
                         LOG('setting hash to #'+ore_data.modals[action].default.failed)
                         set_hash('#'+ore_data.modals[action].default.failed);
                         show_modal(ore_data.modals[action].default.failed);
                     } else {
                         LOG('nothing to do for action: '+action);
                     }
                 } else {
                     LOG('this data has neither success nor failed');
                 }
                 check_hash(window.location.hash);
                 if (data.hasOwnProperty('failed')) {
                     LOG('Showing errors!', data.failed.errors.ore_error);
                     error_msg = show_errors(data.failed.errors.ore_error);
                     wrapper = $(form_container).find('#ore-error-wrapper');
                     LOG('htmls: ', wrapper);
                     LOG('found wrapper ('+wrapper.length+'): ', wrapper);
                     LOG('error message: '+error_msg);
                     wrapper.append(error_msg);
                 }
                 test_wrapper = $(form_container).find('.ore-form');
                 LOG('test_wrapper: ', test_wrapper);
                 children = $(form_container).children();
                 LOG('children: ', children);
                 return true;
             },
             error: function(jqXHR, textStatus, errorThrown) {
                 LOG('failure: jqXHR ', jqXHR);
                 LOG('failure: textStatus ', textStatus);
                 LOG('failure: errorThrown ', errorThrown);
                 return false;
             }
         });
         return true;
     }
     // combine data arrays
     function concat_data(one, two) {
        // LOG('one ', one);
        // LOG('two ', one);
         for (var key in two) {
             one[key] = two[key];
         }
        // LOG('resulting array: ', one);
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
     // show the errors in a useful way
     function show_errors(errors) {
         error_msg = "";
         if (errors.length > 0) {
             error_msg += '<div class="ore-errors"><h2>Errors</h2><ul class="ore-errors">';
             LOG('there are '+errors.length+' errors to report');
             errors.forEach(function(value, key) {
                 error_msg += '<li>'+value+'</li>';
             });
             error_msg += '</ul></div>';
         } else {
             LOG('no errors to report!');
         }
         return error_msg;
     }
    /*
     * End Modal dialogue stuff
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
     // do something based on the hash we find...
     function check_hash(hash) {
         hash = hash.replace('#','');
         //LOG('checking if ore_data is visible: ', ore_data);
         if (hash != "") {
             LOG('Hash found: ('+hash+')');
             // is it a hash we know?
             if (ore_data.modals.hasOwnProperty(hash)) {
                 // do we have the right state for it?
                 if (ore_data.modals[hash].hasOwnProperty('auth')) {
                     // this hash is only relevant if the user is authenticated
                     if (ore_data.modals[hash].auth) {
                         LOG('This modal requires an authencated user');
                         if (authenticated) {
                             LOG('showing modal as user is authenticated');
                             show_modal(hash);
                         } else {
                             LOG('modal is not appropriate for an unauthenticated user.');
                         }
                     } else {
                         LOG('This modal requires an unauthencated user');
                         if (!authenticated) {
                             LOG('showing modal as user is not authenticated');
                             show_modal(hash);
                         } else {
                             LOG('modal is not appropriate for an authenticated user.');
                         }
                     }
                 } else {
                     LOG('This probably isn\'t a hash for a dialogue we want to show: '+hash+'... removing the hash');
                     clear_hash();
                 }
             } else {
                 LOG('this isn\'t a hash we know... ', hash);
             }
         } else {
             LOG('no hash found...');
         }
     }
     /*
      * End hash stuff
      */
    /*
     * Initial actions on first load...
     */
    check_hash(current_hash);
    //set_validator_defaults();
    // run the user status when the page loads
    // if the user is logged in, create the authenticated menu
    // if not, the login/register menu for a visitor
    if (user_status()) {
        authenticated_menu();
    } else {
        visitor_menu();
    }
    // on a click outside the menu, close the menu
    $(document).click(function(){
        LOG('closing menus!');
        //$("."+menu).hide();
        $("."+menu).remove();
    });
    // set this up to submit on 'enter'
    $('input').keypress( function (e) {
        c = e.which ? e.which : e.keyCode;
        LOG('input: ' + c);
        if (c == 13) {
            $('#ore-default').click();
            return false;
        }
    });
});
