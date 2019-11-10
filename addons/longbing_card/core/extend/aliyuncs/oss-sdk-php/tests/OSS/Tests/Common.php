<?php  namespace OSS\Tests;
require_once(__DIR__ . "/../../../autoload.php");
class Common 
{
	public static function getOssClient() 
	{
		try 
		{
			$ossClient = new \OSS\OssClient(getenv("OSS_ACCESS_KEY_ID"), getenv("OSS_ACCESS_KEY_SECRET"), getenv("OSS_ENDPOINT"), false);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			printf("getOssClient" . "creating OssClient instance: FAILED\n");
			printf($e->getMessage() . "\n");
			return null;
		}
		return $ossClient;
	}
	public static function getBucketName() 
	{
		return getenv("OSS_BUCKET");
	}
	public static function createBucket() 
	{
		$ossClient = self::getOssClient();
		if( is_null($ossClient) ) 
		{
			exit( 1 );
		}
		$bucket = self::getBucketName();
		$acl = \OSS\OssClient::OSS_ACL_TYPE_PUBLIC_READ;
		try 
		{
			$ossClient->createBucket($bucket, $acl);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			printf("createBucket" . ": FAILED\n");
			printf($e->getMessage() . "\n");
			return NULL;
		}
		print "createBucket" . ": OK" . "\n";
	}
	public static function waitMetaSync() 
	{
		if( getenv("TRAVIS") ) 
		{
			sleep(10);
		}
	}
}
?>