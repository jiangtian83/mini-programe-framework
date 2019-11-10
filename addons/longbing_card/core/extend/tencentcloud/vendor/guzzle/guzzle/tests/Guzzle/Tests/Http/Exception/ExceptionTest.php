<?php  namespace Guzzle\Tests\Http\Exception;
class ExceptionTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testRequestException() 
	{
		$e = new \Guzzle\Http\Exception\RequestException("Message");
		$request = new \Guzzle\Http\Message\Request("GET", "http://www.guzzle-project.com/");
		$e->setRequest($request);
		$this->assertEquals($request, $e->getRequest());
	}
	public function testBadResponseException() 
	{
		$e = new \Guzzle\Http\Exception\BadResponseException("Message");
		$response = new \Guzzle\Http\Message\Response(200);
		$e->setResponse($response);
		$this->assertEquals($response, $e->getResponse());
	}
	public function testCreatesGenericErrorExceptionOnError() 
	{
		$request = new \Guzzle\Http\Message\Request("GET", "http://www.example.com");
		$response = new \Guzzle\Http\Message\Response(307);
		$e = \Guzzle\Http\Exception\BadResponseException::factory($request, $response);
		$this->assertInstanceOf("Guzzle\\Http\\Exception\\BadResponseException", $e);
	}
	public function testCreatesClientErrorExceptionOnClientError() 
	{
		$request = new \Guzzle\Http\Message\Request("GET", "http://www.example.com");
		$response = new \Guzzle\Http\Message\Response(404);
		$e = \Guzzle\Http\Exception\BadResponseException::factory($request, $response);
		$this->assertInstanceOf("Guzzle\\Http\\Exception\\ClientErrorResponseException", $e);
	}
	public function testCreatesServerErrorExceptionOnServerError() 
	{
		$request = new \Guzzle\Http\Message\Request("GET", "http://www.example.com");
		$response = new \Guzzle\Http\Message\Response(503);
		$e = \Guzzle\Http\Exception\BadResponseException::factory($request, $response);
		$this->assertInstanceOf("Guzzle\\Http\\Exception\\ServerErrorResponseException", $e);
	}
}
?>