<?php

/*
 * specs for modal dialogues.
 * the key is the name, e.g. 'login' and the values are
 * 'title' = the title of the dialogue
 * 'token' = used to build relevant CSS classes/ids for the markup, usually the key with _ replaced by -
 * 'markup' = the HTML exposition and/or form content, not including a wrapper
 * replaceable tokens, with a default or dynamic value are in {}
 * default_action = is the same as the name... this is triggered if the user
 * clicks the button with the 'default_label' or hits the 'ENTR' key
 * An action can have a success or failed outcome, each with a different
 *   result, so that's 'default_success' and 'default_failed'.
 * some forms can have alternative actions, e.g. someone going to the login
 * form might realise they want to go to a different 'destination'.
 * Each 'destination' is a the name of a dialog.
 *
 */
static $modals = array(
    'login' => array(
        'title' => 'Log in to OERu Course site',
        'markup' => '<p>Login Form</p><p class="password-reset">Request a password reset</p>',
        'token' => 'login',
        'default' => array(
            'label' => 'Log in',
            'detail' => 'Once you have entered your details (don\'t let anyone look over your shoulder while you enter your password!), you can log in.',
            'success' => 'successful_login',
            'failed' => 'failed_login',
        'alternative' => array(
            'label' => 'I need to register',
            'detail' => 'If you haven\'t previously registered an account, you need to do that first.',
            'destination' => 'register',
        ),
    ),
    'successful_login' => array(
        'title' => 'Login successful',
        'markup' => '<p>You have successfully logged in as {display_name} ({username}).</p>',
        'token' => 'successful-login',
        'default' => array(
            'label' => 'Ok',
        ),
    ),
    'failed_login' => array(
        'title' => 'Login attempt failed',
        'markup' => '<p>You have entered an invalid email or password. Or, is it possible you have not yet registered?',
        'token' => 'failed-login',
        'default' => array(
            'label' => 'Try again',
            'detail' => 'If you think you might have mistyped or entered the wrong details, you can try again. Or you can register, if you haven\'t previously.',
            'destination' => 'login'
        ),
    ),
    'password_reset' => array(
        'title' => 'Reset your password',
        'markup' => '<p>If you have forgotten your password, you can request a reset. We will need to know the email address you used to register or your username.</p>'.
            '<p class="email"><input id="ore-email" class="email" value="" type="email">email</input></p>'.
            '<p class="username"><input id="ore-username" class="username" value="" type="text">username</input></p>',
        'default' => array(
            'label' => 'Reset Password',
            'detail' => 'A link will be sent to your registered email address. Check your spam folder if it doesn\'t appear within a few minutes')
        ),
    ),
    'register' => array(
        'title' => 'Register on the OERu Course site',
        'markup' => 'Register Form',
        'token' => 'register',
        'default' => array(
            'label' => 'Register',
            'success' => 'successful_registration',
            'failed' => 'failed_registration',
        ),
        'alternative' => array(
            'label' => 'Login',
            'detail' => 'If you think you might have previously registered, you can try logging in.',
            'destination' => 'login',
        ),
    ),
    'successful_registration' => array(
        'title' => 'Registration successful',
        'markup' => '<p>You have successfully registered a user called {display_name} with username {username}, and email {email}.</p>',
        'token' => 'successful-login',
        'default' => array(
            'label' => 'Ok',
        ),
    ),
    'failed_registration' => array(
        'title' => 'Registration failed',
        'markup' => '<p>Your registration failed because of the following {error}:</p> {error_list}',
        'token' => 'failed-registration',
        'default' => array(
            'label' => 'Try again',
            'detail' => 'If you think you might have mistyped or entered the wrong details, you can try again. Or if you think you might have registered perviously, you can try logging in.',
            'destination' => 'register'
        ),
    ),
    'edit_profile' => array(
        'title' => 'Edit your profile',
        'markup' => '<div class="form-group">'.
                '<label for="firstname">First Name</label>'.
                '<input type="text" class="form-control" id="first-name" placeholder="your first name" aria-describedby="helpFirstName">'.
                '<span id="helpFirstName" class="help-block">Your first or given name(s) as you would like it(them) displayed.</span>'.
                '<label for="lastname">Last Name</label>'.
                '<input type="text" class="form-control" id="last-name" placeholder="your last name" aria-describedby="helpLastName">'.
                '<span id="helpLastName" class="help-block">Your last or family name(s) as you would like it(them) displayed.</span>'.
            '</div>'.
            '<div class="form-group">'.
                '<label for="username">Username</label>'.
                '<input type="text" class="form-control" id="username" placeholder="username" aria-describedby="helpUsername">'.
                '<span id="helpUsername" class="help-block">Your preferred username. Allowed letters: a-z0-9_-. Spaces and other special characters not allowed.</span>'.
            '</div>'.
            '<div class="form-group">'.
                '<label for="display-name">Display Name</label>'.
                '<input type="text" class="form-control" id="display-name" placeholder="Ms Sue Smith" aria-describedby="helpDisplayName">'.
                '<span id="helpDisplayName" class="help-block">Your publicly visible name or preferred nickname.</span>'.
          	'</div>'.
            '<div class="form-group">'.
                '<label for="password">Password</label>'.
                '<input type="password" class="form-control" id="password" aria-describedby="helpPassword">'.
                '<span id="helpPassword" class="help-block">At least 8 characters.</span>'.
                '<label for="confirm-password">Confirm password</label>'.
                '<input type="password" class="form-control" id="confirm-password">'.
          	'</div>'.
      		'<div class="form-group">'.
            	'<label for="email">Email</label>'.
                '<input type="text" class="form-control" id="email" placeholder="me@example.com">'.
          	'</div>'.
        	'<div class="form-group">'.
                '<label for="usercountry">Country of origin</label>'.
                '{country_picker}'.
                '<span id="helpUserCountry" class="help-block">Select the country with which you most closely identify.</span>'.
	      	 '</div>',
        'token' => 'edit-profile',
        'default' => array(
            'label' => 'Save',
            'detail' => 'Save your changes.',
            'success' => 'profile_saved',
            'failed' => 'profile_save_failed',
        ),
        'alternative' => array(
            'label' => 'Cancel',
            'detail' => 'Ignore these changes. Leave your profile unchanged.',
        ),
    ),
    'session_expired' => array(
        'title' => 'Session Exp',
        'markup' => '<p>Your session has timed out. To continue, you must renew your session by entering the password for user {username}.</p>'.
            '<p class="password"><input id="ore-password" class="password" value="" type="password">password</input></p>',
        'token' => 'session-expired',
        'default' => array(
            'label' => 'Renew Session',
            'detail' => 'This is a security precaution to reduce the likelihood of an unauthorised person taking over your account if, for example, you accidentally left yourself logged into a computer at an Internet cafe or library.'
        )
    ),
    'enrol' => array(
        'title' => 'Enrol in this OERu Course',
        'markup' => '<p>You can enrol in {course_title} ({course_code}).</p>',
        'token' => 'enrol',
        'default' => array(
            'label' => 'Enrol',
            'detail' => 'Once enrolled, have no fear: you can leave the course any time you like.',
            'success' => 'successfully_enrolled',
            'failed' => 'failed_to_enrol',
        ),
        'alternative' => array(
            'label' => 'Cancel'
        ),
    ),
    'leave' => array(
        'title' => 'Leave this OERu Course',
        'markup' => '<p>You can leave this course, {course_title} without penalty. You can also rejoin in future if you like.</p>',
        'token' => 'leave',
        'default' => array(
            'label' => 'Unenrol',
        ),
    ),
);
