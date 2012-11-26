<?php // $Id: tracker.php,v 1.2 2012-08-12 21:43:55 vf Exp $ 
      // tracker.php - created with Moodle 2.2

$string['pluginname'] = 'Ticket Tracker/User support';
$string['pluginadministration'] = 'Tracker administration';

// Capabilities
$string['tracker:addinstance'] = 'Add a tracker';
$string['tracker:canbecced'] = 'Can be choosen for cc';
$string['tracker:comment'] = 'Comment issues';
$string['tracker:configure'] = 'Configure tracker options';
$string['tracker:configurenetwork'] = 'Configure network features';
$string['tracker:develop'] = 'Be choosen to resolve tickets';
$string['tracker:manage'] = 'Manage issues';
$string['tracker:managepriority'] = 'Manage priority of entries';
$string['tracker:managewatches'] = 'Manage watches on ticket';
$string['tracker:report'] = 'Report tickets';
$string['tracker:resolve'] = 'Resolve tickets';
$string['tracker:seeissues'] = 'See issue content';
$string['tracker:shareelements'] = 'Share elements at site level';
$string['tracker:viewallissues'] = 'See all tickets';
$string['tracker:viewpriority'] = 'View priority of my owned tickets';
$string['tracker:viewreports'] = 'View issue work reports';


