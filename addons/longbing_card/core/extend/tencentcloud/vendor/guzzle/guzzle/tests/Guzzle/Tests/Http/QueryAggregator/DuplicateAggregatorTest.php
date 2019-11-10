<?php  namespace Guzzle\Tests\Http;
class DuplicateAggregatorTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testAggregates() 
	{
		$query = new \Guzzle\Http\QueryString();
		$a = new \Guzzle\Http\QueryAggregator\DuplicateAggregator();
		$key = "facet 1";
		$value = array( "size a", "width b" );
		$result = $a->aggregate($key, $value, $query);
		$this->assertEquals(array( "facet%201" => array( "size%20a", "width%20b" ) ), $result);
	}
	public function testEncodes() 
	{
		$query = new \Guzzle\Http\QueryString();
		$query->useUrlEncoding(false);
		$a = new \Guzzle\Http\QueryAggregator\DuplicateAggregator();
		$key = "facet 1";
		$value = array( "size a", "width b" );
		$result = $a->aggregate($key, $value, $query);
		$this->assertEquals(array( "facet 1" => array( "size a", "width b" ) ), $result);
	}
}
?>