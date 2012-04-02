<html dir="ltr">
<head>
<TITLE>Moodle @ Oakland University - Welcome (Home Page)</TITLE>

<script type="text/javascript">
var gaJsHost = (("https:" == document.location.protocol) ? "https://ssl." : "http://www.");
document.write(unescape("%3Cscript src='" + gaJsHost + "google-analytics.com/ga.js' type='text/javascript'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var pageTracker = _gat._getTracker("UA-15580478-2");
pageTracker._trackPageview();
} catch(err) {}</script>


<SCRIPT LANGUAGE="JavaScript">
function hideOne(number)
{
document.getElementById("idForm" + number).style.display = 'none';            
}
</SCRIPT>

<SCRIPT LANGUAGE="JavaScript">
function showOne(number)
{
document.getElementById("idForm" + number).style.display = 'block';            
}
</SCRIPT>

<SCRIPT LANGUAGE="JavaScript">
function expandit(itemId)
{
	window.setTimeout("toggleSection('" + itemId + "')",10);
}

function toggleSection(itemId) {
	var arrowId = itemId.replace("SectionPanel", "ArrowImage");
	var arrow, item;
	
	if (document.all) {
		item = document.all[itemId];
		arrow = document.all[arrowId];
	}
	
	if (!document.all && document.getElementById) {
		item = document.getElementById(itemId);
		arrow = document.getElementById(arrowId);
	}
		
	if (item.style.display == "none") {
		arrow.src = arrowDown.src;
		item.style.display = "inline";
	}
	else {
		arrow.src = arrowRight.src;	
		item.style.display = "none";		
	}
}
</SCRIPT>

<script id="_ctl1_Menu1_SetupScript" language="JavaScript">
<!--
	var sectionCount = 2;
	var menuId = "_ctl1_Menu1_Menu";
	var arrowDown = new Image();
	arrowDown.src = "../pix/help.gif";
	var arrowRight = new Image();
	arrowRight.src = "../pix/help.gif";

-->
</script>
<?php
//include("outopnav/topnav_wrapper_head.php");
?>
</head>

<LINK REL="STYLESHEET" HREF="default.css" TITLE="format" TYPE="text/css">
<body leftmargin="0" topmargin="0" marginwidth="0" marginheight="0" style="margin-top: 0px; margin-left: 0px; margin-right: 0px; margin-bottom: 0px;">

<?php
//include("outopnav/topnav_wrapper_top.php");
?>

<script type="text/javascript" src="menu/jsmenu_milonic_src.js"></script>
<param copyright="JavaScript Menu by Milonic" value="http://www.milonic.com/"></param>
<script	type="text/javascript">
if(ns4)_d.write("<scr"+"ipt language=JavaScript src=menu/jsmenu_mmenuns4.js><\/scr"+"ipt>");		
else _d.write("<scr"+"ipt language=JavaScript src=menu/jsmenu_mmenudom.js><\/scr"+"ipt>"); 
</script>
<script language=JavaScript src="menu/jsmenu_MDL_buttons.js"></script>

<TABLE id="loginlayouttable" CELLSPACING="0" CELLPADDING="0" style="width:100%; height:100%; border: 0 none; background-color: #FFFFFF;">
    <TR VALIGN="TOP" HEIGHT="80" BGCOLOR="#000000">
        <TD COLSPAN=3><A HREF="http://www.oakland.edu"><IMG SRC="images/outopnavimage.gif" BORDER="0"></A></TD>
    </TR>
    <TR VALIGN="TOP" HEIGHT="44">
        <TD COLSPAN=3><IMG SRC="images/header1024.gif" ALT="Moodle at Oakland University"></TD>
    </TR>
    <TR VALIGN="TOP">
        <TD WIDTH="215">
            <TABLE CELLPADDING=0 CELLSPACING=0 BACKGROUND="images/TableBG.gif" WIDTH=215>
                <TR><TD><script language=JavaScript src="menu/jsmenu_main.js"></script></TD></TR>
            </TABLE>

            <img name="Address" src="images/address.gif" width="215" border="0" ALT="e-Learning Address"><BR>
        </TD>
        <TD ROWSPAN=2 VALIGN="TOP" WIDTH="743"><FONT FACE="ARIAL" SIZE="-1">