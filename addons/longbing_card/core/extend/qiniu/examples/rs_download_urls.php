<?php  require_once(__DIR__ . "/../autoload.php");
$accessKey = getenv("QINIU_ACCESS_KEY");
$secretKey = getenv("QINIU_SECRET_KEY");
$auth = new Qiniu\Auth($accessKey, $secretKey);
$baseUrl = "http://if-pri.qiniudn.com/qiniu.png?imageView2/1/h/500";
$signedUrl = $auth->privateDownloadUrl($baseUrl);
echo $signedUrl;
?>