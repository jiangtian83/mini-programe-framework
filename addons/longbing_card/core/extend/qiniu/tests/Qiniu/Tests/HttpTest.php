<?php  namespace Qiniu\Tests;
class HttpTest extends \PHPUnit_Framework_TestCase 
{
	public function testGet() 
	{
		$response = \Qiniu\Http\Client::get("baidu.com");
		$this->assertEquals($response->statusCode, 200);
		$this->assertNotNull($response->body);
		$this->assertNull($response->error);
	}
	public function testGetQiniu() 
	{
		$response = \Qiniu\Http\Client::get("up.qiniu.com");
		$this->assertEquals(405, $response->statusCode);
		$this->assertNotNull($response->body);
		$this->assertNotNull($response->xReqId());
		$this->assertNotNull($response->xLog());
		$this->assertNotNull($response->error);
	}
	public function testPost() 
	{
		$response = \Qiniu\Http\Client::post("baidu.com", null);
		$this->assertEquals($response->statusCode, 200);
		$this->assertNotNull($response->body);
		$this->assertNull($response->error);
	}
	public function testPostQiniu() 
	{
		$response = \Qiniu\Http\Client::post("up.qiniu.com", null);
		$this->assertEquals($response->statusCode, 400);
		$this->assertNotNull($response->body);
		$this->assertNotNull($response->xReqId());
		$this->assertNotNull($response->xLog());
		$this->assertNotNull($response->error);
	}
}
?>