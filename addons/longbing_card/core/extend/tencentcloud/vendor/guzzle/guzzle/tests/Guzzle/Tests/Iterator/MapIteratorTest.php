<?php  namespace Guzzle\Tests\Iterator;
class MapIteratorTest extends \PHPUnit_Framework_TestCase 
{
	public function testFiltersValues() 
	{
		$i = new \Guzzle\Iterator\MapIterator(new \ArrayIterator(range(0, 100)), function($value) 
		{
			return $value * 10;
		}
		);
		$this->assertEquals(range(0, 1000, 10), iterator_to_array($i, false));
	}
	public function testValidatesCallable() 
	{
		$i = new \Guzzle\Iterator\MapIterator(new \ArrayIterator(), new \stdClass());
	}
}
?>