<?php  namespace Guzzle\Tests\Http;
class EntityBodyTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testFactoryThrowsException() 
	{
		$body = \Guzzle\Http\EntityBody::factory(false);
	}
	public function testFactory() 
	{
		$body = \Guzzle\Http\EntityBody::factory("data");
		$this->assertEquals("data", (string) $body);
		$this->assertEquals(4, $body->getContentLength());
		$this->assertEquals("PHP", $body->getWrapper());
		$this->assertEquals("TEMP", $body->getStreamType());
		$handle = fopen(__DIR__ . "/../../../../phpunit.xml.dist", "r");
		if( !$handle ) 
		{
			$this->fail("Could not open test file");
		}
		$body = \Guzzle\Http\EntityBody::factory($handle);
		$this->assertEquals(__DIR__ . "/../../../../phpunit.xml.dist", $body->getUri());
		$this->assertTrue($body->isLocal());
		$this->assertEquals(__DIR__ . "/../../../../phpunit.xml.dist", $body->getUri());
		$this->assertEquals(filesize(__DIR__ . "/../../../../phpunit.xml.dist"), $body->getContentLength());
		$this->assertTrue($body === \Guzzle\Http\EntityBody::factory($body));
	}
	public function testFactoryCreatesTempStreamByDefault() 
	{
		$body = \Guzzle\Http\EntityBody::factory("");
		$this->assertEquals("PHP", $body->getWrapper());
		$this->assertEquals("TEMP", $body->getStreamType());
		$body = \Guzzle\Http\EntityBody::factory();
		$this->assertEquals("PHP", $body->getWrapper());
		$this->assertEquals("TEMP", $body->getStreamType());
	}
	public function testFactoryCanCreateFromObject() 
	{
		$body = \Guzzle\Http\EntityBody::factory(new \Guzzle\Http\QueryString(array( "foo" => "bar" )));
		$this->assertEquals("foo=bar", (string) $body);
	}
	public function testFactoryEnsuresObjectsHaveToStringMethod() 
	{
		\Guzzle\Http\EntityBody::factory(new \stdClass("a"));
	}
	public function testHandlesCompression() 
	{
		$body = \Guzzle\Http\EntityBody::factory("testing 123...testing 123");
		$this->assertFalse($body->getContentEncoding(), "-> getContentEncoding() must initially return FALSE");
		$size = $body->getContentLength();
		$body->compress();
		$this->assertEquals("gzip", $body->getContentEncoding(), "-> getContentEncoding() must return the correct encoding after compressing");
		$this->assertEquals(gzdeflate("testing 123...testing 123"), (string) $body);
		$this->assertTrue($body->getContentLength() < $size);
		$this->assertTrue($body->uncompress());
		$this->assertEquals("testing 123...testing 123", (string) $body);
		$this->assertFalse($body->getContentEncoding(), "-> getContentEncoding() must reset to FALSE");
		if( in_array("bzip2.*", stream_get_filters()) ) 
		{
			$this->assertTrue($body->compress("bzip2.compress"));
			$this->assertEquals("compress", $body->getContentEncoding(), "-> compress() must set 'compress' as the Content-Encoding");
		}
		$this->assertFalse($body->compress("non-existent"), "-> compress() must return false when a non-existent stream filter is used");
		unset($body);
		$body = \Guzzle\Http\EntityBody::factory(gzencode("test"));
		$this->assertSame($body, $body->setStreamFilterContentEncoding("zlib.deflate"));
		$this->assertTrue($body->uncompress("zlib.inflate"));
		$this->assertEquals("test", (string) $body);
		unset($body);
		$largeString = "";
		for( $i = 0; $i < 25000; $i++ ) 
		{
			$largeString .= chr(rand(33, 126));
		}
		$body = \Guzzle\Http\EntityBody::factory($largeString);
		$this->assertEquals($largeString, (string) $body);
		$this->assertTrue($body->compress());
		$this->assertNotEquals($largeString, (string) $body);
		$compressed = (string) $body;
		$this->assertTrue($body->uncompress());
		$this->assertEquals($largeString, (string) $body);
		$this->assertEquals($compressed, gzdeflate($largeString));
		$body = \Guzzle\Http\EntityBody::factory(fopen(__DIR__ . "/../TestData/compress_test", "w"));
		$this->assertFalse($body->compress());
		unset($body);
		unlink(__DIR__ . "/../TestData/compress_test");
	}
	public function testDeterminesContentType() 
	{
		$body = \Guzzle\Http\EntityBody::factory("testing 123...testing 123");
		$this->assertNull($body->getContentType());
		$body = \Guzzle\Http\EntityBody::factory(fopen(__FILE__, "r"));
		$this->assertContains("text/x-", $body->getContentType());
	}
	public function testCreatesMd5Checksum() 
	{
		$body = \Guzzle\Http\EntityBody::factory("testing 123...testing 123");
		$this->assertEquals(md5("testing 123...testing 123"), $body->getContentMd5());
		$server = $this->getServer()->enqueue("HTTP/1.1 200 OK" . "\r\n" . "Content-Length: 3" . "\r\n\r\n" . "abc");
		$body = \Guzzle\Http\EntityBody::factory(fopen($this->getServer()->getUrl(), "r"));
		$this->assertFalse($body->getContentMd5());
	}
	public function testSeeksToOriginalPosAfterMd5() 
	{
		$body = \Guzzle\Http\EntityBody::factory("testing 123");
		$body->seek(4);
		$this->assertEquals(md5("testing 123"), $body->getContentMd5());
		$this->assertEquals(4, $body->ftell());
		$this->assertEquals("ing 123", $body->read(1000));
	}
	public function testGetTypeFormBodyFactoring() 
	{
		$body = \Guzzle\Http\EntityBody::factory(array( "key1" => "val1", "key2" => "val2" ));
		$this->assertEquals("key1=val1&key2=val2", (string) $body);
	}
	public function testAllowsCustomRewind() 
	{
		$body = \Guzzle\Http\EntityBody::factory("foo");
		$rewound = false;
		$body->setRewindFunction(function($body) use (&$rewound) 
		{
			$rewound = true;
			return $body->seek(0);
		}
		);
		$body->seek(2);
		$this->assertTrue($body->rewind());
		$this->assertTrue($rewound);
	}
	public function testCustomRewindFunctionMustBeCallable() 
	{
		$body = \Guzzle\Http\EntityBody::factory();
		$body->setRewindFunction("foo");
	}
}
?>