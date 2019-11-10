<?php  require_once(__DIR__ . "/../autoload.php");
$accessKey = getenv("QINIU_ACCESS_KEY");
$secretKey = getenv("QINIU_SECRET_KEY");
$bucket = getenv("QINIU_TEST_BUCKET");
$auth = new Qiniu\Auth($accessKey, $secretKey);
$bucketManager = new Qiniu\Storage\BucketManager($auth);
$url = "http://devtools.qiniu.com/qiniu.png";
$key = time() . ".png";
list($ret, $err) = $bucketManager->fetch($url, $bucket, $key);
echo "=====> fetch " . $url . " to bucket: " . $bucket . "  key: " . $key . "\n";
if( $err !== NULL ) 
{
	var_dump($err);
}
else 
{
	print_r($ret);
}
$key = NULL;
list($ret, $err) = $bucketManager->fetch($url, $bucket, $key);
echo "=====> fetch " . $url . " to bucket: " . $bucket . "  key: \$(etag)\n";
if( $err !== NULL ) 
{
	var_dump($err);
}
else 
{
	print_r($ret);
}
?>