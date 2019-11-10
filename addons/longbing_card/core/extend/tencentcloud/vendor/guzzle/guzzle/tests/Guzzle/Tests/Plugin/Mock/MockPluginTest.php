<?php  namespace Guzzle\Tests\Plugin\Mock;
class MockPluginTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testDescribesSubscribedEvents() 
	{
		$this->assertInternalType("array", \Guzzle\Plugin\Mock\MockPlugin::getSubscribedEvents());
	}
	public function testDescribesEvents() 
	{
		$this->assertInternalType("array", \Guzzle\Plugin\Mock\MockPlugin::getAllEvents());
	}
	public function testCanBeTemporary() 
	{
		$plugin = new \Guzzle\Plugin\Mock\MockPlugin();
		$this->assertFalse($plugin->isTemporary());
		$plugin = new \Guzzle\Plugin\Mock\MockPlugin(null, true);
		$this->assertTrue($plugin->isTemporary());
	}
	public function testIsCountable() 
	{
		$plugin = new \Guzzle\Plugin\Mock\MockPlugin();
		$plugin->addResponse(\Guzzle\Http\Message\Response::fromMessage("HTTP/1.1 200 OK\r\nContent-Length: 0\r\n\r\n"));
		$this->assertEquals(1, count($plugin));
	}
	public function testCanClearQueue() 
	{
		$plugin = new \Guzzle\Plugin\Mock\MockPlugin();
		$plugin->addResponse(\Guzzle\Http\Message\Response::fromMessage("HTTP/1.1 200 OK\r\nContent-Length: 0\r\n\r\n"));
		$plugin->clearQueue();
		$this->assertEquals(0, count($plugin));
	}
	public function testCanInspectQueue() 
	{
		$plugin = new \Guzzle\Plugin\Mock\MockPlugin();
		$this->assertInternalType("array", $plugin->getQueue());
		$plugin->addResponse(\Guzzle\Http\Message\Response::fromMessage("HTTP/1.1 200 OK\r\nContent-Length: 0\r\n\r\n"));
		$queue = $plugin->getQueue();
		$this->assertInternalType("array", $queue);
		$this->assertEquals(1, count($queue));
	}
	public function testRetrievesResponsesFromFiles() 
	{
		$response = \Guzzle\Plugin\Mock\MockPlugin::getMockFile(__DIR__ . "/../../TestData/mock_response");
		$this->assertInstanceOf("Guzzle\\Http\\Message\\Response", $response);
		$this->assertEquals(200, $response->getStatusCode());
	}
	public function testThrowsExceptionWhenResponseFileIsNotFound() 
	{
		\Guzzle\Plugin\Mock\MockPlugin::getMockFile("missing/filename");
	}
	public function testInvalidResponsesThrowAnException() 
	{
		$p = new \Guzzle\Plugin\Mock\MockPlugin();
		$p->addResponse($this);
	}
	public function testAddsResponseObjectsToQueue() 
	{
		$p = new \Guzzle\Plugin\Mock\MockPlugin();
		$response = \Guzzle\Http\Message\Response::fromMessage("HTTP/1.1 200 OK\r\nContent-Length: 0\r\n\r\n");
		$p->addResponse($response);
		$this->assertEquals(array( $response ), $p->getQueue());
	}
	public function testAddsResponseFilesToQueue() 
	{
		$p = new \Guzzle\Plugin\Mock\MockPlugin();
		$p->addResponse(__DIR__ . "/../../TestData/mock_response");
		$this->assertEquals(1, count($p));
	}
	public function testAddsMockResponseToRequestFromClient() 
	{
		$p = new \Guzzle\Plugin\Mock\MockPlugin();
		$response = \Guzzle\Plugin\Mock\MockPlugin::getMockFile(__DIR__ . "/../../TestData/mock_response");
		$p->addResponse($response);
		$client = new \Guzzle\Http\Client("http://127.0.0.1:123/");
		$client->getEventDispatcher()->addSubscriber($p, 9999);
		$request = $client->get();
		$request->send();
		$this->assertSame($response, $request->getResponse());
		$this->assertEquals(0, count($p));
	}
	public function testUpdateThrowsExceptionWhenEmpty() 
	{
		$p = new \Guzzle\Plugin\Mock\MockPlugin();
		$p->onRequestBeforeSend(new \Guzzle\Common\Event());
	}
	public function testDetachesTemporaryWhenEmpty() 
	{
		$p = new \Guzzle\Plugin\Mock\MockPlugin(null, true);
		$p->addResponse(\Guzzle\Plugin\Mock\MockPlugin::getMockFile(__DIR__ . "/../../TestData/mock_response"));
		$client = new \Guzzle\Http\Client("http://127.0.0.1:123/");
		$client->getEventDispatcher()->addSubscriber($p, 9999);
		$request = $client->get();
		$request->send();
		$this->assertFalse($this->hasSubscriber($client, $p));
	}
	public function testLoadsResponsesFromConstructor() 
	{
		$p = new \Guzzle\Plugin\Mock\MockPlugin(array( new \Guzzle\Http\Message\Response(200) ));
		$this->assertEquals(1, $p->count());
	}
	public function testStoresMockedRequests() 
	{
		$p = new \Guzzle\Plugin\Mock\MockPlugin(array( new \Guzzle\Http\Message\Response(200), new \Guzzle\Http\Message\Response(200) ));
		$client = new \Guzzle\Http\Client("http://127.0.0.1:123/");
		$client->getEventDispatcher()->addSubscriber($p, 9999);
		$request1 = $client->get();
		$request1->send();
		$this->assertEquals(array( $request1 ), $p->getReceivedRequests());
		$request2 = $client->get();
		$request2->send();
		$this->assertEquals(array( $request1, $request2 ), $p->getReceivedRequests());
		$p->flush();
		$this->assertEquals(array( ), $p->getReceivedRequests());
	}
	public function testReadsBodiesFromMockedRequests() 
	{
		$p = new \Guzzle\Plugin\Mock\MockPlugin(array( new \Guzzle\Http\Message\Response(200) ));
		$p->readBodies(true);
		$client = new \Guzzle\Http\Client("http://127.0.0.1:123/");
		$client->getEventDispatcher()->addSubscriber($p, 9999);
		$body = \Guzzle\Http\EntityBody::factory("foo");
		$request = $client->put();
		$request->setBody($body);
		$request->send();
		$this->assertEquals(3, $body->ftell());
	}
	public function testCanMockBadRequestExceptions() 
	{
		$client = new \Guzzle\Http\Client("http://127.0.0.1:123/");
		$ex = new \Guzzle\Http\Exception\CurlException("Foo");
		$mock = new \Guzzle\Plugin\Mock\MockPlugin(array( $ex ));
		$client->addSubscriber($mock);
		$request = $client->get("foo");
		try 
		{
			$request->send();
			$this->fail("Did not dequeue an exception");
		}
		catch( \Guzzle\Http\Exception\CurlException $e ) 
		{
			$this->assertSame($e, $ex);
			$this->assertSame($request, $ex->getRequest());
		}
	}
}
?>