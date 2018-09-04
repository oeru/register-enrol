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
 * 'auth' is whether the form is limited to those who are authenticated (logged in) - 'true' or not, 'false'
 *
 *
 */
static $modals = array(
    'login' => array(
        'title' => 'Log in to OERu Course site',
        'token' => 'login',
        'auth' => false,
        'markup' => '<div class="form-group">'.
                '<label for="credential">Username or Email</label>'.
                '<input type="text" class="form-control" id="credential" name="credential" placeholder="username or user@email" aria-describedby="helpCredential">'.
                '<span id="helpCredential" class="help-block">Either is acceptable, because both uniquely identify you in this system.</span>'.
            '</div>'.
            '<div class="form-group">'.
                '<label for="password">Password</label>'.
                '<input type="password" class="form-control" id="password" name="password" aria-describedby="helpPassword">'.
                '<span id="helpPassword" class="help-block">Make sure no one is watching as you type this.<br/><br/>Problems? You can do a <span id="ore-password-reset-auxillary-button" class="link ore-button">Password Reset</span><br/><br/>(The OERu encourages you to use "password manager" software to store your passwords and help you create a strong and unique one for each website!)</span>'.
      	    '</div>',
        'default' => array(
            'label' => 'Log in',
            'class' => 'submit',
            'detail' => 'Once you have entered your details (don\'t let anyone look over your shoulder while you enter your password!), you can log in.',
            'success' => 'successful_login',
            'failed' => 'failed_login',
        ),
        'alternative' => array(
            'label' => 'I need to register',
            'class' => 'submit',
            'detail' => 'If you haven\'t previously registered an account, you need to do that first.',
            'destination' => 'register',
        ),
    ),
    'successful_login' => array(
        'title' => 'Login successful',
        'token' => 'successful-login',
        'purpose' => 'confirmation',
        'markup' => '<p>You have successfully logged in as {display_name} ({username}).</p>',
        'default' => array(
            'label' => 'Ok',
            'class' => 'submit',
        ),
    ),
    'failed_login' => array(
        'title' => 'Login attempt failed',
        'token' => 'failed-login',
        'purpose' => 'confirmation',
        'markup' => '<p>You have entered an invalid email or password. Or, is it possible you have not yet registered?'.
            '<div id="ore-error-wrapper"></div>',
        'default' => array(
            'label' => 'Try again',
            'class' => 'submit',
            'detail' => 'If you think you might have mistyped or entered the wrong details, you can try again. Or you can register, if you haven\'t previously.',
            'destination' => 'login'
        ),
    ),
    'password_reset' => array(
        'title' => 'Reset your password',
        'token' => 'password-reset',
        'markup' => '<p>If you have forgotten your password, you can request a reset. We need to know <em>one</em> of the   following: (1) the email address you used to register, or (2) your username.</p>'.
        '<div class="form-group">'.
            '<label for="credential">Enter your Username or Email to identify yourself.</label>'.
            '<input type="text" class="form-control" id="credential" name="credential" placeholder="username or user@email" value="{email}" aria-describedby="helpCredential">'.
            '<span id="helpCredential" class="help-block">Either is acceptable, because both uniquely identify you in this system.</span>'.
        '</div>',
        'default' => array(
            'label' => 'Reset Password',
            'class' => 'submit',
            'detail' => 'A link will be sent to your registered email address. Check your spam folder if it doesn\'t appear within a few minutes',
            'success' => 'successful_reset',
            'failure' => 'failed_reset'
        ),
    ),
    'successful_reset' => array(
        'title' => 'Password Reset Email Sent',
        'token' => 'successful-reset',
        'purpose' => 'confirmation',
        'markup' => '<p>We have sent an email with a password reset link in it, so check your email.</p>'.
            '<p>Clicking that  link will allow you to set a new password</p>'.
            '<p>If you haven\'t received an email from us in 10-15 minutes, check your spam folder, but it could take as long as 30 minutes to get to you. If you don\'t get it at all, you can <a href="https://oeru.org/contact-us">contact us</a> for assistance.</p>'.
            '<p class="note">(For security reasons, we do not store your actual password in our systems)</p>',
        'default' => array(
            'label' => 'Ok',
            'class' => 'submit',
        ),
    ),
    'failed_reset' => array(
        'title' => 'Password Reset Failed',
        'token' => 'failed-reset',
        'purpose' => 'confirmation',
        'markup' => '<p>We were not able to find a user with the details you have entered in our system.</p>'.
            '<p>Please check that you have typed in your chosen identifier - your username or email - correctly.</p>'.
            '<p>If it\'s possible you haven\'t previously registered an account with us, you can do that now.</p>',
        'default' => array(
            'label' => 'Try again',
            'class' => 'submit',
            'success' => 'reset_password',
        ),
        'alternative' => array(
            'label' => 'I need to register',
            'class' => 'submit',
            'detail' => 'If you haven\'t previously registered an account, you need to do that first.',
            'destination' => 'register',
        ),
    ),
    'register' => array(
        'title' => 'Register on the OERu Course site',
        'token' => 'register',
        'auth' => false,
        'markup' => '<div class="form-group">'.
                '<label for="firstname">First Name</label>'.
                '<input type="text" class="form-control" id="first-name" name="first-name" placeholder="your first name" aria-describedby="helpFirstName">'.
                '<span id="helpFirstName" class="help-block">Your first or given name(s) as you would like it(them) displayed.</span>'.
                '<label for="lastname">Last Name</label>'.
                '<input type="text" class="form-control" id="last-name" name="last-name" placeholder="your last name" aria-describedby="helpLastName">'.
                '<span id="helpLastName" class="help-block">Your last or family name(s) as you would like it(them) displayed.</span>'.
            '</div>'.
            '<div class="form-group">'.
                '<label for="username">Username</label>'.
                '<input type="text" class="form-control" id="username" name="username" placeholder="username" aria-describedby="helpUsername">'.
                '<span id="helpUsername" class="help-block">Your preferred username. Allowed letters: a-z0-9_-. Spaces and other special characters not allowed. It must be unique in this OERu system. Please note: this username can only be altered by an administrator after it is created, so please choose wisely.</span>'.
            '</div>'.
            '<div class="form-group">'.
                '<label for="display-name">Display Name</label>'.
                '<input type="text" class="form-control" id="display-name" name="display-name" placeholder="Ms Sue Smith" aria-describedby="helpDisplayName">'.
                '<span id="helpDisplayName" class="help-block">Your publicly visible name or preferred nickname.</span>'.
          	'</div>'.
            '<div class="form-group">'.
                '<label for="password">Password</label>'.
                '<input type="password" class="form-control" id="password" name="password" aria-describedby="helpPassword">'.
                '<span id="helpPassword" class="help-block">At least 8 characters with a mix of lower and upper case letters, numbers, and symbols.</span>'.
                '<label for="confirm-password">Confirm password</label>'.
                '<input type="password" class="form-control" id="confirm-password" name="confirm-password" aria-describedby="helpConfirmPassword">'.
                '<span id="helpConfirmPassword" class="help-block">This must be identical to the Password field.'.
          	'</div>'.
      		'<div class="form-group">'.
            	'<label for="email">Email</label>'.
                '<input type="email" class="form-control" id="email" name="email" placeholder="me@example.com" aria-describedby="helpEmail">'.
                '<span id="helpEmail" class="help-block">Your full email address (must include an "@"). It must be unique in this OERu system.</span>'.
          	'</div>'.
        	'<div class="form-group">'.
                '<label for="usercountry">Country of origin</label>'.
                '{country_picker}'.
                '<span id="helpUserCountry" class="help-block">Select the country with which you most closely identify, which may not be the one in which you currently are, or where you live.</span>'.
	        '</div>',
        'default' => array(
            'label' => 'Register',
            'class' => 'submit',
            'detail' => 'Register a new user account with the details entered above.',
            'success' => 'successful_registration',
            'failed' => 'failed_registration',
        ),
        'alternative' => array(
            'label' => 'Login',
            'class' => 'submit',
            'detail' => 'If you think you might have previously registered, you can try logging in.',
            'destination' => 'login',
        ),
    ),
    'successful_registration' => array(
        'title' => 'Registration successful',
        'token' => 'successful-login',
        'purpose' => 'confirmation',
        'markup' => '<p>You have successfully registered a user called {display_name} with username {username}, and email {email}.</p>',
        'default' => array(
            'label' => 'Ok',
            'class' => 'submit',
        ),
    ),
    'failed_registration' => array(
        'title' => 'Registration failed',
        'token' => 'failed-registration',
        'purpose' => 'confirmation',
        'markup' => '<p>Failed to Register a new user</p>'.
            '<div id="ore-error-wrapper"></div>',
        'default' => array(
            'label' => 'Try again',
            'class' => 'submit',
            'detail' => 'If you think you might have mistyped or entered the wrong details, you can try again. Or if you think you might have registered perviously, you can try logging in.',
            'destination' => 'register'
        ),
    ),
    'edit_profile' => array(
        'title' => 'Edit profile for user <strong>{username}</strong>',
        'token' => 'edit-profile',
        'auth' => true,
        'markup' => '<div class="form-group">'.
                '<label for="firstname">First Name</label>'.
                '<input type="text" class="form-control" id="first-name" name="first-name" placeholder="your first name" aria-describedby="helpFirstName" value="{first_name}">'.
                '<span id="helpFirstName" class="help-block">Your first or given name(s) as you would like it(them) displayed.</span>'.
                '<label for="lastname">Last Name</label>'.
                '<input type="text" class="form-control" id="last-name" name="last-name" placeholder="your last name" aria-describedby="helpLastName" value="{last_name}">'.
                '<span id="helpLastName" class="help-block">Your last or family name(s) as you would like it(them) displayed.</span>'.
            '</div>'.
            '<div class="form-group">'.
                '<label for="display-name">Display Name</label>'.
                '<input type="text" class="form-control" id="display-name" name="display-name" placeholder="Ms Sue Smith" aria-describedby="helpDisplayName" value="{display_name}">'.
                '<span id="helpDisplayName" class="help-block">Your publicly visible name or preferred nickname.</span>'.
          	'</div>'.
      		'<div class="form-group">'.
            	'<label for="email">Email</label>'.
                '<input type="text" class="form-control" id="email" name="email" placeholder="me@example.com" value="{email}" aria-describedby="helpEmail">'.
                '<span id="helpEmail" class="help-block">Your full email address (must include an "@"). It must be unique in this OERu system.</span>'.
          	'</div>'.
        	'<div class="form-group">'.
                '<label for="usercountry">Country of origin</label>'.
                '{country_picker}'.
                '<span id="helpUserCountry" class="help-block">Select the country with which you most closely identify, which may not be the one in which you currently are, or where you live.</span>'.
	      	 '</div>',
         'default' => array(
             'label' => 'Save',
             'class' => 'submit',
             'detail' => 'Save your changes.',
             'success' => 'profile_saved',
             'failed' => 'profile_save_failed',
         ),
         'alternative' => array(
             'label' => 'Cancel',
             'class' => 'cancel',
             'detail' => 'Ignore these changes. Leave your profile unchanged.',
         ),
    ),
    'profile_saved' => array(
        'title' => 'Your profile (username <strong>{username}</strong>) has been saved',
        'token' => 'profile-saved',
        'purpose' => 'confirmation',
        'markup' => '<p>Profile for {display_name} successfully updated.</p>',
        'default' => array(
            'label' => 'Ok',
            'class' => 'submit',
        ),
    ),
    'profile_save_failed' => array(
        'title' => 'Your {username} profile was <strong>not</strong> saved',
        'token' => 'profile-saved',
        'purpose' => 'confirmation',
        'markup' => '<p>Error: profile for {display_name} ({username}) not updated.</p>'.
        '<div id="ore-error-wrapper"></div>',
        'default' => array(
            'label' => 'Ok',
            'class' => 'submit',
        ),
    ),
    'update_password' => array(
        'title' => 'Update password for user <strong>{username}</strong>',
        'token' => 'update-password',
        'markup' => '<div class="form-group">'.
                '<label for="current-password">Current Password</label>'.
                '<input type="password" class="form-control" id="current-password" name="current-password" aria-describedby="helpCurrentPassword">'.
                '<span id="helpCurrentPassword" class="help-block">Your current password, so that we can ensure you\'re you!<br/>If you can\'t remember your password, you can instead do a <span id="ore-password-reset-auxillary-button" class="link ore-button">Password Reset</span><br/>(The OERu encourages you to use "password manager" software to store your passwords and help you create a strong and unique one for each website!)</span>'.
          	'</div>'.
            '<div class="form-group">'.
                '<label for="new-password">New Password</label>'.
                '<input type="password" class="form-control" id="new-password" name="new-password" aria-describedby="helpNewPassword">'.
                '<span id="helpNewPassword" class="help-block">At least 8 characters with a mix of lower and upper case letters, numbers, and symbols.</span>'.
                '<label for="confirm-password">Confirm New Password</label>'.
                '<input type="password" class="form-control" id="confirm-password" name="confirm-password" aria-describedby="helpConfirmPassword">'.
                '<span id="helpConfirmPassword" class="help-block">This must be identical to the New Password field.'.
          	'</div>',
         'default' => array(
             'label' => 'Save',
             'class' => 'submit',
             'detail' => 'Update your password.',
             'success' => 'password_updated',
             'failed' => 'password_update_failed',
         ),
         'alternative' => array(
             'label' => 'Cancel',
             'class' => 'cancel',
             'detail' => 'Leave your password unchanged.',
         ),
    ),
    'password_updated' => array(
        'title' => 'Your password has been updated',
        'token' => 'password-updated',
        'purpose' => 'confirmation',
        'markup' => '<p>The password for {display_name} has been updated.</p>',
        'default' => array(
            'label' => 'Ok',
            'class' => 'submit',
        ),
    ),
    'password_update_failed' => array(
        'title' => 'Your {username} password was <strong>not</strong> updated',
        'token' => 'password-update-failed',
        'purpose' => 'confirmation',
        'markup' => '<p>Password for <strong> {username} </strong> not updated.</p>'.
            '<div id="ore-error-wrapper">&nbsp;</div>',
        'default' => array(
            'label' => 'Ok',
            'class' => 'submit',
        ),
    ),
    'session_expired' => array(
        'title' => 'Session Exp',
        'markup' => '<p>Your session has timed out. To continue, you must renew your session by entering the password for user {username}.</p>'.
            '<p class="password"><input id="password" name="password" class="password" type="password">password</input></p>',
        'token' => 'session-expired',
        'default' => array(
            'label' => 'Renew Session',
            'class' => 'submit',
            'detail' => 'This is a security precaution to reduce the likelihood of an unauthorised person taking over your account if, for example, you accidentally left yourself logged into a computer at an Internet cafe or library.'
        )
    ),
    'enrol' => array(
        'title' => 'Enrol in this OERu Course',
        'auth' => true,
        'token' => 'enrol',
        'markup' => '<p>You can enrol in "{course_title}" ({course_tag}).</p>',
        'default' => array(
            'label' => 'Enrol',
            'class' => 'submit',
            'detail' => 'Please note: <strong>by enrolling in this course, you are opt-in to receiving course instructions via email</strong>. Once enrolled, have no fear: you can leave the course any time you like.',
            'success' => 'successfully_enrolled',
            'failed' => 'failed_to_enrol',
        ),
        'alternative' => array(
            'label' => 'Cancel',
            'class' => 'cancel',
        ),
    ),
    'successfully_enrolled' => array(
        'title' => 'Successfully enrolled in "{course_title}" ({course_tag})',
        'token' => 'successfully-enrolled',
        'purpose' => 'confirmation',
        'markup' => '<p>You have successfully enrolled as {display_name} ({username}).</p>',
        'default' => array(
            'label' => 'Ok',
            'class' => 'submit',
        ),
    ),
    'failed_to_enrol' => array(
        'title' => 'Enrollment in "{course_title}" ({course_tag}) failed.',
        'token' => 'failed-to-enrol',
        'purpose' => 'confirmation',
        'markup' => '<p>It is not clear why your enrollment attempt failed - please <a href="ORE_CONTACT_URL">contact OERu</a> to report this problem.',
        'default' => array(
            'label' => 'Ok',
            'class' => 'submit',
        ),
    ),
    'leave' => array(
        'title' => 'Leave this OERu Course',
        'token' => 'leave',
        'auth' => true,
        'markup' => '<p>You can leave this course, "{course_title}" without penalty. You can also rejoin in future if you like.</p>',
        'default' => array(
            'label' => 'Unenrol',
            'class' => 'submit',
            'success' => 'successfully_unenrolled',
        ),
        'alternative' => array(
            'label' => 'Cancel',
            'class' => 'cancel',
        ),
    ),
    'successfully_unenrolled' => array(
        'title' => 'Successfully unenrolled from "{course_title}" ({course_tag})',
        'token' => 'successfully-unenrolled',
        'purpose' => 'confirmation',
        'markup' => '<p>You have successfully removed your user, {display_name} ({username}).</p>',
        'default' => array(
            'label' => 'Ok',
            'class' => 'submit',
        ),
    ),
    'failed_to_unenrol' => array(
        'title' => 'Unenrolling you from "{course_title}" ({course_tag}) failed.',
        'token' => 'failed-to-unenrol',
        'purpose' => 'confirmation',
        'markup' => '<p>It is not clear why your unenrollment attempt failed - please <a href="ORE_CONTACT_URL">contact OERu</a> to report this problem.',
        'default' => array(
            'label' => 'Ok',
            'class' => 'submit',
        ),
    ),
);
