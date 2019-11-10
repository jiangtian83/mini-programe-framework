<?php  namespace Guzzle\Tests\Http;
class ClientTest extends \Guzzle\Tests\GuzzleTestCase 
{
	private function getLogPlugin() 
	{
		return new \Guzzle\Plugin\Log\LogPlugin(new \Guzzle\Log\ClosureLogAdapter(function($message, $priority, $extras = NULL) 
		{
			echo $message . " " . $priority . " " . implode(" - ", (array) $extras) . "\n";
		}
		));
	}
	public function testAcceptsConfig() 
	{
		$client = new \Guzzle\Http\Client("http://www.google.com/");
		$this->assertEquals("http://www.google.com/", $client->getBaseUrl());
		$this->assertSame($client, $client->setConfig(array( "test" => "123" )));
		$this->assertEquals(array( "test" => "123" ), $client->getConfig()->getAll());
		$this->assertEquals("123", $client->getConfig("test"));
		$this->assertSame($client, $client->setBaseUrl("http://www.test.com/{test}"));
		$this->assertEquals("http://www.test.com/123", $client->getBaseUrl());
		$this->assertEquals("http://www.test.com/{test}", $client->getBaseUrl(false));
		try 
		{
			$client->setConfig(false);
		}
		catch( \InvalidArgumentException $e ) 
		{
		}
	}
	public function testDescribesEvents() 
	{
		$this->assertEquals(array( "client.create_request" ), \Guzzle\Http\Client::getAllEvents());
	}
	public function testConstructorCanAcceptConfig() 
	{
		$client = new \Guzzle\Http\Client("http://www.test.com/", array( "data" => "123" ));
		$this->assertEquals("123", $client->getConfig("data"));
	}
	public function testCanUseCollectionAsConfig() 
	{
		$client = new \Guzzle\Http\Client("http://www.google.com/");
		$client->setConfig(new \Guzzle\Common\Collection(array( "api" => "v1", "key" => "value", "base_url" => "http://www.google.com/" )));
		$this->assertEquals("v1", $client->getConfig("api"));
	}
	public function testExpandsUriTemplatesUsingConfig() 
	{
		$client = new \Guzzle\Http\Client("http://www.google.com/");
		$client->setConfig(array( "api" => "v1", "key" => "value", "foo" => "bar" ));
		$ref = new \ReflectionMethod($client, "expandTemplate");
		$ref->setAccessible(true);
		$this->assertEquals("Testing...api/v1/key/value", $ref->invoke($client, "Testing...api/{api}/key/{key}"));
	}
	public function testClientAttachersObserversToRequests() 
	{
		$this->getServer()->flush();
		$this->getServer()->enqueue("HTTP/1.1 200 OK\r\nContent-Length: 0\r\n\r\n");
		$client = new \Guzzle\Http\Client($this->getServer()->getUrl());
		$logPlugin = $this->getLogPlugin();
		$client->getEventDispatcher()->addSubscriber($logPlugin);
		$request = $client->createRequest();
		$this->assertTrue($this->hasSubscriber($request, $logPlugin));
	}
	public function testClientReturnsValidBaseUrls() 
	{
		$client = new \Guzzle\Http\Client("http://www.{foo}.{data}/", array( "data" => "123", "foo" => "bar" ));
		$this->assertEquals("http://www.bar.123/", $client->getBaseUrl());
		$client->setBaseUrl("http://www.google.com/");
		$this->assertEquals("http://www.google.com/", $client->getBaseUrl());
	}
	public function testClientAddsCurlOptionsToRequests() 
	{
		$client = new \Guzzle\Http\Client("http://www.test.com/", array( "api" => "v1", "curl.options" => array( "CURLOPT_HTTPAUTH" => "CURLAUTH_DIGEST", "abc" => "foo", "blacklist" => "abc", "debug" => true ) ));
		$request = $client->createRequest();
		$options = $request->getCurlOptions();
		$this->assertEquals(CURLAUTH_DIGEST, $options->get(CURLOPT_HTTPAUTH));
		$this->assertEquals("foo", $options->get("abc"));
		$this->assertEquals("abc", $options->get("blacklist"));
	}
	public function testClientAllowsFineGrainedSslControlButIsSecureByDefault() 
	{
		$client = new \Guzzle\Http\Client("https://www.secure.com/");
		$request = $client->createRequest();
		$options = $request->getCurlOptions();
		$this->assertTrue($options->get(CURLOPT_SSL_VERIFYPEER));
		$client = new \Guzzle\Http\Client("https://www.secure.com/");
		$client->setSslVerification(__DIR__);
		$request = $client->createRequest();
		$options = $request->getCurlOptions();
		$this->assertSame(__DIR__, $options->get(CURLOPT_CAPATH));
	}
	public function testConfigSettingsControlSslConfiguration() 
	{
		$client = new \Guzzle\Http\Client("https://www.secure.com/", array( "ssl.certificate_authority" => "system" ));
		$this->assertNull($client->getConfig("curl.options"));
		$client = new \Guzzle\Http\Client("https://www.secure.com/", array( "ssl.certificate_authority" => false ));
		$options = $client->getConfig("curl.options");
		$this->assertArrayNotHasKey(CURLOPT_CAINFO, $options);
		$this->assertSame(false, $options[CURLOPT_SSL_VERIFYPEER]);
		$this->assertSame(0, $options[CURLOPT_SSL_VERIFYHOST]);
	}
	public function testClientAllowsUnsafeOperationIfRequested() 
	{
		$client = new \Guzzle\Http\Client("https://www.secure.com/", array( "api" => "v1" ));
		$client->setSslVerification(false);
		$request = $client->createRequest();
		$options = $request->getCurlOptions();
		$this->assertFalse($options->get(CURLOPT_SSL_VERIFYPEER));
		$this->assertNull($options->get(CURLOPT_CAINFO));
	}
	public function testThrowsExceptionForInvalidCertificate() 
	{
		$client = new \Guzzle\Http\Client("https://www.secure.com/");
		$client->setSslVerification("/path/to/missing/file");
	}
	public function testClientAllowsSettingSpecificSslCaInfo() 
	{
		$client = new \Guzzle\Http\Client("https://www.secure.com/", array( "api" => "v1" ));
		$client->setSslVerification(__FILE__);
		$request = $client->createRequest();
		$options = $request->getCurlOptions();
		$this->assertSame(__FILE__, $options->get(CURLOPT_CAINFO));
	}
	public function testClientPreventsInadvertentInsecureVerifyHostSetting() 
	{
		$client = new \Guzzle\Http\Client("https://www.secure.com/", array( "api" => "v1" ));
		$client->setSslVerification(__FILE__, true, true);
	}
	public function testClientPreventsInvalidVerifyPeerSetting() 
	{
		$client = new \Guzzle\Http\Client("https://www.secure.com/", array( "api" => "v1" ));
		$client->setSslVerification(__FILE__, "yes");
	}
	public function testClientAddsParamsToRequests() 
	{
		\Guzzle\Common\Version::$emitWarnings = false;
		$client = new \Guzzle\Http\Client("http://www.example.com", array( "api" => "v1", "request.params" => array( "foo" => "bar", "baz" => "jar" ) ));
		$request = $client->createRequest();
		$this->assertEquals("bar", $request->getParams()->get("foo"));
		$this->assertEquals("jar", $request->getParams()->get("baz"));
		\Guzzle\Common\Version::$emitWarnings = true;
	}
	public function urlProvider() 
	{
		$u = $this->getServer()->getUrl() . "base/";
		$u2 = $this->getServer()->getUrl() . "base?z=1";
		return array( array( $u, "", $u ), array( $u, "relative/path/to/resource", $u . "relative/path/to/resource" ), array( $u, "relative/path/to/resource?a=b&c=d", $u . "relative/path/to/resource?a=b&c=d" ), array( $u, "/absolute/path/to/resource", $this->getServer()->getUrl() . "absolute/path/to/resource" ), array( $u, "/absolute/path/to/resource?a=b&c=d", $this->getServer()->getUrl() . "absolute/path/to/resource?a=b&c=d" ), array( $u2, "/absolute/path/to/resource?a=b&c=d", $this->getServer()->getUrl() . "absolute/path/to/resource?a=b&c=d&z=1" ), array( $u2, "relative/path/to/resource", $this->getServer()->getUrl() . "base/relative/path/to/resource?z=1" ), array( $u2, "relative/path/to/resource?another=query", $this->getServer()->getUrl() . "base/relative/path/to/resource?another=query&z=1" ) );
	}
	public function testBuildsRelativeUrls($baseUrl, $url, $result) 
	{
		$client = new \Guzzle\Http\Client($baseUrl);
		$this->assertEquals($result, $client->get($url)->getUrl());
	}
	public function testAllowsConfigsToBeChangedAndInjectedInBaseUrl() 
	{
		$client = new \Guzzle\Http\Client("http://{a}/{b}");
		$this->assertEquals("http:///", $client->getBaseUrl());
		$this->assertEquals("http://{a}/{b}", $client->getBaseUrl(false));
		$client->setConfig(array( "a" => "test.com", "b" => "index.html" ));
		$this->assertEquals("http://test.com/index.html", $client->getBaseUrl());
	}
	public function testCreatesRequestsWithDefaultValues() 
	{
		$client = new \Guzzle\Http\Client($this->getServer()->getUrl() . "base");
		$request = $client->createRequest();
		$this->assertEquals("GET", $request->getMethod());
		$this->assertEquals($client->getBaseUrl(), $request->getUrl());
		$request = $client->createRequest("DELETE");
		$this->assertEquals("DELETE", $request->getMethod());
		$this->assertEquals($client->getBaseUrl(), $request->getUrl());
		$request = $client->createRequest("HEAD", "http://www.test.com/");
		$this->assertEquals("HEAD", $request->getMethod());
		$this->assertEquals("http://www.test.com/", $request->getUrl());
		$request = $client->createRequest("PUT");
		$this->assertEquals("PUT", $request->getMethod());
		$client->getConfig()->set("a", 1)->set("b", 2);
		$request = $client->createRequest("PUT", "/path/{a}?q={b}");
		$this->assertEquals($request->getUrl(), $this->getServer()->getUrl() . "path/1?q=2");
	}
	public function testClientHasHelperMethodsForCreatingRequests() 
	{
		$url = $this->getServer()->getUrl();
		$client = new \Guzzle\Http\Client($url . "base");
		$this->assertEquals("GET", $client->get()->getMethod());
		$this->assertEquals("PUT", $client->put()->getMethod());
		$this->assertEquals("POST", $client->post()->getMethod());
		$this->assertEquals("HEAD", $client->head()->getMethod());
		$this->assertEquals("DELETE", $client->delete()->getMethod());
		$this->assertEquals("OPTIONS", $client->options()->getMethod());
		$this->assertEquals("PATCH", $client->patch()->getMethod());
		$this->assertEquals($url . "base/abc", $client->get("abc")->getUrl());
		$this->assertEquals($url . "zxy", $client->put("/zxy")->getUrl());
		$this->assertEquals($url . "zxy?a=b", $client->post("/zxy?a=b")->getUrl());
		$this->assertEquals($url . "base?a=b", $client->head("?a=b")->getUrl());
		$this->assertEquals($url . "base?a=b", $client->delete("/base?a=b")->getUrl());
	}
	public function testClientInjectsConfigsIntoUrls() 
	{
		$client = new \Guzzle\Http\Client("http://www.test.com/api/v1", array( "test" => "123" ));
		$request = $client->get("relative/{test}");
		$this->assertEquals("http://www.test.com/api/v1/relative/123", $request->getUrl());
	}
	public function testAllowsEmptyBaseUrl() 
	{
		$client = new \Guzzle\Http\Client();
		$request = $client->get("http://www.google.com/");
		$this->assertEquals("http://www.google.com/", $request->getUrl());
		$request->setResponse(new \Guzzle\Http\Message\Response(200), true);
		$request->send();
	}
	public function testAllowsCustomCurlMultiObjects() 
	{
		$mock = $this->getMock("Guzzle\\Http\\Curl\\CurlMulti", array( "add", "send" ));
		$mock->expects($this->once())->method("add")->will($this->returnSelf());
		$mock->expects($this->once())->method("send")->will($this->returnSelf());
		$client = new \Guzzle\Http\Client();
		$client->setCurlMulti($mock);
		$request = $client->get();
		$request->setResponse(new \Guzzle\Http\Message\Response(200), true);
		$client->send($request);
	}
	public function testClientSendsMultipleRequests() 
	{
		$client = new \Guzzle\Http\Client($this->getServer()->getUrl());
		$mock = new \Guzzle\Plugin\Mock\MockPlugin();
		$responses = array( new \Guzzle\Http\Message\Response(200), new \Guzzle\Http\Message\Response(201), new \Guzzle\Http\Message\Response(202) );
		$mock->addResponse($responses[0]);
		$mock->addResponse($responses[1]);
		$mock->addResponse($responses[2]);
		$client->getEventDispatcher()->addSubscriber($mock);
		$requests = array( $client->get(), $client->head(), $client->put("/", null, "test") );
		$this->assertEquals(array( $responses[0], $responses[1], $responses[2] ), $client->send($requests));
	}
	public function testClientSendsSingleRequest() 
	{
		$client = new \Guzzle\Http\Client($this->getServer()->getUrl());
		$mock = new \Guzzle\Plugin\Mock\MockPlugin();
		$response = new \Guzzle\Http\Message\Response(200);
		$mock->addResponse($response);
		$client->getEventDispatcher()->addSubscriber($mock);
		$this->assertEquals($response, $client->send($client->get()));
	}
	public function testClientThrowsExceptionForSingleRequest() 
	{
		$client = new \Guzzle\Http\Client($this->getServer()->getUrl());
		$mock = new \Guzzle\Plugin\Mock\MockPlugin();
		$response = new \Guzzle\Http\Message\Response(404);
		$mock->addResponse($response);
		$client->getEventDispatcher()->addSubscriber($mock);
		$client->send($client->get());
	}
	public function testClientThrowsExceptionForMultipleRequests() 
	{
		$client = new \Guzzle\Http\Client($this->getServer()->getUrl());
		$mock = new \Guzzle\Plugin\Mock\MockPlugin();
		$mock->addResponse(new \Guzzle\Http\Message\Response(200));
		$mock->addResponse(new \Guzzle\Http\Message\Response(404));
		$client->getEventDispatcher()->addSubscriber($mock);
		$client->send(array( $client->get(), $client->head() ));
	}
	public function testQueryStringsAreNotDoubleEncoded() 
	{
		$client = new \Guzzle\Http\Client("http://test.com", array( "path" => array( "foo", "bar" ), "query" => "hi there", "data" => array( "test" => "a&b" ) ));
		$request = $client->get("{/path*}{?query,data*}");
		$this->assertEquals("http://test.com/foo/bar?query=hi%20there&test=a%26b", $request->getUrl());
		$this->assertEquals("hi there", $request->getQuery()->get("query"));
		$this->assertEquals("a&b", $request->getQuery()->get("test"));
	}
	public function testQueryStringsAreNotDoubleEncodedUsingAbsolutePaths() 
	{
		$client = new \Guzzle\Http\Client("http://test.com", array( "path" => array( "foo", "bar" ), "query" => "hi there" ));
		$request = $client->get("http://test.com{?query}");
		$this->assertEquals("http://test.com?query=hi%20there", $request->getUrl());
		$this->assertEquals("hi there", $request->getQuery()->get("query"));
	}
	public function testAllowsUriTemplateInjection() 
	{
		$client = new \Guzzle\Http\Client("http://test.com");
		$ref = new \ReflectionMethod($client, "getUriTemplate");
		$ref->setAccessible(true);
		$a = $ref->invoke($client);
		$this->assertSame($a, $ref->invoke($client));
		$client->setUriTemplate(new \Guzzle\Parser\UriTemplate\UriTemplate());
		$this->assertNotSame($a, $ref->invoke($client));
	}
	public function testAllowsCustomVariablesWhenExpandingTemplates() 
	{
		$client = new \Guzzle\Http\Client("http://test.com", array( "test" => "hi" ));
		$ref = new \ReflectionMethod($client, "expandTemplate");
		$ref->setAccessible(true);
		$uri = $ref->invoke($client, "http://{test}{?query*}", array( "query" => array( "han" => "solo" ) ));
		$this->assertEquals("http://hi?han=solo", $uri);
	}
	public function testUriArrayAllowsCustomTemplateVariables() 
	{
		$client = new \Guzzle\Http\Client();
		$vars = array( "var" => "hi" );
		$this->assertEquals("/hi", (string) $client->createRequest("GET", array( "/{var}", $vars ))->getUrl());
		$this->assertEquals("/hi", (string) $client->get(array( "/{var}", $vars ))->getUrl());
		$this->assertEquals("/hi", (string) $client->put(array( "/{var}", $vars ))->getUrl());
		$this->assertEquals("/hi", (string) $client->post(array( "/{var}", $vars ))->getUrl());
		$this->assertEquals("/hi", (string) $client->head(array( "/{var}", $vars ))->getUrl());
		$this->assertEquals("/hi", (string) $client->options(array( "/{var}", $vars ))->getUrl());
	}
	public function testAllowsDefaultHeaders() 
	{
		\Guzzle\Common\Version::$emitWarnings = false;
		$default = array( "X-Test" => "Hi!" );
		$other = array( "X-Other" => "Foo" );
		$client = new \Guzzle\Http\Client();
		$client->setDefaultHeaders($default);
		$this->assertEquals($default, $client->getDefaultHeaders()->getAll());
		$client->setDefaultHeaders(new \Guzzle\Common\Collection($default));
		$this->assertEquals($default, $client->getDefaultHeaders()->getAll());
		$request = $client->createRequest("GET", null, $other);
		$this->assertEquals("Hi!", $request->getHeader("X-Test"));
		$this->assertEquals("Foo", $request->getHeader("X-Other"));
		$request = $client->createRequest("GET", null, new \Guzzle\Common\Collection($other));
		$this->assertEquals("Hi!", $request->getHeader("X-Test"));
		$this->assertEquals("Foo", $request->getHeader("X-Other"));
		$request = $client->createRequest("GET");
		$this->assertEquals("Hi!", $request->getHeader("X-Test"));
		\Guzzle\Common\Version::$emitWarnings = true;
	}
	public function testDontReuseCurlMulti() 
	{
		$client1 = new \Guzzle\Http\Client();
		$client2 = new \Guzzle\Http\Client();
		$this->assertNotSame($client1->getCurlMulti(), $client2->getCurlMulti());
	}
	public function testGetDefaultUserAgent() 
	{
		$client = new \Guzzle\Http\Client();
		$agent = $this->readAttribute($client, "userAgent");
		$version = curl_version();
		$testAgent = sprintf("Guzzle/%s curl/%s PHP/%s", \Guzzle\Common\Version::VERSION, $version["version"], PHP_VERSION);
		$this->assertEquals($agent, $testAgent);
		$client->setUserAgent("foo");
		$this->assertEquals("foo", $this->readAttribute($client, "userAgent"));
	}
	public function testOverwritesUserAgent() 
	{
		$client = new \Guzzle\Http\Client();
		$request = $client->createRequest("GET", "http://www.foo.com", array( "User-agent" => "foo" ));
		$this->assertEquals("foo", (string) $request->getHeader("User-Agent"));
	}
	public function testUsesDefaultUserAgent() 
	{
		$client = new \Guzzle\Http\Client();
		$request = $client->createRequest("GET", "http://www.foo.com");
		$this->assertContains("Guzzle/", (string) $request->getHeader("User-Agent"));
	}
	public function testCanSetDefaultRequestOptions() 
	{
		$client = new \Guzzle\Http\Client();
		$client->getConfig()->set("request.options", array( "query" => array( "test" => "123", "other" => "abc" ), "headers" => array( "Foo" => "Bar", "Baz" => "Bam" ) ));
		$request = $client->createRequest("GET", "http://www.foo.com?test=hello", array( "Foo" => "Test" ));
		$this->assertEquals("Test", (string) $request->getHeader("Foo"));
		$this->assertEquals("hello", $request->getQuery()->get("test"));
		$this->assertEquals("abc", $request->getQuery()->get("other"));
		$this->assertEquals("Bam", (string) $request->getHeader("Baz"));
	}
	public function testCanSetSetOptionsOnRequests() 
	{
		$client = new \Guzzle\Http\Client();
		$request = $client->createRequest("GET", "http://www.foo.com?test=hello", array( "Foo" => "Test" ), null, array( "cookies" => array( "michael" => "test" ) ));
		$this->assertEquals("test", $request->getCookie("michael"));
	}
	public function testHasDefaultOptionsHelperMethods() 
	{
		$client = new \Guzzle\Http\Client();
		$client->setDefaultOption("headers/foo", "bar");
		$this->assertEquals("bar", $client->getDefaultOption("headers/foo"));
		$client->setDefaultOption("allow_redirects", false);
		$this->assertFalse($client->getDefaultOption("allow_redirects"));
		$this->assertEquals(array( "headers" => array( "foo" => "bar" ), "allow_redirects" => false ), $client->getConfig("request.options"));
		$request = $client->get("/");
		$this->assertEquals("bar", $request->getHeader("foo"));
	}
	public function testHeadCanUseOptions() 
	{
		$client = new \Guzzle\Http\Client();
		$head = $client->head("http://www.foo.com", array( ), array( "query" => array( "foo" => "bar" ) ));
		$this->assertEquals("bar", $head->getQuery()->get("foo"));
	}
	public function testCanSetRelativeUrlStartingWithHttp() 
	{
		$client = new \Guzzle\Http\Client("http://www.foo.com");
		$this->assertEquals("http://www.foo.com/httpfoo", $client->createRequest("GET", "httpfoo")->getUrl());
	}
}
?>