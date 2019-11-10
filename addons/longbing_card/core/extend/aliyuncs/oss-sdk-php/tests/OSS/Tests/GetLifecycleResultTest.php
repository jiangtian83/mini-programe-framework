<?php  namespace OSS\Tests;
class GetLifecycleResultTest extends \PHPUnit_Framework_TestCase 
{
	private $validXml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n<LifecycleConfiguration>\r\n<Rule>\r\n<ID>delete obsoleted files</ID>\r\n<Prefix>obsoleted/</Prefix>\r\n<Status>Enabled</Status>\r\n<Expiration><Days>3</Days></Expiration>\r\n</Rule>\r\n<Rule>\r\n<ID>delete temporary files</ID>\r\n<Prefix>temporary/</Prefix>\r\n<Status>Enabled</Status>\r\n<Expiration><Date>2022-10-12T00:00:00.000Z</Date></Expiration>\r\n<Expiration2><Date>2022-10-12T00:00:00.000Z</Date></Expiration2>\r\n</Rule>\r\n</LifecycleConfiguration>";
	public function testParseValidXml() 
	{
		$response = new \OSS\Http\ResponseCore(array( ), $this->validXml, 200);
		$result = new \OSS\Result\GetLifecycleResult($response);
		$this->assertTrue($result->isOK());
		$this->assertNotNull($result->getData());
		$this->assertNotNull($result->getRawResponse());
		$lifecycleConfig = $result->getData();
		$this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml($lifecycleConfig->serializeToXml()));
	}
	private function cleanXml($xml) 
	{
		return str_replace("\n", "", str_replace("\r", "", $xml));
	}
	public function testInvalidResponse() 
	{
		$response = new \OSS\Http\ResponseCore(array( ), $this->validXml, 300);
		try 
		{
			new \OSS\Result\GetLifecycleResult($response);
			$this->assertTrue(false);
		}
		catch( \OSS\Core\OssException $e ) 
		{
		}
	}
}