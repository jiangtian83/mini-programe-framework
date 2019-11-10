<?php  require_once(__DIR__ . "/../autoload.php");
$accessKey = getenv("QINIU_ACCESS_KEY");
$secretKey = getenv("QINIU_SECRET_KEY");
$bucket = getenv("QINIU_TEST_BUCKET");
$auth = new Qiniu\Auth($accessKey, $secretKey);
$key = "php-logo.png";
$uploadMgr = new Qiniu\Storage\UploadManager();
$pfop = "imageMogr2/rotate/90|saveas/" . Qiniu\base64_urlSafeEncode($bucket . ":php-logo-rotate.png");
$notifyUrl = "http://notify.fake.com";
$pipeline = "sdktest";
$policy = array( "persistentOps" => $pfop, "persistentNotifyUrl" => $notifyUrl, "persistentPipeline" => $pipeline );
$token = $auth->uploadToken($bucket, NULL, 3600, $policy);
list($ret, $err) = $uploadMgr->putFile($token, NULL, $key);
echo "\n====> putFile result: \n";
if( $err !== NULL ) 
{
	var_dump($err);
}
else 
{
	var_dump($ret);
}
?>