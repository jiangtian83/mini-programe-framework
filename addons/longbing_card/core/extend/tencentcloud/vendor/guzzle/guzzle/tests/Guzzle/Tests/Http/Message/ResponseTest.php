<?php  namespace Guzzle\Tests\Message;
class ResponseTest extends \Guzzle\Tests\GuzzleTestCase 
{
	protected $response = NULL;
	public function setup() 
	{
		$this->response = new \Guzzle\Http\Message\Response(200, new \Guzzle\Common\Collection(array( "Accept-Ranges" => "bytes", "Age" => "12", "Allow" => "GET, HEAD", "Cache-Control" => "no-cache", "Content-Encoding" => "gzip", "Content-Language" => "da", "Content-Length" => "348", "Content-Location" => "/index.htm", "Content-Disposition" => "attachment; filename=fname.ext", "Content-MD5" => "Q2hlY2sgSW50ZWdyaXR5IQ==", "Content-Range" => "bytes 21010-47021/47022", "Content-Type" => "text/html; charset=utf-8", "Date" => "Tue, 15 Nov 1994 08:12:31 GMT", "ETag" => "737060cd8c284d8af7ad3082f209582d", "Expires" => "Thu, 01 Dec 1994 16:00:00 GMT", "Last-Modified" => "Tue, 15 Nov 1994 12:45:26 GMT", "Location" => "http://www.w3.org/pub/WWW/People.html", "Pragma" => "no-cache", "Proxy-Authenticate" => "Basic", "Retry-After" => "120", "Server" => "Apache/1.3.27 (Unix) (Red-Hat/Linux)", "Set-Cookie" => "UserID=JohnDoe; Max-Age=3600; Version=1", "Trailer" => "Max-Forwards", "Transfer-Encoding" => "chunked", "Vary" => "*", "Via" => "1.0 fred, 1.1 nowhere.com (Apache/1.1)", "Warning" => "199 Miscellaneous warning", "WWW-Authenticate" => "Basic" )), "body");
	}
	public function tearDown() 
	{
		unset($this->response);
	}
	public function testConstructor() 
	{
		$params = new \Guzzle\Common\Collection();
		$body = \Guzzle\Http\EntityBody::factory("");
		$response = new \Guzzle\Http\Message\Response(200, $params, $body);
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals($body, $response->getBody());
		$this->assertEquals("OK", $response->getReasonPhrase());
		$this->assertEquals("HTTP/1.1 200 OK\r\n\r\n", $response->getRawHeaders());
		$response = new \Guzzle\Http\Message\Response(200, $params);
		$this->assertEquals("HTTP/1.1 200 OK\r\n\r\n", $response->getRawHeaders());
		$response = new \Guzzle\Http\Message\Response(200, null, "data");
		$this->assertInstanceOf("Guzzle\\Http\\EntityBody", $response->getBody());
		$response = new \Guzzle\Http\Message\Response(200, null, \Guzzle\Http\EntityBody::factory("data"));
		$this->assertInstanceOf("Guzzle\\Http\\EntityBody", $response->getBody());
		$this->assertEquals("data", $response->getBody(true));
		$response = new \Guzzle\Http\Message\Response(200, null, "0");
		$this->assertSame("0", $response->getBody(true), "getBody(true) should return \"0\" if response body is \"0\".");
		try 
		{
		}
		catch( \Guzzle\Http\HttpException $e ) 
		{
		}
		$response = new \Guzzle\Http\Message\Response(2);
		$this->assertEquals(2, $response->getStatusCode());
		$this->assertEquals("", $response->getReasonPhrase());
		try 
		{
			$response = new \Guzzle\Http\Message\Response(200, "adidas");
			$this->fail("Response did not throw exception when passing invalid \$headers");
		}
		catch( \Guzzle\Http\Exception\BadResponseException $e ) 
		{
		}
	}
	public function test__toString() 
	{
		$response = new \Guzzle\Http\Message\Response(200);
		$this->assertEquals("HTTP/1.1 200 OK\r\n\r\n", (string) $response);
		$response = new \Guzzle\Http\Message\Response(200, array( "X-Test" => "Guzzle" ));
		$this->assertEquals("HTTP/1.1 200 OK\r\nX-Test: Guzzle\r\n\r\n", (string) $response);
		$response = new \Guzzle\Http\Message\Response(200, array( "Content-Length" => 4 ), "test");
		$this->assertEquals("HTTP/1.1 200 OK\r\nContent-Length: 4\r\n\r\ntest", (string) $response);
	}
	public function testFactory() 
	{
		$response = \Guzzle\Http\Message\Response::fromMessage("HTTP/1.1 200 OK\r\nContent-Length: 4\r\n\r\ntest");
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals("OK", $response->getReasonPhrase());
		$this->assertEquals(4, (string) $response->getContentLength());
		$this->assertEquals("test", $response->getBody(true));
		$response = \Guzzle\Http\Message\Response::fromMessage("HTTP/1.1 200 OK\r\nContent-Length: 4\r\n\r\ntest");
		$this->assertEquals(4, (string) $response->getContentLength());
		$this->assertEquals("test", $response->getBody(true));
	}
	public function testFactoryCanCreateHeadResponses() 
	{
		$response = \Guzzle\Http\Message\Response::fromMessage("HTTP/1.1 200 OK\r\nContent-Length: 4\r\n\r\n");
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals("OK", $response->getReasonPhrase());
		$this->assertEquals(4, (string) $response->getContentLength());
		$this->assertEquals("", $response->getBody(true));
	}
	public function testFactoryRequiresMessage() 
	{
		$this->assertFalse(\Guzzle\Http\Message\Response::fromMessage(""));
	}
	public function testGetBody() 
	{
		$body = \Guzzle\Http\EntityBody::factory("");
		$response = new \Guzzle\Http\Message\Response(403, new \Guzzle\Common\Collection(), $body);
		$this->assertEquals($body, $response->getBody());
		$response->setBody("foo");
		$this->assertEquals("foo", $response->getBody(true));
	}
	public function testManagesStatusCode() 
	{
		$response = new \Guzzle\Http\Message\Response(403);
		$this->assertEquals(403, $response->getStatusCode());
	}
	public function testGetMessage() 
	{
		$response = new \Guzzle\Http\Message\Response(200, new \Guzzle\Common\Collection(array( "Content-Length" => 4 )), "body");
		$this->assertEquals("HTTP/1.1 200 OK\r\nContent-Length: 4\r\n\r\nbody", $response->getMessage());
	}
	public function testGetRawHeaders() 
	{
		$response = new \Guzzle\Http\Message\Response(200, new \Guzzle\Common\Collection(array( "Keep-Alive" => 155, "User-Agent" => "Guzzle", "Content-Length" => 4 )), "body");
		$this->assertEquals("HTTP/1.1 200 OK\r\nKeep-Alive: 155\r\nUser-Agent: Guzzle\r\nContent-Length: 4\r\n\r\n", $response->getRawHeaders());
	}
	public function testHandlesStatusAndStatusCodes() 
	{
		$response = new \Guzzle\Http\Message\Response(200, new \Guzzle\Common\Collection(), "body");
		$this->assertEquals("OK", $response->getReasonPhrase());
		$this->assertSame($response, $response->setStatus(204));
		$this->assertEquals("No Content", $response->getReasonPhrase());
		$this->assertEquals(204, $response->getStatusCode());
		$this->assertSame($response, $response->setStatus(204, "Testing!"));
		$this->assertEquals("Testing!", $response->getReasonPhrase());
		$this->assertEquals(204, $response->getStatusCode());
		$response->setStatus(2000);
		$this->assertEquals(2000, $response->getStatusCode());
		$this->assertEquals("", $response->getReasonPhrase());
		$response->setStatus(200, "Foo");
		$this->assertEquals(200, $response->getStatusCode());
		$this->assertEquals("Foo", $response->getReasonPhrase());
	}
	public function testIsClientError() 
	{
		$response = new \Guzzle\Http\Message\Response(403);
		$this->assertTrue($response->isClientError());
		$response = new \Guzzle\Http\Message\Response(200);
		$this->assertFalse($response->isClientError());
	}
	public function testIsError() 
	{
		$response = new \Guzzle\Http\Message\Response(403);
		$this->assertTrue($response->isError());
		$response = new \Guzzle\Http\Message\Response(200);
		$this->assertFalse($response->isError());
		$response = new \Guzzle\Http\Message\Response(500);
		$this->assertTrue($response->isError());
	}
	public function testIsInformational() 
	{
		$response = new \Guzzle\Http\Message\Response(100);
		$this->assertTrue($response->isInformational());
		$response = new \Guzzle\Http\Message\Response(200);
		$this->assertFalse($response->isInformational());
	}
	public function testIsRedirect() 
	{
		$response = new \Guzzle\Http\Message\Response(301);
		$this->assertTrue($response->isRedirect());
		$response = new \Guzzle\Http\Message\Response(200);
		$this->assertFalse($response->isRedirect());
	}
	public function testIsServerError() 
	{
		$response = new \Guzzle\Http\Message\Response(500);
		$this->assertTrue($response->isServerError());
		$response = new \Guzzle\Http\Message\Response(400);
		$this->assertFalse($response->isServerError());
	}
	public function testIsSuccessful() 
	{
		$response = new \Guzzle\Http\Message\Response(200);
		$this->assertTrue($response->isSuccessful());
		$response = new \Guzzle\Http\Message\Response(403);
		$this->assertFalse($response->isSuccessful());
	}
	public function testGetAcceptRanges() 
	{
		$this->assertEquals("bytes", $this->response->getAcceptRanges());
	}
	public function testCalculatesAge() 
	{
		$this->assertEquals(12, $this->response->calculateAge());
		$this->response->removeHeader("Age");
		$this->response->removeHeader("Date");
		$this->assertNull($this->response->calculateAge());
		$this->response->setHeader("Date", gmdate(\Guzzle\Http\ClientInterface::HTTP_DATE, strtotime("-1 minute")));
		$this->assertTrue($this->response->getAge() - 60 <= 5);
	}
	public function testGetAllow() 
	{
		$this->assertEquals("GET, HEAD", $this->response->getAllow());
	}
	public function testGetCacheControl() 
	{
		$this->assertEquals("no-cache", $this->response->getCacheControl());
	}
	public function testGetContentEncoding() 
	{
		$this->assertEquals("gzip", $this->response->getContentEncoding());
	}
	public function testGetContentLanguage() 
	{
		$this->assertEquals("da", $this->response->getContentLanguage());
	}
	public function testGetContentLength() 
	{
		$this->assertEquals("348", $this->response->getContentLength());
	}
	public function testGetContentLocation() 
	{
		$this->assertEquals("/index.htm", $this->response->getContentLocation());
	}
	public function testGetContentDisposition() 
	{
		$this->assertEquals("attachment; filename=fname.ext", $this->response->getContentDisposition());
	}
	public function testGetContentMd5() 
	{
		$this->assertEquals("Q2hlY2sgSW50ZWdyaXR5IQ==", $this->response->getContentMd5());
	}
	public function testGetContentRange() 
	{
		$this->assertEquals("bytes 21010-47021/47022", $this->response->getContentRange());
	}
	public function testGetContentType() 
	{
		$this->assertEquals("text/html; charset=utf-8", $this->response->getContentType());
	}
	public function testGetDate() 
	{
		$this->assertEquals("Tue, 15 Nov 1994 08:12:31 GMT", $this->response->getDate());
	}
	public function testGetEtag() 
	{
		$this->assertEquals("737060cd8c284d8af7ad3082f209582d", $this->response->getEtag());
	}
	public function testGetExpires() 
	{
		$this->assertEquals("Thu, 01 Dec 1994 16:00:00 GMT", $this->response->getExpires());
	}
	public function testGetLastModified() 
	{
		$this->assertEquals("Tue, 15 Nov 1994 12:45:26 GMT", $this->response->getLastModified());
	}
	public function testGetLocation() 
	{
		$this->assertEquals("http://www.w3.org/pub/WWW/People.html", $this->response->getLocation());
	}
	public function testGetPragma() 
	{
		$this->assertEquals("no-cache", $this->response->getPragma());
	}
	public function testGetProxyAuthenticate() 
	{
		$this->assertEquals("Basic", $this->response->getProxyAuthenticate());
	}
	public function testGetServer() 
	{
		$this->assertEquals("Apache/1.3.27 (Unix) (Red-Hat/Linux)", $this->response->getServer());
	}
	public function testGetSetCookie() 
	{
		$this->assertEquals("UserID=JohnDoe; Max-Age=3600; Version=1", $this->response->getSetCookie());
	}
	public function testGetMultipleSetCookie() 
	{
		$this->response->addHeader("Set-Cookie", "UserID=Mike; Max-Age=200");
		$this->assertEquals(array( "UserID=JohnDoe; Max-Age=3600; Version=1", "UserID=Mike; Max-Age=200" ), $this->response->getHeader("Set-Cookie")->toArray());
	}
	public function testGetSetCookieNormalizesHeaders() 
	{
		$this->response->addHeaders(array( "Set-Cooke" => "boo", "set-cookie" => "foo" ));
		$this->assertEquals(array( "UserID=JohnDoe; Max-Age=3600; Version=1", "foo" ), $this->response->getHeader("Set-Cookie")->toArray());
		$this->response->addHeaders(array( "set-cookie" => "fubu" ));
		$this->assertEquals(array( "UserID=JohnDoe; Max-Age=3600; Version=1", "foo", "fubu" ), $this->response->getHeader("Set-Cookie")->toArray());
	}
	public function testGetTrailer() 
	{
		$this->assertEquals("Max-Forwards", $this->response->getTrailer());
	}
	public function testGetTransferEncoding() 
	{
		$this->assertEquals("chunked", $this->response->getTransferEncoding());
	}
	public function testGetVary() 
	{
		$this->assertEquals("*", $this->response->getVary());
	}
	public function testReturnsViaHeader() 
	{
		$this->assertEquals("1.0 fred, 1.1 nowhere.com (Apache/1.1)", $this->response->getVia());
	}
	public function testGetWarning() 
	{
		$this->assertEquals("199 Miscellaneous warning", $this->response->getWarning());
	}
	public function testReturnsWwwAuthenticateHeader() 
	{
		$this->assertEquals("Basic", $this->response->getWwwAuthenticate());
	}
	public function testReturnsConnectionHeader() 
	{
		$this->assertEquals(null, $this->response->getConnection());
		$this->response->setHeader("Connection", "close");
		$this->assertEquals("close", $this->response->getConnection());
	}
	public function testReturnsHeaders() 
	{
		$this->assertEquals("Basic", $this->response->getHeader("WWW-Authenticate", null, true));
		$this->assertEquals("chunked", $this->response->getHeader("Transfer-Encoding", null, false));
	}
	public function testHasTransferInfo() 
	{
		$stats = array( "url" => "http://www.google.com/", "content_type" => "text/html; charset=ISO-8859-1", "http_code" => 200, "header_size" => 606, "request_size" => 53, "filetime" => -1, "ssl_verify_result" => 0, "redirect_count" => 0, "total_time" => 0.093284, "namelookup_time" => 0.001349, "connect_time" => 0.01635, "pretransfer_time" => 0.016358, "size_upload" => 0, "size_download" => 10330, "speed_download" => 110737, "speed_upload" => 0, "download_content_length" => -1, "upload_content_length" => 0, "starttransfer_time" => 0.07066, "redirect_time" => 0 );
		$this->assertNull($this->response->getInfo("url"));
		$this->assertEquals(array( ), $this->response->getInfo());
		$this->response->setInfo($stats);
		$this->assertEquals($stats, $this->response->getInfo());
		$this->assertEquals(606, $this->response->getInfo("header_size"));
		$this->assertNull($this->response->getInfo("does_not_exist"));
	}
	private function getResponse($code, array $headers = NULL, \Guzzle\Http\EntityBody $body = NULL) 
	{
		return new \Guzzle\Http\Message\Response($code, $headers, $body);
	}
	public function testDeterminesIfItCanBeCached() 
	{
		$this->assertTrue($this->getResponse(200)->canCache());
		$this->assertTrue($this->getResponse(410)->canCache());
		$this->assertFalse($this->getResponse(404)->canCache());
		$this->assertTrue($this->getResponse(200, array( "Cache-Control" => "public" ))->canCache());
		$this->assertFalse($this->getResponse(200, array( "Cache-Control" => "private, no-store" ))->canCache());
		$tmp = tempnam("/tmp", "not-readable");
		$resource = fopen($tmp, "w");
		$this->assertFalse($this->getResponse(200, array( "Transfer-Encoding" => "chunked" ), \Guzzle\Http\EntityBody::factory($resource, 10))->canCache());
		unlink($tmp);
		$tmp = tempnam("/tmp", "not-readable");
		$resource = fopen($tmp, "w");
		$this->assertTrue($this->getResponse(200, array( array( "Content-Length" => 0 ) ), \Guzzle\Http\EntityBody::factory($resource, 0))->canCache());
		unlink($tmp);
	}
	public function testDeterminesResponseMaxAge() 
	{
		$this->assertEquals(null, $this->getResponse(200)->getMaxAge());
		$this->assertEquals(140, $this->getResponse(200, array( "Cache-Control" => "s-maxage=140" ))->getMaxAge());
		$this->assertEquals(120, $this->getResponse(200, array( "Cache-Control" => "max-age=120" ))->getMaxAge());
		$this->assertEquals(120, $this->getResponse(200, array( "Cache-Control" => "max-age=120", "Expires" => gmdate(\Guzzle\Http\ClientInterface::HTTP_DATE, strtotime("+1 day")) ))->getMaxAge());
		$this->assertGreaterThanOrEqual(82400, $this->getResponse(200, array( "Expires" => gmdate(\Guzzle\Http\ClientInterface::HTTP_DATE, strtotime("+1 day")) ))->getMaxAge());
		$this->assertGreaterThanOrEqual(82400, $this->getResponse(200, array( "Expires" => gmdate(\Guzzle\Http\ClientInterface::HTTP_DATE, strtotime("+1 day")) ))->getMaxAge());
	}
	public function testDeterminesIfItCanValidate() 
	{
		$response = new \Guzzle\Http\Message\Response(200);
		$this->assertFalse($response->canValidate());
		$response->setHeader("ETag", "123");
		$this->assertTrue($response->canValidate());
		$response->removeHeader("ETag");
		$this->assertFalse($response->canValidate());
		$response->setHeader("Last-Modified", "123");
		$this->assertTrue($response->canValidate());
	}
	public function testCalculatesFreshness() 
	{
		$response = new \Guzzle\Http\Message\Response(200);
		$this->assertNull($response->isFresh());
		$this->assertNull($response->getFreshness());
		$response->setHeader("Cache-Control", "max-age=120");
		$response->setHeader("Age", 100);
		$this->assertEquals(20, $response->getFreshness());
		$this->assertTrue($response->isFresh());
		$response->setHeader("Age", 120);
		$this->assertEquals(0, $response->getFreshness());
		$this->assertTrue($response->isFresh());
		$response->setHeader("Age", 150);
		$this->assertEquals(-30, $response->getFreshness());
		$this->assertFalse($response->isFresh());
	}
	public function testHandlesProtocols() 
	{
		$this->assertSame($this->response, $this->response->setProtocol("HTTP", "1.0"));
		$this->assertEquals("HTTP", $this->response->getProtocol());
		$this->assertEquals("1.0", $this->response->getProtocolVersion());
	}
	public function testComparesContentType() 
	{
		$response = new \Guzzle\Http\Message\Response(200, array( "Content-Type" => "text/html; charset=ISO-8859-4" ));
		$this->assertTrue($response->isContentType("text/html"));
		$this->assertTrue($response->isContentType("TExT/html"));
		$this->assertTrue($response->isContentType("charset=ISO-8859-4"));
		$this->assertFalse($response->isContentType("application/xml"));
	}
	public function testResponseDeterminesIfMethodIsAllowedBaseOnAllowHeader() 
	{
		$response = new \Guzzle\Http\Message\Response(200, array( "Allow" => "OPTIONS, POST, deletE,GET" ));
		$this->assertTrue($response->isMethodAllowed("get"));
		$this->assertTrue($response->isMethodAllowed("GET"));
		$this->assertTrue($response->isMethodAllowed("options"));
		$this->assertTrue($response->isMethodAllowed("post"));
		$this->assertTrue($response->isMethodAllowed("Delete"));
		$this->assertFalse($response->isMethodAllowed("put"));
		$this->assertFalse($response->isMethodAllowed("PUT"));
		$response = new \Guzzle\Http\Message\Response(200);
		$this->assertFalse($response->isMethodAllowed("get"));
	}
	public function testParsesJsonResponses() 
	{
		$response = new \Guzzle\Http\Message\Response(200, array( ), "{\"foo\": \"bar\"}");
		$this->assertEquals(array( "foo" => "bar" ), $response->json());
		$response = new \Guzzle\Http\Message\Response(200);
		$this->assertEquals(array( ), $response->json());
	}
	public function testThrowsExceptionWhenFailsToParseJsonResponse() 
	{
		$response = new \Guzzle\Http\Message\Response(200, array( ), "{\"foo\": \"");
		$response->json();
	}
	public function testParsesXmlResponses() 
	{
		$response = new \Guzzle\Http\Message\Response(200, array( ), "<abc><foo>bar</foo></abc>");
		$this->assertEquals("bar", (string) $response->xml()->foo);
		$response = new \Guzzle\Http\Message\Response(200);
		$this->assertEmpty((string) $response->xml()->foo);
	}
	public function testThrowsExceptionWhenFailsToParseXmlResponse() 
	{
		$response = new \Guzzle\Http\Message\Response(200, array( ), "<abc");
		$response->xml();
	}
	public function testResponseIsSerializable() 
	{
		$response = new \Guzzle\Http\Message\Response(200, array( "Foo" => "bar" ), "test");
		$r = unserialize(serialize($response));
		$this->assertEquals(200, $r->getStatusCode());
		$this->assertEquals("bar", (string) $r->getHeader("Foo"));
		$this->assertEquals("test", (string) $r->getBody());
	}
	public function testPreventsComplexExternalEntities() 
	{
		$xml = "<?xml version=\"1.0\"?><!DOCTYPE scan[<!ENTITY test SYSTEM \"php://filter/read=convert.base64-encode/resource=ResponseTest.php\">]><scan>&test;</scan>";
		$response = new \Guzzle\Http\Message\Response(200, array( ), $xml);
		$oldCwd = getcwd();
		chdir(__DIR__);
		try 
		{
			$xml = $response->xml();
			chdir($oldCwd);
			$this->markTestIncomplete("Did not throw the expected exception! XML resolved as: " . $xml->asXML());
		}
		catch( \Exception $e ) 
		{
			chdir($oldCwd);
		}
	}
}