$string['AND'] = 'AND';
$string['IN'] = 'IN';
$string['alltracks'] = 'Watch my work in all trackers';
$string['abandonned'] = 'Abandonned';
$string['action'] = 'Action';
$string['activeplural'] = 'Actives';
$string['resolvedplural'] = 'Resolved';
$string['addacomment'] = 'Add a comment';
$string['addanoption'] = 'Add an option';
$string['addaquerytomemo'] = 'Add this search query to "my queries"';
$string['addawatcher'] = 'Add a watcher';
$string['addtothetracker'] = 'Add to this tracker';
$string['administration'] = 'Administration';
$string['administrators'] = 'Administrators';
$string['any'] = 'All';
$string['askraise'] = 'Ask resolvers to raise priority';
$string['assignedto'] = 'Assigned to';
$string['assignee'] = 'Assignee';
$string['attributes'] = 'Attributes';
$string['browse'] = 'Browse';
$string['browser'] = 'Browser';
$string['build'] = 'Version';
$string['by'] = '<i>assigned by</i>';
$string['cascade'] = 'Send upper level';
$string['cascadedticket'] = 'Transferred from: ';
$string['cced'] = 'Cced';
$string['ccs'] = 'Ccs';
$string['checkbox'] = 'Checkbox'; // @DYNA
$string['checkboxhoriz'] = 'Checkbox horizontal'; // @DYNA
$string['chooselocal'] = 'Choose a local tracker as parent';
$string['chooseremote'] = 'Choose a remote host';
$string['chooseremoteparent'] = 'Choose a remote instance';
$string['clearsearch'] = 'Clear search criteria';
$string['comment'] = 'Comment';
$string['comments'] = 'Comments';
$string['component'] = 'Component';
$string['createnewelement'] = 'Create a new element';
$string['createdinmonth'] = 'Created in current month';
$string['currentbinding'] = 'Current cascade';
$string['countbyreporter'] = 'By reporter';
$string['database'] = 'Database';
$string['datereported'] = 'Report date';
$string['defaultassignee'] = 'Default assignee';
$string['deleteattachedfile'] = 'Delete attachement';
$string['dependancies'] = 'Dependencies';
$string['dependson'] = 'Depends on ';
$string['descriptionisempty'] = 'Description is empty';
$string['doaddelementcheckbox'] = 'Add a checkbox element'; // @DYNA
$string['doaddelementcheckboxhoriz'] = 'Add a checkbox element'; // @DYNA
$string['doaddelementdropdown'] = 'Add a dropdown element'; // @DYNA
$string['doaddelementfile'] = 'Add an attachment file element'; // @DYNA
$string['doaddelementradio'] = 'Add a radio element'; // @DYNA
$string['doaddelementradiohoriz'] = 'Add a radio element'; // @DYNA
$string['doaddelementtext'] = 'Add a text field'; // @DYNA
$string['doaddelementtextarea'] = 'Add a text area'; // @DYNA
$string['doupdateelementcheckbox'] = 'Update a checkbox element'; // @DYNA
$string['doupdateelementcheckboxhoriz'] = 'Update a checkbox element'; // @DYNA
$string['doupdateelementdropdown'] = 'Update a dropdown element';// @DYNA
$string['doupdateelementfile'] = 'Update a attachment file element'; // @DYNA
$string['doupdateelementradio'] = 'Update a radio button element'; // @DYNA
$string['doupdateelementradiohoriz'] = 'Update a radio button element'; // @DYNA
$string['doupdateelementtext'] = 'Update a text field'; // @DYNA
$string['doupdateelementtextarea'] = 'Update a text area'; // @DYNA
$string['dropdown'] = 'Dropdown';
$string['editoptions'] = 'Update options';
$string['editproperties'] = 'Update properties';
$string['editquery'] = 'Change a stored query';
$string['editwatch'] = 'Change a cc registering';
$string['elements'] = 'Available elements';
$string['elementsused'] = 'Used elements';
$string['elucidationratio'] = 'Elucidation ratio';
$string['emailoptions'] = 'Mail options';
$string['emergency'] = 'Urgent query';
$string['emptydefinition'] = 'Target tracker has no definition.';
$string['enablecomments'] = 'Allow comments';
$string['enablecomments_help'] = 'When this option is enabled, readers of issue records can add comments in the tracker.';
$string['errorcoursemisconfigured'] = 'Course is misconfigured';
$string['errorcoursemodid'] = 'Course Module ID was incorrect';
$string['errorfindingaction'] = 'Error:  Cannot find action: {$a}';
$string['errormoduleincorrect'] = 'Course module is incorrect';
$string['errornoaccessallissues'] = 'You do not have access to view all issues.';
$string['errornoaccessissue'] = 'You do not have access to view this issue.';
$string['errornoeditissue'] = 'You do not have access to edit this issue.';
$string['errorbadlistformat'] = 'Only numbers (or a list of numbers seperated by a comma (",") allowed in the issue number field';
$string['errorcookie'] = 'Failed to set cookie: {$a} .';
$string['errorremote'] = 'Remote error: {$a}';
$string['errorupdateelement'] = 'Could not update element';
$string['errorcanotaddelementtouse'] = 'Cannot add element to list of elements to use for this tracker';
$string['erroraddissueattribute'] = 'Could not submit issue(s) attribute(s). Case {$a} ';
$string['errorelementinuse'] = 'Element already in use';
$string['errorrecordissue'] = 'Could not submit issue'; 
$string['errorcannotdeleteelementtouse'] = 'Cannot delete element from list of elements to use for this tracker';
$string['errorunabletosabequery'] = 'Unable to save query as query';
$string['errorunabletosavequeryid'] = 'Unable to update query id {$a}';
$string['errorannotdeletequeryid'] = 'Cannot delete query id: {$a]';
$string['errorcannotdeletearboncopyforuser'] = 'Cannot delete carbon copy {$a->issue} for user : {$a->userid}';
$string['errorannotdeletecarboncopies'] = 'Cannot delete carbon copies for user : {$a}';
$string['errorcannoteditwatch'] = 'Cannot edit this watch';
$string['errorcannotclearelementsforissue'] = 'Could not clear elements for issue {$a}';
$string['evolutionbymonth'] = 'Issue state evolution';
$string['file'] = 'Attached file';
$string['follow'] = 'Follow';
$string['generaltrend'] = 'Trend';
$string['hassolution'] = 'A solution is published for this issue';
$string['hideccs'] = 'Hide watchers';
$string['hidecomments'] = 'Hide comments';
$string['hidedependancies'] = 'Hide dependancies';
$string['hidehistory'] = 'Hide history';
$string['history'] = 'Assignees';
$string['iamadeveloper'] = 'I\'m a developer';
$string['iamnotadeveloper'] = 'I\'m not a developer';
$string['icanmanage'] = 'I can manage issue entries';
$string['icannotmanage'] = 'I cannot manage';
$string['icannotreport'] = 'I cannot report';
$string['icannotresolve'] = 'I\'m not a resolver';
$string['icanreport'] = 'I can report';
$string['icanresolve'] = 'I am assigned on some tickets';
$string['id'] = 'Identifier';
$string['inworkinmonth'] = 'Still in work';
$string['intest'] = 'Testing';
$string['intro'] = 'Description';
$string['issueid'] = 'Ticket';
$string['issuename'] = 'Ticket label ';
$string['issuenumber'] = 'Ticket';
$string['issues'] = 'ticket records';
$string['knownelements'] = 'Known tracker form elements';
$string['listissues'] = 'List view';
$string['local'] = 'Local';
$string['lowerpriority'] = 'Lower priority';
$string['lowertobottom'] = 'Lower to basement';
$string['manageelements'] = 'Manage elements';
$string['managenetwork'] = 'Cascade and network setup';
$string['manager'] = 'Manager';
$string['me'] = 'My profile';
$string['mode_bugtracker'] = 'Team bug tracker';
$string['mode_ticketting'] = 'User support ticketting';
$string['modulename'] = 'User support - Tracker';
$string['modulename_help'] = 'The Tracker activity allows tracking tickets for help, bug report, or also trackable activities in a course.

