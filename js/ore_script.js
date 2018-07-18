/* created by Dave Lane, dave@oerfoundation.org, https://oeru.org */
var ORE_DEBUG = true; // set to false to disable debugging
function LOG() { if (ORE_DEBUG) { console.log.apply(this, arguments); }}
LOG('ORE DEBUG =', ORE_DEBUG); // only prints if DEBUG = true

//LOG('register-enrol: ', ore_data);
LOG('before jquery!');
// jQuery selectors and related functions in that context
// initiated after page is "ready"
jQuery(document).ready(function() {
    LOG('register-enrol: ', ore_data);

    var $ = jQuery;
    var form = $(this);

    /*
     * initialise jquery tooltips with custom functionality
     * credit for this: https://gist.github.com/csasbach/867744
     */
    var tooltip = '.ore-tooltip';  // trigger for popup content
    var popup = 'ore-popup';  // the actual popup class

    function enable_tooltips() {
        // popup display offsets
        var voffset = 6;
        var hoffset = 6;
        // popup pause and fade times in milliseconds
        var ptime = 3000;
        var ftime = 1000;

        LOG('enable tooltips');
        $(tooltip).each(function() {
            // grab the content from the title attribute and remove the
            // title attrib to avoid normal popup-on-hover behaviour
            $(this).data('title', $(this).attr('title'));
            LOG('sorted tooltip with text '+$(this).data('title'));
            $(this).removeAttr('title');
            // show popup on mouseover/hover
            $(tooltip).mouseover(function() {
                LOG('mouseover');
                // remove any currently displaying popups
                $(this).next('.'+popup).remove();
                // create the popup
                text = $(this).data('title');
                LOG('new text: '+text);
                $(this).after('<div class="'+popup+'"><p>'+text+'</p></div>');
                // manage positioning of the popups (voffset pixels above and hoffset left of tooltip trigger)
                var left = $(this).position().left + $(this).width()+hoffset;
    		    var top = $(this).position().top-voffset;
        		$(this).next().css('left',left);
                $(this).next().css('top',top);
                enable_popup();
            });
            // manage clicks, e.g. from touch devices
            $(tooltip).click(function() {
                LOG('click');
                $(this).mouseover();
                // after a ptime pause, then fade out over ftime second
                $(this).next().animate({opacity: 0.9}, {duration: ptime, complete: function() {
                    $(this).fadeOut(ftime);
                }});
            });
            // remove popup on mouseout
            $(tooltip).mouseout(function() {
                LOG('mouseout');
    			$(this).next('.'+popup).remove();
            });

        });
    }
    function enable_popup() {
        LOG('enabling popup');
        $('.'+popup).each(function() {
            // if the user explicitly clicks on a popup
            $('.'+popup).click(function() {
                LOG('click on popup');
                $(this).remove();
            });
        });
    }
    /*
     * end tooltip stuff
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

    // handle the submit button being pushed
    $('#ore-submit').click(function() {
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
                    // initialise tooltips
                    enable_tooltips();
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
    });

    // the end of the jQuery loop...
}); // });
LOG('after jquery!');
