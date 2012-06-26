<CENTER><FONT FACE=ARIAL SIZE=+1><B>Moodle @ Oakland University</B></FONT></CENTER>
<CENTER>
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
<TD ALIGN="LEFT"><INPUT TYPE="TEXT" SIZE=20 name="username"><FONT FACE=ARIAL SIZE=-1>@oakland.edu</FONT></TD>
</TR>
<TR>
<TD BGCOLOR="BEIGE"><FONT FACE=ARIAL SIZE=-1><B>NetID Password</B></FONT></TD>
<TD ALIGN="LEFT"><INPUT TYPE="PASSWORD" name="password" size="20"></TD>
</TR>
<TR>
<TD COLSPAN=2 ALIGN="CENTER">
<INPUT TYPE="SUBMIT" value="Login to Moodle"><BR>
</TD>
</TR>
</TABLE>
</CENTER>
</form>

<P><?php formerr($errormsg) ?>

</CENTER>
