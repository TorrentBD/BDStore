<?php
error_reporting(E_ALL ^ E_DEPRECATED);
?>
<?php   
$db_host = "localhost"; 
$db_username = "root";   
$db_pass = "";  
$db_name = "mystore"; 
 
$con = mysqli_connect("$db_host","$db_username","$db_pass","$db_name") or die ("could not connect to mysql");
           
?>