<?php  namespace Guzzle\Tests\Plugin\Cache;
class DefaultCanCacheStrategyTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testReturnsRequestcanCacheRequest() 
	{
		$strategy = new \Guzzle\Plugin\Cache\DefaultCanCacheStrategy();
		$request = new \Guzzle\Http\Message\Request("GET", "http://foo.com");
		$this->assertTrue($strategy->canCacheRequest($request));
	}
	public function testDoesNotCacheNoStore() 
	{
		$strategy = new \Guzzle\Plugin\Cache\DefaultCanCacheStrategy();
		$request = new \Guzzle\Http\Message\Request("GET", "http://foo.com", array( "cache-control" => "no-store" ));
		$this->assertFalse($strategy->canCacheRequest($request));
	}
	public function testCanCacheResponse() 
	{
		$response = $this->getMockBuilder("Guzzle\\Http\\Message\\Response")->setMethods(array( "canCache" ))->setConstructorArgs(array( 200 ))->getMock();
		$response->expects($this->once())->method("canCache")->will($this->returnValue(true));
		$strategy = new \Guzzle\Plugin\Cache\DefaultCanCacheStrategy();
		$this->assertTrue($strategy->canCacheResponse($response));
	}
}
?>