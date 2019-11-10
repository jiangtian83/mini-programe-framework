<?php  require_once(__DIR__ . "/../autoload.php");
$accessKey = "Access_Key";
$secretKey = "Secret_Key";
$auth = new Qiniu\Auth($accessKey, $secretKey);
$bucket = "Bucket_Name";
$token = $auth->uploadToken($bucket);
$uploadMgr = new Qiniu\Storage\UploadManager();
?>