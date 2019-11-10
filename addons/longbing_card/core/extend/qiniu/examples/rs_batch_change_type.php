<?php  require_once(__DIR__ . "/../autoload.php");
$accessKey = getenv("QINIU_ACCESS_KEY");
$secretKey = getenv("QINIU_SECRET_KEY");
$bucket = getenv("QINIU_TEST_BUCKET");
$auth = new Qiniu\Auth($accessKey, $secretKey);
$config = new Qiniu\Config();
$bucketManager = new Qiniu\Storage\BucketManager($auth, $config);
$keys = array( "qiniu.mp4", "qiniu.png", "qiniu.jpg" );
$keyTypePairs = array( );
foreach( $keys as $key ) 
{
	$keyTypePairs[$key] = 1;
}
$ops = $bucketManager->buildBatchChangeType($bucket, $keyTypePairs);
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