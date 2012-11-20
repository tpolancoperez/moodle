<?php // $Id: connect_to_itunes.php,v .1 2007/01/10 22:18 jharwell Exp $
      // Create an authentication token and launch iTunesU 
    require_once('../../config.php');
    global $DB;

    $id = required_param('id', PARAM_INT);   // This page should only be called from a course

    if (!$course = $DB->get_record('course',array('id'=>$id))) {
        error("That's an invalid course id");
    }

    require_login($course->id);

    require_once('./ituneslib.php');

    // Generate the Token
    $token = generate_token($USER->username, $USER->id, $COURSE->shortname, $COURSE->id, $COURSE->idnumber);

    // Post Token to iTunes
    $response = post_token($token);
?>

<?php //This is a good block for testing
/*
<HTML>
<HEAD><TITLE>Going to iTunesU</TITLE></HEAD>
<BODY>
<H1>Data Pulled From Environment</H1>
User Id: <?php print $USER->id; ?><br>
Username: <?php print $USER->username; ?><br>
First Name: <?php print $USER->firstname; ?><br>
Last Name: <?php print $USER->lastname; ?><br>
E-mail: <?php print $USER->email; ?><br>
Course Id: <?php print $COURSE->id; ?><br>
Course Name: <?php print $COURSE->fullname; ?><br>
Course Abbr: <?php print $COURSE->shortname; ?><br>
Course Id Number: <?php print $COURSE->idnumber; ?>
Course Full Data: <?php //print var_dump($COURSE); ?>
<H1>iTunesU Code</H1>
<b>The iTunesU Server Response:</b><br>
<PRE>
<?php print "Token is: ".var_dump($token)."\n"; ?>
<?php print "Response is: ".$response; ?>
</PRE>
</BODY>
</HTML>
*/
?>
<?php //This is the business end of the script
print $response;
?>
