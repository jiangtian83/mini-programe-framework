<?php  namespace Guzzle\Tests\Service\Command;
class OperationCommandTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testHasRequestSerializer() 
	{
		$operation = new \Guzzle\Service\Command\OperationCommand();
		$a = $operation->getRequestSerializer();
		$b = new \Guzzle\Service\Command\DefaultRequestSerializer(\Guzzle\Service\Command\LocationVisitor\VisitorFlyweight::getInstance());
		$operation->setRequestSerializer($b);
		$this->assertNotSame($a, $operation->getRequestSerializer());
	}
	public function testPreparesRequestUsingSerializer() 
	{
		$op = new \Guzzle\Service\Command\OperationCommand(array( ), new \Guzzle\Service\Description\Operation());
		$op->setClient(new \Guzzle\Service\Client());
		$s = $this->getMockBuilder("Guzzle\\Service\\Command\\RequestSerializerInterface")->setMethods(array( "prepare" ))->getMockForAbstractClass();
		$s->expects($this->once())->method("prepare")->will($this->returnValue(new \Guzzle\Http\Message\EntityEnclosingRequest("POST", "http://foo.com")));
		$op->setRequestSerializer($s);
		$op->prepare();
	}
	public function testParsesResponsesWithResponseParser() 
	{
		$op = new \Guzzle\Service\Command\OperationCommand(array( ), new \Guzzle\Service\Description\Operation());
		$p = $this->getMockBuilder("Guzzle\\Service\\Command\\ResponseParserInterface")->setMethods(array( "parse" ))->getMockForAbstractClass();
		$p->expects($this->once())->method("parse")->will($this->returnValue(array( "foo" => "bar" )));
		$op->setResponseParser($p);
		$op->setClient(new \Guzzle\Service\Client());
		$request = $op->prepare();
		$request->setResponse(new \Guzzle\Http\Message\Response(200), true);
		$this->assertEquals(array( "foo" => "bar" ), $op->execute());
	}
	public function testParsesResponsesUsingModelParserWhenMatchingModelIsFound() 
	{
		$description = \Guzzle\Service\Description\ServiceDescription::factory(array( "operations" => array( "foo" => array( "responseClass" => "bar", "responseType" => "model" ) ), "models" => array( "bar" => array( "type" => "object", "properties" => array( "Baz" => array( "type" => "string", "location" => "xml" ) ) ) ) ));
		$op = new \Guzzle\Service\Command\OperationCommand(array( ), $description->getOperation("foo"));
		$op->setClient(new \Guzzle\Service\Client());
		$request = $op->prepare();
		$request->setResponse(new \Guzzle\Http\Message\Response(200, array( "Content-Type" => "application/xml" ), "<Foo><Baz>Bar</Baz></Foo>"), true);
		$result = $op->execute();
		$this->assertEquals(new \Guzzle\Service\Resource\Model(array( "Baz" => "Bar" )), $result);
	}
	public function testAllowsRawResponses() 
	{
		$description = new \Guzzle\Service\Description\ServiceDescription(array( "operations" => array( "foo" => array( "responseClass" => "bar", "responseType" => "model" ) ), "models" => array( "bar" => array( ) ) ));
		$op = new \Guzzle\Service\Command\OperationCommand(array( \Guzzle\Service\Command\OperationCommand::RESPONSE_PROCESSING => \Guzzle\Service\Command\OperationCommand::TYPE_RAW ), $description->getOperation("foo"));
		$op->setClient(new \Guzzle\Service\Client());
		$request = $op->prepare();
		$response = new \Guzzle\Http\Message\Response(200, array( "Content-Type" => "application/xml" ), "<Foo><Baz>Bar</Baz></Foo>");
		$request->setResponse($response, true);
		$this->assertSame($response, $op->execute());
	}
}
?>