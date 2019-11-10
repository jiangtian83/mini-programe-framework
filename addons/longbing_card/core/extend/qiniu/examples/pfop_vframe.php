<?php  require_once(__DIR__ . "/../autoload.php");
$accessKey = getenv("QINIU_ACCESS_KEY");
$secretKey = getenv("QINIU_SECRET_KEY");
$bucket = getenv("QINIU_TEST_BUCKET");
$auth = new Qiniu\Auth($accessKey, $secretKey);
$key = "qiniu.mp4";
$pipeline = "sdktest";
$notifyUrl = "http://375dec79.ngrok.com/notify.php";
$force = false;
$config = new Qiniu\Config();
$config->useHTTPS = true;
$pfop = new Qiniu\Processing\PersistentFop($auth, $config);
$fops = "vframe/jpg/offset/1/w/480/h/360/rotate/90|saveas/" . Qiniu\base64_urlSafeEncode($bucket . ":qiniu_480x360.jpg");
list($id, $err) = $pfop->execute($bucket, $key, $fops, $pipeline, $notifyUrl, $force);
echo "\n====> pfop avthumb result: \n";
if( $err != NULL ) 
{
	var_dump($err);
}
else 
{
	echo "PersistentFop Id: " . $id . "\n";
}
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