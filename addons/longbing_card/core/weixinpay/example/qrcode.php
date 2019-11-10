<?php  error_reporting(1);
require_once("phpqrcode/phpqrcode.php");
$url = urldecode($_GET["data"]);
QRcode::png($url);
?>