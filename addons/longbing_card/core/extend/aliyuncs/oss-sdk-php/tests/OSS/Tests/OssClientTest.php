<?php  namespace OSS\Tests;
class OssClientTest extends \PHPUnit_Framework_TestCase 
{
	public function testConstrunct() 
	{
		try 
		{
			$ossClient = new \OSS\OssClient("id", "key", "http://oss-cn-hangzhou.aliyuncs.com");
			$this->assertFalse($ossClient->isUseSSL());
			$ossClient->setUseSSL(true);
			$this->assertTrue($ossClient->isUseSSL());
			$this->assertTrue(true);
			$this->assertEquals(3, $ossClient->getMaxRetries());
			$ossClient->setMaxTries(4);
			$this->assertEquals(4, $ossClient->getMaxRetries());
			$ossClient->setTimeout(10);
			$ossClient->setConnectTimeout(20);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			assertFalse(true);
		}
	}
	public function testConstrunct2() 
	{
		try 
		{
			$ossClient = new \OSS\OssClient("id", "", "http://oss-cn-hangzhou.aliyuncs.com");
			$this->assertFalse(true);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertEquals("access key secret is empty", $e->getMessage());
		}
	}
	public function testConstrunct3() 
	{
		try 
		{
			$ossClient = new \OSS\OssClient("", "key", "http://oss-cn-hangzhou.aliyuncs.com");
			$this->assertFalse(true);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertEquals("access key id is empty", $e->getMessage());
		}
	}
	public function testConstrunct4() 
	{
		try 
		{
			$ossClient = new \OSS\OssClient("id", "key", "");
			$this->assertFalse(true);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertEquals("endpoint is empty", $e->getMessage());
		}
	}
	public function testConstrunct5() 
	{
		try 
		{
			$ossClient = new \OSS\OssClient("id", "key", "123.123.123.1");
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertTrue(false);
		}
	}
	public function testConstrunct6() 
	{
		try 
		{
			$ossClient = new \OSS\OssClient("id", "key", "https://123.123.123.1");
			$this->assertTrue($ossClient->isUseSSL());
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertTrue(false);
		}
	}
	public function testConstrunct7() 
	{
		try 
		{
			$ossClient = new \OSS\OssClient("id", "key", "http://123.123.123.1");
			$this->assertFalse($ossClient->isUseSSL());
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertTrue(false);
		}
	}
	public function testConstrunct8() 
	{
		try 
		{
			$ossClient = new \OSS\OssClient("id", "key", "http://123.123.123.1", true);
			$ossClient->listBuckets();
			$this->assertFalse(true);
		}
		catch( \OSS\Core\OssException $e ) 
		{
		}
	}
	public function testConstrunct9() 
	{
		try 
		{
			$accessKeyId = " " . getenv("OSS_ACCESS_KEY_ID") . " ";
			$accessKeySecret = " " . getenv("OSS_ACCESS_KEY_SECRET") . " ";
			$endpoint = " " . getenv("OSS_ENDPOINT") . "/ ";
			$ossClient = new \OSS\OssClient($accessKeyId, $accessKeySecret, $endpoint, false);
			$ossClient->listBuckets();
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertFalse(true);
		}
	}
}
?>