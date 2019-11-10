<?php  require_once(__DIR__ . "/../autoload.php");
$pfop = new Qiniu\Processing\PersistentFop(NULL, NULL);
$persistentId = "z1.5b8a48e5856db843bc24cfc3";
list($ret, $err) = $pfop->status($persistentId);
if( $err ) 
{
	print_r($err);
}
else 
{
	print_r($ret);
}
?>