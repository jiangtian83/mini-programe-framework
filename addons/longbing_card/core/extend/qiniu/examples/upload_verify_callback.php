<?php  require_once(__DIR__ . "/../autoload.php");
$accessKey = getenv("QINIU_ACCESS_KEY");
$secretKey = getenv("QINIU_SECRET_KEY");
$bucket = getenv("QINIU_TEST_BUCKET");
$auth = new Qiniu\Auth($accessKey, $secretKey);
$callbackBody = file_get_contents("php://input");
$contentType = "application/x-www-form-urlencoded";
$authorization = $_SERVER["HTTP_AUTHORIZATION"];
$url = "http://172.30.251.210/upload_verify_callback.php";
$isQiniuCallback = $auth->verifyCallback($contentType, $authorization, $url, $callbackBody);
if( $isQiniuCallback ) 
{
	$resp = array( "ret" => "success" );
}
else 
{
	$resp = array( "ret" => "failed" );
}
echo json_encode($resp);
?>