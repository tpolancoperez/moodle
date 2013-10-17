<?php

$string['pluginname'] = 'QuickmailSMS';
$string['quickmailsms:cansend'] = "Allows users to send text message through QuickmailSMS";
$string['quickmailsms:canconfig'] = "Allows users to configure QuickmailSMS instance.";
$string['quickmailsms:canimpersonate'] = "Allows users to log in as other users and view history.";
$string['quickmailsms:allowalternate'] = "Allows users to add an alternate email for courses.";
$string['quickmailsms:addinstance'] = "Allows users to add an instance of the quickmailSMS block to a course.";
$string['quickmailsms:myaddinstance'] = "Allows users to add an instance of the quickmailSMS block to their myMoodle page.";
$string['backup_history'] = 'Include QuickmailSMS History';
$string['restore_history'] = 'Restore QuickmailSMS History';
$string['overwrite_history'] = 'Overwrite QuickmailSMS History';
$string['alternate'] = 'Alternate Mobile Numbers';
$string['composenew'] = 'Compose New Text';
$string['email'] = 'Text';
$string['drafts'] = 'View Drafts';
$string['history'] = 'View History';
$string['log'] = $string['history'];
$string['from'] = 'From';
$string['selected'] = 'Selected Recipients';
$string['add_button'] = 'Add';
$string['remove_button'] = 'Remove';
$string['add_all'] = 'Add All';
$string['remove_all'] = 'Remove All';
$string['role_filter'] = 'Role Filter';
$string['no_filter'] = 'No filter';
$string['potential_users'] = 'Potential Recipents';
$string['potential_sections'] = 'Potential Sections';
$string['no_section'] = 'Not in a section';
$string['all_sections'] = 'All Sections';
$string['attachment'] = 'Attachment(s)';
$string['subject'] = 'Subject';
$string['message'] = 'Text <u>only</u> Message<br />(all formatting will be erased)';
$string['send_email'] = 'Send Text';
$string['save_draft'] = 'Save Draft';
$string['actions'] = 'Actions';
$string['signature'] = 'Signatures';
$string['delete_confirm'] = 'Are you sure you want to delete message with the following details: {$a}';
$string['title'] = 'Title';
$string['sig'] ='Signature';
$string['default_flag'] = 'Default';
$string['config'] = 'Configuration';
$string['receipt'] = 'Receive a copy';
$string['receipt_help'] = 'Receive a copy of the text being sent';

$string['no_alternates'] = 'No alternate texts found for {$a->fullname}. Continue to make one.';

$string['select_users'] = 'Select Users ...';
$string['select_groups'] = 'Select Sections ...';

// Config form strings
$string['allowstudents'] = 'Allow students to use QuickmailSMS';
$string['select_roles'] = 'Roles to filter by';
$string['reset'] = 'Restore System Defaults';
$string['no_agreement'] = '{$a->firstname} {$a->lastname} did not agree to text messaging.';

$string['no_type'] = '{$a} is not in the acceptable type viewer. Please use the applciation correctly.';
$string['no_email'] = 'Could not text {$a->firstname} {$a->lastname}.';
$string['no_log'] = 'You have no text history yet.';
$string['no_drafts'] = 'You have no text drafts.';
$string['no_subject'] = 'You must have a subject';
$string['no_course'] = 'Invalid Course with id of {$a}';
$string['no_permission'] = 'You do not have permission to send texts with QuickmailSMS.';
$string['no_users'] = 'There are no users you are capable of texting.';
$string['no_selected'] = 'You must select some users for texting.';
$string['not_valid'] = 'This is not a valid text log viewer type: {$a}';
$string['not_valid_user'] = 'You can not view other text history.';
$string['not_valid_action'] = 'You must provide a valid action: {$a}';
$string['not_valid_typeid'] = 'You must provide a valid mobile number for {$a}';
$string['delete_failed'] = 'Failed to delete text';
$string['required'] = 'Please fill in the required fields.';
$string['prepend_class'] = 'Prepend Course name';
$string['prepend_class_desc'] = 'Prepend the course shortname to the subject of the text.';
$string['courselayout'] = 'Course Layout';
$string['courselayout_desc'] = 'Use _Course_ page layout  when rendering the QuickmailSMS block pages. Enable this setting, if you are getting Moodle form fixed width issues.';
$string['are_you_sure'] = 'Are you sure you want to delete {$a->title}? This action cannot be reversed.';

// Alternate Email strings
$string['alternate_new'] = 'Add Alternate Mobile Number';
$string['sure'] = 'Are you sure you want to delete {$a->address}? This action cannot be undone.';
$string['valid'] = 'Activation Status';
$string['approved'] = 'Approved';
$string['waiting'] = 'Waiting';
$string['entry_activated'] = 'Alternate number {$a->address} can now be used in {$a->course}.';
$string['entry_key_not_valid'] = 'Activation link is no longer valid for {$a->address}. Continue to resend activation link.';
$string['entry_saved'] = 'Alternate number {$a->address} has been saved.';
$string['entry_success'] = 'A text to verify that the address is valid has been sent to {$a->address}. Instructions on how to activate the address is contained in its contents.';
$string['entry_failure'] = 'A text could not be sent to {$a->address}. Please verify that {$a->address} exists, and try again.';
$string['alternate_from'] = 'Moodle: QuickmailSMS';
$string['alternate_subject'] = 'Alternate mobile number verification';
$string['alternate_body'] = '
<p>
{$a->fullname} added {$a->address} as an alternate sending address for {$a->course}.
</p>

<p>
The purpose of this text was to verify that this mobilie exists and if active, and 
the owner of this address has the appropriate permissions in Moodle.
</p>

<p>
If you wish to complete the verification process, please continue by visiting
the following url: {$a->url}.
</p>

<p>
If the description of this text does not make any sense to you, then you may have
received it by mistake. Simply discard this message.
</p>

Thank you.
';
