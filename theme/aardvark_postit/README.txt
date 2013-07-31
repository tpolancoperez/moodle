AARDVARK POST-IT
----------------

This version of Aardvark Post-IT (2012072101) is set up for Moodle 2.3.1
Change Log: 21st July 2012
--------------------------
It looks like Moodle 2.3 has regressed in some way. Although you could move the blocks in the previous "Drag-n-Drop" version of Aardvark Post-IT, the pagelayout was not working properly, either when sideblocks were docked and the profilebar had blocks, or when blocks were moved to one side only and some blocks were in the profilebar. This behaviour has now become a major issue which has been logged in Moodle Tracker MDL-34496.

To keep Aardvark-Postit still available for Moodle 2.3.1, albeit sans the custom block regions, I have created two new text areas instead which have replaced the custom block regions in the profile bar, so that Admins or anyone with the correct permissions, can add a couple of short notes to users. These areas look like two Postit Notes, hence the release name. 

This version of Aardvark Post-IT (2012071202) is set up for Moodle 2.3.1
Change Log: 12th July 2012
--------------------------
The fix came a week later than expected, but along with it brought new problems which are fixed in this version,
These are:
    - correct version.php for this version of Aardvak Post-IT
    - body classes now set in layout/default.php which determines blocks in the profilebar when sideblocks are docked.
    - some CSS udates: darker text color in grader table headers.
Modifications:
Split profileblock.php into two seperate files for simplicity making for easier maintenance
    - profilelogin.php
    - profileblock.php


This version of Aardvark Post-IT (2012060200) is set up for Moodle 2.3.1
Change Log: 2nd July 2012
-------------------------

Made some changes to profileblock.php to enable blocks to be moved into the profilebar
when using the new Drag-n-Drop AJAX feature in Moodle 2.3.x.
This version of Aardvark Post-IT will not work in Moodle 2.3 but is expected to work correctly in Moodle 2.3.1 which is due out on the 6th July 2012.

Change Log: 5th April 2012
--------------------------

Added Custom CSS setting.
Fixed pagelayout problem when some blocks where docked and others in the profile-bar were not.
Fixed report pagelayouts to allow scroll in gradebook.

LATEST UPDATES (2012051100)
--------------

AARDVARK POST-IT (Moodle 2.2.x Theme)

Fixed missing headers due to incorect setting for STANDARD pagelayout in aardvark_postit/config.php and also added redirect pagelayout
Cleaned up CSS whitespace
Used UNICODE characters rather than images for the toggle tab in the profilebar
Removed toggle5 script from profilebar and moved it to theme/aardvark_postit/javascript/ directory as a seperat file
Added javascript toggle5 file in theme/aardvark_postit/config.php so that it gets included on each page

Customized for Fuller Theological Seminary
----------------------------------------------
Version: Moodle 2.3.1
Customized: Logo, background image, layout/profilelogin, profile image size, activity icons, authorization CAS or oter users.
By: Thelma Polanco-Perez
