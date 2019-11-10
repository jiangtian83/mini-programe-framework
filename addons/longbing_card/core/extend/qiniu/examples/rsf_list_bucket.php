<?php  require_once(__DIR__ . "/../autoload.php");
$accessKey = getenv("QINIU_ACCESS_KEY");
$secretKey = getenv("QINIU_SECRET_KEY");
$bucket = getenv("QINIU_TEST_BUCKET");
$auth = new Qiniu\Auth($accessKey, $secretKey);
$bucketManager = new Qiniu\Storage\BucketManager($auth);
$prefix = "";
$marker = "";
$limit = 200;
$delimiter = "/";
do 
{
	list($ret, $err) = $bucketManager->listFiles($bucket, $prefix, $marker, $limit, $delimiter);
	if( $err !== NULL ) 
	{
		echo "\n====> list file err: \n";
		var_dump($err);
	}
	else 
	{
		$marker = NULL;
		if( array_key_exists("marker", $ret) ) 
		{
			$marker = $ret["marker"];
		}
		echo "Marker: " . $marker . "\n";
		echo "\nList Items====>\n";
		count($ret["items"]);
		print "items count:" . count($ret["items"]) . "\n";
		if( array_key_exists("commonPrefixes", $ret) ) 
		{
			print_r($ret["commonPrefixes"]);
		}
	}
}
while( !empty($marker) );
?>