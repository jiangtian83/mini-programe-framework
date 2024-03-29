<?php  namespace Guzzle\Tests\Service\Command;
class CommandTest extends AbstractCommandTest 
{
	public function testConstructorAddsDefaultParams() 
	{
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$this->assertEquals("123", $command->get("test"));
		$this->assertFalse($command->isPrepared());
		$this->assertFalse($command->isExecuted());
	}
	public function testDeterminesShortName() 
	{
		$api = new \Guzzle\Service\Description\Operation(array( "name" => "foobar" ));
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand(array( ), $api);
		$this->assertEquals("foobar", $command->getName());
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$this->assertEquals("mock_command", $command->getName());
		$command = new \Guzzle\Tests\Service\Mock\Command\Sub\Sub();
		$this->assertEquals("sub.sub", $command->getName());
	}
	public function testGetRequestThrowsExceptionBeforePreparation() 
	{
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$command->getRequest();
	}
	public function testGetResponseExecutesCommandsWhenNeeded() 
	{
		$response = new \Guzzle\Http\Message\Response(200);
		$client = $this->getClient();
		$this->setMockResponse($client, array( $response ));
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$command->setClient($client);
		$this->assertSame($response, $command->getResponse());
		$this->assertSame($response, $command->getResponse());
	}
	public function testGetResultExecutesCommandsWhenNeeded() 
	{
		$response = new \Guzzle\Http\Message\Response(200);
		$client = $this->getClient();
		$this->setMockResponse($client, array( $response ));
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$command->setClient($client);
		$this->assertSame($response, $command->getResult());
		$this->assertSame($response, $command->getResult());
	}
	public function testSetClient() 
	{
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$client = $this->getClient();
		$command->setClient($client);
		$this->assertEquals($client, $command->getClient());
		unset($client);
		unset($command);
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$client = $this->getClient();
		$command->setClient($client)->prepare();
		$this->assertEquals($client, $command->getClient());
		$this->assertTrue($command->isPrepared());
	}
	public function testExecute() 
	{
		$client = $this->getClient();
		$response = new \Guzzle\Http\Message\Response(200, array( "Content-Type" => "application/xml" ), "<xml><data>123</data></xml>");
		$this->setMockResponse($client, array( $response ));
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$this->assertSame($command, $command->setClient($client));
		$this->assertInstanceOf("SimpleXMLElement", $command->execute());
		$this->assertTrue($command->isPrepared());
		$this->assertTrue($command->isExecuted());
		$this->assertSame($response, $command->getResponse());
		$this->assertInstanceOf("Guzzle\\Http\\Message\\Request", $command->getRequest());
		$this->assertInstanceOf("SimpleXMLElement", $command->getResult());
		$this->assertEquals("123", (string) $command->getResult()->data);
	}
	public function testConvertsJsonResponsesToArray() 
	{
		$client = $this->getClient();
		$this->setMockResponse($client, array( new \Guzzle\Http\Message\Response(200, array( "Content-Type" => "application/json" ), "{ \"key\": \"Hi!\" }") ));
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$command->setClient($client);
		$command->execute();
		$this->assertEquals(array( "key" => "Hi!" ), $command->getResult());
	}
	public function testConvertsInvalidJsonResponsesToArray() 
	{
		$json = "{ \"key\": \"Hi!\" }invalid";
		if( json_decode($json) && JSON_ERROR_NONE === json_last_error() ) 
		{
			$this->markTestSkipped("php-pecl-json library regression issues");
		}
		$client = $this->getClient();
		$this->setMockResponse($client, array( new \Guzzle\Http\Message\Response(200, array( "Content-Type" => "application/json" ), $json) ));
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$command->setClient($client);
		$command->execute();
	}
	public function testProcessResponseIsNotXml() 
	{
		$client = $this->getClient();
		$this->setMockResponse($client, array( new \Guzzle\Http\Message\Response(200, array( "Content-Type" => "application/octet-stream" ), "abc,def,ghi") ));
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$client->execute($command);
		$this->assertFalse($command->getResult() instanceof \SimpleXMLElement);
	}
	public function testExecuteThrowsExceptionWhenNoClientIsSet() 
	{
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$command->execute();
	}
	public function testPrepareThrowsExceptionWhenNoClientIsSet() 
	{
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$command->prepare();
	}
	public function testCommandsAllowsCustomRequestHeaders() 
	{
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$command->getRequestHeaders()->set("test", "123");
		$this->assertInstanceOf("Guzzle\\Common\\Collection", $command->getRequestHeaders());
		$this->assertEquals("123", $command->getRequestHeaders()->get("test"));
		$command->setClient($this->getClient())->prepare();
		$this->assertEquals("123", (string) $command->getRequest()->getHeader("test"));
	}
	public function testCommandsAllowsCustomRequestHeadersAsArray() 
	{
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand(array( \Guzzle\Service\Command\AbstractCommand::HEADERS_OPTION => array( "Foo" => "Bar" ) ));
		$this->assertInstanceOf("Guzzle\\Common\\Collection", $command->getRequestHeaders());
		$this->assertEquals("Bar", $command->getRequestHeaders()->get("Foo"));
	}
	private function getOperation() 
	{
		return new \Guzzle\Service\Description\Operation(array( "name" => "foobar", "httpMethod" => "POST", "class" => "Guzzle\\Tests\\Service\\Mock\\Command\\MockCommand", "parameters" => array( "test" => array( "default" => "123", "type" => "string" ) ) ));
	}
	public function testCommandsUsesOperation() 
	{
		$api = $this->getOperation();
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand(array( ), $api);
		$this->assertSame($api, $command->getOperation());
		$command->setClient($this->getClient())->prepare();
		$this->assertEquals("123", $command->get("test"));
		$this->assertSame($api, $command->getOperation($api));
	}
	public function testCloneMakesNewRequest() 
	{
		$client = $this->getClient();
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand(array( ), $this->getOperation());
		$command->setClient($client);
		$command->prepare();
		$this->assertTrue($command->isPrepared());
		$command2 = clone $command;
		$this->assertFalse($command2->isPrepared());
	}
	public function testHasOnCompleteMethod() 
	{
		$that = $this;
		$called = 0;
		$testFunction = function($command) use (&$called, $that) 
		{
			$called++;
			$that->assertInstanceOf("Guzzle\\Service\\Command\\CommandInterface", $command);
		}
		;
		$client = $this->getClient();
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand(array( "command.on_complete" => $testFunction ), $this->getOperation());
		$command->setClient($client);
		$command->prepare()->setResponse(new \Guzzle\Http\Message\Response(200), true);
		$command->execute();
		$this->assertEquals(1, $called);
	}
	public function testOnCompleteMustBeCallable() 
	{
		$client = $this->getClient();
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$command->setOnComplete("foo");
	}
	public function testCanSetResultManually() 
	{
		$client = $this->getClient();
		$client->getEventDispatcher()->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array( new \Guzzle\Http\Message\Response(200) )));
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$client->execute($command);
		$command->setResult("foo!");
		$this->assertEquals("foo!", $command->getResult());
	}
	public function testCanInitConfig() 
	{
		$command = $this->getMockBuilder("Guzzle\\Service\\Command\\AbstractCommand")->setConstructorArgs(array( array( "foo" => "bar" ), new \Guzzle\Service\Description\Operation(array( "parameters" => array( "baz" => new \Guzzle\Service\Description\Parameter(array( "default" => "baaar" )) ) )) ))->getMockForAbstractClass();
		$this->assertEquals("bar", $command["foo"]);
		$this->assertEquals("baaar", $command["baz"]);
	}
	public function testAddsCurlOptionsToRequestsWhenPreparing() 
	{
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand(array( "foo" => "bar", "curl.options" => array( "CURLOPT_PROXYPORT" => 8080 ) ));
		$client = new \Guzzle\Service\Client();
		$command->setClient($client);
		$request = $command->prepare();
		$this->assertEquals(8080, $request->getCurlOptions()->get(CURLOPT_PROXYPORT));
	}
	public function testIsInvokable() 
	{
		$client = $this->getClient();
		$response = new \Guzzle\Http\Message\Response(200);
		$this->setMockResponse($client, array( $response ));
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$command->setClient($client);
		$this->assertSame($response, $command());
	}
	public function testCreatesDefaultOperation() 
	{
		$command = $this->getMockBuilder("Guzzle\\Service\\Command\\AbstractCommand")->getMockForAbstractClass();
		$this->assertInstanceOf("Guzzle\\Service\\Description\\Operation", $command->getOperation());
	}
	public function testAllowsValidatorToBeInjected() 
	{
		$command = $this->getMockBuilder("Guzzle\\Service\\Command\\AbstractCommand")->getMockForAbstractClass();
		$v = new \Guzzle\Service\Description\SchemaValidator();
		$command->setValidator($v);
		$this->assertSame($v, $this->readAttribute($command, "validator"));
	}
	public function testCanDisableValidation() 
	{
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$command->setClient(new \Guzzle\Service\Client());
		$v = $this->getMockBuilder("Guzzle\\Service\\Description\\SchemaValidator")->setMethods(array( "validate" ))->getMock();
		$v->expects($this->never())->method("validate");
		$command->setValidator($v);
		$command->set(\Guzzle\Service\Command\AbstractCommand::DISABLE_VALIDATION, true);
		$command->prepare();
	}
	public function testValidatorDoesNotUpdateNonDefaultValues() 
	{
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand(array( "test" => 123, "foo" => "bar" ));
		$command->setClient(new \Guzzle\Service\Client());
		$command->prepare();
		$this->assertEquals(123, $command->get("test"));
		$this->assertEquals("bar", $command->get("foo"));
	}
	public function testValidatorUpdatesDefaultValues() 
	{
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$command->setClient(new \Guzzle\Service\Client());
		$command->prepare();
		$this->assertEquals(123, $command->get("test"));
		$this->assertEquals("abc", $command->get("_internal"));
	}
	public function testValidatesCommandBeforeSending() 
	{
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$command->setClient(new \Guzzle\Service\Client());
		$v = $this->getMockBuilder("Guzzle\\Service\\Description\\SchemaValidator")->setMethods(array( "validate", "getErrors" ))->getMock();
		$v->expects($this->any())->method("validate")->will($this->returnValue(false));
		$v->expects($this->any())->method("getErrors")->will($this->returnValue(array( "[Foo] Baz", "[Bar] Boo" )));
		$command->setValidator($v);
		$command->prepare();
	}
	public function testValidatesAdditionalParameters() 
	{
		$description = \Guzzle\Service\Description\ServiceDescription::factory(array( "operations" => array( "foo" => array( "parameters" => array( "baz" => array( "type" => "integer" ) ), "additionalParameters" => array( "type" => "string" ) ) ) ));
		$client = new \Guzzle\Service\Client();
		$client->setDescription($description);
		$command = $client->getCommand("foo", array( "abc" => false, "command.headers" => array( "foo" => "bar" ) ));
		$command->prepare();
	}
	public function testCanAccessValidationErrorsFromCommand() 
	{
		$validationErrors = array( "[Foo] Baz", "[Bar] Boo" );
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$command->setClient(new \Guzzle\Service\Client());
		$this->assertFalse($command->getValidationErrors());
		$v = $this->getMockBuilder("Guzzle\\Service\\Description\\SchemaValidator")->setMethods(array( "validate", "getErrors" ))->getMock();
		$v->expects($this->any())->method("getErrors")->will($this->returnValue($validationErrors));
		$command->setValidator($v);
		$this->assertEquals($validationErrors, $command->getValidationErrors());
	}
	public function testCanChangeResponseBody() 
	{
		$body = \Guzzle\Http\EntityBody::factory();
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$command->setClient(new \Guzzle\Service\Client());
		$command->set(\Guzzle\Service\Command\AbstractCommand::RESPONSE_BODY, $body);
		$request = $command->prepare();
		$this->assertSame($body, $this->readAttribute($request, "responseBody"));
	}
}
?>