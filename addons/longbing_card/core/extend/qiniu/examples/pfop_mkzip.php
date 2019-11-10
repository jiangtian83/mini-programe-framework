<?php  require_once(__DIR__ . "/../autoload.php");
$accessKey = getenv("QINIU_ACCESS_KEY");
$secretKey = getenv("QINIU_SECRET_KEY");
$bucket = getenv("QINIU_TEST_BUCKET");
$key = "qiniu.png";
$auth = new Qiniu\Auth($accessKey, $secretKey);
$pipeline = "sdktest";
$pfop = new Qiniu\Processing\PersistentFop($auth, NULL);
$url1 = "http://phpsdk.qiniudn.com/php-logo.png";
$url2 = "http://phpsdk.qiniudn.com/1.png";
$zipKey = "test.zip";
$fops = "mkzip/2/url/" . Qiniu\base64_urlSafeEncode($url1);
$fops .= "/url/" . Qiniu\base64_urlSafeEncode($url2);
$fops .= "|saveas/" . Qiniu\base64_urlSafeEncode((string) $bucket . ":" . $zipKey);
$notify_url = NULL;
$force = false;
list($id, $err) = $pfop->execute($bucket, $key, $fops, $pipeline, $notify_url, $force);
echo "\n====> pfop mkzip result: \n";
if( $err != NULL ) 
{
	var_dump($err);
}
else 
{
	echo "PersistentFop Id: " . $id . "\n";
	$res = "http://api.qiniu.com/status/get/prefop?id=" . $id;
	echo "Processing result: " . $res;
}
?>