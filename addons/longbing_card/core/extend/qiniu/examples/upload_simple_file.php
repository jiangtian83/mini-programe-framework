<?php  require_once(__DIR__ . "/../autoload.php");
$accessKey = getenv("QINIU_ACCESS_KEY");
$secretKey = getenv("QINIU_SECRET_KEY");
$bucket = getenv("QINIU_TEST_BUCKET");
$auth = new Qiniu\Auth($accessKey, $secretKey);
$token = $auth->uploadToken($bucket);
$filePath = "./php-logo.png";
$key = "my-php-logo.png";
$uploadMgr = new Qiniu\Storage\UploadManager();
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
?>