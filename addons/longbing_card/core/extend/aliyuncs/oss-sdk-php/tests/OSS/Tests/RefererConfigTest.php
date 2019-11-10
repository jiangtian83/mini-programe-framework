<?php  namespace OSS\Tests;
class RefererConfigTest extends \PHPUnit_Framework_TestCase 
{
	private $validXml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n<RefererConfiguration>\r\n<AllowEmptyReferer>true</AllowEmptyReferer>\r\n<RefererList>\r\n<Referer>http://www.aliyun.com</Referer>\r\n<Referer>https://www.aliyun.com</Referer>\r\n<Referer>http://www.*.com</Referer>\r\n<Referer>https://www.?.aliyuncs.com</Referer>\r\n</RefererList>\r\n</RefererConfiguration>";
	private $validXml2 = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n<RefererConfiguration>\r\n<AllowEmptyReferer>true</AllowEmptyReferer>\r\n<RefererList>\r\n<Referer>http://www.aliyun.com</Referer>\r\n</RefererList>\r\n</RefererConfiguration>";
	public function testParseValidXml() 
	{
		$refererConfig = new \OSS\Model\RefererConfig();
		$refererConfig->parseFromXml($this->validXml);
		$this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml($refererConfig->serializeToXml()));
	}
	public function testParseValidXml2() 
	{
		$refererConfig = new \OSS\Model\RefererConfig();
		$refererConfig->parseFromXml($this->validXml2);
		$this->assertEquals(true, $refererConfig->isAllowEmptyReferer());
		$this->assertEquals(1, count($refererConfig->getRefererList()));
		$this->assertEquals($this->cleanXml($this->validXml2), $this->cleanXml(strval($refererConfig)));
	}
	private function cleanXml($xml) 
	{
		return str_replace("\n", "", str_replace("\r", "", $xml));
	}
}