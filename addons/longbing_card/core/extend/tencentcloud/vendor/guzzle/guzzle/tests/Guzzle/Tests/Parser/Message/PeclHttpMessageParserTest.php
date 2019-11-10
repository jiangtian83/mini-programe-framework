<?php  namespace Guzzle\Tests\Parser\Message;
class PeclHttpMessageParserTest extends MessageParserProvider 
{
	protected function setUp() 
	{
		if( !function_exists("http_parse_message") ) 
		{
			$this->markTestSkipped("pecl_http is not available.");
		}
	}
	public function testParsesRequests($message, $parts) 
	{
		$parser = new \Guzzle\Parser\Message\PeclHttpMessageParser();
		$this->compareRequestResults($parts, $parser->parseRequest($message));
	}
	public function testParsesResponses($message, $parts) 
	{
		$parser = new \Guzzle\Parser\Message\PeclHttpMessageParser();
		$this->compareResponseResults($parts, $parser->parseResponse($message));
	}
}
?>