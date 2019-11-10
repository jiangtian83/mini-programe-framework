<?php  require_once(__DIR__ . "/../autoload.php");
$accessKey = getenv("QINIU_ACCESS_KEY");
$secretKey = getenv("QINIU_SECRET_KEY");
$auth = new Qiniu\Auth($accessKey, $secretKey);
$urls = array( "http://phpsdk.qiniudn.com/qiniu.jpg", "http://phpsdk.qiniudn.com/qiniu2.jpg" );
$dirs = array( "http://phpsdk.qiniudn.com/test/" );
$cdnManager = new Qiniu\Cdn\CdnManager($auth);
list($refreshResult, $refreshErr) = $cdnManager->refreshUrlsAndDirs($urls, $dirs);
if( $refreshErr != NULL ) 
{
	var_dump($refreshErr);
}
else 
{
	echo "refresh request sent\n";
	print_r($refreshResult);
}
list($refreshResult, $refreshErr) = $cdnManager->refreshUrls($urls);
if( $refreshErr != NULL ) 
{
	var_dump($refreshErr);
}
else 
{
	echo "refresh request sent\n";
	print_r($refreshResult);
}
list($refreshResult, $refreshErr) = $cdnManager->refreshDirs($dirs);
if( $refreshErr != NULL ) 
{
	var_dump($refreshErr);
}
else 
{
	echo "refresh request sent\n";
	print_r($refreshResult);
}
?>