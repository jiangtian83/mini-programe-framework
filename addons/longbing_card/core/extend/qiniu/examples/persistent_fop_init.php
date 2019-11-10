<?php  require_once(__DIR__ . "/../autoload.php");
$accessKey = "Access_Key";
$secretKey = "Secret_Key";
$auth = new Qiniu\Auth($accessKey, $secretKey);
$bucket = "Bucket_Name";
$pipeline = "pipeline_name";
$pfop = new Qiniu\Processing\PersistentFop($auth, $bucket, $pipeline);
?>