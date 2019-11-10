<?php  require_once(__DIR__ . "/../autoload.php");
$encryptKey = "your_domain_timestamp_antileech_encryptkey";
$url1 = "http://phpsdk.qiniuts.com/24.jpg?avinfo";
$url2 = "http://phpsdk.qiniuts.com/24.jpg";
$durationInSeconds = 3600;
$signedUrl = Qiniu\Cdn\CdnManager::createTimestampAntiLeechUrl($url1, $encryptKey, $durationInSeconds);
print $signedUrl;
?>