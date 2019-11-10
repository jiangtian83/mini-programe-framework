<?php  require_once(__DIR__ . "/../autoload.php");
$accessKey = getenv("QINIU_ACCESS_KEY");
$secretKey = getenv("QINIU_SECRET_KEY");
$bucket = getenv("QINIU_TEST_BUCKET");
$auth = new Qiniu\Auth($accessKey, $secretKey);
$config = new Qiniu\Config();
$bucketManager = new Qiniu\Storage\BucketManager($auth, $config);
$keyMimePairs = array( "qiniu.mp4" => "video/x-mp4", "qiniu.png" => "image/x-png", "qiniu.jpg" => "image/x-jpg" );
$ops = $bucketManager->buildBatchChangeMime($bucket, $keyMimePairs);
list($ret, $err) = $bucketManager->batch($ops);
if( $err ) 
{
	print_r($err);
}
else 
{
	print_r($ret);
}
?>