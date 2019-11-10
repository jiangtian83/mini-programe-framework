<?php  namespace Guzzle\Tests\Http\Exception;
class MultiTransferExceptionTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testHasRequests() 
	{
		$r1 = new \Guzzle\Http\Message\Request("GET", "http://www.foo.com");
		$r2 = new \Guzzle\Http\Message\Request("GET", "http://www.foo.com");
		$e = new \Guzzle\Http\Exception\MultiTransferException();
		$e->addSuccessfulRequest($r1);
		$e->addFailedRequest($r2);
		$this->assertEquals(array( $r1 ), $e->getSuccessfulRequests());
		$this->assertEquals(array( $r2 ), $e->getSuccessfulRequests());
		$this->assertEquals(array( $r1, $r2 ), $e->getAllRequests());
		$this->assertTrue($e->containsRequest($r1));
		$this->assertTrue($e->containsRequest($r2));
		$this->assertFalse($e->containsRequest(new \Guzzle\Http\Message\Request("POST", "/foo")));
	}
	public function testCanSetRequests() 
	{
		$s = array( $r1 = new \Guzzle\Http\Message\Request("GET", "http://www.foo.com") );
		$f = array( $r2 = new \Guzzle\Http\Message\Request("GET", "http://www.foo.com") );
		$e = new \Guzzle\Http\Exception\MultiTransferException();
		$e->setSuccessfulRequests($s);
		$e->setFailedRequests($f);
		$this->assertEquals(array( $r1 ), $e->getSuccessfulRequests());
		$this->assertEquals(array( $r2 ), $e->getSuccessfulRequests());
	}
	public function testAssociatesExceptionsWithRequests() 
	{
		$r1 = new \Guzzle\Http\Message\Request("GET", "http://www.foo.com");
		$re1 = new \Exception("foo");
		$re2 = new \Exception("bar");
		$e = new \Guzzle\Http\Exception\MultiTransferException();
		$e->add($re2);
		$e->addFailedRequestWithException($r1, $re1);
		$this->assertSame($re1, $e->getExceptionForFailedRequest($r1));
		$this->assertNull($e->getExceptionForFailedRequest(new \Guzzle\Http\Message\Request("POST", "/foo")));
	}
}
?>