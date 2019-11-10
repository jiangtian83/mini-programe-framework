<?php  namespace Guzzle\Tests\Plugin\Cache;
class CallbackCanCacheStrategyTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testConstructorEnsuresCallbackIsCallable() 
	{
		$p = new \Guzzle\Plugin\Cache\CallbackCanCacheStrategy(new \stdClass());
	}
	public function testUsesCallback() 
	{
		$c = new \Guzzle\Plugin\Cache\CallbackCanCacheStrategy(function($request) 
		{
			return true;
		}
		);
		$this->assertTrue($c->canCacheRequest(new \Guzzle\Http\Message\Request("DELETE", "http://www.foo.com")));
	}
	public function testIntegrationWithCachePlugin() 
	{
		$c = new \Guzzle\Plugin\Cache\CallbackCanCacheStrategy(function($request) 
		{
			return true;
		}
		, function($response) 
		{
			return true;
		}
		);
		$request = new \Guzzle\Http\Message\Request("DELETE", "http://www.foo.com");
		$response = \Guzzle\Http\Message\Response::fromMessage("HTTP/1.1 200 OK\r\n" . "Expires: Mon, 26 Jul 1997 05:00:00 GMT\r\n" . "Last-Modified: Wed, 09 Jan 2013 08:48:53 GMT\r\n" . "Content-Length: 2\r\n" . "Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0\r\n\r\n" . "hi");
		$this->assertTrue($c->canCacheRequest($request));
		$this->assertTrue($c->canCacheResponse($response));
		$s = $this->getMockBuilder("Guzzle\\Plugin\\Cache\\DefaultCacheStorage")->setConstructorArgs(array( new \Guzzle\Cache\DoctrineCacheAdapter(new \Doctrine\Common\Cache\ArrayCache()) ))->setMethods(array( "fetch" ))->getMockForAbstractClass();
		$s->expects($this->once())->method("fetch")->will($this->returnValue($response));
		$plugin = new \Guzzle\Plugin\Cache\CachePlugin(array( "can_cache" => $c, "storage" => $s ));
		$plugin->onRequestBeforeSend(new \Guzzle\Common\Event(array( "request" => $request )));
		$this->assertEquals(200, $request->getResponse()->getStatusCode());
		$this->assertEquals("hi", $request->getResponse()->getBody(true));
	}
}
?>