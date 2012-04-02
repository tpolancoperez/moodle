-------------------------------------------------------------------------------
ELLUMINATE LIVE MODULE AND BLOCK.
-------------------------------------------------------------------------------
2007.04.23
The modification contained herein was provided by Open Knowledge Technologies
(http://www.oktech.ca/) and Remote Learner (www.remote-learner.net) in
association with Elluminate.

Contributors:
Justin Filip (jfilip@oktech.ca)
Mike Churchward (mike@oktech.ca)

-------------------------------------------------------------------------------
WIKI DOCUMNTATION:

For the latest updated documentation, please refer to the MoodleDocs wiki page
http://docs.moodle.org/en/ElluminateLive_module

-------------------------------------------------------------------------------
ELLUMINATE LIVE! INSTALLATION:

To setup the Moodle adapter you must copy the files supplied in the
elluminate_addons/ directory into your Elluminate Live! server installation.

Deployment instructions of the new Moodle adapter:
1.  Stop the Elluminate Live! Manager service/daemon
2.  Open the moodleadapterconfiguration.xml file in an XML editor / text
    editor
3.  Copy the contents of the file into the clipboard
4.  Open the configuration.xml file (located in the
    ElluminateLive/manager/tomcat/webapps/ROOT/WEB-INF/resources folder)
5.  Search for the following <adapters>
6.  Insert the clipboard buffer after the above line.
7.  Save the configuration file.
8.  Open ELMMoodle.tmpl file.
9.  Replace the text [IPADDRESS] with the appropriate IP address or dns name
    of the Elluminate Live! Manager server along with the port (i.e.
    elm.elluminate.com:8080)
10. Save the file into the following directory:
    ElluminateLive/server7_X/sessions (where X is your specific server
    revision you're running: i.e. 'server7_0' or 'server7_2')
11. Copy the supplied JAR file (blackboard2_0_elm.jar) into your manager
    (in the ElluminateLive/manager/tomcat/webapps/ROOT/WEB-INF/lib folder)
12. Restart the Elluminate Live Manager service/daemon.

-------------------------------------------------------------------------------
MOODLE INSTALLATION:

To install and use, unzip this file into your Moodle root directory making sure
that you 'use folder names'. This will create the following new block and 
module:

/blocks/elluminatelive
/mod/elluminatelive

If you are using a theme with custom icons you have to copy the activity
module's icon into your theme.  To do so create the directory

/theme/THEME-NAME/pix/mod/elluminatelive/

and copy the icon.gif from the /mod/elluminatelive/ directory there.

NOTE: replace THEME-NAME with the name of the directory where you theme is.

Visit your admin section to complete installation. 

-------------------------------------------------------------------------------
FUNCTION:

This activity allows you to create and manage Elluminate Live meetings.  These 
meetings will be created for you on the configured Elluminate Live! server. You 
can add moderators and participants to the meetings you create and also choose 
to send out notifications to participating users reminding them of the upcoming 
meeting.

Once you have installed the module, visit the module configuration screen to
add the server information for your Elluminate Live! server.

SUPPORT:

In Canada, contact info@oktech.ca.
In USA and other countries, contact info@remote-learner.net.