<?php  namespace Guzzle\Tests\Plugin\Log;
class LogPluginTest extends \Guzzle\Tests\GuzzleTestCase 
{
	protected $adapter = NULL;
	public function setUp() 
	{
		$this->adapter = new \Guzzle\Log\ClosureLogAdapter(function($message) 
		{
			echo $message;
		}
		);
	}
	public function testIgnoresCurlEventsWhenNotWiringBodies() 
	{
		$p = new \Guzzle\Plugin\Log\LogPlugin($this->adapter);
		$this->assertNotEmpty($p->getSubscribedEvents());
		$event = new \Guzzle\Common\Event(array( "request" => new \Guzzle\Http\Message\Request("GET", "http://foo.com") ));
		$p->onCurlRead($event);
		$p->onCurlWrite($event);
		$p->onRequestBeforeSend($event);
	}
	public function testLogsWhenComplete() 
	{
		$output = "";
		$p = new \Guzzle\Plugin\Log\LogPlugin(new \Guzzle\Log\ClosureLogAdapter(function($message) use (&$output) 
		{
			$output = $message;
		}
		), "{method} {resource} | {code} {res_body}");
		$p->onRequestSent(new \Guzzle\Common\Event(array( "request" => new \Guzzle\Http\Message\Request("GET", "http://foo.com"), "response" => new \Guzzle\Http\Message\Response(200, array( ), "Foo") )));
		$this->assertEquals("GET / | 200 Foo", $output);
	}
	public function testWiresBodiesWhenNeeded() 
	{
		$client = new \Guzzle\Http\Client($this->getServer()->getUrl());
		$plugin = new \Guzzle\Plugin\Log\LogPlugin($this->adapter, "{req_body} | {res_body}", true);
		$client->getEventDispatcher()->addSubscriber($plugin);
		$request = $client->put();
		$this->getServer()->enqueue("HTTP/1.1 200 OK\r\nContent-Length: 4\r\n\r\nsend");
		$stream = fopen($this->getServer()->getUrl(), "r");
		$request->setBody(\Guzzle\Http\EntityBody::factory($stream, 4));
		$tmpFile = tempnam(sys_get_temp_dir(), "non_repeatable");
		$request->setResponseBody(\Guzzle\Http\EntityBody::factory(fopen($tmpFile, "w")));
		$this->getServer()->enqueue("HTTP/1.1 200 OK\r\nContent-Length: 8\r\n\r\nresponse");
		ob_start();
		$request->send();
		$message = ob_get_clean();
		unlink($tmpFile);
		$this->assertContains("send", $message);
		$this->assertContains("response", $message);
	}
	public function testHasHelpfulStaticFactoryMethod() 
	{
		$s = fopen("php://temp", "r+");
		$client = new \Guzzle\Http\Client();
		$client->addSubscriber(\Guzzle\Plugin\Log\LogPlugin::getDebugPlugin(true, $s));
		$request = $client->put("http://foo.com", array( "Content-Type" => "Foo" ), "Bar");
		$request->setresponse(new \Guzzle\Http\Message\Response(200), true);
		$request->send();
		rewind($s);
		$contents = stream_get_contents($s);
		$this->assertContains("# Request:", $contents);
		$this->assertContainsIns("PUT / HTTP/1.1", $contents);
		$this->assertContains("# Response:", $contents);
		$this->assertContainsIns("HTTP/1.1 200 OK", $contents);
		$this->assertContains("# Errors:", $contents);
	}
}
?>