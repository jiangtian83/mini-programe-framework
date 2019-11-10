<?php  require_once(__DIR__ . "/../autoload.php");
$accessKey = getenv("QINIU_ACCESS_KEY");
$secretKey = getenv("QINIU_SECRET_KEY");
$bucket = getenv("QINIU_TEST_BUCKET");
$key = "qiniu.mp4";
$newMime = "video/x-mp4";
$auth = new Qiniu\Auth($accessKey, $secretKey);
$config = new Qiniu\Config();
$bucketManager = new Qiniu\Storage\BucketManager($auth, $config);
$err = $bucketManager->changeMime($bucket, $key, $newMime);
if( $err ) 
{
	print_r($err);
}
?>