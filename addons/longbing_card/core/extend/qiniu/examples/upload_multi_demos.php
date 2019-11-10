<?php  require_once(__DIR__ . "/../autoload.php");
$accessKey = getenv("QINIU_ACCESS_KEY");
$secretKey = getenv("QINIU_SECRET_KEY");
$bucket = getenv("QINIU_TEST_BUCKET");
$pipeline = "sdktest";
$auth = new Qiniu\Auth($accessKey, $secretKey);
$token = $auth->uploadToken($bucket);
$uploadMgr = new Qiniu\Storage\UploadManager();
list($ret, $err) = $uploadMgr->put($token, NULL, "content string");
echo "\n====> put result: \n";
if( $err !== NULL ) 
{
	var_dump($err);
}
else 
{
	var_dump($ret);
}
$filePath = "./php-logo.png";
$key = "php-logo.png";
list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);
echo "\n====> putFile result: \n";
if( $err !== NULL ) 
{
	var_dump($err);
}
else 
{
	var_dump($ret);
}
$policy = array( "callbackUrl" => "http://172.30.251.210/upload_verify_callback.php", "callbackBody" => "filename=\$(fname)&filesize=\$(fsize)" );
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
$wmImg = Qiniu\base64_urlSafeEncode("http://devtools.qiniudn.com/qiniu.png");
$pfop = "avthumb/m3u8/wmImage/" . $wmImg;
$notifyUrl = "http://notify.fake.com";
$policy = array( "persistentOps" => $pfop, "persistentNotifyUrl" => $notifyUrl, "persistentPipeline" => $pipeline );
$token = $auth->uploadToken($bucket, NULL, 3600, $policy);
print $token;
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