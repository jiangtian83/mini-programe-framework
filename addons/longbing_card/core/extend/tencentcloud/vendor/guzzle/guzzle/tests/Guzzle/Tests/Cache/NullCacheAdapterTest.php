<?php  namespace Guzzle\Tests\Common\Cache;
class NullCacheAdapterTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testNullCacheAdapter() 
	{
		$c = new \Guzzle\Cache\NullCacheAdapter();
		$this->assertEquals(false, $c->contains("foo"));
		$this->assertEquals(true, $c->delete("foo"));
		$this->assertEquals(false, $c->fetch("foo"));
		$this->assertEquals(true, $c->save("foo", "bar"));
	}
}
?>