<?php  require_once(__DIR__ . "/../autoload.php");
$accessKey = getenv("QINIU_ACCESS_KEY");
$secretKey = getenv("QINIU_SECRET_KEY");
$bucket = getenv("QINIU_TEST_BUCKET");
$auth = new Qiniu\Auth($accessKey, $secretKey);
$bucketManager = new Qiniu\Storage\BucketManager($auth);
$prefix = "";
$marker = "";
$limit = 100;
$delimiter = "/";
list($ret, $err) = $bucketManager->listFiles($bucket, $prefix, $marker, $limit, $delimiter);
if( $err !== NULL ) 
{
	echo "\n====> list file err: \n";
	var_dump($err);
}
else 
{
	if( array_key_exists("marker", $ret) ) 
	{
		echo "Marker:" . $ret["marker"] . "\n";
	}
	echo "\nList Iterms====>\n";
}
?>