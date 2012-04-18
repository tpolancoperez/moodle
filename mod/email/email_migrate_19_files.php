<?php
set_time_limit(36000);

echo "START<br/>";
require_once('../../config.php');

require_login();

//Get all courses with Internal Email
$sql = "SELECT course";
$sql.= " FROM mdl_email";
$sql.= " WHERE course=72278";
$sql.= " GROUP BY course";
$sql.= ";";
$courses = $DB->get_records_sql($sql);
foreach($courses as $course){
    $dir_email = $CFG->dataroot."/".$course->course."/moddata/email";
    if(file_exists($dir_email)){
        echo $dir_email."<br/>\n";
        echo "<hr/>\n";
        if(is_dir($dir_email))
        {
            //Accounts
            if($h_account = opendir($dir_email))
            {
                while(($dir_account = readdir($h_account)) !== false)
                {
                    if($dir_account != "." && $dir_account != ".." && is_dir($dir_email."/".$dir_account))
                    {
                        echo "_ ".$dir_account."<br>\n";
                        
                        //Mails
                        if($h_mail = opendir($dir_email."/".$dir_account))
                        {
                            while(($dir_mail = readdir($h_mail)) !== false)
                            {
                                if($dir_mail != "." && $dir_mail != ".." && is_dir($dir_email."/".$dir_account."/".$dir_mail))
                                {
                                    echo "_ _ ".$dir_mail."<br>\n";
                                    
                                    //File Attachments
                                    if($h_file = opendir($dir_email."/".$dir_account."/".$dir_mail))
                                    {
                                        while(($filename = readdir($h_file)) !== false){
                                            if($filename != "." && $filename != ".." && !is_dir($dir_email."/".$dir_account."/".$dir_mail."/".$dir_mail."/".$filename))
                                            {
                                                echo "_ _ _ ".$filename."<br/>\n";
                                            }      
                                        }
                                        closedir($h_file);
                                     
                                    }
                                    //
                                    
                                }
                            }
                            closedir($h_mail);
                        }
                        //
                        
                    }
                }
                closedir($h_account);
            }
            //
        }
    }
}
echo "END<br/>\n";
?>
