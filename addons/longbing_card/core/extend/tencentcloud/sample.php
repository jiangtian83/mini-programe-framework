<?php  require("vendor/autoload.php");
$cosClient = new Qcloud\Cos\Client(array( "region" => "COS_REGION", "credentials" => array( "secretId" => "COS_KEY", "secretKey" => "COS_SECRET" ) ));
$bucket = "test2-1252448703";
$key = "a.txt";
$local_path = "E:/a.txt";
try 
{
	$result = $cosClient->putObject(array( "Bucket" => $bucket, "Key" => $key, "Body" => "Hello World!" ));
	print_r($result);
	echo $result["ETag"];
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->putObject(array( "Bucket" => $bucket, "Key" => $key, "Body" => fopen($local_path, "rb") ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->putObject(array( "Bucket" => $bucket, "Key" => $key, "Body" => fopen($local_path, "rb"), "ACL" => "string", "CacheControl" => "string", "ContentDisposition" => "string", "ContentEncoding" => "string", "ContentLanguage" => "string", "ContentLength" => integer, "cONTENTType" => "string", "Expires" => "mixed type: string (date format)|int (unix timestamp)|\\DateTime", "GrantFullControl" => "string", "GrantRead" => "string", "GrantWrite" => "string", "Metadata" => array( "string" => "string" ), "StorageClass" => "string" ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->upload($bucket = $bucket, $key = $key, $body = "Hello World!");
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->upload($bucket = $bucket, $key = $key, $body = fopen($local_path, "rb"));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->upload($bucket = $bucket, $key = $key, $body = fopen($local_path, "rb"), $options = array( "ACL" => "string", "CacheControl" => "string", "ContentDisposition" => "string", "ContentEncoding" => "string", "ContentLanguage" => "string", "ContentLength" => integer, "ContentType" => "string", "Expires" => "mixed type: string (date format)|int (unix timestamp)|\\DateTime", "GrantFullControl" => "string", "GrantRead" => "string", "GrantWrite" => "string", "Metadata" => array( "string" => "string" ), "StorageClass" => "string" ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$command = $cosClient->getCommand("putObject", array( "Bucket" => $bucket, "Key" => $key, "Body" => "" ));
	$signedUrl = $command->createPresignedUrl("+10 minutes");
	echo $signedUrl;
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$command = $cosClient->getCommand("uploadPart", array( "Bucket" => $bucket, "Key" => $key, "UploadId" => "", "PartNumber" => "1", "Body" => "" ));
	$signedUrl = $command->createPresignedUrl("+10 minutes");
	echo $signedUrl;
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$command = $cosClient->getCommand("putObject", array( "Bucket" => $bucket, "Key" => $key, "Body" => "" ));
	$signedUrl = $command->createAuthorization("+10 minutes");
	echo $signedUrl;
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->getObject(array( "Bucket" => $bucket, "Key" => $key ));
	echo $result["Body"];
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->getObject(array( "Bucket" => $bucket, "Key" => $key, "SaveAs" => $local_path ));
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->getObject(array( "Bucket" => $bucket, "Key" => $key, "Range" => "bytes=0-10", "SaveAs" => $local_path ));
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->getObject(array( "Bucket" => $bucket, "Key" => $key, "ResponseCacheControl" => "string", "ResponseContentDisposition" => "string", "ResponseContentEncoding" => "string", "ResponseContentLanguage" => "string", "ResponseContentType" => "string", "ResponseExpires" => "mixed type: string (date format)|int (unix timestamp)|\\DateTime", "SaveAs" => $local_path ));
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$signedUrl = $cosClient->getObjectUrl($bucket, $key, "+10 minutes");
	echo $signedUrl;
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->deleteObject(array( "Bucket" => $bucket, "Key" => $key, "VersionId" => "string" ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->deleteObjects(array( "Bucket" => "string", "Objects" => array( array( "Key" => $key, "VersionId" => "string" ) ) ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->headObject(array( "Bucket" => $bucket, "Key" => "11", "VersionId" => "111" ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->listBuckets();
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->createBucket(array( "Bucket" => $bucket ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->deleteBucket(array( "Bucket" => $bucket ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->headBucket(array( "Bucket" => $bucket ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->listObjects(array( "Bucket" => $bucket ));
	foreach( $result["Contents"] as $rt ) 
	{
		print_r($rt);
	}
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->listObjects(array( "Bucket" => $bucket, "Prefix" => "string" ));
	foreach( $result["Contents"] as $rt ) 
	{
		print_r($rt);
	}
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->getBucketLocation(array( "Bucket" => "lewzylu02" ));
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->putBucketVersioning(array( "Bucket" => $bucket, "Status" => "Enabled" ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->listObjectVersions(array( "Bucket" => $bucket, "Prefix" => "string" ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->getBucketVersioning(array( "Bucket" => $bucket ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->putBucketAcl(array( "Bucket" => $bucket, "Grants" => array( array( "Grantee" => array( "DisplayName" => "qcs::cam::uin/327874225:uin/327874225", "ID" => "qcs::cam::uin/327874225:uin/327874225", "Type" => "CanonicalUser" ), "Permission" => "FULL_CONTROL" ) ), "Owner" => array( "DisplayName" => "qcs::cam::uin/3210232098:uin/3210232098", "ID" => "qcs::cam::uin/3210232098:uin/3210232098" ) ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->getBucketAcl(array( "Bucket" => $bucket ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->putObjectAcl(array( "Bucket" => $bucket, "Key" => $key, "Grants" => array( array( "Grantee" => array( "DisplayName" => "qcs::cam::uin/327874225:uin/327874225", "ID" => "qcs::cam::uin/327874225:uin/327874225", "Type" => "CanonicalUser" ), "Permission" => "FULL_CONTROL" ) ), "Owner" => array( "DisplayName" => "qcs::cam::uin/3210232098:uin/3210232098", "ID" => "qcs::cam::uin/3210232098:uin/3210232098" ) ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->getObjectAcl(array( "Bucket" => $bucket, "Key" => $key ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->putBucketLifecycle(array( "Bucket" => $bucket, "Rules" => array( array( "Expiration" => array( "Days" => 1000 ), "ID" => "id1", "Filter" => array( "Prefix" => "documents/" ), "Status" => "Enabled", "Transitions" => array( array( "Days" => 200, "StorageClass" => "NEARLINE" ) ) ) ) ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->getBucketLifecycle(array( "Bucket" => $bucket ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->deleteBucketLifecycle(array( "Bucket" => $bucket ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->putBucketCors(array( "Bucket" => $bucket, "CORSRules" => array( array( "ID" => "1234", "AllowedHeaders" => array( "*" ), "AllowedMethods" => array( "PUT" ), "AllowedOrigins" => array( "http://www.qq.com" ) ) ) ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->getBucketCors(array( ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->deleteBucketCors(array( "Bucket" => $bucket ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->putBucketReplication(array( "Bucket" => $bucket, "Role" => "qcs::cam::uin/327874225:uin/327874225", "Rules" => array( array( "Status" => "Enabled", "ID" => "string", "Prefix" => "string", "Destination" => array( "Bucket" => "qcs::cos:ap-guangzhou::lewzylu01-1252448703", "StorageClass" => "standard" ) ) ) ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->getBucketReplication(array( "Bucket" => $bucket ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->deleteBucketReplication(array( "Bucket" => $bucket ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->putBucketNotification(array( "Bucket" => $bucket, "CloudFunctionConfigurations" => array( array( "Id" => "test-1", "Filter" => array( "Key" => array( "FilterRules" => array( array( "Name" => "Prefix", "Value" => "111" ), array( "Name" => "Suffix", "Value" => "111" ) ) ) ), "CloudFunction" => "qcs:0:video:sh:appid/1253125191:video/10010", "Events" => array( "Event" => "cos:ObjectCreated:*" ) ), array( "Id" => "test-2", "Filter" => array( "Key" => array( "FilterRules" => array( array( "Name" => "Prefix", "Value" => "111" ), array( "Name" => "Suffix", "Value" => "111" ) ) ) ), "CloudFunction" => "qcs:0:video:sh:appid/1253125191:video/10010", "Events" => array( "Event" => "cos:ObjectRemove:*" ) ) ) ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->getBucketNotification(array( "Bucket" => $bucket ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->copyObject(array( "Bucket" => $bucket, "CopySource" => "{bucket}.cos.{region}.myqcloud.com/{cos_path}?versionId={versionId}", "Key" => "string" ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->copy($bucket = $bucket, $key = $key, $copysource = "{bucket}.cos.{region}.myqcloud.com/{cos_path}", $options = array( "VersionId" => "{versionId}" ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->restoreObject(array( "Bucket" => $bucket, "Key" => $key, "Days" => 7, "CASJobParameters" => array( "Tier" => "Bulk" ) ));
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$prefix = "";
	$marker = "";
	while( true ) 
	{
		$result = $cosClient->listObjects(array( "Bucket" => $bucket, "Marker" => $marker, "MaxKeys" => 1000 ));
		foreach( $result["Contents"] as $rt ) 
		{
			print_r($rt["Key"] . " ");
		}
		$marker = $result["NextMarker"];
		if( !$result["IsTruncated"] ) 
		{
			break;
		}
	}
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	while( true ) 
	{
		$result = $cosClient->listMultipartUploads(array( "Bucket" => $bucket, "Prefix" => "" ));
		if( count($result["Uploads"]) == 0 ) 
		{
			break;
		}
		foreach( $result["Uploads"] as $upload ) 
		{
			try 
			{
				$rt = $cosClient->abortMultipartUpload(array( "Bucket" => $bucket, "Key" => $upload["Key"], "UploadId" => $upload["UploadId"] ));
				print_r($rt);
			}
			catch( Exception $e ) 
			{
				echo $e;
			}
		}
	}
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->resumeUpload($bucket = $bucket, $key = $key, $body = fopen("E:/test.txt", "rb"), $uploadId = "152448808231afdf221eb558ab15d1e455d2afd025c5663936142fdf5614ebf6d1668e2eda");
	print_r($result);
}
catch( Exception $e ) 
{
	echo $e;
}
try 
{
	$result = $cosClient->listBuckets();
	foreach( $result["Buckets"] as $bucket ) 
	{
		$region = $bucket["Location"];
		$name = $bucket["Name"];
		if( startswith($name, "lewzylu") ) 
		{
			try 
			{
				$cosClient2 = new Qcloud\Cos\Client(array( "region" => $region, "credentials" => array( "secretId" => getenv("COS_KEY"), "secretKey" => getenv("COS_SECRET") ) ));
				$rt = $cosClient2->deleteBucket(array( "Bucket" => $name ));
				print_r($rt);
			}
			catch( Exception $e ) 
			{
			}
		}
	}
}
catch( Exception $e ) 
{
	echo $e;
}
function startsWith($haystack, $needle) 
{
	$length = strlen($needle);
	return substr($haystack, 0, $length) === $needle;
}
?>