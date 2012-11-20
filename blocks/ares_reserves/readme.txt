***********************************************
***********************************************
****                                       ****
****    Ares Reserves Moodle Block v2.0    ****
****    Atlas Systems, Inc.                ****
****    April 14, 2011                     ****
****                                       ****
***********************************************
***********************************************

The following text details procedures for the installation and configuration of the Ares Reserves block developed by Atlas Systems, Inc. for the Moodle course manage system. This is version 2.0 of the 

This block allows Ares owners that are using Moodle to integrate the Moodle system with Ares.


== Installation ==
1) Copy the ares_reserve folder into your moodle/blocks directory.
2) Login to Moodle as an administrator and open http://yourmoodlesite/admin/index.php, replacing yourmoodlesite with the location of your Moodle site.
3) After Moodle verifies the installation, click continue and proceed to the ares global customization screen by clicking Modules/Blocks/Ares Reserves.
4) Enter a user agent (see the User Agents section for more information), web service address, web site address, and optionally a default display format (see the Item Formats section for more information). The service and site addresses MUST point to your Ares Web Service and Ares Web Site folders.  For example, if my areswebservice.dll is located at http://localhost/ares/ws/areswebservice.dll, then the value for Ares Web Service Address should be http://localhost/ares/ws.
5) Click save changes.

Now any instructor can login to Moodle, select their course, and add the Ares Reserves block by turning on editing and using the add block interface in Moodle.


== Customization ==

There are several major ways in which the Ares Reserves block can be customized.  The first is via Global Configuration.  This was done during installation when the user agent, web service and web site address were set.  These global configuration settings affect all instances of the Ares block.  

In addition to the global configuration settings, there are instance configuration settings.  These are accessed by adding the block to a course and clicking the edit icon while editing is enabled in Moodle.  The following settings can be customized for each instance:

	1) Semester - This setting determines what semester is used to create the course in ares.  This is only necessary if the course does not already exist and you want the block to create it for you.

	2) Student Item Display Format - This controls the format that is used to display Reserve Items when students view the course (see Item Formats below).

	3) Student Item Display Mode - This controls the mode that is used to display Reserve Items to students.  The available options are:
		A) None - Hides reserve items completely.  This is best used if your course has a large number of reserve items attached to it that would end up causing the ares block display to become too large.
		B) Text - Displays the items with no link to the item in Ares.
		C) Link - Displays the items as links which will take the user to the item description page in the ares web interface.

	4) Student Course Display Mode - This controls whether or not students are given a link to the course in the ares web interface.

	5) Teacher Item Display Format - This does the same thing as the student version, but affects the teacher's view.

	6) Teacher Item Display Mode - This does the same thing as the student version, but affects the teacher's view.
	
	7) Teacher Course Display Mode - This does the same thing as the student version, but affects the teacher's view.

The third form of customization is via languages.  Almost all of the static text used in the Ares block can be customized via language format files. 

English language text can be changed in the lang\en\block_ares_reserves.php file. Only the strings to the right of the = on each line should be changed.  You should also take care not to accidentally delete the single quotes around the strings or use single quotes in your modified string with escaping them using the backslahs (\) character.

The final piece of customization that can be done to the Ares block is adding or removing item formats.  The available item formats are controlled by php code in the ItemDisplayFormats.php file.  By modifying this file according to the guidelines outlined in the "Item Formats" section below, you can easily add your own item formats or remove/modify the formats that come with the block.


== User Agents ==
In order to enhance security without the nuisance of having to replicate passwords from moodle to ares, the ares web service uses user agents to identify the moodle
block when it attempts to access the web service.  In order for this to work, you must pick a user agent string you wish to use and add that string both to moodle 
and your Ares database.
	Adding the user agent to Moodle:
	Open the Ares Reserves block global configuration by logging in to Moodle as an administrator, clicking modules/blocks/Ares Reserves, and typing the user agent string into the User Agent field.  This is CASE SENSITIVE.
	Adding the user agent to the Ares database:
	During installing of the Ares web service a new table should have been added to your Ares database named WebServiceAgents.  This table has only one field, Agent. Simply add a row containing the user agent string you wish to use with the Ares Reserves block in Moodle.


== Item Formats ==
By default, the Ares block includes a few possible reserve item formats.  This can be modified fairly easily with only a very limited grasp of PHP. To add an item format, navigate to and open your moodle/blocks/ares_reserves/ItemDisplayFormats.php file.  Once this is open you will see two php objects: a function named FormatItemText and an array named $formatTypes.  Both of these objects require minor modifications for each new display format. The first and easiest change required is to the $formatTypes array.  Entries in this array come in pairs in the format of "formatName" => "formatDescription"
and are delimited by commas.  All you have to do is add a new entry in this array using the desired formatname and a short description or example of the format. Please keep in mind that the description is what is displayed in the format configuration drop downs. After adding your format type entry to the $formatTypes array, you must add a case for your format type in the switch structure contained in the FormatItemText function. The string for this new case must match the name of your format type as entered in the $formatTypes array. Once you have added your case block, you can add whatever
code is necessary to create the desired format to your newly created case block.  For the most simplistic formats, the following example can be used with few changes:
	return "$item->Title - $item->Author";
This produces a reserve item entry that looks like this: Scientific American - A Study of Smoking Affects on the Body. The key to creating item formats formats is the $item-> part of that example.  $item is a variable provided by the block that contains pieces of of information about the reserve item currently being formatted for display.  Using the syntax $item->FieldName allows you to reference fields from the item.  A complete list of these fields is listed below.  For more complicated formats (or for value checking, like the type that is done in the included formats, if statements and other php constructs 
that are not covered here may be necessary.

Item Format Fields:
	ItemID
	Username
	ClassId
	PickupLocation
	ProcessingLocation
	CurrentStatus
	CurrentStatusDate
	ItemType
	DigitalItem
	Location
	ShelfLocation
	AresDocument
	InstructorProvided
	CopyrightRequired
	CopyrightObtained
	Active
	ActiveDate
	InactiveDate
	CallNumber
	ReasonForCancellation
	Proxy
	Title
	Author
	Publisher
	PublicationLocation
	PublicationDate
	Edition
	ISXN
	EspNumber
	CitedIn
	Doi
	ArticleTitle
	Volume
	Issue
	JournalYear
	JournalMonth
	Pages
	DocumentType
	ItemFormat
	Description
	CccNumer
	LoanPeriod
	DirectLink