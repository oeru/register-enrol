# register-enrol
A plugin allowing learners to login or register (as WP users) and enrol (and unenroll) in OERu courses (subsites).

## Credits

This plugin is licensed under the GPLv3 and is, in part, derived from [Login With Ajax](https://wordpress.org/plugins/login-with-ajax/) (also GPLv3 licensed) with significant changes to its behaviour and with additional content, especially related to multisite behaviour.

## Plugin Purpose

The [OERu](https://oeru.org) offers tertiary (university) level online courses (each course is a subsite of the OERu's [course.oeru.org](https://course.oeru.org) WordPress multisite) for learners, who can participate either anonymously or (for their own benefit) as registered WordPress users.

Registered users can choose to formally enrol in one or more OERu courses.

Being registered confers benefits like proof of participation, maintenance of "state", e.g. previously filled in form values (i.e. via session variables), as well as more social benefits like being able to post messages to fellow learners via individual course 'WEnotes Feed' pages or register a personal blog feed to be scanned for course-related posts.

This plugin provides streamlined services to allow a learner who wants to register for the Course site to do so, and then, as an authenticated (logged in) WordPress user, gives them the ability to easily enrol (or unenrol) in any of  our catalogue of courses.

## Features

* fully multisite aware, allowing authenticated users to "enrol" in networks/blogs/subsites(the WP nomenclature is somewhat inconsistent), what we call "courses".
* confirmation of all changes in authentication (registered, logged in, logged out) and course enrolled or unenrolled state
* a login status indicator tab showing the user's status at all times, e.g. "anonymous visitor", "registered but not logged in", "logged in", and in the context of a course "not enrolled" or "enrolled".
* modal dialogs for user interaction and messaging
* mobile compatible layout and user interface
* logs the participation of logged in learners (WP users) in courses (subsites)
* provides a dashboard of enrolled courses for logged in learners



## Requirements, Use Cases, and Future Plans

See our [wiki](https://github.com/oeru/register-enrol/wiki) for discussions on these things.

## Userful references:

* For rewriting the URLs of login-related activities, the [custom-login-url plugin](https://wordpress.org/plugins/custom-login-url/) is very helpful
