<?php  require_once(__DIR__ . "/../autoload.php");
$accessKey = getenv("QINIU_ACCESS_KEY");
$secretKey = getenv("QINIU_SECRET_KEY");
$auth = new Qiniu\Auth($accessKey, $secretKey);
$cdnManager = new Qiniu\Cdn\CdnManager($auth);
$domains = array( "javasdk.qiniudn.com", "phpsdk.qiniudn.com" );
$startDate = "2017-08-20";
$endDate = "2017-08-21";
$granularity = "day";
list($bandwidthData, $getBandwidthErr) = $cdnManager->getBandwidthData($domains, $startDate, $endDate, $granularity);
if( $getBandwidthErr != NULL ) 
{
	var_dump($getBandwidthErr);
}
else 
{
	echo "get bandwidth data success\n";
	print_r($bandwidthData);
}
?>