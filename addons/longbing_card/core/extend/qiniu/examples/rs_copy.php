<?php  require_once(__DIR__ . "/../autoload.php");
$accessKey = getenv("QINIU_ACCESS_KEY");
$secretKey = getenv("QINIU_SECRET_KEY");
$bucket = getenv("QINIU_TEST_BUCKET");
$key = "qiniu.mp4";
$auth = new Qiniu\Auth($accessKey, $secretKey);
$config = new Qiniu\Config();
$bucketManager = new Qiniu\Storage\BucketManager($auth, $config);
$srcBucket = $bucket;
$destBucket = $bucket;
$srcKey = $key;
$destKey = $key . "_copy";
$err = $bucketManager->copy($srcBucket, $srcKey, $destBucket, $destKey, true);
if( $err ) 
{
	print_r($err);
}
?>