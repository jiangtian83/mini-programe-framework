<?php  require_once(__DIR__ . "/../autoload.php");
$accessKey = getenv("QINIU_ACCESS_KEY");
$secretKey = getenv("QINIU_SECRET_KEY");
$testAuth = new Qiniu\Auth($accessKey, $secretKey);
$bucketName = "phpsdk";
$key = "php-logo.png";
$key2 = "niu.jpg";
$bucketNameBC = "phpsdk-bc";
$bucketNameNA = "phpsdk-na";
$dummyAccessKey = "abcdefghklmnopq";
$dummySecretKey = "1234567890";
$dummyAuth = new Qiniu\Auth($dummyAccessKey, $dummySecretKey);
$timestampAntiLeechEncryptKey = getenv("QINIU_TIMESTAMP_ENCRPTKEY");
$customDomain = "http://phpsdk.qiniuts.com";
$tid = getenv("TRAVIS_JOB_NUMBER");
if( !empty($tid) ) 
{
	$pid = getmypid();
	$tid = strstr($tid, ".");
	$tid .= "." . $pid;
}
function qiniuTempFile($size) 
{
	$fileName = tempnam(sys_get_temp_dir(), "qiniu_");
	$file = fopen($fileName, "wb");
	if( 0 < $size ) 
	{
		fseek($file, $size - 1);
		fwrite($file, " ");
	}
	fclose($file);
	return $fileName;
}
?>