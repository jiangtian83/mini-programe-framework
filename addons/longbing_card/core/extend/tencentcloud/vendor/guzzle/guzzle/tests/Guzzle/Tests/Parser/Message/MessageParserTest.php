<?php  namespace Guzzle\Tests\Parser\Message;
class MessageParserTest extends MessageParserProvider 
{
	public function testParsesRequests($message, $parts) 
	{
		$parser = new \Guzzle\Parser\Message\MessageParser();
		$this->compareRequestResults($parts, $parser->parseRequest($message));
	}
	public function testParsesResponses($message, $parts) 
	{
		$parser = new \Guzzle\Parser\Message\MessageParser();
		$this->compareResponseResults($parts, $parser->parseResponse($message));
	}
	public function testParsesRequestsWithMissingProtocol() 
	{
		$parser = new \Guzzle\Parser\Message\MessageParser();
		$parts = $parser->parseRequest("GET /\r\nHost: Foo.com\r\n\r\n");
		$this->assertEquals("GET", $parts["method"]);
		$this->assertEquals("HTTP", $parts["protocol"]);
		$this->assertEquals("1.1", $parts["version"]);
	}
	public function testParsesRequestsWithMissingVersion() 
	{
		$parser = new \Guzzle\Parser\Message\MessageParser();
		$parts = $parser->parseRequest("GET / HTTP\r\nHost: Foo.com\r\n\r\n");
		$this->assertEquals("GET", $parts["method"]);
		$this->assertEquals("HTTP", $parts["protocol"]);
		$this->assertEquals("1.1", $parts["version"]);
	}
	public function testParsesResponsesWithMissingReasonPhrase() 
	{
		$parser = new \Guzzle\Parser\Message\MessageParser();
		$parts = $parser->parseResponse("HTTP/1.1 200\r\n\r\n");
		$this->assertEquals("200", $parts["code"]);
		$this->assertEquals("", $parts["reason_phrase"]);
		$this->assertEquals("HTTP", $parts["protocol"]);
		$this->assertEquals("1.1", $parts["version"]);
	}
}
?>