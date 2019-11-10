<?php  namespace Guzzle\Tests\Http;
class PhpAggregatorTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testEncodes() 
	{
		$query = new \Guzzle\Http\QueryString();
		$query->useUrlEncoding(false);
		$a = new \Guzzle\Http\QueryAggregator\PhpAggregator();
		$key = "t";
		$value = array( "v1" => "a", "v2" => "b", "v3" => array( "v4" => "c", "v5" => "d" ) );
		$result = $a->aggregate($key, $value, $query);
		$this->assertEquals(array( "t[v1]" => "a", "t[v2]" => "b", "t[v3][v4]" => "c", "t[v3][v5]" => "d" ), $result);
	}
}
?>