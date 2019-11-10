<?php  namespace OSS\Tests;
class LoggingConfigTest extends \PHPUnit_Framework_TestCase 
{
	private $validXml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n<BucketLoggingStatus>\r\n<LoggingEnabled>\r\n<TargetBucket>TargetBucket</TargetBucket>\r\n<TargetPrefix>TargetPrefix</TargetPrefix>\r\n</LoggingEnabled>\r\n</BucketLoggingStatus>";
	private $nullXml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n<BucketLoggingStatus/>";
	public function testParseValidXml() 
	{
		$loggingConfig = new \OSS\Model\LoggingConfig();
		$loggingConfig->parseFromXml($this->validXml);
		$this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml(strval($loggingConfig)));
	}
	public function testConstruct() 
	{
		$loggingConfig = new \OSS\Model\LoggingConfig("TargetBucket", "TargetPrefix");
		$this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml($loggingConfig->serializeToXml()));
	}
	public function testFailedConstruct() 
	{
		$loggingConfig = new \OSS\Model\LoggingConfig("TargetBucket", null);
		$this->assertEquals($this->cleanXml($this->nullXml), $this->cleanXml($loggingConfig->serializeToXml()));
	}
	private function cleanXml($xml) 
	{
		return str_replace("\n", "", str_replace("\r", "", $xml));
	}
}