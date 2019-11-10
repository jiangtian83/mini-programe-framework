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
$pfop = new Qiniu\Processing\PersistentFop($auth, $config);
$base64URL = Qiniu\base64_urlSafeEncode("http://devtools.qiniu.com/qiniu.png");
$fops = "avthumb/mp4/s/640x360/vb/1.4m/image/" . $base64URL . "|saveas/" . Qiniu\base64_urlSafeEncode($bucket . ":qiniu_wm.mp4");
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