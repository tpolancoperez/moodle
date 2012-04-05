<?php  // $Id: sendmail.php,v 1.1 2006/10/18 16:41:20 tmas Exp $
/**
 * This page recive an new mails have send.
 *
 * @author Toni Mas
 * @version $Id: sendmail.php,v 1.4 2006/10/18 16:41:20 tmas Exp $
 * @uses $CFG
 * @package email
 * @license The source code packaged with this file is Free Software, Copyright (C) 2006 by
 *          <toni.mas at uib dot es>.
 *          It's licensed under the AFFERO GENERAL PUBLIC LICENSE unless stated otherwise.
 *          You can get copies of the licenses here:
 * 		                   http://www.affero.org/oagpl.html
 *          AFFERO GENERAL PUBLIC LICENSE is also included in the file called "COPYING".
 **/

    require_once('../../config.php');
    require_once('lib.php');

    global $CFG, $DB;

    $id 	= optional_param('id', 0, PARAM_INT); 			// Email ID instance/course
    $a  	= optional_param('a', 0, PARAM_INT);  			// account ID
    $action 	= optional_param('action', '', PARAM_ALPHANUM); 	// Action to execute
    $mailid 	= optional_param('mailid', 0, PARAM_INT); 		// email ID
    $folderid	= optional_param('folderid', 0, PARAM_INT); 		// folder ID
    $filterid	= optional_param('filterid', 0, PARAM_INT);		// filter ID


    if ($id) {
        if (! $cm = $DB->get_record('course_modules', array('id'=>$id))) {
            print_error('nocoursemodid','email');
        }

        if (! $email = $DB->get_record('email', array('id'=>$cm->instance))) {
            print_error('noemailinstance','email');
        }
    } else {
        print_error('nocourseemail','email');
    }


    require_login($cm->course);

    // Options for new mail and new folder
    $options = new stdClass();
    $options->id = $id;
    $options->a	 = $a;
    $options->mailid = $mailid;
    $options->folderid = $folderid;
    $options->filterid = $filterid;
    $options->folderoldid = isset($folderoldid) ? $folderoldid : NULL;
    
    
    $selectedusers = NULL;
    
    include_once('sendmail_form.php'); 
    
    $context = get_context_instance(CONTEXT_MODULE, $cm->id);
    
    $formoptions = get_form_options($email, $mail, $options, $selectedusers, $context);
    
    $mform = new mod_email_sendmail_form('sendmail.php', $formoptions);
    
    //Form processing and displaying is done here
    if ($mform->is_cancelled()) {
        //Handle form cancel operation, if cancel button is present on form
        redirect($CFG->wwwroot.'/mod/email/view.php?id='.$cm->id);
    } else if ($form = $mform->get_data()) {
        
        //Handle File and Content attachments.
        $draftitemid = file_get_submitted_draft_itemid('attachments');
        file_prepare_draft_area($draftitemid, $modcontext->id, 'mod_email', 'attachments', empty($mail->id)?null:$mail->id, $formoptions["attachmentoptions"]);

        $draftid_editor = file_get_submitted_draft_itemid('body');
        $form->body["text"] = file_prepare_draft_area($draftid_editor, $context->id, 'mod_email', 'body', empty($mail->id)?null:$mail->id, $formoptions["bodyoptions"], $form->body["text"]);

    
        //because of the methods used whe sending the To,Cc, & Bcc fields they aren't handled by Moodle Form API
        if(isset($_POST["to"])){
            $form->to = $_POST["to"];
        }
        //In this case you process validated data. $mform->get_data() returns data posted in form.

        
        if (! empty($form->send) or ! empty($form->draft)) {
            // Associted accountid
            if (! $account = $DB->get_record('email_account', array('emailid'=>$email->id, 'userid'=>$USER->id))) {
                print_error('noaccount','email');
            }
            $mail->accountid = $account->id;

            // Generic URL for send mails errors
            $baseurl =  $CFG->wwwroot.'/mod/email/view.php?id='.$cm->id.'&amp;mailid='.$options->mailid.'&amp;subject=\''.$form->subject.'\'&amp;body=\''.$form->body['text'].'\'';

            // Check destinataries if no drafting
            if ( !( isset($form->to) or isset($form->cc) or isset($form->bcc) )  and empty($form->draft)) {

                    $url = email_build_url($options);
                    $error = EMAIL_NOSENDERS;

                    // Redirect to new mail form, for it's not empty
                    redirect($CFG->wwwroot.'/mod/email/view.php?'.$url.'&action=newmail&subject='.$form->subject.'&body='.$form->body['text'].'&error='.$error);
            }

            // Check subject
            if (empty($form->subject)) {

                    $url = email_build_url($options);
                    $error = EMAIL_NOSUBJECT;

                    // Redirect to new mail form, for it's not empty
                    redirect($CFG->wwwroot.'/mod/email/view.php?'.$url.'&amp;action=\'newmail\'&amp;body='.$form->body['text'].'&amp;error='.$error.'');
            } else {
                    // Strip all tags except multilang
                    $mail->subject = clean_param(strip_tags($form->subject, '<lang><span>'), PARAM_CLEAN);
            }            

            // For body no checked, because no have problem if is empty

            // Check Moodle Version to use an diferents functions ( only defined in 1.7 )
            if ($CFG->version >= 2006101000) {
                if (! $cm = get_coursemodule_from_instance('email', $email->id, $email->course)) {
                    $cm->id = 0;
                }
                $form->body['text'] = trusttext_strip($form->body['text']);
            }

            // Add body
            $mail->body = $form->body['text'];

            // Add new mail, in the Inbox or corresponding folder
            if ( empty($form->draft) ) {
                if(!isset($form->cc)){ $form->cc = array();}
                if(!isset($form->bcc)){ $form->bcc = array();}

                if (! $mailid = email_add_new_mail($mail, $form->to, $form->cc, $form->bcc, $form->mailid, $context, $formoptions["attachmentoptions"], $formoptions["bodyoptions"]) ) {
                        notify('Could not send mail');
                }
            } else {
                // Save in Draft
                if (! $mailid = email_add_new_mail_in_draft($mail, $attachments, $form->mailid) ) {
                        notify('Could not save mail in as draft');
                }
            }

            if(!isset($form->action)){ $form->action = '';}

            // If it's reply's, can attach old attachments

            if ( $form->action == 'reply' or $form->action == 'forward') {
                $i = 0;
                $oldattach = "oldattachment$i";
                if ( $form->$oldattach ) {
                        while (true) {
                                $attach = email_strip_attachment($form->$oldattach);
                                email_copy_attachments($form->$oldattach, $mailid, NULL, $attach);
                                $i++;
                                $oldattach = "oldattachment$i";
                                if ( empty($form->$oldattach ) ) {
                                        break;
                                }
                        }
                }
            }

            if ( empty($form->draft) ) {
                    $legend = get_string('sendok', 'email');
            } else {
                    $legend = get_string('draftok', 'email');
            }

            redirect($CFG->wwwroot.'/mod/email/view.php?id='.$cm->id.'&action=displaymessage&message='.$legend);

        } else {
            //cancelled
            redirect($CFG->wwwroot.'/mod/email/view.php?id='.$cm->id);
        }

    } else {
    	notify('Email data if empty');
    }
?>
