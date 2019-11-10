<?php  namespace Guzzle\Tests\Plugin\Cache;
class DenyRevalidationTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testDeniesRequestRevalidation() 
	{
		$deny = new \Guzzle\Plugin\Cache\DenyRevalidation();
		$this->assertFalse($deny->revalidate(new \Guzzle\Http\Message\Request("GET", "http://foo.com"), new \Guzzle\Http\Message\Response(200)));
	}
}
?>