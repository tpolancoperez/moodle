<?php
echo "START<br/><br/>";
require_once('../../config.php');

//Get all courses with duplicate instances of Internal Email
$sql = "SELECT a.id";
$sql.= " FROM mdl_attforblock a";
$data = $DB->get_records_sql($sql);
echo "(".count($data).") instances of Attendance.<br/>".$sql."<br/><br/>";

function displayStatus($table, $sql, $message){
    global $DB;
    
    $data = $DB->get_records_sql($sql);
    $c = count($data);
    $message = "(".$c.") $message<br/>";
    if($c>0){
        $del_sql = "DELETE FROM :table WHERE id IN (:sql)";
        $message = "<b>".$message.str_replace(array(":table",":sql"), array($table, $sql), $del_sql)."</b>";
    }else{
        $message = $message.$sql;
    }
    $message.= "<br/><br/>";
    echo $message;
}
    

$table = "mdl_attendance_statuses";
$sql = "SELECT s.id";
$sql.= " FROM $table s";
$sql.= " LEFT JOIN mdl_course c";
$sql.= "    ON c.id = s.courseid";  
$sql.= " WHERE c.id IS NULL";
$sql.= " AND s.courseid != 0";
$message = "Statuses - courseid not found";
displayStatus($table, $sql, $message);


$table = "mdl_attendance_sessions";
$sql = "SELECT s.id";
$sql.= " FROM $table s";
$sql.= " LEFT JOIN mdl_course c";
$sql.= "    ON c.id = s.courseid"; 
$sql.= " WHERE c.id IS NULL";
$message = "Sessions - courseid not found";
displayStatus($table, $sql, $message);

        
$table = "mdl_attendance_log";
$sql = "SELECT l.id";
$sql.= " FROM $table l";
$sql.= " LEFT JOIN mdl_attendance_sessions s";
$sql.= "    ON s.id = l.sessionid";
$sql.= " WHERE s.id IS NULL";
$message = "Logs - sessionid not found";
displayStatus($table, $sql, $message);

        
$table = "mdl_attendance_log";
$sql = "SELECT l.id";
$sql.= " FROM $table l";
$sql.= " LEFT JOIN mdl_user u";
$sql.= "    ON u.id = l.studentid";
$sql.= " WHERE u.id IS NULL";
$message = "Logs - studentid not found<br/>";
displayStatus($table, $sql, $message);


$table = "mdl_attendance_log";
$sql = "SELECT l.id";
$sql.= " FROM $table l";
$sql.= " LEFT JOIN mdl_attendance_statuses s";
$sql.= "    ON s.id = l.statusid";
$sql.= " WHERE s.id IS NULL";
$message = "Logs - statusid not found<br/>";
displayStatus($table, $sql, $message);


$table = "mdl_attendance_log";
$sql = "SELECT l.id";
$sql.= " FROM $table l";
$sql.= " LEFT JOIN mdl_attendance_statuses s";
$sql.= "    ON s.id = l.statusid";
$sql.= " WHERE s.id IS NULL";
$message = "Logs - statusid not found<br/>";
displayStatus($table, $sql, $message);


echo "END<br/>";
?>
