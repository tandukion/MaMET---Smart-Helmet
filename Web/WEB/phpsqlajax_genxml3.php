<?php

include("koneksim.php");

// Start XML file, create parent node

$dom = new DOMDocument("1.0");
$node = $dom->createElement("markers");
$parnode = $dom->appendChild($node);

include('koneksim.php');
    $toffset=7*60*60; //converting 7 hours to seconds.
    $txoffset=8*60*60; //converting 7 hours to seconds.
    $timeFormat="H:i";
    $dateFormat="d-m-Y"; //set the date format
    $time=gmdate($timeFormat, time()+$toffset); //get GMT date + 7
    $date=gmdate($dateFormat);
    $datea=date('Y-m-d', strtotime('+0 days'));
    $dateb=date('Y-m-d', strtotime('-7 days'));

$query = "SELECT * from kecelakaan where tanggal BETWEEN '$dateb' AND '$datea'";
$result = mysql_query($query);
if (!$result) {
  die('Invalid query: ' . mysql_error());
}

header("Content-type: text/xml");

// Iterate through the rows, adding XML nodes for each

while ($row = @mysql_fetch_assoc($result)){
  // ADD TO XML DOCUMENT NODE
  $node = $dom->createElement("marker");
  $newnode = $parnode->appendChild($node);
  $newnode->setAttribute("address", $row['address']);
  $newnode->setAttribute("lat", $row['lat']);
  $newnode->setAttribute("lng", $row['lng']);
  $newnode->setAttribute("tanggal", $row['tanggal']);
}

echo $dom->saveXML();

?>