The activity allows creating the tracking form with attributes elements from a list of configurable elements. Some elements can be shared at site
level to be reused in other trackers.

the ticket (or task) can be assigned for work to another user.

The tracked ticket is a statefull ticket that sends state change notifications to any follower that has enabled notifications. A user can choose which state changes he tracks usually.

Tickets can be chained in dependancy, so it may be easy to follow a cause/consequence ticket sequence.

History of changes are tracked for each ticket.

Ticket tracker can be cascaded locally or through MNET allowing a ticket manager to send a ticket to a remote (higher level) ticket collector.

Trackers can now be chained so that ticket can be moved between trackers. 
';
$string['modulenameplural'] = 'User support - trackers';
$string['month'] = 'Month';
$string['myassignees'] = 'Resolver I assigned';
$string['myissues'] = 'Tickets I resolve';
$string['mypreferences'] = 'My preferences';
$string['myprofile'] = 'My profile';
$string['myqueries'] = 'My queries';
$string['mytickets'] = 'My tickets';
$string['mywatches'] = 'My watches';
$string['mywork'] = 'My work';
$string['name'] = 'Name';
$string['namecannotbeblank'] = 'Name cannot be empty';
$string['newissue'] = 'New ticket';
$string['noassignees'] = 'No assignee';
$string['nocomments'] = 'No comments';
$string['nodevelopers'] = 'No developpers';
$string['nodata'] = 'No data to show.';
$string['noelements'] = 'No element';
$string['noelementscreated'] = 'No element created';
$string['nofile'] = 'No uploaded file';
$string['noissuesreported'] = 'No ticket here';
$string['noissuesresolved'] = 'No resolved ticket';
$string['nolocalcandidate'] = 'No local candidate for cascading';
$string['nooptions'] = 'No option';
$string['nomnet'] = 'Moodle network seems disabled';
$string['noqueryssaved'] = 'No stored query';
$string['noremotehosts'] = 'No network host available';
$string['noremotetrackers'] = 'No remote tracker available';
$string['noreporters'] = 'No reporters, there are probably no issues entered here.';
$string['noresolvers'] = 'No resolvers';
$string['noresolvingissue'] = 'No ticket assigned';
$string['notickets'] = 'No owned tickets.';
$string['noticketsorassignation'] = 'No tickets or assignations';
$string['notifications'] = 'Notifications';
$string['notifications_help'] = 'This parameter enables or disables mail notifications from the Tracker. If enabled, some events or state changes within the tracker will trigger mail messages to the concerned users.';
$string['notrackeradmins'] = 'No admins';
$string['nowatches'] = 'No watches';
$string['numberofissues'] = 'Ticket count';
$string['observers'] = 'Observers';
$string['open'] = 'Open';
$string['option'] = 'Option ';
$string['optionisused'] = 'This options id already in use for this element.';
$string['order'] = 'Order';
$string['pages'] = 'Pages';
$string['posted'] = 'Posted';
$string['potentialresolvers'] = 'Potential resolvers';
$string['preferences'] = 'Preferences';
$string['prefsnote'] = 'Preferences setups which default notifications you may receive when creating a new entry or when you register a watch for an existing issue';
$string['priorityid'] = 'Priority';
$string['priority'] = 'Attributed Priority';
$string['profile'] = 'User settings';
$string['published'] = 'Published';
$string['queries'] = 'Queries';
$string['query'] = 'Query';
$string['queryname'] = 'Query label';
$string['radio'] = 'Radio buttons'; // @DYNA
$string['radiohoriz'] = 'Horizontal radio buttons'; // @DYNA
$string['raisepriority'] = 'Raise priority';
$string['raiserequestcaption'] = 'Priority raising request for a ticket';
$string['raiserequesttitle'] = 'Ask for raising priority';
$string['raisetotop'] = 'Raise to ceiling';
$string['reason'] = 'Reason';
$string['register'] = 'Watch this ticket';
$string['reportanissue'] = 'Post a ticket';
$string['reportedby'] = 'Reported by';
$string['reporter'] = 'Reporter';
$string['resolution'] = 'Solution';
$string['resolved'] = 'Resolved';
$string['resolvedplural'] = 'Resolved';
$string['resolvedplural2'] = 'Resolved';
$string['resolver'] = 'My issues';
$string['resolvers'] = 'Resolvers';
$string['resolving'] = 'Resolving';
$string['runninginmonth'] = 'Running in current month';
$string['saveasquery'] = 'Save a query';
$string['savequery'] = 'Save the query';
$string['search'] = 'Search';
$string['searchresults'] = 'Search results';
$string['searchwiththat'] = 'Launch this query again';
$string['selectparent'] = 'Parent selection';
$string['sendrequest'] = 'Send request';
$string['setwhenopens'] = 'Don\'t advise me when opens';
$string['setwhenresolves'] = 'Don\'t advise me when resolves';
$string['setwhenpublished'] = 'Don\'t advise me when solution is published';
$string['setwhentesting'] = 'Don\'t advise me when a solution is tested';
$string['setwhenthrown'] = 'Don\'t advise me when is abandonned';
$string['setwhenwaits'] = 'Don\'t advise me when waits';
$string['setwhenworks'] = 'Don\'t advise me when on work';
$string['setoncomment'] = 'Send me the coments';
$string['sharethiselement'] = 'Turn this element sitewide';
$string['sharing'] = 'Sharing';
$string['showccs'] = 'Show watchers';
$string['showcomments'] = 'Show comments';
$string['showdependancies'] = 'Show dependancies';
$string['showhistory'] = 'Show history';
$string['site'] = 'Site';
$string['solution'] = 'Solution';
$string['sortorder'] = 'Order';
$string['standalone'] = 'Standalone tracker (top level support).';
$string['status'] = 'Status';
$string['submission'] = 'A new ticket is reported in tracker [{$a]}';
$string['submitbug'] = 'Submit the ticket';
$string['subtrackers'] = 'Subtrackers';
$string['sum_opened'] = 'Opened';
$string['sum_posted'] = 'Waiting';
$string['sum_reported'] = 'Reported';
$string['sum_resolved'] = 'Solved';
$string['summary'] = 'Summary';
$string['supportmode'] = 'Support mode';
$string['supportmode_help'] = 'Support mode has effect on who have access to which ticket scope';
$string['testing'] = 'Being tested';
$string['text'] = 'Textfield'; // @DYNA
$string['textarea'] = 'Textarea'; // @DYNA
$string['thanks'] = 'Thanks to contributing to the constant enhancement of this service.';
$string['ticketprefix'] = 'Ticket prefix';
$string['tickets'] = 'Tickets';
$string['tracker-levelaccess'] = 'My capabilities in this tracker';
$string['tracker_description'] = '<p>When publishing this service, you allow trackers from {$a} to cascade the support tickets to a local tracker.</p>
<ul><li><i>Depends on</i>: You have to suscribe {$a} to this service.</li></ul>
<p>Suscribing to this service allows local trackers to send support tickets to some tracker in {$a}.</p>
<ul><li><i>Depends on</i>: You have to publish this service on {$a}.</li></ul>';
$string['tracker_name'] = 'Tracker module services';
$string['tracker_service_name'] = 'Tracker module services';
$string['trackerelements'] = 'Tracker\'s definition';
$string['trackereventchanged'] = 'Issue state change in tracker [{$a]}';
$string['trackerhost'] = 'Parent host for tracker';
$string['trackername'] = 'Tracker name';
$string['transfer'] = 'Transfered';
$string['transfered'] = 'Transfered';
$string['transferservice'] = 'Support ticket cascading';
$string['turneditingoff'] = 'Turn editing off';
$string['turneditingon'] = 'Turn editing on';
$string['type'] = 'Type';
$string['unassigned'] = 'Unassigned' ;
$string['unbind'] = 'Unbind cascade';
$string['unmatchingelements'] = 'Both tracker definition do not match. This may result in unexpected behaviour when cascading support tickets.';
$string['unregisterall'] = 'Unregister from all' ;
$string['unsetoncomment'] = 'Advise me when posting comments';
$string['unsetwhenopens'] = 'Advise me when opens';
$string['unsetwhenresolves'] = 'Advise me when resolves';
$string['unsetwhenpublished'] = 'Advise me when solution is published';
$string['unsetwhentesting'] = 'Advise me when a solution is tested';
$string['unsetwhenthrown'] = 'Advise me when is thrown';
$string['unsetwhenwaits'] = 'Advise me when waits';
$string['unsetwhenworks'] = 'Advise me when got working';
$string['urgentraiserequestcaption'] = 'A user has requested an urgent priority demand';
$string['urgentsignal'] = 'URGENT QUERY';
$string['view'] = 'Views';
$string['vieworiginal'] = 'See original';
$string['voter'] = 'Vote';
$string['validated'] = 'Validated';
$string['waiting'] = 'Waiting';
$string['watches'] = 'Watches';
$string['youneedanaccount'] = 'You need an authorized account here to report a ticket';
$string['statehistory'] = 'States';

