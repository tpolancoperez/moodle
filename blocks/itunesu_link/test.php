<?php
print "Hello World";
$r = new HttpRequest($siteurl, HttpRequest::METH_POST, array("timeout" => 20, "connecttimeout" => 5));
print $r;
print "Goodby";
?>
