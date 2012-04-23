<?php
set_time_limit(0);

echo "START ".date("Y-m-d H:i:s")."<br/>";
require_once('../../config.php');

require_login();

$fs = get_file_storage();


//Get all courses with Internal Email
$sql = "SELECT";
    $sql.= " id,";
    $sql.= " course";
$sql.= " FROM mdl_email";
$sql.= ";";
$emails = $DB->get_records_sql($sql);
foreach($emails as $email){
    echo "------\n";
    
    if (! $cm = get_coursemodule_from_instance("email", $email->id, $email->course)) {
        exit;
    }
    if (! $context = context_module::instance($cm->id)){
        exit;
    }
                                            
    
    $dir_email = $CFG->dataroot."/".$email->course."/moddata/email";
    if(file_exists($dir_email) && is_dir($dir_email))
    {
        //Accounts
        if($h_account = opendir($dir_email))
        {
            while(($dir_account = readdir($h_account)) !== false)
            {
                if($dir_account != "." && $dir_account != ".." && is_dir($dir_email."/".$dir_account))
                {
                    //Mails
                    if($h_mail = opendir($dir_email."/".$dir_account))
                    {
                        while(($dir_mail = readdir($h_mail)) !== false)
                        {
                            if($dir_mail != "." && $dir_mail != ".." && is_dir($dir_email."/".$dir_account."/".$dir_mail))
                            {
                                //File Attachments
                                if($h_file = opendir($dir_email."/".$dir_account."/".$dir_mail))
                                {
                                    while(($filename = readdir($h_file)) !== false){
                                        $fullpath = $dir_email."/".$dir_account."/".$dir_mail."/".$filename;
                                        if($filename != "." && $filename != ".." && !is_dir($fullpath))
                                        {
                                            $file = $fs->get_file($context->id, 'mod_email', 'attachments', $dir_mail, "/", $filename);
                                            if($file===false){
                                                echo "[Error File Not Migrated] ".$fullpath."\n";
                                                
                                            }else{
                                                //Code to remove file after migration
                                                $del_ok = unlink($fullpath);
                                                if($del_ok){
                                                    echo "[Removed] ".$fullpath."\n"; //Migrated and Removed
                                                }else{
                                                    echo "[ERROR Removing] ".$fullpath."\n";
                                                }
                                            }
                                        }      
                                    }
                                    closedir($h_file);
                                }
                            }
                        }
                        closedir($h_mail);
                    }
                }
            }
            closedir($h_account);
        }
    }
}
echo "END ".date("Y-m-d H:i:s")."<br/>\n";
?>
