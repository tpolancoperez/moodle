AutoEnrol Enrolment Method
*********************

When added to a course this enrolment plugin can enrol users onto a course automatically,
either as they log into your Moodle site or as they click on the course. It is intended 
for use on site-wide courses such as "Moodle Help" or "Learner Voice". 

In addition the plugin has advanced functionality to support autmatically grouping and
filtering users based upon their attributes. Depending on how your user accounts are set
up this may help you to give access to certain user groups.

The plugin adds new permissions to help you manage the use of the plugin. The
first permission called "config" allows the user to add a new instance of the plugin
and change only the most basic options. The second permission is called "method" and gives
users the ability to change when the user will be enrolled on a course, and what type of 
enrolment it will be (ie something other than student). There are further permissions 
relating to the plugin which should be reviewed through Moodle's "Define Roles" page.

Changelog
**********
v1.2   - New functionality and tweaks:
           It is now possible to add multiple instances to a single course.
           Added an option to give instance a custom label.
           Filtering functions now allow for partial matches.
           Expanded filtering functions to include email address.
           Added an option to limit number of enrolments. 
           By default, users are now only enrolled if they aren't already enrolled on a course.
           Individual users can now be manually unenrolled through Users > Enrolled Users.
           Added a permission for users to unenrol themselves if not enrolling during login.
v1.1   - Minor update. Improved instance configuration form compatability with Moodle 2.5
v1.0   - Stable release. New config option to "Add instance to new courses"
v0.91  - Bug Fix - Filtering was being bypassed when enrolling on site-login
v0.9   - Beta release

Install
**********

Copy the plugin directory "autoenrol" into moodle\enrol\. 
Check admin notifications to install.

Maintainer
**********

The block has been written and is being maintained by Mark Ward for Burton & 
South Derbyshire College (UK) with support from Matthew Cannings of Sandwell College (UK).


Contact
*******

http://moodle.org/user/profile.php?id=489101
http://moodlemark.com


License
*******

Released Under the GNU General Public Licence http://www.gnu.org/copyleft/gpl.html