<?php  namespace Guzzle\Tests\Plugin\Cookie;
class CookiePluginTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testExtractsAndStoresCookies() 
	{
		$response = new \Guzzle\Http\Message\Response(200);
		$mock = $this->getMockBuilder("Guzzle\\Plugin\\Cookie\\CookieJar\\ArrayCookieJar")->setMethods(array( "addCookiesFromResponse" ))->getMock();
		$mock->expects($this->exactly(1))->method("addCookiesFromResponse")->with($response);
		$plugin = new \Guzzle\Plugin\Cookie\CookiePlugin($mock);
		$plugin->onRequestSent(new \Guzzle\Common\Event(array( "response" => $response )));
	}
	public function testAddsCookiesToRequests() 
	{
		$cookie = new \Guzzle\Plugin\Cookie\Cookie(array( "name" => "foo", "value" => "bar" ));
		$mock = $this->getMockBuilder("Guzzle\\Plugin\\Cookie\\CookieJar\\ArrayCookieJar")->setMethods(array( "getMatchingCookies" ))->getMock();
		$mock->expects($this->once())->method("getMatchingCookies")->will($this->returnValue(array( $cookie )));
		$plugin = new \Guzzle\Plugin\Cookie\CookiePlugin($mock);
		$client = new \Guzzle\Http\Client();
		$client->getEventDispatcher()->addSubscriber($plugin);
		$request = $client->get("http://www.example.com");
		$plugin->onRequestBeforeSend(new \Guzzle\Common\Event(array( "request" => $request )));
		$this->assertEquals("bar", $request->getCookie("foo"));
	}
	public function testCookiesAreExtractedFromRedirectResponses() 
	{
		$plugin = new \Guzzle\Plugin\Cookie\CookiePlugin(new \Guzzle\Plugin\Cookie\CookieJar\ArrayCookieJar());
		$this->getServer()->flush();
		$this->getServer()->enqueue(array( "HTTP/1.1 302 Moved Temporarily\r\n" . "Set-Cookie: test=583551; expires=Wednesday, 23-Mar-2050 19:49:45 GMT; path=/\r\n" . "Location: /redirect\r\n\r\n", "HTTP/1.1 200 OK\r\n" . "Content-Length: 0\r\n\r\n", "HTTP/1.1 200 OK\r\n" . "Content-Length: 0\r\n\r\n" ));
		$client = new \Guzzle\Http\Client($this->getServer()->getUrl());
		$client->getEventDispatcher()->addSubscriber($plugin);
		$client->get()->send();
		$request = $client->get();
		$request->send();
		$this->assertEquals("test=583551", $request->getHeader("Cookie"));
		$requests = $this->getServer()->getReceivedRequests(true);
		$this->assertEquals("test=583551", $requests[2]->getHeader("Cookie"));
		$this->assertEquals("test=583551", $requests[1]->getHeader("Cookie"));
	}
	public function testCookiesAreNotAddedWhenParamIsSet() 
	{
		$jar = new \Guzzle\Plugin\Cookie\CookieJar\ArrayCookieJar();
		$plugin = new \Guzzle\Plugin\Cookie\CookiePlugin($jar);
		$jar->add(new \Guzzle\Plugin\Cookie\Cookie(array( "domain" => "example.com", "path" => "/", "name" => "test", "value" => "hi", "expires" => time() + 3600 )));
		$client = new \Guzzle\Http\Client("http://example.com");
		$client->getEventDispatcher()->addSubscriber($plugin);
		$request = $client->get();
		$request->setResponse(new \Guzzle\Http\Message\Response(200), true);
		$request->send();
		$this->assertEquals("hi", $request->getCookie("test"));
		$request = $client->get();
		$request->getParams()->set("cookies.disable", true);
		$request->setResponse(new \Guzzle\Http\Message\Response(200), true);
		$request->send();
		$this->assertNull($request->getCookie("test"));
	}
	public function testProvidesCookieJar() 
	{
		$jar = new \Guzzle\Plugin\Cookie\CookieJar\ArrayCookieJar();
		$plugin = new \Guzzle\Plugin\Cookie\CookiePlugin($jar);
		$this->assertSame($jar, $plugin->getCookieJar());
	}
	public function testEscapesCookieDomains() 
	{
		$cookie = new \Guzzle\Plugin\Cookie\Cookie(array( "domain" => "/foo/^\$[A-Z]+/" ));
		$this->assertFalse($cookie->matchesDomain("foo"));
	}
}
?>