<?php  namespace Guzzle\Tests\Service\Exception;
class CommandTransferExceptionTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testStoresCommands() 
	{
		$c1 = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$c2 = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$e = new \Guzzle\Service\Exception\CommandTransferException("Test");
		$e->addSuccessfulCommand($c1)->addFailedCommand($c2);
		$this->assertSame(array( $c1 ), $e->getSuccessfulCommands());
		$this->assertSame(array( $c2 ), $e->getFailedCommands());
		$this->assertSame(array( $c1, $c2 ), $e->getAllCommands());
	}
	public function testConvertsMultiExceptionIntoCommandTransfer() 
	{
		$r1 = new \Guzzle\Http\Message\Request("GET", "http://foo.com");
		$r2 = new \Guzzle\Http\Message\Request("GET", "http://foobaz.com");
		$e = new \Guzzle\Http\Exception\MultiTransferException("Test", 123);
		$e->addSuccessfulRequest($r1)->addFailedRequest($r2);
		$ce = \Guzzle\Service\Exception\CommandTransferException::fromMultiTransferException($e);
		$this->assertInstanceOf("Guzzle\\Service\\Exception\\CommandTransferException", $ce);
		$this->assertEquals("Test", $ce->getMessage());
		$this->assertEquals(123, $ce->getCode());
		$this->assertSame(array( $r1 ), $ce->getSuccessfulRequests());
		$this->assertSame(array( $r2 ), $ce->getFailedRequests());
	}
	public function testCanRetrieveExceptionForCommand() 
	{
		$r1 = new \Guzzle\Http\Message\Request("GET", "http://foo.com");
		$e1 = new \Exception("foo");
		$c1 = $this->getMockBuilder("Guzzle\\Tests\\Service\\Mock\\Command\\MockCommand")->setMethods(array( "getRequest" ))->getMock();
		$c1->expects($this->once())->method("getRequest")->will($this->returnValue($r1));
		$e = new \Guzzle\Http\Exception\MultiTransferException("Test", 123);
		$e->addFailedRequestWithException($r1, $e1);
		$ce = \Guzzle\Service\Exception\CommandTransferException::fromMultiTransferException($e);
		$ce->addFailedCommand($c1);
		$this->assertSame($e1, $ce->getExceptionForFailedCommand($c1));
	}
	public function testAddsNonRequestExceptions() 
	{
		$e = new \Guzzle\Http\Exception\MultiTransferException();
		$e->add(new \Exception("bar"));
		$e->addFailedRequestWithException(new \Guzzle\Http\Message\Request("GET", "http://www.foo.com"), new \Exception("foo"));
		$ce = \Guzzle\Service\Exception\CommandTransferException::fromMultiTransferException($e);
		$this->assertEquals(2, count($ce));
	}
}
?>