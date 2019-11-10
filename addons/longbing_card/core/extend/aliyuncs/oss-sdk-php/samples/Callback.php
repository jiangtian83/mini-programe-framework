<?php  require_once(__DIR__ . "/Common.php");
$bucket = Common::getBucketName();
$ossClient = Common::getOssClient();
if( is_null($ossClient) ) 
{
	exit( 1 );
}
$url = "{\r\n        \"callbackUrl\":\"callback.oss-demo.com:23450\",\r\n        \"callbackHost\":\"oss-cn-hangzhou.aliyuncs.com\",\r\n        \"callbackBody\":\"bucket=\${bucket}&object=\${object}&etag=\${etag}&size=\${size}&mimeType=\${mimeType}&imageInfo.height=\${imageInfo.height}&imageInfo.width=\${imageInfo.width}&imageInfo.format=\${imageInfo.format}&my_var1=\${x:var1}&my_var2=\${x:var2}\",\r\n         \"callbackBodyType\":\"application/x-www-form-urlencoded\"\r\n\r\n    }";
$var = "{\r\n        \"x:var1\":\"value1\",\r\n        \"x:var2\":\"值2\"\r\n    }";
$options = array( OSS\OssClient::OSS_CALLBACK => $url, OSS\OssClient::OSS_CALLBACK_VAR => $var );
$result = $ossClient->putObject($bucket, "b.file", "random content", $options);
Common::println($result["body"]);
Common::println($result["info"]["http_code"]);
$object = "multipart-callback-test.txt";
$copiedObject = "multipart-callback-test.txt.copied";
$ossClient->putObject($bucket, $copiedObject, file_get_contents(__FILE__));
$upload_id = $ossClient->initiateMultipartUpload($bucket, $object);
$copyId = 1;
$eTag = $ossClient->uploadPartCopy($bucket, $copiedObject, $bucket, $object, $copyId, $upload_id);
$upload_parts[] = array( "PartNumber" => $copyId, "ETag" => $eTag );
$listPartsInfo = $ossClient->listParts($bucket, $object, $upload_id);
$json = "{\r\n        \"callbackUrl\":\"callback.oss-demo.com:23450\",\r\n        \"callbackHost\":\"oss-cn-hangzhou.aliyuncs.com\",\r\n        \"callbackBody\":\"{\\\"mimeType\\\":\${mimeType},\\\"size\\\":\${size},\\\"x:var1\\\":\${x:var1},\\\"x:var2\\\":\${x:var2}}\",\r\n        \"callbackBodyType\":\"application/json\"\r\n    }";
$var = "{\r\n        \"x:var1\":\"value1\",\r\n        \"x:var2\":\"值2\"\r\n    }";
$options = array( OSS\OssClient::OSS_CALLBACK => $json, OSS\OssClient::OSS_CALLBACK_VAR => $var );
$result = $ossClient->completeMultipartUpload($bucket, $object, $upload_id, $upload_parts, $options);
Common::println($result["body"]);
Common::println($result["info"]["http_code"]);
?>