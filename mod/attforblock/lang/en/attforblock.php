<?PHP // $Id: attforblock.php,v 1.1.2.4 2009/04/12 17:50:11 dlnsk Exp $ 
      // attendanceblk.php - created with Moodle 1.5.3+ (2005060230)


$string['Aacronym'] = 'A';
$string['Afull'] = 'Absent';
$string['Eacronym'] = 'E';
$string['Efull'] = 'Excused';
$string['Lacronym'] = 'L';
$string['Lfull'] = 'Late';
$string['Pacronym'] = 'P';
$string['Pfull'] = 'Present';
$string['acronym'] = 'Acronym';
$string['add'] = 'Add';
$string['addmultiplesessions'] = 'Add multiple sessions';
$string['addsession'] = 'Add session';
$string['allcourses'] = 'All courses';
$string['alltaken'] = 'All taken';
$string['attendanceforthecourse'] = 'Attendance for the course';
$string['attendancegrade'] = 'Attendance grade';
$string['attendancenotstarted'] = 'Attendance has not started yet for this course';
$string['attendancepercent'] = 'Attendance percent';
$string['attendancereport'] = 'Attendance report';
$string['attendancesuccess'] = 'Attendance has been successfully taken';
$string['attendanceupdated'] = 'Attendance successfully updated';
$string['attforblock:changepreferences'] = 'Changing Preferences';
$string['attforblock:changeattendances'] = 'Changing Attendances';
$string['attforblock:export'] = 'Export Reports';
$string['attforblock:manageattendances'] = 'Manage Attendances';
$string['attforblock:takeattendances'] = 'Taking Attendances';
$string['attforblock:view'] = 'Viewing Attendances';
$string['attforblock:viewreports'] = 'Viewing Reports';
$string['attrecords'] = 'Attendances records';
$string['changeduration'] = 'Change duration';
$string['changesession'] = 'Change session';
$string['countofselected'] = 'Count of selected';
$string['createmultiplesessions'] = 'Create multiple sessions';
$string['createonesession'] = 'Create one session for the course';
$string['defaults'] = 'Defaults';
$string['delete'] = 'Delete';
$string['deletelogs'] = 'Delete attendance data';
$string['deleteselected'] = 'Delete selected';
$string['deletesession'] = 'Delete session';
$string['deletesessions'] = 'Delete all sessions';
$string['deletingsession'] = 'Deleting session for the course';
$string['deletingstatus'] = 'Deleting status for the course';
$string['description'] = 'Description';
$string['display'] = 'Display';
$string['downloadexcel'] = 'Download in Excel format';
$string['downloadooo'] = 'Download in OpenOffice format';
$string['downloadtext'] = 'Download in text format';
$string['duration'] = 'Duration';
$string['editsession'] = 'Edit Session';
$string['endofperiod'] = 'End of period';
$string['errorinaddingsession'] = 'Error in adding session';
$string['erroringeneratingsessions'] = 'Error in generating sessions ';
$string['hiddensessions'] = 'Hidden sessions';
$string['identifyby'] = 'Identify student by';
$string['includenottaken'] = 'Include not taken sessions';
$string['indetail'] = 'In detail...';
$string['moduledescription'] = 'You can add only one module Attendance per course.<br />Removal of this module will not entail removal of the data!';
$string['modulename'] = 'Attendance';
$string['modulenameplural'] = 'Attendances';
$string['months'] = 'Months';
$string['myvariables'] = 'My Variables';
$string['myvariables_help'] = 'Change settings here to modify the default attendance categories and points assigned to each category.';
$string['newdate'] = 'New date';
$string['newduration'] = 'New duration';
$string['noattforuser'] = 'No attendance records exist for the user';
$string['nodescription'] = 'Regular class session';
$string['noguest'] = 'Guest can\'t see attendance';
$string['noofdaysabsent'] = 'No of days absent';
$string['noofdaysexcused'] = 'No of days excused';
$string['noofdayslate'] = 'No of days late';
$string['noofdayspresent'] = 'No of days present';
$string['nosessiondayselected'] = 'No Session day selected';
$string['nosessionexists'] = 'No Session exists for this course';
$string['notfound'] = 'Attendance activity not found in this course!';
$string['olddate'] = 'Old date';
$string['period'] = 'Frequency';
$string['remarks'] = 'Remarks';
$string['report'] = 'Report';
$string['resetdescription'] = 'Remember that deleting attendance data will erase information from database. You can just hide older sessions having changed start date of course!';
$string['resetstatuses'] = 'Reset statuses to default';
$string['restoredefaults'] = 'Restore defaults';
$string['session'] = 'Session';
$string['sessionadded'] = 'Session successfully added';
$string['sessionalreadyexists'] = 'Session already exists for this date';
$string['sessiondate'] = 'Session Date';
$string['sessiondays'] = 'Session Days';
$string['sessiondeleted'] = 'Session successfully deleted';
$string['sessionenddate'] = 'Session end date';
$string['sessionexist'] = 'Session not added (already exists)!';
$string['sessions'] = 'Sessions';
$string['sessionscompleted'] = 'Sessions completed';
$string['sessionsgenerated'] = 'Sessions successfully generated';
$string['sessionstartdate'] = 'Session start date';
$string['sessionupdated'] = 'Session successfully updated';
$string['settings'] = 'Settings';
$string['showdefaults'] = 'Show defaults';
$string['status'] = 'Status';
$string['statusdeleted'] = 'Status deleted';
$string['strftimedmyhm'] = '%m/%d/%Y %H:%M'; // line added to allow multiple sessions in the same day
$string['strftimehm'] = '%H:%M'; //line added to allow display of time
$string['studentid'] = 'Student ID';
$string['takeattendance'] = 'Take attendance';
$string['thiscourse'] = 'This course';
$string['update'] = 'Update';
$string['variable'] = 'variable';
$string['variablesupdated'] = 'Variables successfully updated';
$string['versionforprinting'] = 'version for printing';
$string['week'] = 'week(s)';
$string['weeks'] = 'Weeks';
$string['youcantdo'] = 'You can\'t do anything';


