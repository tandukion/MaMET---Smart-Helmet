<?php
  $host = "localhost";
  $userdb = "root";
  $passdb = "kosong";
  $dbname = "mamet";
 
  $con = mysql_connect($host,$userdb,$passdb);
  if (!$con)
  {
     die('Gagal melakukan koneksi : ' . mysql_error());
  }else{
     mysql_select_db($dbname);
  }
?>