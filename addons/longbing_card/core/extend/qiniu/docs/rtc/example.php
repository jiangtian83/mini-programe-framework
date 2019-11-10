<?php  require_once("../../autoload.php");
$ak = "gwd_gV4gPKZZsmEOvAuNU1AcumicmuHooTfu64q5";
$sk = "xxxx";
$auth = new Qiniu\Auth($ak, $sk);
$client = new Qiniu\Rtc\AppClient($auth);
$hub = "lfxlive";
$title = "lfxl";
try 
{
	$resp = $client->createApp($hub, $title, $maxUsers);
	print_r($resp);
	$resp = $client->getApp("dgdl5ge8y");
	print_r($resp);
	$mergePublishRtmp = NULL;
	$mergePublishRtmp["enable"] = true;
	$resp = $client->updateApp("dgdl5ge8y", $hub, $title, $maxUsers, $mergePublishRtmp);
	print_r($resp);
	$resp = $client->deleteApp("dgdl5ge8y");
	print_r($resp);
	$resp = $client->listUser("dgbfvvzid", "lfxl");
	print_r($resp);
	$resp = $client->kickUser("dgbfvvzid", "lfx", "qiniu-f6e07b78-4dc8-45fb-a701-a9e158abb8e6");
	print_r($resp);
	$resp = $client->listActiveRooms("dgbfvvzid", "lfx", NULL, NULL);
	print_r($resp);
	$resp = $client->appToken("dgd4vecde", "lfxl", "1111", time() + 3600, "user");
	print_r($resp);
}
catch( Exception $e ) 
{
	echo "Error:";
	echo $e;
	echo "\n";
}
?>