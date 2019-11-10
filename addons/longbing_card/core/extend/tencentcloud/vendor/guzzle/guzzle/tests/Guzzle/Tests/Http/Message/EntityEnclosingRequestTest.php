<?php  namespace Guzzle\Tests\Http\Message;
class EntityEnclosingRequestTest extends \Guzzle\Tests\GuzzleTestCase 
{
	protected $client = NULL;
	public function setUp() 
	{
		$this->client = new \Guzzle\Http\Client();
	}
	public function tearDown() 
	{
		$this->client = null;
	}
	public function testConstructorConfiguresRequest() 
	{
		$request = new \Guzzle\Http\Message\EntityEnclosingRequest("PUT", "http://test.com", array( "X-Test" => "123" ));
		$request->setBody("Test");
		$this->assertEquals("123", $request->getHeader("X-Test"));
		$this->assertNull($request->getHeader("Expect"));
	}
	public function testCanSetBodyWithoutOverridingContentType() 
	{
		$request = new \Guzzle\Http\Message\EntityEnclosingRequest("PUT", "http://test.com", array( "Content-Type" => "foooooo" ));
		$request->setBody("{\"a\":\"b\"}");
		$this->assertEquals("foooooo", $request->getHeader("Content-Type"));
	}
	public function testRequestIncludesBodyInMessage() 
	{
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("PUT", "http://www.guzzle-project.com/", null, "data");
		$this->assertEquals("PUT / HTTP/1.1\r\n" . "Host: www.guzzle-project.com\r\n" . "Content-Length: 4\r\n\r\n" . "data", (string) $request);
	}
	public function testRequestIncludesPostBodyInMessageOnlyWhenNoPostFiles() 
	{
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("POST", "http://www.guzzle-project.com/", null, array( "foo" => "bar" ));
		$this->assertEquals("POST / HTTP/1.1\r\n" . "Host: www.guzzle-project.com\r\n" . "Content-Type: application/x-www-form-urlencoded; charset=utf-8\r\n\r\n" . "foo=bar", (string) $request);
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("POST", "http://www.guzzle-project.com/", null, array( "foo" => "@" . __FILE__ ));
		$this->assertEquals("POST / HTTP/1.1\r\n" . "Host: www.guzzle-project.com\r\n" . "Content-Type: multipart/form-data\r\n" . "Expect: 100-Continue\r\n\r\n", (string) $request);
	}
	public function testAddsPostFieldsAndSetsContentLength() 
	{
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("POST", "http://www.guzzle-project.com/", null, array( "data" => "123" ));
		$this->assertEquals("POST / HTTP/1.1\r\n" . "Host: www.guzzle-project.com\r\n" . "Content-Type: application/x-www-form-urlencoded; charset=utf-8\r\n\r\n" . "data=123", (string) $request);
	}
	public function testAddsPostFilesAndSetsContentType() 
	{
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("POST", "http://www.test.com/")->addPostFiles(array( "file" => __FILE__ ))->addPostFields(array( "a" => "b" ));
		$message = (string) $request;
		$this->assertEquals("multipart/form-data", $request->getHeader("Content-Type"));
		$this->assertEquals("100-Continue", $request->getHeader("Expect"));
	}
	public function testRequestBodyContainsPostFiles() 
	{
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("POST", "http://www.test.com/");
		$request->addPostFields(array( "test" => "123" ));
		$this->assertContains("\r\n\r\ntest=123", (string) $request);
	}
	public function testRequestBodyAddsContentLength() 
	{
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("PUT", "http://www.test.com/");
		$request->setBody(\Guzzle\Http\EntityBody::factory("test"));
		$this->assertEquals(4, (string) $request->getHeader("Content-Length"));
		$this->assertFalse($request->hasHeader("Transfer-Encoding"));
	}
	public function testRequestBodyDoesNotUseContentLengthWhenChunked() 
	{
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("PUT", "http://www.test.com/", array( "Transfer-Encoding" => "chunked" ), "test");
		$this->assertNull($request->getHeader("Content-Length"));
		$this->assertTrue($request->hasHeader("Transfer-Encoding"));
	}
	public function testRequestHasMutableBody() 
	{
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("PUT", "http://www.guzzle-project.com/", null, "data");
		$body = $request->getBody();
		$this->assertInstanceOf("Guzzle\\Http\\EntityBody", $body);
		$this->assertSame($body, $request->getBody());
		$newBody = \Guzzle\Http\EntityBody::factory("foobar");
		$request->setBody($newBody);
		$this->assertEquals("foobar", (string) $request->getBody());
		$this->assertSame($newBody, $request->getBody());
	}
	public function testSetPostFields() 
	{
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("POST", "http://www.guzzle-project.com/");
		$this->assertInstanceOf("Guzzle\\Http\\QueryString", $request->getPostFields());
		$fields = new \Guzzle\Http\QueryString(array( "a" => "b" ));
		$request->addPostFields($fields);
		$this->assertEquals($fields->getAll(), $request->getPostFields()->getAll());
		$this->assertEquals(array( ), $request->getPostFiles());
	}
	public function testSetPostFiles() 
	{
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("POST", $this->getServer()->getUrl())->setClient(new \Guzzle\Http\Client())->addPostFiles(array( __FILE__ ))->addPostFields(array( "test" => "abc" ));
		$request->getCurlOptions()->set("debug", true);
		$this->assertEquals(array( "test" => "abc" ), $request->getPostFields()->getAll());
		$files = $request->getPostFiles();
		$post = $files["file"][0];
		$this->assertEquals("file", $post->getFieldName());
		$this->assertContains("text/x-", $post->getContentType());
		$this->assertEquals(__FILE__, $post->getFilename());
		$this->getServer()->enqueue("HTTP/1.1 200 OK\r\nContent-Length: 0\r\n\r\n");
		$request->send();
		$this->assertNotNull($request->getHeader("Content-Length"));
		$this->assertContains("multipart/form-data; boundary=", (string) $request->getHeader("Content-Type"), "-> cURL must add the boundary");
	}
	public function testSetPostFilesThrowsExceptionWhenFileIsNotFound() 
	{
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("POST", "http://www.guzzle-project.com/")->addPostFiles(array( "file" => "filenotfound.ini" ));
	}
	public function testThrowsExceptionWhenNonStringsAreAddedToPost() 
	{
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("POST", "http://www.guzzle-project.com/")->addPostFile("foo", new \stdClass());
	}
	public function testAllowsContentTypeInPostUploads() 
	{
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("POST", "http://www.guzzle-project.com/")->addPostFile("foo", __FILE__, "text/plain");
		$this->assertEquals(array( new \Guzzle\Http\Message\PostFile("foo", __FILE__, "text/plain") ), $request->getPostFile("foo"));
	}
	public function testGuessesContentTypeOfPostUpload() 
	{
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("POST", "http://www.guzzle-project.com/")->addPostFile("foo", __FILE__);
		$file = $request->getPostFile("foo");
		$this->assertContains("text/x-", $file[0]->getContentType());
	}
	public function testAllowsContentDispositionFieldsInPostUploadsWhenSettingInBulk() 
	{
		$postFile = new \Guzzle\Http\Message\PostFile("foo", __FILE__, "text/x-php");
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("POST", "http://www.guzzle-project.com/")->addPostFiles(array( "foo" => $postFile ));
		$this->assertEquals(array( $postFile ), $request->getPostFile("foo"));
	}
	public function testPostRequestsUseApplicationXwwwForUrlEncodedForArrays() 
	{
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("POST", "http://www.guzzle-project.com/");
		$request->setPostField("a", "b");
		$this->assertContains("\r\n\r\na=b", (string) $request);
		$this->assertEquals("application/x-www-form-urlencoded; charset=utf-8", $request->getHeader("Content-Type"));
	}
	public function testProcessMethodAddsContentType() 
	{
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("POST", "http://www.guzzle-project.com/");
		$request->setPostField("a", "b");
		$this->assertEquals("application/x-www-form-urlencoded; charset=utf-8", $request->getHeader("Content-Type"));
	}
	public function testPostRequestsUseMultipartFormDataWithFiles() 
	{
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("POST", "http://www.guzzle-project.com/");
		$request->addPostFiles(array( "file" => __FILE__ ));
		$this->assertEquals("multipart/form-data", $request->getHeader("Content-Type"));
	}
	public function testCanSendMultipleRequestsUsingASingleRequestObject() 
	{
		$this->getServer()->flush();
		$this->getServer()->enqueue(array( "HTTP/1.1 200 OK\r\nContent-Length: 0\r\n\r\n", "HTTP/1.1 201 Created\r\nContent-Length: 0\r\n\r\n" ));
		$request = \Guzzle\Http\Message\RequestFactory::getInstance()->create("PUT", $this->getServer()->getUrl())->setBody("test")->setClient(new \Guzzle\Http\Client());
		$request->send();
		$this->assertEquals(200, $request->getResponse()->getStatusCode());
		$request->setBody("abcdefg", "application/json", false);
		$request->send();
		$this->assertEquals(201, $request->getResponse()->getStatusCode());
		$requests = $this->getServer()->getReceivedRequests(true);
		$this->assertEquals(2, count($requests));
		$this->assertEquals(4, (string) $requests[0]->getHeader("Content-Length"));
		$this->assertEquals(7, (string) $requests[1]->getHeader("Content-Length"));
	}
	public function testRemovingPostFieldRebuildsPostFields() 
	{
		$request = new \Guzzle\Http\Message\EntityEnclosingRequest("POST", "http://test.com");
		$request->setPostField("test", "value");
		$request->removePostField("test");
		$this->assertNull($request->getPostField("test"));
	}
	public function testUsesChunkedTransferWhenBodyLengthCannotBeDetermined() 
	{
		$this->getServer()->enqueue("HTTP/1.1 200 OK\r\nContent-Length: 0\r\n\r\n");
		$request = new \Guzzle\Http\Message\EntityEnclosingRequest("PUT", "http://test.com/");
		$request->setBody(fopen($this->getServer()->getUrl(), "r"));
		$this->assertEquals("chunked", $request->getHeader("Transfer-Encoding"));
		$this->assertFalse($request->hasHeader("Content-Length"));
	}
	public function testThrowsExceptionWhenContentLengthCannotBeDeterminedAndUsingHttp1() 
	{
		$request = new \Guzzle\Http\Message\EntityEnclosingRequest("PUT", "http://test.com/");
		$this->getServer()->enqueue("HTTP/1.1 200 OK\r\nContent-Length: 0\r\n\r\n");
		$request->setProtocolVersion("1.0");
		$request->setBody(fopen($this->getServer()->getUrl(), "r"));
	}
	public function testAllowsNestedPostData() 
	{
		$request = new \Guzzle\Http\Message\EntityEnclosingRequest("POST", "http://test.com/");
		$request->addPostFields(array( "a" => array( "b", "c" ) ));
		$this->assertEquals(array( "a" => array( "b", "c" ) ), $request->getPostFields()->getAll());
	}
	public function testAllowsEmptyFields() 
	{
		$request = new \Guzzle\Http\Message\EntityEnclosingRequest("POST", "http://test.com/");
		$request->addPostFields(array( "a" => "" ));
		$this->assertEquals(array( "a" => "" ), $request->getPostFields()->getAll());
	}
	public function testFailsOnInvalidFiles() 
	{
		$request = new \Guzzle\Http\Message\EntityEnclosingRequest("POST", "http://test.com/");
		$request->addPostFiles(array( "a" => new \stdClass() ));
	}
	public function testHandlesEmptyStrings() 
	{
		$request = new \Guzzle\Http\Message\EntityEnclosingRequest("POST", "http://test.com/");
		$request->addPostFields(array( "a" => "", "b" => null, "c" => "Foo" ));
		$this->assertEquals(array( "a" => "", "b" => null, "c" => "Foo" ), $request->getPostFields()->getAll());
	}
	public function testHoldsPostFiles() 
	{
		$request = new \Guzzle\Http\Message\EntityEnclosingRequest("POST", "http://test.com/");
		$request->addPostFile("foo", __FILE__);
		$request->addPostFile(new \Guzzle\Http\Message\PostFile("foo", __FILE__));
		$this->assertArrayHasKey("foo", $request->getPostFiles());
		$foo = $request->getPostFile("foo");
		$this->assertEquals(2, count($foo));
		$this->assertEquals(__FILE__, $foo[0]->getFilename());
		$this->assertEquals(__FILE__, $foo[1]->getFilename());
		$request->removePostFile("foo");
		$this->assertEquals(array( ), $request->getPostFiles());
	}
	public function testAllowsAtPrefixWhenAddingPostFiles() 
	{
		$request = new \Guzzle\Http\Message\EntityEnclosingRequest("POST", "http://test.com/");
		$request->addPostFiles(array( "foo" => "@" . __FILE__ ));
		$foo = $request->getPostFile("foo");
		$this->assertEquals(__FILE__, $foo[0]->getFilename());
	}
	public function testSetStateToTransferWithEmptyBodySetsContentLengthToZero() 
	{
		$request = new \Guzzle\Http\Message\EntityEnclosingRequest("POST", "http://test.com/");
		$request->setState($request::STATE_TRANSFER);
		$this->assertEquals("0", (string) $request->getHeader("Content-Length"));
	}
	public function testSettingExpectHeaderCutoffChangesRequest() 
	{
		$request = new \Guzzle\Http\Message\EntityEnclosingRequest("PUT", "http://test.com/");
		$request->setHeader("Expect", "100-Continue");
		$request->setExpectHeaderCutoff(false);
		$this->assertNull($request->getHeader("Expect"));
		$request->setHeader("Expect", "100-Continue");
		$request->setExpectHeaderCutoff(10);
		$this->assertNull($request->getHeader("Expect"));
		$request->setBody("foo");
		$this->assertNull($request->getHeader("Expect"));
		$request->setBody("foobazbarbamboo");
		$this->assertNotNull($request->getHeader("Expect"));
	}
	public function testStrictRedirectsCanBeSpecifiedOnEntityEnclosingRequests() 
	{
		$request = new \Guzzle\Http\Message\EntityEnclosingRequest("PUT", "http://test.com/");
		$request->configureRedirects(true);
		$this->assertTrue($request->getParams()->get(\Guzzle\Http\RedirectPlugin::STRICT_REDIRECTS));
	}
	public function testCanDisableRedirects() 
	{
		$request = new \Guzzle\Http\Message\EntityEnclosingRequest("PUT", "http://test.com/");
		$request->configureRedirects(false, false);
		$this->assertTrue($request->getParams()->get(\Guzzle\Http\RedirectPlugin::DISABLE));
	}
	public function testSetsContentTypeWhenSettingBodyByGuessingFromEntityBody() 
	{
		$request = new \Guzzle\Http\Message\EntityEnclosingRequest("PUT", "http://test.com/foo");
		$request->setBody(\Guzzle\Http\EntityBody::factory(fopen(__FILE__, "r")));
		$this->assertEquals("text/x-php", (string) $request->getHeader("Content-Type"));
	}
	public function testDoesNotCloneBody() 
	{
		$request = new \Guzzle\Http\Message\EntityEnclosingRequest("PUT", "http://test.com/foo");
		$request->setBody("test");
		$newRequest = clone $request;
		$newRequest->setBody("foo");
		$this->assertInternalType("string", (string) $request->getBody());
	}
}
?>