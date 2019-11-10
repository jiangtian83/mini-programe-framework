<?php  namespace OSS\Tests;
class GetLoggingResultTest extends \PHPUnit_Framework_TestCase 
{
	private $validXml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n<BucketLoggingStatus>\r\n<LoggingEnabled>\r\n<TargetBucket>TargetBucket</TargetBucket>\r\n<TargetPrefix>TargetPrefix</TargetPrefix>\r\n</LoggingEnabled>\r\n</BucketLoggingStatus>";
	public function testParseValidXml() 
	{
		$response = new \OSS\Http\ResponseCore(array( ), $this->validXml, 200);
		$result = new \OSS\Result\GetLoggingResult($response);
		$this->assertTrue($result->isOK());
		$this->assertNotNull($result->getData());
		$this->assertNotNull($result->getRawResponse());
		$loggingConfig = $result->getData();
		$this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml($loggingConfig->serializeToXml()));
		$this->assertEquals("TargetBucket", $loggingConfig->getTargetBucket());
		$this->assertEquals("TargetPrefix", $loggingConfig->getTargetPrefix());
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
			new \OSS\Result\GetLoggingResult($response);
			$this->assertTrue(false);
		}
		catch( \OSS\Core\OssException $e ) 
		{
		}
	}
}