<?php  namespace Qcloud\Cos\Tests;
class BucketTest extends \PHPUnit_Framework_TestCase 
{
	private $cosClient = NULL;
	private $bucket = NULL;
	protected function setUp() 
	{
		$this->bucket = getenv("COS_BUCKET");
		TestHelper::nuke($this->bucket);
		$this->cosClient = new \Qcloud\Cos\Client(array( "region" => getenv("COS_REGION"), "credentials" => array( "appId" => getenv("COS_APPID"), "secretId" => getenv("COS_KEY"), "secretKey" => getenv("COS_SECRET") ) ));
		sleep(5);
	}
	protected function tearDown() 
	{
		TestHelper::nuke($this->bucket);
	}
	public function testCreateExistingBucket() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertTrue($e->getExceptionCode() === "BucketAlreadyOwnedByYou" && $e->getStatusCode() === 409);
		}
	}
	public function testCreateInvalidBucket() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => "qwe_213" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertTrue($e->getExceptionCode() === "InvalidBucketName" && $e->getStatusCode() === 400);
		}
	}
	public function testCreatePrivateBucket() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket, "ACL" => "private" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testCreatePublicReadBucket() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket, "ACL" => "public-read" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testCreateInvalidACLBucket() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket, "ACL" => "public" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertTrue($e->getExceptionCode() === "InvalidArgument" && $e->getStatusCode() === 400);
		}
	}
	public function testPutBucketAclPrivate() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->PutBucketAcl(array( "Bucket" => $this->bucket, "ACL" => "private" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutBucketAclPublicRead() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->PutBucketAcl(array( "Bucket" => $this->bucket, "ACL" => "public-read" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutBucketAclInvalid() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->PutBucketAcl(array( "Bucket" => $this->bucket, "ACL" => "public" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertTrue($e->getExceptionCode() === "InvalidArgument" && $e->getStatusCode() === 400);
		}
	}
	public function testPutBucketAclReadToUser() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->PutBucketAcl(array( "Bucket" => $this->bucket, "GrantRead" => "id=\"qcs::cam::uin/2779643970:uin/2779643970\"" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutBucketAclWriteToUser() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->PutBucketAcl(array( "Bucket" => $this->bucket, "GrantWrite" => "id=\"qcs::cam::uin/2779643970:uin/2779643970\"" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutBucketAclFullToUser() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->PutBucketAcl(array( "Bucket" => $this->bucket, "GrantFullControl" => "id=\"qcs::cam::uin/2779643970:uin/2779643970\"" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutBucketAclToUsers() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->PutBucketAcl(array( "Bucket" => $this->bucket, "GrantFullControl" => "id=\"qcs::cam::uin/2779643970:uin/2779643970\",id=\"qcs::cam::uin/2779643970:uin/2779643970\",id=\"qcs::cam::uin/2779643970:uin/2779643970\"" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutBucketAclToSubuser() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->PutBucketAcl(array( "Bucket" => $this->bucket, "GrantFullControl" => "id=\"qcs::cam::uin/2779643970:uin/2779643970\"" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutBucketAclReadWriteFull() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->PutBucketAcl(array( "Bucket" => $this->bucket, "GrantRead" => "id=\"qcs::cam::uin/123:uin/123\"", "GrantWrite" => "id=\"qcs::cam::uin/2779643970:uin/2779643970\"", "GrantFullControl" => "id=\"qcs::cam::uin/2779643970:uin/2779643970\"" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutBucketAclInvalidGrant() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->PutBucketAcl(array( "Bucket" => $this->bucket, "GrantFullControl" => "id=\"qcs::camuin/321023:uin/2779643970\"" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertTrue($e->getExceptionCode() === "InvalidArgument" && $e->getStatusCode() === 400);
		}
	}
	public function testPutBucketAclByBody() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->PutBucketAcl(array( "Bucket" => $this->bucket, "Grants" => array( array( "Grantee" => array( "DisplayName" => "qcs::cam::uin/2779643970:uin/2779643970", "ID" => "qcs::cam::uin/2779643970:uin/2779643970", "Type" => "CanonicalUser" ), "Permission" => "FULL_CONTROL" ) ), "Owner" => array( "DisplayName" => "qcs::cam::uin/2779643970:uin/2779643970", "ID" => "qcs::cam::uin/2779643970:uin/2779643970" ) ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutBucketAclByBodyToAnyone() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->PutBucketAcl(array( "Bucket" => $this->bucket, "Grants" => array( array( "Grantee" => array( "DisplayName" => "qcs::cam::anyone:anyone", "ID" => "qcs::cam::anyone:anyone", "Type" => "CanonicalUser" ), "Permission" => "FULL_CONTROL" ) ), "Owner" => array( "DisplayName" => "qcs::cam::uin/2779643970:uin/2779643970", "ID" => "qcs::cam::uin/2779643970:uin/2779643970" ) ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutBucketAclBucketNonexisted() 
	{
		try 
		{
			$this->cosClient->PutBucketAcl(array( "Bucket" => $this->bucket, "GrantFullControl" => "id=\"qcs::cam::uin/321023:uin/2779643970\"" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertTrue($e->getExceptionCode() === "NoSuchBucket" && $e->getStatusCode() === 404);
		}
	}
	public function testPutBucketAclCover() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->PutBucketAcl(array( "Bucket" => $this->bucket, "GrantFullControl" => "id=\"qcs::cam::uin/2779643970:uin/2779643970\"", "GrantRead" => "id=\"qcs::cam::uin/2779643970:uin/2779643970\"", "GrantWrite" => "id=\"qcs::cam::uin/2779643970:uin/2779643970\"" ));
			$this->cosClient->PutBucketAcl(array( "Bucket" => $this->bucket, "GrantWrite" => "id=\"qcs::cam::uin/2779643970:uin/2779643970\"" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testHeadBucket() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->HeadBucket(array( "Bucket" => $this->bucket ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testHeadBucketNonexisted() 
	{
		try 
		{
			$this->cosClient->HeadBucket(array( "Bucket" => $this->bucket ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertTrue($e->getExceptionCode() === "NoSuchBucket" && $e->getStatusCode() === 404);
		}
	}
	public function testGetBucketEmpty() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->ListObjects(array( "Bucket" => $this->bucket ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testGetBucketNonexisted() 
	{
		try 
		{
			$this->cosClient->ListObjects(array( "Bucket" => $this->bucket ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertTrue($e->getExceptionCode() === "NoSuchBucket" && $e->getStatusCode() === 404);
		}
	}
	public function testPutBucketCors() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->putBucketCors(array( "Bucket" => $this->bucket, "CORSRules" => array( array( "ID" => "1234", "AllowedHeaders" => array( "*" ), "AllowedMethods" => array( "PUT" ), "AllowedOrigins" => array( "*" ), "ExposeHeaders" => array( "*" ), "MaxAgeSeconds" => 1 ), array( "ID" => "12345", "AllowedHeaders" => array( "*" ), "AllowedMethods" => array( "PUT" ), "AllowedOrigins" => array( "*" ), "ExposeHeaders" => array( "*" ), "MaxAgeSeconds" => 1 ) ) ));
			$this->cosClient->getBucketCors(array( "Bucket" => $this->bucket ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testGetBucketCors() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->putBucketCors(array( "Bucket" => $this->bucket, "CORSRules" => array( array( "ID" => "1234", "AllowedHeaders" => array( "*" ), "AllowedMethods" => array( "PUT" ), "AllowedOrigins" => array( "*" ), "ExposeHeaders" => array( "*" ), "MaxAgeSeconds" => 1 ), array( "ID" => "12345", "AllowedHeaders" => array( "*" ), "AllowedMethods" => array( "PUT" ), "AllowedOrigins" => array( "*" ), "ExposeHeaders" => array( "*" ), "MaxAgeSeconds" => 1 ) ) ));
			$this->cosClient->getBucketCors(array( "Bucket" => $this->bucket ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testGetBucketCorsNull() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->getBucketCors(array( "Bucket" => $this->bucket ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertTrue($e->getExceptionCode() === "NoSuchCORSConfiguration" && $e->getStatusCode() === 404);
		}
	}
	public function testGetBucketCorsNonExisted() 
	{
		try 
		{
			$this->cosClient->getBucketCors(array( "Bucket" => $this->bucket ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertTrue($e->getExceptionCode() === "NoSuchBucket" && $e->getStatusCode() === 404);
		}
	}
	public function testGetBucketLifecycle() 
	{
		try 
		{
			$result = $this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$result = $this->cosClient->putBucketLifecycle(array( "Bucket" => $this->bucket, "Rules" => array( array( "Status" => "Enabled", "Filter" => array( "Tag" => array( "Key" => "datalevel", "Value" => "backup" ) ), "Transitions" => array( array( "Days" => 30, "StorageClass" => "Standard_IA" ), array( "Days" => 365, "StorageClass" => "Archive" ) ), "Expiration" => array( "Days" => 3650 ) ) ) ));
			$result = $this->cosClient->getBucketLifecycle(array( "Bucket" => $this->bucket ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testDeleteBucketLifecycle() 
	{
		try 
		{
			$result = $this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$result = $this->cosClient->putBucketLifecycle(array( "Bucket" => $this->bucket, "Rules" => array( array( "Status" => "Enabled", "Filter" => array( "Tag" => array( "Key" => "datalevel", "Value" => "backup" ) ), "Transitions" => array( array( "Days" => 30, "StorageClass" => "Standard_IA" ), array( "Days" => 365, "StorageClass" => "Archive" ) ), "Expiration" => array( "Days" => 3650 ) ) ) ));
			$result = $this->cosClient->deleteBucketLifecycle(array( "Bucket" => $this->bucket ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutBucketLifecycleNonFilter() 
	{
		try 
		{
			$result = $this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$result = $this->cosClient->putBucketLifecycle(array( "Bucket" => $this->bucket, "Rules" => array( array( "Expiration" => array( "Days" => 1000 ), "ID" => "id1", "Status" => "Enabled", "Transitions" => array( array( "Days" => 100, "StorageClass" => "Standard_IA" ) ) ) ) ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertTrue($e->getExceptionCode() === "NoSuchBucket" && $e->getStatusCode() === 404);
		}
	}
	public function testPutBucket2() 
	{
		try 
		{
			try 
			{
				$this->cosClient->deleteBucket(array( "Bucket" => "12345-" . $this->bucket ));
			}
			catch( \Exception $e ) 
			{
			}
			$this->cosClient->createBucket(array( "Bucket" => "12345-" . $this->bucket ));
			$this->cosClient->deleteBucket(array( "Bucket" => "12345-" . $this->bucket ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutBucket3() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket . "-12333-4445" ));
			$this->cosClient->deleteBucket(array( "Bucket" => $this->bucket . "-12333-4445" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testGetBucketLocation() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->getBucketLocation(array( "Bucket" => $this->bucket ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testGetBucketLocationNonExisted() 
	{
		try 
		{
			$this->cosClient->getBucketLocation(array( "Bucket" => $this->bucket ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertTrue($e->getExceptionCode() === "NoSuchBucket" && $e->getStatusCode() === 404);
		}
	}
	public function testPutObjectEncryption() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->putObject(array( "Bucket" => $this->bucket, "Key" => "11//32//43", "Body" => "Hello World!", "ServerSideEncryption" => "AES256" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testCopyBigFile() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->Copy($bucket = $this->bucket, $key = "test10G", $copysource = "lewzylu01-1251668577.cos.ap-guangzhou.myqcloud.com/test10G");
			$rt = $this->cosClient->headObject(array( "Bucket" => $this->$bucket, "Key" => "test10G" ));
			assertTrue(true, $rt["ContentLength"] == 10485760000);
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutObjectIntoNonexistedBucket() 
	{
		try 
		{
			$this->cosClient->putObject(array( "Bucket" => $this->bucket, "Key" => "hello.txt", "Body" => "Hello World" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertTrue($e->getExceptionCode() === "NoSuchBucket");
			$this->assertTrue($e->getStatusCode() === 404);
		}
	}
	public function testUploadSmallObject() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->upload($this->bucket, "你好.txt", "Hello World");
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutObjectEmpty() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->upload($this->bucket, "你好.txt", "123");
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutObjectExisted() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->upload($this->bucket, "你好.txt", "1234124");
			$this->cosClient->upload($this->bucket, "你好.txt", "请二位qwe");
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutObjectMeta() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->putObject(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "Body" => "1234124", "Metadata" => array( "lew" => str_repeat("a", 1 * 1024) ) ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutObjectMeta2K() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->putObject(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "Body" => "1234124", "Metadata" => array( "lew" => str_repeat("a", 3 * 1024) ) ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertTrue($e->getExceptionCode() === "KeyTooLong" && $e->getStatusCode() === 400);
		}
	}
	public function testUploadComplexObject() 
	{
		try 
		{
			$result = $this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->upload($this->bucket, "→↓←→↖↗↙↘! \\\"#\$%&'()*+,-./0123456789:;<=>@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\\]^_`abcdefghijklmnopqrstuvwxyz{|}~", "Hello World");
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testUploadLargeObject() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->upload($this->bucket, "hello.txt", str_repeat("a", 9 * 1024 * 1024));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testGetObject() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->upload($this->bucket, "你好.txt", "Hello World");
			$this->cosClient->getObject(array( "Bucket" => $this->bucket, "Key" => "你好.txt" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testGetObjectSpecialName() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->upload($this->bucket, "你好<>!@#^%^&*&(&^!@#@!.txt", "Hello World");
			$this->cosClient->getObject(array( "Bucket" => $this->bucket, "Key" => "你好<>!@#^%^&*&(&^!@#@!.txt" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testGetObjectIfMatchTrue() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->upload($this->bucket, "你好.txt", "Hello World");
			$this->cosClient->getObject(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "IfMatch" => "\"b10a8db164e0754105b7a99be72e3fe5\"" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testGetObjectIfMatchFalse() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->upload($this->bucket, "你好.txt", "Hello World");
			$this->cosClient->getObject(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "IfMatch" => "\"\"" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertTrue($e->getExceptionCode() === "PreconditionFailed" && $e->getStatusCode() === 412);
		}
	}
	public function testGetObjectIfNoneMatchTrue() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->upload($this->bucket, "你好.txt", "Hello World");
			$this->cosClient->getObject(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "IfNoneMatch" => "\"b10a8db164e0754105b7a99be72e3fe5\"" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertTrue($e->getExceptionCode() === "NotModified" && $e->getStatusCode() === 304);
		}
	}
	public function testGetObjectIfNoneMatchFalse() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->upload($this->bucket, "你好.txt", "Hello World");
			$this->cosClient->getObject(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "IfNoneMatch" => "\"\"" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testGetObjectUrl() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->getObjectUrl($this->bucket, "hello.txt", "+10 minutes");
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutObjectACL() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->upload($this->bucket, "11", "hello.txt");
			$this->cosClient->PutObjectAcl(array( "Bucket" => $this->bucket, "Key" => "11", "Grants" => array( array( "Grantee" => array( "DisplayName" => "qcs::cam::uin/2779643970:uin/2779643970", "ID" => "qcs::cam::uin/2779643970:uin/2779643970", "Type" => "CanonicalUser" ), "Permission" => "FULL_CONTROL" ) ), "Owner" => array( "DisplayName" => "qcs::cam::uin/2779643970:uin/2779643970", "ID" => "qcs::cam::uin/2779643970:uin/2779643970" ) ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testGetObjectACL() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->upload($this->bucket, "11", "hello.txt");
			$this->cosClient->PutObjectAcl(array( "Bucket" => $this->bucket, "Key" => "11", "Grants" => array( array( "Grantee" => array( "DisplayName" => "qcs::cam::uin/2779643970:uin/2779643970", "ID" => "qcs::cam::uin/2779643970:uin/2779643970", "Type" => "CanonicalUser" ), "Permission" => "FULL_CONTROL" ) ), "Owner" => array( "DisplayName" => "qcs::cam::uin/2779643970:uin/2779643970", "ID" => "qcs::cam::uin/2779643970:uin/2779643970" ) ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutObjectAclPrivate() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->putObject(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "Body" => "123" ));
			$this->cosClient->PutObjectAcl(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "ACL" => "private" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutObjectAclPublicRead() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->putObject(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "Body" => "123" ));
			$this->cosClient->PutObjectAcl(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "ACL" => "public-read" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutObjectAclInvalid() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->putObject(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "Body" => "123" ));
			$this->cosClient->PutObjectAcl(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "ACL" => "public" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertTrue($e->getExceptionCode() === "InvalidArgument" && $e->getStatusCode() === 400);
		}
	}
	public function testPutObjectAclReadToUser() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->putObject(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "Body" => "123" ));
			$this->cosClient->PutObjectAcl(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "GrantRead" => "id=\"qcs::cam::uin/2779643970:uin/2779643970\"" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutObjectAclFullToUser() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->putObject(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "Body" => "123" ));
			$this->cosClient->PutObjectAcl(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "GrantFullControl" => "id=\"qcs::cam::uin/2779643970:uin/2779643970\"" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutObjectAclToUsers() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->putObject(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "Body" => "123" ));
			$this->cosClient->PutObjectAcl(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "GrantFullControl" => "id=\"qcs::cam::uin/2779643970:uin/2779643970\",id=\"qcs::cam::uin/2779643970:uin/2779643970\",id=\"qcs::cam::uin/2779643970:uin/2779643970\"" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutObjectAclToSubuser() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->putObject(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "Body" => "123" ));
			$this->cosClient->PutObjectAcl(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "GrantFullControl" => "id=\"qcs::cam::uin/2779643970:uin/2779643970\"" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutObjectAclInvalidGrant() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->putObject(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "Body" => "123" ));
			$this->cosClient->PutObjectAcl(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "GrantFullControl" => "id=\"qcs::camuin/321023:uin/2779643970\"" ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertTrue($e->getExceptionCode() === "InvalidArgument" && $e->getStatusCode() === 400);
		}
	}
	public function testPutObjectAclByBody() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->putObject(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "Body" => "123" ));
			$this->cosClient->PutObjectAcl(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "Grants" => array( array( "Grantee" => array( "DisplayName" => "qcs::cam::uin/2779643970:uin/2779643970", "ID" => "qcs::cam::uin/2779643970:uin/2779643970", "Type" => "CanonicalUser" ), "Permission" => "FULL_CONTROL" ) ), "Owner" => array( "DisplayName" => "qcs::cam::uin/2779643970:uin/2779643970", "ID" => "qcs::cam::uin/2779643970:uin/2779643970" ) ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
	public function testPutObjectAclByBodyToAnyone() 
	{
		try 
		{
			$this->cosClient->createBucket(array( "Bucket" => $this->bucket ));
			$this->cosClient->putObject(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "Body" => "123" ));
			$this->cosClient->putObjectAcl(array( "Bucket" => $this->bucket, "Key" => "你好.txt", "Grants" => array( array( "Grantee" => array( "DisplayName" => "qcs::cam::anyone:anyone", "ID" => "qcs::cam::anyone:anyone", "Type" => "CanonicalUser" ), "Permission" => "FULL_CONTROL" ) ), "Owner" => array( "DisplayName" => "qcs::cam::uin/2779643970:uin/2779643970", "ID" => "qcs::cam::uin/2779643970:uin/2779643970" ) ));
		}
		catch( \Qcloud\Cos\Exception\ServiceResponseException $e ) 
		{
			$this->assertFalse(true, $e);
		}
	}
}
?>