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
        //data-toggle="modal"
        //data-target="#userModal"
        /*text = '<div class="ore-left"><p>If you have already registered, please</p><p style="text-align: center;"><span id="ore-login-button" class="button ore-button">Log In</span></p></div><div class="ore-right"><p>If not, we invite you to</p><p style="text-align: center;"> <span id="ore-register-button" class="button ore-button">Register</span></p></div>';*/
        text = '<div class="ore-left"><p>If you do not yet have a login, we invite you to</p><p style="text-align: center;"> <span id="ore-register-button" class="button ore-button" data-toggle="modal" data-target="#ore-modal">Register</span></p></div><div class="ore-right"><p>If you have already registered, please</p><p style="text-align: center;"><span id="ore-login-button" class="button ore-button" data-toggle="modal" data-target="#ore-modal">Log In</span></p></div>';
        //<ul><li><a id="item-1" class="menu-item">Clickable link 1</a></li><li><a id="item-2" class="menu-item">Clickable link 2</a></li><ul>
        LOG('new text: '+text);
        // enable menus
        prepare_menu(text);
    }
    /*
     * Menu for an unauthenticated (anonymous) user
     */
    function authenticated_menu(data) {
        user = data.user;
        text = '<p>You are logged in as '+user.display_name+' ('+user.username+').</p><p>You can <span id="ore-edit-profile-button" class="button ore-button" data-toggle="modal" data-target="#ore-modal">Edit Your Profile</span></p>';
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
                // after a pausetime pause, then fade out over fadetime second,
                // unless a mouse is hovering
                /*$(.ore-button).click(function() {
                    LOG('button clicked!');
                });*/

                LOG('click');
                set_menu_fade();
                // .next() means the menu popup node
                $(this).next().hover( function() {
                        $(this).next().animate({opacity: 1.0}, {duration: 0, complete: function() {
                            // fade it back to full
                            LOG('we got moving');
                            $(this).fadeIn();
                            // reset the clock
                            set_menu_fade();
                        }
                    });
                });
                // if the user clicks within the box, fade shortly thereafter
                //
                // Process Menu Click Events
                $(this).next().click( function (e) {
                    LOG('click in menu - this should be passed on!')
                    //menu = $(this).next();
                    LOG('this = ', $(this));
                    LOG('e = ', e);
                    if (e.target.id == 'ore-edit-profile-button') {
                        LOG('Launch Edit Profile!');
                        //window.history.pushState("object or string", "OERu Register Enrol - Edit Profile", "/register-enrol/edit-profile");
                        LOG('data: ', ore_data.modals.edit_profile);
                        form = $('#ore-login-status').append(ore_data.modals.edit_profile.markup);
                        //form = $(this).next().append(ore_data.modals.edit_profile.markup);
                        //form.attr('id', 'ore-container');
                        //LOG('form ', form);
                    } else if (e.target.id == 'ore-login-button') {
                        LOG('Launch Login!');
                        window.history.pushState("object or string", "OERu Register Enrol - Login", "/register-enrol/login");
                    } else if (e.target.id == 'ore-register-button') {
                        LOG('Launch Register!');
                        window.history.pushState("object or string", "OERu Register Enrol - Register", "/register-enrol/register");
                    } else {
                        LOG('click within menu isn\'t on a known button');
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
    $('#ore-container').on('click', '.ore-button', function(e) {
        LOG('click! e ', e);
        $(this).parent().modal('hide');
    });
    $('#')

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

    /*
     * Custom events
     */
    // handle the submit button being pushed
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
