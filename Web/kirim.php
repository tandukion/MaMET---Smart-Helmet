<?php
	$latit='';
	$longit='';
	
	$conn = mysql_connect("192.168.0.7","gogok","kosong");
	mysql_select_db("mamet");
	
	$query = "INSERT INTO user
	(latitude,longitude) values (`$latit`,`$longit`)";
	$result = mysql_query($query) or die("REPORT Fail Query Save DATA.");
?>