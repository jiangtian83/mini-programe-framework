<?php  require_once(__DIR__ . "/../autoload.php");
$accessKey = getenv("QINIU_ACCESS_KEY");
$secretKey = getenv("QINIU_SECRET_KEY");
$auth = new Qiniu\Auth($accessKey, $secretKey);
$cdnManager = new Qiniu\Cdn\CdnManager($auth);
$domains = array( "javasdk.qiniudn.com", "phpsdk.qiniudn.com" );
$logDate = "2017-08-20";
list($logListData, $getLogErr) = $cdnManager->getCdnLogList($domains, $logDate);
if( $getLogErr != NULL ) 
{
	var_dump($getLogErr);
}
else 
{
	echo "get cdn log list success\n";
	print_r($logListData);
}
?>