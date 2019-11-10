<?php  namespace Guzzle\Tests\Plugin\Cache;
class SkipRevalidationTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testSkipsRequestRevalidation() 
	{
		$skip = new \Guzzle\Plugin\Cache\SkipRevalidation();
		$this->assertTrue($skip->revalidate(new \Guzzle\Http\Message\Request("GET", "http://foo.com"), new \Guzzle\Http\Message\Response(200)));
	}
}
?>