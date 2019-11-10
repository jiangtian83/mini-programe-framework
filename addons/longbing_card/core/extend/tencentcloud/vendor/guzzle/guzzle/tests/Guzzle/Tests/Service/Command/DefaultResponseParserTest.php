<?php  namespace Guzzle\Tests\Service\Command;
class DefaultResponseParserTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testParsesXmlResponses() 
	{
		$op = new \Guzzle\Service\Command\OperationCommand(array( ), new \Guzzle\Service\Description\Operation());
		$op->setClient(new \Guzzle\Service\Client());
		$request = $op->prepare();
		$request->setResponse(new \Guzzle\Http\Message\Response(200, array( "Content-Type" => "application/xml" ), "<Foo><Baz>Bar</Baz></Foo>"), true);
		$this->assertInstanceOf("SimpleXMLElement", $op->execute());
	}
	public function testParsesJsonResponses() 
	{
		$op = new \Guzzle\Service\Command\OperationCommand(array( ), new \Guzzle\Service\Description\Operation());
		$op->setClient(new \Guzzle\Service\Client());
		$request = $op->prepare();
		$request->setResponse(new \Guzzle\Http\Message\Response(200, array( "Content-Type" => "application/json" ), "{\"Baz\":\"Bar\"}"), true);
		$this->assertEquals(array( "Baz" => "Bar" ), $op->execute());
	}
	public function testThrowsExceptionWhenParsingJsonFails() 
	{
		$op = new \Guzzle\Service\Command\OperationCommand(array( ), new \Guzzle\Service\Description\Operation());
		$op->setClient(new \Guzzle\Service\Client());
		$request = $op->prepare();
		$request->setResponse(new \Guzzle\Http\Message\Response(200, array( "Content-Type" => "application/json" ), "{\"Baz\":ddw}"), true);
		$op->execute();
	}
	public function testAddsContentTypeWhenExpectsIsSetOnCommand() 
	{
		$op = new \Guzzle\Service\Command\OperationCommand(array( ), new \Guzzle\Service\Description\Operation());
		$op["command.expects"] = "application/json";
		$op->setClient(new \Guzzle\Service\Client());
		$request = $op->prepare();
		$request->setResponse(new \Guzzle\Http\Message\Response(200, null, "{\"Baz\":\"Bar\"}"), true);
		$this->assertEquals(array( "Baz" => "Bar" ), $op->execute());
	}
}
?>