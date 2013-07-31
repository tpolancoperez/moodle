(a very alpha version - not for production sites yet!)

***BE SURE TO DELETE ANY EXISTING INSTALLATION OF THE QUICKMAILSMS BLOCK BEFORE UPGRADING***
Go to Blocks->mangage blocks->Delete, next to quickmailsms

Installation is like any other block - upload it to moodle/blocks and go to Admin->Notifications.  Basically, it works just like the old quickmailsms block:  You create three custom profile fields with the following specifications:

1.

type - checkbox
shortname - opt
name - whatever you feel appropriate
required - yes
display on signup - yes
Who is the field visible to? - visible to user
checked by default - No

2.

type - select
shortname - mobileprovider
name - whatever you feel appropriate
required - no
display on signup - yes
Who is the field visible to? - visible to user
menu options -
Please select one...
AT&T ~@txt.att.net~
Verizon ~@vtext.com~
T-Mobile ~@tmomail.net~
Sprint PCS ~@messaging.sprintpcs.com~
Virgin Mobile ~@vmobl.com~
US Cellular ~@email.uscc.net~
Nextel ~@messaging.nextel.com~
Boost ~@myboostmobile.com~
Alltel ~@message.alltel.com~

Default value - Please select one...

3

type - text
shortname - mobilephone
name - whatever you feel appropriate
required - no
display on signup - yes
Who is the field visible to? - visible to user
Display size - 10
Maximum Length - 10
