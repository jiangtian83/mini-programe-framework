<?php  require_once(__DIR__ . "/../autoload.php");
$accessKey = "Access_Key";
$secretKey = "Secret_Key";
$auth = new Qiniu\Auth($accessKey, $secretKey);
$bucket = "Bucket_Name";
$pipeline = "pipeline_name";
$notifyUrl = "http://375dec79.ngrok.com/notify.php";
$pfop = new Qiniu\Processing\PersistentFop($auth, $bucket, $pipeline, $notifyUrl);
$id = "z2.5955c739e3d0041bf80c9baa";
list($ret, $err) = $pfop->status($id);
echo "\n====> pfop avthumb status: \n";
if( $err != NULL ) 
{
	var_dump($err);
}
else 
{
	var_dump($ret);
}
?>