$string['strftimedm'] = '%m/%d';
$string['strftimedmy'] = '%m/%d/%Y';
$string['strftimedmyw'] = '%m/%d/%y&nbsp;(%a)';
$string['strftimeshortdate'] = '%m/%d/%Y';

$string['hiddensessions_help'] = "If the session date is earlier than the course start date, the session will be hidden, and will not appear on this screen or in the gradebook. To make the session reappear, reset the course start date so it is earlier than the session date.<br /><br />
By setting a session date earlier than the course start date, you can hide unwanted sessions. This can be useful if you want to hide sessions from the gradebook but not delete them entirely (remember that only visible sessions appear in the gradebook).";

$string['createmultiplesessions_help'] = "This function allows you to create multiple sessions in one simple step.<br /><br />
<b>Session Start Date:</b> Select the start date of your course (the first day of class)<br />
<b>Session End Date:</b> Select the last day of class (the last day you want to take attendance).<br />
<b>Session Days:</b> Select the days of the week when your class will meet (for example, Monday/Wednesday/Friday).<br />
<b>Repeats every:</b> If your class will meet every week, select 1; if it will meet every other week, select 2; every 3rd week, select 3, etc.";

$string['export'] = 'Export Reports';
$string['export_help'] = "Use this option to select the format for the export data.";

$string['sessions_help'] = 'Attendance sessions are shown here. If no sessions appear, click on the "Add" tab to create new sessions.<br /><br />
Take attendance by clicking on the green icon under the "actions" menu (left side).<br /><br />
Edit each session by clicking on the middle "edit session" icon. After taking attendance you can edit the session by clicking on the description.<br /><br />
Delete the session entirely by clicking on the delete icon on the right side.';

$string['withselected'] = 'With selected';

$string['report_help'] = 'Modify attendance data by adjusting the display settings. Data may also be downloaded for offline viewing and analysis via the "Report" pulldown menu found at the bottom of the report screen.';
$string['display_help'] = 'Use this option to modify the way the attendance record is displayed. You can chose between "all taken" which will show all sessions, "weeks" which will show weekly averages, and "months" to display monthly averages for each student.';

$string['studentview'] = 'Student View';
$string['studentview_help'] = 'This screen allows you to view the attendance records for a single student. Use the "version for printing" link to print a copy without the tabs and headers displayed on the webpage.';

$string['nosessionsforexport'] = "No sessions were found.  Check your Export options.";
$string['pluginadministration'] = 'Attendance administration';
$string['pluginname'] = $string['modulename'];

$string['alreadyenabled'] = "Attendance was already enabled for this course. Only one Attendance activity link is allowed per course.";
?>
