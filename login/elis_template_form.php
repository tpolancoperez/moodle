<FONT FACE=ARIAL SIZE=+1><B>Moodle @ Oakland University</B></FONT>
<CENTER>
<?php
if (session_is_registered('cas')) {
?>
<div><TABLE BORDER=1 CELLPADDING=3 CELLSPACING=0 BORDERCOLOR="TAN"><TR><TD><FONT FACE=ARIAL SIZE=-1>You have logged out of Moodle, but you may still be logged into CAS.<br><br>
<a href="https://cas.oakland.edu/cas/logout" alt="CAS Logout">Click here</a> to logout of CAS.</FONT></TD></TR></TABLE></div>
<?php
    session_unregister('cas');
    //unset($SESSION->cas);
}
?>

<!--
<P><TABLE BORDER=3 CELLPADDING=3 CELLSPACING=0 BORDERCOLOR="ORANGE" WIDTH="50%">
<TR BGCOLOR="YELLOW"><TD>
<TABLE CELLPADDING=3 CELLSPACING=0>
<TR><TD><IMG SRC="exclaim.jpg" ALIGN="LEFT" HSPACE="5" VSPACE="5" ALT="Note!"></TD>
<TD><FONT FACE=ARIAL SIZE=-1>
<B>Moodle</B> and <B>Elluminate</B> will be unavailable on <B>Wednesday, February 9th</B> from <B>6 - 8 a.m.</B> for scheduled maintenance.
</FONT></TD></TR></TABLE>
</TD></TR></TABLE>
-->

<!-- line below was removed by JC on 8/10/2011 - we found that the onsubmit param was calling a non-existent function -->
<!-- <form action="index.php" method="post" name="login" id="login" onsubmit="return check_fields();"> -->
<form action="index.php" method="post" name="login" id="login">
<P><TABLE BORDER=1 CELLPADDING=3 CELLSPACING=0 BORDERCOLOR="TAN">
<TR BGCOLOR="BEIGE" ALIGN="CENTER">
<TD COLSPAN=2><FONT FACE=ARIAL SIZE=-1>
<P><IMG SRC="images/moodlelogo.gif"><BR>
Type in your NetID (OU e-mail) Username and Password below and then click SUBMIT.<BR>
When typing in your NetID Username, do <B>not</B> include the "@oakland.edu" part.</FONT></TD>
</FONT></TD>
</TR>
<TR>
<TD BGCOLOR="BEIGE"><FONT FACE=ARIAL SIZE=-1><B>NetID Username</B></FONT></TD>
<TD><INPUT TYPE="TEXT" SIZE=12 name="username" size="15" value="<?php p($frm->username) ?>" alt="<?php print_string("username") ?>" /><FONT FACE=ARIAL SIZE=-1>@oakland.edu</FONT></TD>
</TR>
<TR>
<TD BGCOLOR="BEIGE"><FONT FACE=ARIAL SIZE=-1><B>NetID Password</B></FONT></TD>
<TD><INPUT TYPE="PASSWORD" SIZE=12 name="password" size="15" value="" alt="<?php print_string("password") ?>" /></TD>
</TR>
<TR>
<TD COLSPAN=2 ALIGN="CENTER">
<INPUT TYPE="SUBMIT" value="Login to Moodle"><BR>
<FONT FACE=ARIAL SIZE=-1>(<?php print_string("cookiesenabled");?>)<?php echo $OUTPUT->help_icon('cookiesenabled')?>
</TD>
</TR>
</TABLE>
</CENTER>
</form>

<P><?php $OUTPUT->error_text($errormsg); ?>

<CENTER><P><TABLE>
<TR id="_ctl1_Menu1_MenuSection0_SectionHeader";><TD ALIGN=CENTER><FONT FACE="ARIAL" SIZE=-1>Trouble logging in?  View the <a href="javascript:void(0)" onClick="expandit('_ctl1_Menu1_MenuSection0_SectionPanel')">login information</A></A>. <span id="_ctl1_Menu1_MenuSection0_SectionArrow"><a href="javascript:void(0)" onClick="expandit('_ctl1_Menu1_MenuSection0_SectionPanel')"><img id="_ctl1_Menu1_MenuSection0_ArrowImage" src="../pix/help.gif" border="0"></a></TD></TR>
<TR id="_ctl1_Menu1_MenuSection0_SectionPanel" style="display:none"><TD>

	<!--- Hidden Message Starts Here --->
	<P><TABLE BORDER=1 CELLPADDING=3 CELLSPACING=0 BORDERCOLOR="TAN">
	<TR><TD><FONT FACE=ARIAL SIZE=-1>
	<P><B>Browser</B><BR>
	It is best to use the free browser, Firefox, to access Moodle. Download it from:<BR>
	<A HREF="http://www.mozilla.com">http://www.mozilla.com</A>

	<P><B>Oakland University Email Account</B><BR>
	You must login to Moodle with your NetID (oakland.edu email) account and password. Obtain this account (you'll need your SAIL ID and PIN) and/or change your password at:
	<A HREF="https://netid.oakland.edu/profile/">https://netid.oakland.edu/profile/</A><BR>
	Please remember that anytime you change your NetID (oakland.edu) password, it will be reflected in your login to Moodle.

	<P><B>Logging into Moodle</B>
	<UL>
	<LI>Open the Moodle Login page:  <A HREF="https://moodle.oakland.edu">https://moodle.oakland.edu</A>
	<LI>Log-in using the first part of your NetID (oakland.edu email) username and your email password<BR>
	e.g. if your email is jwilson3@Oakland.edu then:<BR>
	Username: jwilson3<BR>
	Password: whatever your Oakland University email password is
	</UL>

	<P>If you continue to have problems or your courses aren't present, you may need to see if you are properly registered or contact and fill out the <A HREF="http://www2.oakland.edu/elis/help.cfm?LMS=2">help request form</A> for e-Learning and Instructional Support.
	</TD></TR>
	</TABLE>

</TD></TR>
</TABLE>

<P><A HREF="http://www2.oakland.edu/elis/password.cfm">Forgot your password?</A>

<FONT COLOR=000000><P>Need additional technical assistance?  <A HREF="http://www2.oakland.edu/elis/help.cfm?LMS=2">Request Help</A>.</FONT>

<form action="index.php" method="post">
<input type="hidden" name="username" value="guest1">
<input type="hidden" name="password" value="guest1">
<input type="submit" VALUE="View Demo Course">
</form>

<!--<form action="index.php" method="post" name="guestlogin">
<input type="hidden" name="username" value="guest" />
<input type="hidden" name="password" value="guest" />
<input type="submit" value="<?php print_string("loginguest") ?>" />
</form>-->

</CENTER>
