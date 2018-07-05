# register-enrol
A plugin allowing learners to register (as WP users) and (un)enrol in OERu courses (subsites).

## Plugin Purpose

The [OERu](https://oeru.org) offers tertiary (university) level online courses (each course is a subsite of the OERu's [course.oeru.org](https://course.oeru.org) WordPress multisite) for learners, who can participate either anonymously or (for their own benefit) as registered WordPress users.

Registered users can choose to formally enrol in one or more OERu courses.

Being registered confers benefits like proof of participation, maintenance of "state", e.g. previously filled in form values (i.e. via session variables), as well as more social benefits like being able to post messages to fellow learners via individual course 'WEnotes Feed' pages or register a personal blog feed to be scanned for course-related posts.

This plugin provides streamlined services to allow a learner who wants to register for the Course site to do so, and then, as an authenticated (logged in) WordPress user, gives them the ability to easily enrol (or unenrol) in any of  our catalogue of courses.

## Use Cases

1. Learner is directed to a Course page (one of the subsites) from an external link or the main OERu course catalogue. They want to register for the course. They click an "enroll" link which takes them to a page explaining the process. They need to
    1. register an account on the Course site as a pre-requisite for enrolling for any OERU course, and then,
    2. enroll for the specific course.

Of course, the enrolling could be done implicitly here, when the user completes their registration by redirecting the user to the course site with an "enrol this user for this course" variable set.

2. A Learner is already registered on the Course site and wants to enrol in a course. They go to the course page and click on the "Enrol" button. They are directed to a page which
  - if they are not logged in - asks them to log in (or register if they haven't previously done so) and, having done so, redirects them to the course front page with the "enrol this user for this course" variable set, doing so implicitly.
  - if they're already logged in - redirects them to the same page with the "enrol this user in this course" variable set - and the site displays a "you are enrolled "

3. A learner wants to see what courses she's enrolled in and goes to the user profile page (e.g. by clicking the "person" icon in the menu or a "my account" link). The page shows her a list of courses for which she's enrolled (including the date enrolled) and provides an "unenrol" button for each (which if clicked, will ask "are you sure"). The page will also offer a link to view other available courses (or perhaps a list of courses currently available on the Course site, in which the learner can enrol)
