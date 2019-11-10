<?php  namespace OSS\Tests;
class AclResultTest extends \PHPUnit_Framework_TestCase 
{
	private $validXml = "<?xml version=\"1.0\" ?>\r\n<AccessControlPolicy>\r\n    <Owner>\r\n        <ID>00220120222</ID>\r\n        <DisplayName>user_example</DisplayName>\r\n    </Owner>\r\n    <AccessControlList>\r\n        <Grant>public-read</Grant>\r\n    </AccessControlList>\r\n</AccessControlPolicy>";
	private $invalidXml = "<?xml version=\"1.0\" ?>\r\n<AccessControlPolicy>\r\n</AccessControlPolicy>";
	public function testParseValidXml() 
	{
		$response = new \OSS\Http\ResponseCore(array( ), $this->validXml, 200);
		$result = new \OSS\Result\AclResult($response);
		$this->assertEquals("public-read", $result->getData());
	}
	public function testParseNullXml() 
	{
		$response = new \OSS\Http\ResponseCore(array( ), "", 200);
		try 
		{
			new \OSS\Result\AclResult($response);
			$this->assertTrue(false);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertEquals("body is null", $e->getMessage());
		}
	}
	public function testParseInvalidXml() 
	{
		$response = new \OSS\Http\ResponseCore(array( ), $this->invalidXml, 200);
		try 
		{
			new \OSS\Result\AclResult($response);
			$this->assertFalse(true);
		}
		catch( \OSS\Core\OssException $e ) 
		{
			$this->assertEquals("xml format exception", $e->getMessage());
		}
	}
}