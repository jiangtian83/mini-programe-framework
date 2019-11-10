<?php  namespace OSS\Tests;
class GetCorsResultTest extends \PHPUnit_Framework_TestCase 
{
	private $validXml = "<?xml version=\"1.0\" encoding=\"utf-8\"?>\r\n<CORSConfiguration>\r\n<CORSRule>\r\n<AllowedOrigin>http://www.b.com</AllowedOrigin>\r\n<AllowedOrigin>http://www.a.com</AllowedOrigin>\r\n<AllowedOrigin>http://www.a.com</AllowedOrigin>\r\n<AllowedMethod>GET</AllowedMethod>\r\n<AllowedMethod>PUT</AllowedMethod>\r\n<AllowedMethod>POST</AllowedMethod>\r\n<AllowedHeader>x-oss-test</AllowedHeader>\r\n<AllowedHeader>x-oss-test2</AllowedHeader>\r\n<AllowedHeader>x-oss-test2</AllowedHeader>\r\n<AllowedHeader>x-oss-test3</AllowedHeader>\r\n<ExposeHeader>x-oss-test1</ExposeHeader>\r\n<ExposeHeader>x-oss-test1</ExposeHeader>\r\n<ExposeHeader>x-oss-test2</ExposeHeader>\r\n<MaxAgeSeconds>10</MaxAgeSeconds>\r\n</CORSRule>\r\n<CORSRule>\r\n<AllowedOrigin>http://www.b.com</AllowedOrigin>\r\n<AllowedMethod>GET</AllowedMethod>\r\n<AllowedHeader>x-oss-test</AllowedHeader>\r\n<ExposeHeader>x-oss-test1</ExposeHeader>\r\n<MaxAgeSeconds>110</MaxAgeSeconds>\r\n</CORSRule>\r\n</CORSConfiguration>";
	public function testParseValidXml() 
	{
		$response = new \OSS\Http\ResponseCore(array( ), $this->validXml, 200);
		$result = new \OSS\Result\GetCorsResult($response);
		$this->assertTrue($result->isOK());
		$this->assertNotNull($result->getData());
		$this->assertNotNull($result->getRawResponse());
		$corsConfig = $result->getData();
		$this->assertEquals($this->cleanXml($this->validXml), $this->cleanXml($corsConfig->serializeToXml()));
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
			new \OSS\Result\GetCorsResult($response);
			$this->assertTrue(false);
		}
		catch( \OSS\Core\OssException $e ) 
		{
		}
	}
}