<?php  namespace Guzzle\Tests\Plugin\Async;
class AsyncPluginTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testSubscribesToEvents() 
	{
		$events = \Guzzle\Plugin\Async\AsyncPlugin::getSubscribedEvents();
		$this->assertArrayHasKey("request.before_send", $events);
		$this->assertArrayHasKey("request.exception", $events);
		$this->assertArrayHasKey("curl.callback.progress", $events);
	}
	public function testEnablesProgressCallbacks() 
	{
		$p = new \Guzzle\Plugin\Async\AsyncPlugin();
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("PUT", "http://www.example.com");
		$event = new \Guzzle\Common\Event(array( "request" => $request ));
		$p->onBeforeSend($event);
		$this->assertEquals(true, $request->getCurlOptions()->get("progress"));
	}
	public function testAddsTimesOutAfterSending() 
	{
		$p = new \Guzzle\Plugin\Async\AsyncPlugin();
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("PUT", "http://www.example.com");
		$handle = \Guzzle\Http\Curl\CurlHandle::factory($request);
		$event = new \Guzzle\Common\Event(array( "request" => $request, "handle" => $handle->getHandle(), "uploaded" => 10, "upload_size" => 10, "downloaded" => 0 ));
		$p->onCurlProgress($event);
	}
	public function testEnsuresRequestIsSet() 
	{
		$p = new \Guzzle\Plugin\Async\AsyncPlugin();
		$event = new \Guzzle\Common\Event(array( "uploaded" => 10, "upload_size" => 10, "downloaded" => 0 ));
		$p->onCurlProgress($event);
	}
	public function testMasksCurlExceptions() 
	{
		$p = new \Guzzle\Plugin\Async\AsyncPlugin();
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("PUT", "http://www.example.com");
		$e = new \Guzzle\Http\Exception\CurlException("Error");
		$event = new \Guzzle\Common\Event(array( "request" => $request, "exception" => $e ));
		$p->onRequestTimeout($event);
		$this->assertEquals(\Guzzle\Http\Message\RequestInterface::STATE_COMPLETE, $request->getState());
		$this->assertEquals(200, $request->getResponse()->getStatusCode());
		$this->assertTrue($request->getResponse()->hasHeader("X-Guzzle-Async"));
	}
	public function testEnsuresIntegration() 
	{
		$this->getServer()->flush();
		$this->getServer()->enqueue("HTTP/1.1 204 FOO\r\nContent-Length: 4\r\n\r\ntest");
		$client = new \Guzzle\Http\Client($this->getServer()->getUrl());
		$request = $client->post("/", null, array( "foo" => "bar" ));
		$request->getEventDispatcher()->addSubscriber(new \Guzzle\Plugin\Async\AsyncPlugin());
		$request->send();
		$this->assertEquals("", $request->getResponse()->getBody(true));
		$this->assertTrue($request->getResponse()->hasHeader("X-Guzzle-Async"));
		$received = $this->getServer()->getReceivedRequests(true);
		$this->assertEquals("POST", $received[0]->getMethod());
	}
}
?>