$string['reports'] = 'Reports';
$string['count'] = 'Count';
$string['countbystate'] = 'Report by status';
$string['countbymonth'] = 'By monthly creation report';
$string['countbyassignee'] = 'By assignee';
$string['evolution'] = 'Trends';
$string['print'] = 'Print';
$string['errorinvalidelementID'] = 'Element ID is not set';
$string['errordbupdate'] = 'Could not update element';
$string['errorcannotaddelementtouse'] = 'Cannot add element to list of elements to use for this tracker';
$string['erroralreadyinuse'] = 'Element already in use';
$string['errorcannotdeleteelement'] = 'Cannot delete element from list of elements to use for this tracker';
$string['errorcannotsetparent'] = 'Cannot set parent in this tracker';
$string['errorcannothideelement'] = 'Cannot hide element from form for this tracker';
$string['errorcannotshowelement'] = 'Cannot show element in form for this tracker';
$string['errorcannotunbindparent'] = 'Cannot unbind parent of this tracker';
$string['errorcannotujpdateoptionbecauseused'] = 'Cannot update the element option because it is currently being used as a attribute for an issue';
$string['errorcannotdeleteoption'] = 'Error trying to delete element option';
$string['errorcannotviewelementoption'] = 'Cannot view element options';
$string['errorcannotcreateelementoption'] = 'Could not create element option';
$string['errorcannotupdateelement'] = 'Could not update element';
$string['errorelementdoesnotexist'] = 'Element does not exist';
$string['errorinvalidelementid'] = 'Invalid element. Cannot edit element id';
$string['options'] = 'Options';
$string['errorcannotsubmitticket'] = 'Error registering new ticket';
$string['errorcannotlogoldownership'] = 'Could not log old ownership';
$string['errorcannotupdatetrackerissue'] = 'Could not update tracker issue';
$string['errorcannotdeleteolddependancy'] = 'Could not delete old dependancies';
$string['errorcannotwritedependancy'] = 'Could not write dependancy record';
$string['errorcannotwritecomment'] = 'Error writing comment';
$string['errorcannotdeletecc'] = 'Cannot delete carbon copy';
$string['errorcannotupdateissuecascade'] = 'Could not update issue for cascade';
$string['errorremote'] = 'Error on remote side<br/> {$a} ';
$string['errorremotesendingcascade'] = 'Error on sending cascade :<br/> {$a}';
$string['errorcannotsaveprefs'] = 'Could not insert preference record';
$string['errorcannotupdateprefs'] = 'Could not update preference record';
$string['errorcannotupdatewatcher'] = 'Could not update watcher';

