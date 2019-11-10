<?php  namespace Guzzle\Tests\Service;
class ClientTest extends \Guzzle\Tests\GuzzleTestCase 
{
	protected $service = NULL;
	protected $serviceTest = NULL;
	public function setUp() 
	{
		$this->serviceTest = new \Guzzle\Service\Description\ServiceDescription(array( "test_command" => new \Guzzle\Service\Description\Operation(array( "doc" => "documentationForCommand", "method" => "DELETE", "class" => "Guzzle\\Tests\\Service\\Mock\\Command\\MockCommand", "args" => array( "bucket" => array( "required" => true ), "key" => array( "required" => true ) ) )) ));
		$this->service = \Guzzle\Service\Description\ServiceDescription::factory(__DIR__ . "/../TestData/test_service.json");
	}
	public function testAllowsCustomClientParameters() 
	{
		$client = new Mock\MockClient(null, array( \Guzzle\Service\Client::COMMAND_PARAMS => array( \Guzzle\Service\Command\AbstractCommand::RESPONSE_PROCESSING => "foo" ) ));
		$command = $client->getCommand("mock_command");
		$this->assertEquals("foo", $command->get(\Guzzle\Service\Command\AbstractCommand::RESPONSE_PROCESSING));
	}
	public function testFactoryCreatesClient() 
	{
		$client = \Guzzle\Service\Client::factory(array( "base_url" => "http://www.test.com/", "test" => "123" ));
		$this->assertEquals("http://www.test.com/", $client->getBaseUrl());
		$this->assertEquals("123", $client->getConfig("test"));
	}
	public function testFactoryDoesNotRequireBaseUrl() 
	{
		$client = \Guzzle\Service\Client::factory();
	}
	public function testDescribesEvents() 
	{
		$this->assertInternalType("array", \Guzzle\Service\Client::getAllEvents());
	}
	public function testExecutesCommands() 
	{
		$this->getServer()->flush();
		$this->getServer()->enqueue("HTTP/1.1 200 OK\r\nContent-Length: 0\r\n\r\n");
		$client = new \Guzzle\Service\Client($this->getServer()->getUrl());
		$cmd = new Mock\Command\MockCommand();
		$client->execute($cmd);
		$this->assertInstanceOf("Guzzle\\Http\\Message\\Response", $cmd->getResponse());
		$this->assertInstanceOf("Guzzle\\Http\\Message\\Response", $cmd->getResult());
		$this->assertEquals(1, count($this->getServer()->getReceivedRequests(false)));
	}
	public function testExecutesCommandsWithArray() 
	{
		$client = new \Guzzle\Service\Client("http://www.test.com/");
		$client->getEventDispatcher()->addSubscriber(new \Guzzle\Plugin\Mock\MockPlugin(array( new \Guzzle\Http\Message\Response(200), new \Guzzle\Http\Message\Response(200) )));
		$set = array( new Mock\Command\MockCommand(), new Mock\Command\MockCommand() );
		$client->execute($set);
		$this->assertTrue($set[0]->isExecuted());
		$this->assertTrue($set[1]->isExecuted());
	}
	public function testThrowsExceptionWhenInvalidCommandIsExecuted() 
	{
		$client = new \Guzzle\Service\Client();
		$client->execute(new \stdClass());
	}
	public function testThrowsExceptionWhenMissingCommand() 
	{
		$client = new \Guzzle\Service\Client();
		$mock = $this->getMock("Guzzle\\Service\\Command\\Factory\\FactoryInterface");
		$mock->expects($this->any())->method("factory")->with($this->equalTo("test"))->will($this->returnValue(null));
		$client->setCommandFactory($mock);
		$client->getCommand("test");
	}
	public function testCreatesCommandsUsingCommandFactory() 
	{
		$mockCommand = new Mock\Command\MockCommand();
		$client = new Mock\MockClient();
		$mock = $this->getMock("Guzzle\\Service\\Command\\Factory\\FactoryInterface");
		$mock->expects($this->any())->method("factory")->with($this->equalTo("foo"))->will($this->returnValue($mockCommand));
		$client->setCommandFactory($mock);
		$command = $client->getCommand("foo", array( "acl" => "123" ));
		$this->assertSame($mockCommand, $command);
		$command = $client->getCommand("foo", array( "acl" => "123" ));
		$this->assertSame($mockCommand, $command);
		$this->assertSame($client, $command->getClient());
	}
	public function testOwnsServiceDescription() 
	{
		$client = new Mock\MockClient();
		$this->assertNull($client->getDescription());
		$description = $this->getMock("Guzzle\\Service\\Description\\ServiceDescription");
		$this->assertSame($client, $client->setDescription($description));
		$this->assertSame($description, $client->getDescription());
	}
	public function testOwnsResourceIteratorFactory() 
	{
		$client = new Mock\MockClient();
		$method = new \ReflectionMethod($client, "getResourceIteratorFactory");
		$method->setAccessible(TRUE);
		$rf1 = $method->invoke($client);
		$rf = $this->readAttribute($client, "resourceIteratorFactory");
		$this->assertInstanceOf("Guzzle\\Service\\Resource\\ResourceIteratorClassFactory", $rf);
		$this->assertSame($rf1, $rf);
		$rf = new \Guzzle\Service\Resource\ResourceIteratorClassFactory("Guzzle\\Tests\\Service\\Mock");
		$client->setResourceIteratorFactory($rf);
		$this->assertNotSame($rf1, $rf);
	}
	public function testClientResetsRequestsBeforeExecutingCommands() 
	{
		$this->getServer()->flush();
		$this->getServer()->enqueue(array( "HTTP/1.1 200 OK\r\nContent-Length: 2\r\n\r\nHi", "HTTP/1.1 200 OK\r\nContent-Length: 1\r\n\r\nI" ));
		$client = new Mock\MockClient($this->getServer()->getUrl());
		$command = $client->getCommand("mock_command");
		$client->execute($command);
		$client->execute($command);
		$this->assertEquals("I", $command->getResponse()->getBody(true));
	}
	public function testClientCreatesIterators() 
	{
		$client = new Mock\MockClient();
		$iterator = $client->getIterator("mock_command", array( "foo" => "bar" ), array( "limit" => 10 ));
		$this->assertInstanceOf("Guzzle\\Tests\\Service\\Mock\\Model\\MockCommandIterator", $iterator);
		$this->assertEquals(10, $this->readAttribute($iterator, "limit"));
		$command = $this->readAttribute($iterator, "originalCommand");
		$this->assertEquals("bar", $command->get("foo"));
	}
	public function testClientCreatesIteratorsWithNoOptions() 
	{
		$client = new Mock\MockClient();
		$iterator = $client->getIterator("mock_command");
		$this->assertInstanceOf("Guzzle\\Tests\\Service\\Mock\\Model\\MockCommandIterator", $iterator);
	}
	public function testClientCreatesIteratorsWithCommands() 
	{
		$client = new Mock\MockClient();
		$command = new Mock\Command\MockCommand();
		$iterator = $client->getIterator($command);
		$this->assertInstanceOf("Guzzle\\Tests\\Service\\Mock\\Model\\MockCommandIterator", $iterator);
		$iteratorCommand = $this->readAttribute($iterator, "originalCommand");
		$this->assertSame($command, $iteratorCommand);
	}
	public function testClientHoldsInflector() 
	{
		$client = new Mock\MockClient();
		$this->assertInstanceOf("Guzzle\\Inflection\\MemoizingInflector", $client->getInflector());
		$inflector = new \Guzzle\Inflection\Inflector();
		$client->setInflector($inflector);
		$this->assertSame($inflector, $client->getInflector());
	}
	public function testClientAddsGlobalCommandOptions() 
	{
		$client = new Mock\MockClient("http://www.foo.com", array( \Guzzle\Service\Client::COMMAND_PARAMS => array( "mesa" => "bar" ) ));
		$command = $client->getCommand("mock_command");
		$this->assertEquals("bar", $command->get("mesa"));
	}
	public function testSupportsServiceDescriptionBaseUrls() 
	{
		$description = new \Guzzle\Service\Description\ServiceDescription(array( "baseUrl" => "http://foo.com" ));
		$client = new \Guzzle\Service\Client();
		$client->setDescription($description);
		$this->assertEquals("http://foo.com", $client->getBaseUrl());
	}
	public function testMergesDefaultCommandParamsCorrectly() 
	{
		$client = new Mock\MockClient("http://www.foo.com", array( \Guzzle\Service\Client::COMMAND_PARAMS => array( "mesa" => "bar", "jar" => "jar" ) ));
		$command = $client->getCommand("mock_command", array( "jar" => "test" ));
		$this->assertEquals("bar", $command->get("mesa"));
		$this->assertEquals("test", $command->get("jar"));
	}
	public function testWrapsSingleCommandExceptions() 
	{
		$client = new Mock\MockClient("http://foobaz.com");
		$mock = new \Guzzle\Plugin\Mock\MockPlugin(array( new \Guzzle\Http\Message\Response(401) ));
		$client->addSubscriber($mock);
		$client->execute(new Mock\Command\MockCommand());
	}
	public function testWrapsMultipleCommandExceptions() 
	{
		$client = new Mock\MockClient("http://foobaz.com");
		$mock = new \Guzzle\Plugin\Mock\MockPlugin(array( new \Guzzle\Http\Message\Response(200), new \Guzzle\Http\Message\Response(200), new \Guzzle\Http\Message\Response(404), new \Guzzle\Http\Message\Response(500) ));
		$client->addSubscriber($mock);
		$cmds = array( new Mock\Command\MockCommand(), new Mock\Command\MockCommand(), new Mock\Command\MockCommand(), new Mock\Command\MockCommand() );
		try 
		{
			$client->execute($cmds);
		}
		catch( \Guzzle\Service\Exception\CommandTransferException $e ) 
		{
			$this->assertEquals(2, count($e->getFailedRequests()));
			$this->assertEquals(2, count($e->getSuccessfulRequests()));
			$this->assertEquals(2, count($e->getFailedCommands()));
			$this->assertEquals(2, count($e->getSuccessfulCommands()));
			foreach( $e->getSuccessfulCommands() as $c ) 
			{
				$this->assertTrue($c->getResponse()->isSuccessful());
			}
			foreach( $e->getFailedCommands() as $c ) 
			{
				$this->assertFalse($c->getRequest()->getResponse()->isSuccessful());
			}
		}
	}
	public function testGetCommandAfterTwoSetDescriptions() 
	{
		$service1 = \Guzzle\Service\Description\ServiceDescription::factory(__DIR__ . "/../TestData/test_service.json");
		$service2 = \Guzzle\Service\Description\ServiceDescription::factory(__DIR__ . "/../TestData/test_service_3.json");
		$client = new Mock\MockClient();
		$client->setDescription($service1);
		$client->getCommand("foo_bar");
		$client->setDescription($service2);
		$client->getCommand("baz_qux");
	}
}
?>