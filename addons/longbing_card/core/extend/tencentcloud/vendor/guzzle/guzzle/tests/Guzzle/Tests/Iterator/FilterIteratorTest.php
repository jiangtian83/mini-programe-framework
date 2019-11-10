<?php  namespace Guzzle\Tests\Iterator;
class FilterIteratorTest extends \PHPUnit_Framework_TestCase 
{
	public function testFiltersValues() 
	{
		$i = new \Guzzle\Iterator\FilterIterator(new \ArrayIterator(range(0, 100)), function($value) 
		{
			return $value % 2;
		}
		);
		$this->assertEquals(range(1, 99, 2), iterator_to_array($i, false));
	}
	public function testValidatesCallable() 
	{
		$i = new \Guzzle\Iterator\FilterIterator(new \ArrayIterator(), new \stdClass());
	}
}
?>