// help strings

$string['elements_help'] = '<p>
Issue submission form can be customized by adding form elements. The "summary", "description", et "reportedby" fields are as default, but any additional qualifier can be added to the issue description.
</p>
<p>
Elements that can be added are "form elements" i.e. standard form widgets that can represent any qualifier or open description, such as radio buttons, checkboxes, dropdown, textfields or textareas.
</p>
<p>Elements are set using the following properties:
</p>
<h3>A name</h3>
<p>This name is the element identifier, technically speaking. It must be a token using alphanumeric chars and the _ character, without spaces or non printable chars. The name will not appear on the user interface.</p>
<h3>Description</h3>
<p>The description is used when the element has to be identified on the user interface.</p>
<h3>Options</h3>
<p>Some elements have a finite list of option values.
</p>
<p>Options are added after the element is created.</p>
<p>Fieldtexts and textareas do not have any options.</p> ';

$string['options_help'] = '<h3>A name</h3>
<p>The name identifies the option value. It should be a token using alphanumeric chars and _ without spaces or non printable chars.</p>
<h3>Description</h3>
<p>The description is the viewable counterpart of the option code.</p>

<h3>Option ordering</h3>

<p>You may define the order in which the options appear in the lists.</p>

<p>Textfield and textarea elements do not have any options.</p> ';


$string['ticketprefix_help'] = '## Task Tracking / User support

### Ticket Prefix

This parameter allows defining a fixed prefix thatt will be prepended to the issue numerical identifier. This should allow better identification of a issue entry in documents, forum posts...';

$string['urgentquery_help'] = '## Task Tracking / User support

### Urgent query

Checking this checkbox will send a signal to developpers or tickets managers so your issue can be considered more quickly.

Please consider although that there is no automated process using directly this variable. The acceptation of the emergency will be depending on how urgent support administrators have considered your demand.';

$string['mods_help'] = '
This module provides an amdinistrator or techical operator a way to collect locally issues on a Moodle implementation. It may be used mainly as an overall system tool for Moodle administration and support to end users, but also can be used as any other module for student projects. It can be instanciated several times within a course space. 
The issue description form is fully customisable. The tracker administrator can add as many description he needs by adding form elements. The integrated search engine do ajust itself to this customization.';

$string['defaultassignee_help'] = '

You might require incoming tickets are preassigned to one of the available resolvers.';

?>


