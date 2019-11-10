<?php  namespace Guzzle\Tests\Service\Command;
class OperationResponseParserTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testHasVisitors() 
	{
		$p = new \Guzzle\Service\Command\OperationResponseParser(new \Guzzle\Service\Command\LocationVisitor\VisitorFlyweight(array( )));
		$visitor = new \Guzzle\Service\Command\LocationVisitor\Response\BodyVisitor();
		$p->addVisitor("foo", $visitor);
		$this->assertSame($visitor, $this->readAttribute($p, "factory")->getResponseVisitor("foo"));
	}
	public function testUsesParentParser() 
	{
		$p = new \Guzzle\Service\Command\OperationResponseParser(new \Guzzle\Service\Command\LocationVisitor\VisitorFlyweight());
		$operation = new \Guzzle\Service\Description\Operation();
		$operation->setServiceDescription(new \Guzzle\Service\Description\ServiceDescription());
		$op = new \Guzzle\Service\Command\OperationCommand(array( ), $operation);
		$op->setResponseParser($p)->setClient(new \Guzzle\Service\Client());
		$op->prepare()->setResponse(new \Guzzle\Http\Message\Response(200, array( "Content-Type" => "application/xml" ), "<F><B>C</B></F>"), true);
		$this->assertInstanceOf("SimpleXMLElement", $op->execute());
	}
	public function testVisitsLocations() 
	{
		$parser = new \Guzzle\Service\Command\OperationResponseParser(new \Guzzle\Service\Command\LocationVisitor\VisitorFlyweight(array( )));
		$parser->addVisitor("statusCode", new \Guzzle\Service\Command\LocationVisitor\Response\StatusCodeVisitor());
		$parser->addVisitor("reasonPhrase", new \Guzzle\Service\Command\LocationVisitor\Response\ReasonPhraseVisitor());
		$parser->addVisitor("json", new \Guzzle\Service\Command\LocationVisitor\Response\JsonVisitor());
		$op = new \Guzzle\Service\Command\OperationCommand(array( ), $this->getDescription()->getOperation("test"));
		$op->setResponseParser($parser)->setClient(new \Guzzle\Service\Client());
		$op->prepare()->setResponse(new \Guzzle\Http\Message\Response(201), true);
		$result = $op->execute();
		$this->assertEquals(201, $result["code"]);
		$this->assertEquals("Created", $result["phrase"]);
	}
	public function testVisitsLocationsForJsonResponse() 
	{
		$parser = \Guzzle\Service\Command\OperationResponseParser::getInstance();
		$operation = $this->getDescription()->getOperation("test");
		$op = new \Guzzle\Service\Command\OperationCommand(array( ), $operation);
		$op->setResponseParser($parser)->setClient(new \Guzzle\Service\Client());
		$op->prepare()->setResponse(new \Guzzle\Http\Message\Response(200, array( "Content-Type" => "application/json" ), "{\"baz\":\"bar\",\"enigma\":\"123\"}"), true);
		$result = $op->execute();
		$this->assertEquals(array( "baz" => "bar", "enigma" => "123", "code" => 200, "phrase" => "OK" ), $result->toArray());
	}
	public function testSkipsUnkownModels() 
	{
		$parser = \Guzzle\Service\Command\OperationResponseParser::getInstance();
		$operation = $this->getDescription()->getOperation("test");
		$operation->setResponseClass("Baz")->setResponseType("model");
		$op = new \Guzzle\Service\Command\OperationCommand(array( ), $operation);
		$op->setResponseParser($parser)->setClient(new \Guzzle\Service\Client());
		$op->prepare()->setResponse(new \Guzzle\Http\Message\Response(201), true);
		$this->assertInstanceOf("Guzzle\\Http\\Message\\Response", $op->execute());
	}
	public function testAllowsModelProcessingToBeDisabled() 
	{
		$parser = \Guzzle\Service\Command\OperationResponseParser::getInstance();
		$operation = $this->getDescription()->getOperation("test");
		$op = new \Guzzle\Service\Command\OperationCommand(array( "command.response_processing" => "native" ), $operation);
		$op->setResponseParser($parser)->setClient(new \Guzzle\Service\Client());
		$op->prepare()->setResponse(new \Guzzle\Http\Message\Response(200, array( "Content-Type" => "application/json" ), "{\"baz\":\"bar\",\"enigma\":\"123\"}"), true);
		$result = $op->execute();
		$this->assertInstanceOf("Guzzle\\Service\\Resource\\Model", $result);
		$this->assertEquals(array( "baz" => "bar", "enigma" => "123" ), $result->toArray());
	}
	public function testCanInjectModelSchemaIntoModels() 
	{
		$parser = new \Guzzle\Service\Command\OperationResponseParser(\Guzzle\Service\Command\LocationVisitor\VisitorFlyweight::getInstance(), true);
		$desc = $this->getDescription();
		$operation = $desc->getOperation("test");
		$op = new \Guzzle\Service\Command\OperationCommand(array( ), $operation);
		$op->setResponseParser($parser)->setClient(new \Guzzle\Service\Client());
		$op->prepare()->setResponse(new \Guzzle\Http\Message\Response(200, array( "Content-Type" => "application/json" ), "{\"baz\":\"bar\",\"enigma\":\"123\"}"), true);
		$result = $op->execute();
		$this->assertSame($result->getStructure(), $desc->getModel("Foo"));
	}
	public function testDoesNotParseXmlWhenNotUsingXmlVisitor() 
	{
		$parser = \Guzzle\Service\Command\OperationResponseParser::getInstance();
		$description = \Guzzle\Service\Description\ServiceDescription::factory(array( "operations" => array( "test" => array( "responseClass" => "Foo" ) ), "models" => array( "Foo" => array( "type" => "object", "properties" => array( "baz" => array( "location" => "body" ) ) ) ) ));
		$operation = $description->getOperation("test");
		$op = new \Guzzle\Service\Command\OperationCommand(array( ), $operation);
		$op->setResponseParser($parser)->setClient(new \Guzzle\Service\Client());
		$brokenXml = "<broken><><><<xml>>>>>";
		$op->prepare()->setResponse(new \Guzzle\Http\Message\Response(200, array( "Content-Type" => "application/xml" ), $brokenXml), true);
		$result = $op->execute();
		$this->assertEquals(array( "baz" ), $result->getKeys());
		$this->assertEquals($brokenXml, (string) $result["baz"]);
	}
	public function testVisitsAdditionalProperties() 
	{
		$parser = \Guzzle\Service\Command\OperationResponseParser::getInstance();
		$description = \Guzzle\Service\Description\ServiceDescription::factory(array( "operations" => array( "test" => array( "responseClass" => "Foo" ) ), "models" => array( "Foo" => array( "type" => "object", "properties" => array( "code" => array( "location" => "statusCode" ) ), "additionalProperties" => array( "location" => "json", "type" => "object", "properties" => array( "a" => array( "type" => "string", "filters" => "strtoupper" ) ) ) ) ) ));
		$operation = $description->getOperation("test");
		$op = new \Guzzle\Service\Command\OperationCommand(array( ), $operation);
		$op->setResponseParser($parser)->setClient(new \Guzzle\Service\Client());
		$json = "[{\"a\":\"test\"},{\"a\":\"baz\"}]";
		$op->prepare()->setResponse(new \Guzzle\Http\Message\Response(200, array( "Content-Type" => "application/json" ), $json), true);
		$result = $op->execute()->toArray();
		$this->assertEquals(array( "code" => 200, array( "a" => "TEST" ), array( "a" => "BAZ" ) ), $result);
	}
	public function testAdditionalPropertiesDisabledDiscardsData() 
	{
		$parser = \Guzzle\Service\Command\OperationResponseParser::getInstance();
		$description = \Guzzle\Service\Description\ServiceDescription::factory(array( "operations" => array( "test" => array( "responseClass" => "Foo" ) ), "models" => array( "Foo" => array( "type" => "object", "additionalProperties" => false, "properties" => array( "name" => array( "location" => "json", "type" => "string" ), "nested" => array( "location" => "json", "type" => "object", "additionalProperties" => false, "properties" => array( "width" => array( "type" => "integer" ) ) ), "code" => array( "location" => "statusCode" ) ) ) ) ));
		$operation = $description->getOperation("test");
		$op = new \Guzzle\Service\Command\OperationCommand(array( ), $operation);
		$op->setResponseParser($parser)->setClient(new \Guzzle\Service\Client());
		$json = "{\"name\":\"test\", \"volume\":2.0, \"nested\":{\"width\":10,\"bogus\":1}}";
		$op->prepare()->setResponse(new \Guzzle\Http\Message\Response(200, array( "Content-Type" => "application/json" ), $json), true);
		$result = $op->execute()->toArray();
		$this->assertEquals(array( "name" => "test", "nested" => array( "width" => 10 ), "code" => 200 ), $result);
	}
	public function testCreatesCustomResponseClassInterface() 
	{
		$parser = \Guzzle\Service\Command\OperationResponseParser::getInstance();
		$description = \Guzzle\Service\Description\ServiceDescription::factory(array( "operations" => array( "test" => array( "responseClass" => "Guzzle\\Tests\\Mock\\CustomResponseModel" ) ) ));
		$operation = $description->getOperation("test");
		$op = new \Guzzle\Service\Command\OperationCommand(array( ), $operation);
		$op->setResponseParser($parser)->setClient(new \Guzzle\Service\Client());
		$op->prepare()->setResponse(new \Guzzle\Http\Message\Response(200, array( "Content-Type" => "application/json" ), "hi!"), true);
		$result = $op->execute();
		$this->assertInstanceOf("Guzzle\\Tests\\Mock\\CustomResponseModel", $result);
		$this->assertSame($op, $result->command);
	}
	public function testEnsuresResponseClassExists() 
	{
		$parser = \Guzzle\Service\Command\OperationResponseParser::getInstance();
		$description = \Guzzle\Service\Description\ServiceDescription::factory(array( "operations" => array( "test" => array( "responseClass" => "Foo\\Baz\\Bar" ) ) ));
		$operation = $description->getOperation("test");
		$op = new \Guzzle\Service\Command\OperationCommand(array( ), $operation);
		$op->setResponseParser($parser)->setClient(new \Guzzle\Service\Client());
		$op->prepare()->setResponse(new \Guzzle\Http\Message\Response(200, array( "Content-Type" => "application/json" ), "hi!"), true);
		$op->execute();
	}
	public function testEnsuresResponseClassImplementsResponseClassInterface() 
	{
		$parser = \Guzzle\Service\Command\OperationResponseParser::getInstance();
		$description = \Guzzle\Service\Description\ServiceDescription::factory(array( "operations" => array( "test" => array( "responseClass" => "Guzzle\\Tests\\Service\\Command\\OperationResponseParserTest" ) ) ));
		$operation = $description->getOperation("test");
		$op = new \Guzzle\Service\Command\OperationCommand(array( ), $operation);
		$op->setResponseParser($parser)->setClient(new \Guzzle\Service\Client());
		$op->prepare()->setResponse(new \Guzzle\Http\Message\Response(200, array( "Content-Type" => "application/json" ), "hi!"), true);
		$op->execute();
	}
	protected function getDescription() 
	{
		return \Guzzle\Service\Description\ServiceDescription::factory(array( "operations" => array( "test" => array( "responseClass" => "Foo" ) ), "models" => array( "Foo" => array( "type" => "object", "properties" => array( "baz" => array( "type" => "string", "location" => "json" ), "code" => array( "location" => "statusCode" ), "phrase" => array( "location" => "reasonPhrase" ) ) ) ) ));
	}
	public function testCanAddListenerToParseDomainObjects() 
	{
		$client = new \Guzzle\Service\Client();
		$client->setDescription(\Guzzle\Service\Description\ServiceDescription::factory(array( "operations" => array( "test" => array( "responseClass" => "FooBazBar" ) ) )));
		$foo = new \stdClass();
		$client->getEventDispatcher()->addListener("command.parse_response", function($e) use ($foo) 
		{
			$e["result"] = $foo;
		}
		);
		$command = $client->getCommand("test");
		$command->prepare()->setResponse(new \Guzzle\Http\Message\Response(200), true);
		$result = $command->execute();
		$this->assertSame($result, $foo);
	}
	public function testAdditionalPropertiesWithRefAreResolved() 
	{
		$parser = \Guzzle\Service\Command\OperationResponseParser::getInstance();
		$description = \Guzzle\Service\Description\ServiceDescription::factory(array( "operations" => array( "test" => array( "responseClass" => "Foo" ) ), "models" => array( "Baz" => array( "type" => "string" ), "Foo" => array( "type" => "object", "additionalProperties" => array( "\$ref" => "Baz", "location" => "json" ) ) ) ));
		$operation = $description->getOperation("test");
		$op = new \Guzzle\Service\Command\OperationCommand(array( ), $operation);
		$op->setResponseParser($parser)->setClient(new \Guzzle\Service\Client());
		$json = "{\"a\":\"a\",\"b\":\"b\",\"c\":\"c\"}";
		$op->prepare()->setResponse(new \Guzzle\Http\Message\Response(200, array( "Content-Type" => "application/json" ), $json), true);
		$result = $op->execute()->toArray();
		$this->assertEquals(array( "a" => "a", "b" => "b", "c" => "c" ), $result);
	}
}
?>