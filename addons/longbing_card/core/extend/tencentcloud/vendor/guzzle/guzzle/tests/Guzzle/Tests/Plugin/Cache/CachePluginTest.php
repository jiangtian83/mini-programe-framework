<?php  namespace Guzzle\Tests\Plugin\Cache;
class CachePluginTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testAddsDefaultStorage() 
	{
		$plugin = new \Guzzle\Plugin\Cache\CachePlugin();
		$this->assertInstanceOf("Guzzle\\Plugin\\Cache\\CacheStorageInterface", $this->readAttribute($plugin, "storage"));
	}
	public function testAddsDefaultCollaborators() 
	{
		$this->assertNotEmpty(\Guzzle\Plugin\Cache\CachePlugin::getSubscribedEvents());
		$plugin = new \Guzzle\Plugin\Cache\CachePlugin(array( "storage" => $this->getMockBuilder("Guzzle\\Plugin\\Cache\\CacheStorageInterface")->getMockForAbstractClass() ));
		$this->assertInstanceOf("Guzzle\\Plugin\\Cache\\CacheStorageInterface", $this->readAttribute($plugin, "storage"));
		$this->assertInstanceOf("Guzzle\\Plugin\\Cache\\CanCacheStrategyInterface", $this->readAttribute($plugin, "canCache"));
		$this->assertInstanceOf("Guzzle\\Plugin\\Cache\\RevalidationInterface", $this->readAttribute($plugin, "revalidation"));
	}
	public function testAddsCallbackCollaborators() 
	{
		$this->assertNotEmpty(\Guzzle\Plugin\Cache\CachePlugin::getSubscribedEvents());
		$plugin = new \Guzzle\Plugin\Cache\CachePlugin(array( "can_cache" => function() 
		{
		}
		));
		$this->assertInstanceOf("Guzzle\\Plugin\\Cache\\CallbackCanCacheStrategy", $this->readAttribute($plugin, "canCache"));
	}
	public function testCanPassCacheAsOnlyArgumentToConstructor() 
	{
		$p = new \Guzzle\Plugin\Cache\CachePlugin(new \Guzzle\Cache\DoctrineCacheAdapter(new \Doctrine\Common\Cache\ArrayCache()));
		$p = new \Guzzle\Plugin\Cache\CachePlugin(new \Guzzle\Plugin\Cache\DefaultCacheStorage(new \Guzzle\Cache\DoctrineCacheAdapter(new \Doctrine\Common\Cache\ArrayCache())));
	}
	public function testUsesCreatedCacheStorage() 
	{
		$plugin = new \Guzzle\Plugin\Cache\CachePlugin(array( "adapter" => $this->getMockBuilder("Guzzle\\Cache\\CacheAdapterInterface")->getMockForAbstractClass() ));
		$this->assertInstanceOf("Guzzle\\Plugin\\Cache\\CacheStorageInterface", $this->readAttribute($plugin, "storage"));
	}
	public function testUsesProvidedOptions() 
	{
		$can = $this->getMockBuilder("Guzzle\\Plugin\\Cache\\CanCacheStrategyInterface")->getMockForAbstractClass();
		$revalidate = $this->getMockBuilder("Guzzle\\Plugin\\Cache\\RevalidationInterface")->getMockForAbstractClass();
		$plugin = new \Guzzle\Plugin\Cache\CachePlugin(array( "storage" => $this->getMockBuilder("Guzzle\\Plugin\\Cache\\CacheStorageInterface")->getMockForAbstractClass(), "can_cache" => $can, "revalidation" => $revalidate ));
		$this->assertSame($can, $this->readAttribute($plugin, "canCache"));
		$this->assertSame($revalidate, $this->readAttribute($plugin, "revalidation"));
	}
	public function satisfyProvider() 
	{
		$req1 = new \Guzzle\Http\Message\Request("GET", "http://foo.com", array( "Cache-Control" => "no-cache" ));
		return array( array( new \Guzzle\Http\Message\Request("GET", "http://foo.com", array( "Cache-Control" => "max-age=20" )), new \Guzzle\Http\Message\Response(200, array( "Age" => 100 )), false, false ), array( new \Guzzle\Http\Message\Request("GET", "http://foo.com"), new \Guzzle\Http\Message\Response(200, array( "Cache-Control" => "max-age=10", "Age" => 100 )), false, false ), array( new \Guzzle\Http\Message\Request("GET", "http://foo.com", array( "Cache-Control" => "max-stale=15" )), new \Guzzle\Http\Message\Response(200, array( "Cache-Control" => "max-age=90", "Age" => 100 )), true, false ), array( new \Guzzle\Http\Message\Request("GET", "http://foo.com", array( "Cache-Control" => "max-stale=5" )), new \Guzzle\Http\Message\Response(200, array( "Cache-Control" => "max-age=90", "Age" => 100 )), false, false ), array( $req1, new \Guzzle\Http\Message\Response(200), true, true ), array( new \Guzzle\Http\Message\Request("GET", "http://foo.com"), new \Guzzle\Http\Message\Response(200, array( "ETag" => "ABC", "Expires" => date("c", strtotime("+1 year")) )), true, true ) );
	}
	public function testChecksIfResponseCanSatisfyRequest($request, $response, $can, $revalidates) 
	{
		$didRevalidate = false;
		$storage = $this->getMockBuilder("Guzzle\\Plugin\\Cache\\CacheStorageInterface")->getMockForAbstractClass();
		$revalidate = $this->getMockBuilder("Guzzle\\Plugin\\Cache\\DefaultRevalidation")->setMethods(array( "revalidate" ))->setConstructorArgs(array( $storage ))->getMockForAbstractClass();
		$revalidate->expects($this->any())->method("revalidate")->will($this->returnCallback(function() use (&$didRevalidate) 
		{
			$didRevalidate = true;
			return true;
		}
		));
		$plugin = new \Guzzle\Plugin\Cache\CachePlugin(array( "storage" => $storage, "revalidation" => $revalidate ));
		$this->assertEquals($can, $plugin->canResponseSatisfyRequest($request, $response));
		$this->assertEquals($didRevalidate, $revalidates);
	}
	public function satisfyFailedProvider() 
	{
		return array( array( new \Guzzle\Http\Message\Request("GET", "http://foo.com", array( )), new \Guzzle\Http\Message\Response(200, array( "Age" => 100 )), false ), array( new \Guzzle\Http\Message\Request("GET", "http://foo.com", array( "Cache-Control" => "stale-if-error" )), new \Guzzle\Http\Message\Response(200, array( "Age" => 100, "Cache-Control" => "max-age=50" )), true ), array( new \Guzzle\Http\Message\Request("GET", "http://foo.com", array( "Cache-Control" => "stale-if-error=50" )), new \Guzzle\Http\Message\Response(200, array( "Age" => 100, "Cache-Control" => "max-age=50" )), true ), array( new \Guzzle\Http\Message\Request("GET", "http://foo.com", array( "Cache-Control" => "stale-if-error=20" )), new \Guzzle\Http\Message\Response(200, array( "Age" => 100, "Cache-Control" => "max-age=50" )), false ), array( new \Guzzle\Http\Message\Request("GET", "http://foo.com", array( )), new \Guzzle\Http\Message\Response(200, array( "Age" => 100, "Cache-Control" => "max-age=50, stale-if-error" )), true ), array( new \Guzzle\Http\Message\Request("GET", "http://foo.com", array( )), new \Guzzle\Http\Message\Response(200, array( "Age" => 100, "Cache-Control" => "max-age=50, stale-if-error=50" )), true ), array( new \Guzzle\Http\Message\Request("GET", "http://foo.com", array( )), new \Guzzle\Http\Message\Response(200, array( "Age" => 100, "Cache-Control" => "max-age=50, stale-if-error=20" )), false ), array( new \Guzzle\Http\Message\Request("GET", "http://foo.com", array( "Cache-Control" => "stale-if-error=50" )), new \Guzzle\Http\Message\Response(200, array( "Age" => 100, "Cache-Control" => "max-age=50, stale-if-error=20" )), false ), array( new \Guzzle\Http\Message\Request("GET", "http://foo.com", array( "Cache-Control" => "stale-if-error=20" )), new \Guzzle\Http\Message\Response(200, array( "Age" => 100, "Cache-Control" => "max-age=50, stale-if-error=50" )), false ) );
	}
	public function testChecksIfResponseCanSatisfyFailedRequest($request, $response, $can) 
	{
		$plugin = new \Guzzle\Plugin\Cache\CachePlugin();
		$this->assertEquals($can, $plugin->canResponseSatisfyFailedRequest($request, $response));
	}
	public function testDoesNothingWhenRequestIsNotCacheable() 
	{
		$storage = $this->getMockBuilder("Guzzle\\Plugin\\Cache\\CacheStorageInterface")->setMethods(array( "fetch" ))->getMockForAbstractClass();
		$storage->expects($this->never())->method("fetch");
		$plugin = new \Guzzle\Plugin\Cache\CachePlugin(array( "storage" => $storage, "can_cache" => new \Guzzle\Plugin\Cache\CallbackCanCacheStrategy(function() 
		{
			return false;
		}
		) ));
		$plugin->onRequestBeforeSend(new \Guzzle\Common\Event(array( "request" => new \Guzzle\Http\Message\Request("GET", "http://foo.com") )));
	}
	public function satisfiableProvider() 
	{
		$date = new \DateTime("-10 seconds");
		return array( array( new \Guzzle\Http\Message\Response(200, array( ), "foo") ), array( new \Guzzle\Http\Message\Response(200, array( "Date" => $date->format("c"), "Cache-Control" => "max-age=5" ), "foo") ) );
	}
	public function testInjectsSatisfiableResponses($response) 
	{
		$storage = $this->getMockBuilder("Guzzle\\Plugin\\Cache\\CacheStorageInterface")->setMethods(array( "fetch" ))->getMockForAbstractClass();
		$storage->expects($this->once())->method("fetch")->will($this->returnValue($response));
		$plugin = new \Guzzle\Plugin\Cache\CachePlugin(array( "storage" => $storage ));
		$request = new \Guzzle\Http\Message\Request("GET", "http://foo.com", array( "Cache-Control" => "max-stale" ));
		$plugin->onRequestBeforeSend(new \Guzzle\Common\Event(array( "request" => $request )));
		$plugin->onRequestSent(new \Guzzle\Common\Event(array( "request" => $request, "response" => $request->getResponse() )));
		$this->assertEquals($response->getStatusCode(), $request->getResponse()->getStatusCode());
		$this->assertEquals((string) $response->getBody(), (string) $request->getResponse()->getBody());
		$this->assertTrue($request->getResponse()->hasHeader("Age"));
		if( $request->getResponse()->isFresh() === false ) 
		{
			$this->assertContains("110", (string) $request->getResponse()->getHeader("Warning"));
		}
		$this->assertSame(sprintf("%s GuzzleCache/%s", $request->getProtocolVersion(), \Guzzle\Common\Version::VERSION), (string) $request->getHeader("Via"));
		$this->assertSame(sprintf("%s GuzzleCache/%s", $request->getProtocolVersion(), \Guzzle\Common\Version::VERSION), (string) $request->getResponse()->getHeader("Via"));
		$this->assertTrue($request->getParams()->get("cache.lookup"));
		$this->assertTrue($request->getParams()->get("cache.hit"));
		$this->assertTrue($request->getResponse()->hasHeader("X-Cache-Lookup"));
		$this->assertTrue($request->getResponse()->hasHeader("X-Cache"));
		$this->assertEquals("HIT from GuzzleCache", (string) $request->getResponse()->getHeader("X-Cache"));
		$this->assertEquals("HIT from GuzzleCache", (string) $request->getResponse()->getHeader("X-Cache-Lookup"));
	}
	public function satisfiableOnErrorProvider() 
	{
		$date = new \DateTime("-10 seconds");
		return array( array( new \Guzzle\Http\Message\Response(200, array( "Date" => $date->format("c"), "Cache-Control" => "max-age=5, stale-if-error" ), "foo") ) );
	}
	public function testInjectsSatisfiableResponsesOnError($cacheResponse) 
	{
		$storage = $this->getMockBuilder("Guzzle\\Plugin\\Cache\\CacheStorageInterface")->setMethods(array( "fetch" ))->getMockForAbstractClass();
		$storage->expects($this->exactly(2))->method("fetch")->will($this->returnValue($cacheResponse));
		$plugin = new \Guzzle\Plugin\Cache\CachePlugin(array( "storage" => $storage ));
		$request = new \Guzzle\Http\Message\Request("GET", "http://foo.com", array( "Cache-Control" => "max-stale" ));
		$plugin->onRequestBeforeSend(new \Guzzle\Common\Event(array( "request" => $request )));
		$plugin->onRequestError($event = new \Guzzle\Common\Event(array( "request" => $request, "response" => $request->getResponse() )));
		$response = $event["response"];
		$this->assertEquals($cacheResponse->getStatusCode(), $response->getStatusCode());
		$this->assertEquals((string) $cacheResponse->getBody(), (string) $response->getBody());
		$this->assertTrue($response->hasHeader("Age"));
		if( $response->isFresh() === false ) 
		{
			$this->assertContains("110", (string) $response->getHeader("Warning"));
		}
		$this->assertSame(sprintf("%s GuzzleCache/%s", $request->getProtocolVersion(), \Guzzle\Common\Version::VERSION), (string) $request->getHeader("Via"));
		$this->assertSame(sprintf("%s GuzzleCache/%s", $request->getProtocolVersion(), \Guzzle\Common\Version::VERSION), (string) $response->getHeader("Via"));
		$this->assertTrue($request->getParams()->get("cache.lookup"));
		$this->assertSame("error", $request->getParams()->get("cache.hit"));
		$this->assertTrue($response->hasHeader("X-Cache-Lookup"));
		$this->assertTrue($response->hasHeader("X-Cache"));
		$this->assertEquals("HIT from GuzzleCache", (string) $response->getHeader("X-Cache-Lookup"));
		$this->assertEquals("HIT_ERROR from GuzzleCache", (string) $response->getHeader("X-Cache"));
	}
	public function testInjectsSatisfiableResponsesOnException($cacheResponse) 
	{
		$storage = $this->getMockBuilder("Guzzle\\Plugin\\Cache\\CacheStorageInterface")->setMethods(array( "fetch" ))->getMockForAbstractClass();
		$storage->expects($this->exactly(2))->method("fetch")->will($this->returnValue($cacheResponse));
		$plugin = new \Guzzle\Plugin\Cache\CachePlugin(array( "storage" => $storage ));
		$request = new \Guzzle\Http\Message\Request("GET", "http://foo.com", array( "Cache-Control" => "max-stale" ));
		$plugin->onRequestBeforeSend(new \Guzzle\Common\Event(array( "request" => $request )));
		$plugin->onRequestException(new \Guzzle\Common\Event(array( "request" => $request, "response" => $request->getResponse(), "exception" => $this->getMock("Guzzle\\Http\\Exception\\CurlException") )));
		$plugin->onRequestSent(new \Guzzle\Common\Event(array( "request" => $request, "response" => $response = $request->getResponse() )));
		$this->assertEquals($cacheResponse->getStatusCode(), $response->getStatusCode());
		$this->assertEquals((string) $cacheResponse->getBody(), (string) $response->getBody());
		$this->assertTrue($response->hasHeader("Age"));
		if( $response->isFresh() === false ) 
		{
			$this->assertContains("110", (string) $response->getHeader("Warning"));
		}
		$this->assertSame(sprintf("%s GuzzleCache/%s", $request->getProtocolVersion(), \Guzzle\Common\Version::VERSION), (string) $request->getHeader("Via"));
		$this->assertSame(sprintf("%s GuzzleCache/%s", $request->getProtocolVersion(), \Guzzle\Common\Version::VERSION), (string) $response->getHeader("Via"));
		$this->assertTrue($request->getParams()->get("cache.lookup"));
		$this->assertSame("error", $request->getParams()->get("cache.hit"));
		$this->assertTrue($response->hasHeader("X-Cache-Lookup"));
		$this->assertTrue($response->hasHeader("X-Cache"));
		$this->assertEquals("HIT from GuzzleCache", (string) $response->getHeader("X-Cache-Lookup"));
		$this->assertEquals("HIT_ERROR from GuzzleCache", (string) $response->getHeader("X-Cache"));
	}
	public function unsatisfiableOnErrorProvider() 
	{
		$date = new \DateTime("-10 seconds");
		return array( array( false, array( "Cache-Control" => "no-store" ), new \Guzzle\Http\Message\Response(200, array( "Date" => $date->format("D, d M Y H:i:s T"), "Cache-Control" => "max-age=5, stale-if-error" ), "foo") ), array( true, array( "Cache-Control" => "stale-if-error=4" ), new \Guzzle\Http\Message\Response(200, array( "Date" => $date->format("D, d M Y H:i:s T"), "Cache-Control" => "max-age=5, stale-if-error" ), "foo") ), array( true, array( "Cache-Control" => "stale-if-error" ), new \Guzzle\Http\Message\Response(200, array( "Date" => $date->format("D, d M Y H:i:s T"), "Cache-Control" => "max-age=5, stale-if-error=4" ), "foo") ) );
	}
	public function testDoesNotInjectUnsatisfiableResponsesOnError($requestCanCache, $requestHeaders, $cacheResponse) 
	{
		$storage = $this->getMockBuilder("Guzzle\\Plugin\\Cache\\CacheStorageInterface")->setMethods(array( "fetch" ))->getMockForAbstractClass();
		$storage->expects($this->exactly(($requestCanCache ? 2 : 0)))->method("fetch")->will($this->returnValue($cacheResponse));
		$plugin = new \Guzzle\Plugin\Cache\CachePlugin(array( "storage" => $storage ));
		$request = new \Guzzle\Http\Message\Request("GET", "http://foo.com", $requestHeaders);
		$plugin->onRequestBeforeSend(new \Guzzle\Common\Event(array( "request" => $request )));
		$plugin->onRequestError($event = new \Guzzle\Common\Event(array( "request" => $request, "response" => $response = $request->getResponse() )));
		$this->assertSame($response, $event["response"]);
	}
	public function testDoesNotInjectUnsatisfiableResponsesOnException($requestCanCache, $requestHeaders, $responseParts) 
	{
		$storage = $this->getMockBuilder("Guzzle\\Plugin\\Cache\\CacheStorageInterface")->setMethods(array( "fetch" ))->getMockForAbstractClass();
		$storage->expects($this->exactly(($requestCanCache ? 2 : 0)))->method("fetch")->will($this->returnValue($responseParts));
		$plugin = new \Guzzle\Plugin\Cache\CachePlugin(array( "storage" => $storage ));
		$request = new \Guzzle\Http\Message\Request("GET", "http://foo.com", $requestHeaders);
		$plugin->onRequestBeforeSend(new \Guzzle\Common\Event(array( "request" => $request )));
		$plugin->onRequestException($event = new \Guzzle\Common\Event(array( "request" => $request, "response" => $response = $request->getResponse(), "exception" => $this->getMock("Guzzle\\Http\\Exception\\CurlException") )));
		$this->assertSame($response, $request->getResponse());
	}
	public function testCachesResponsesWhenCacheable() 
	{
		$cache = new \Doctrine\Common\Cache\ArrayCache();
		$plugin = new \Guzzle\Plugin\Cache\CachePlugin($cache);
		$request = new \Guzzle\Http\Message\Request("GET", "http://foo.com");
		$response = new \Guzzle\Http\Message\Response(200, array( ), "Foo");
		$plugin->onRequestBeforeSend(new \Guzzle\Common\Event(array( "request" => $request )));
		$plugin->onRequestSent(new \Guzzle\Common\Event(array( "request" => $request, "response" => $response )));
		$data = $this->readAttribute($cache, "data");
		$this->assertNotEmpty($data);
	}
	public function testPurgesRequests() 
	{
		$storage = $this->getMockBuilder("Guzzle\\Plugin\\Cache\\CacheStorageInterface")->setMethods(array( "purge" ))->getMockForAbstractClass();
		$storage->expects($this->atLeastOnce())->method("purge");
		$plugin = new \Guzzle\Plugin\Cache\CachePlugin(array( "storage" => $storage ));
		$request = new \Guzzle\Http\Message\Request("GET", "http://foo.com", array( "X-Foo" => "Bar" ));
		$plugin->purge($request);
	}
	public function testAutoPurgesRequests() 
	{
		$storage = $this->getMockBuilder("Guzzle\\Plugin\\Cache\\CacheStorageInterface")->setMethods(array( "purge" ))->getMockForAbstractClass();
		$storage->expects($this->atLeastOnce())->method("purge");
		$plugin = new \Guzzle\Plugin\Cache\CachePlugin(array( "storage" => $storage, "auto_purge" => true ));
		$client = new \Guzzle\Http\Client();
		$request = $client->put("http://foo.com", array( "X-Foo" => "Bar" ));
		$request->addSubscriber($plugin);
		$request->setResponse(new \Guzzle\Http\Message\Response(200), true);
		$request->send();
	}
}
?>