<?php  namespace Guzzle\Tests\Http;
class CommaAggregatorTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testAggregates() 
	{
		$query = new \Guzzle\Http\QueryString();
		$a = new \Guzzle\Http\QueryAggregator\CommaAggregator();
		$key = "test 123";
		$value = array( "foo 123", "baz", "bar" );
		$result = $a->aggregate($key, $value, $query);
		$this->assertEquals(array( "test%20123" => "foo%20123,baz,bar" ), $result);
	}
	public function testEncodes() 
	{
		$query = new \Guzzle\Http\QueryString();
		$query->useUrlEncoding(false);
		$a = new \Guzzle\Http\QueryAggregator\CommaAggregator();
		$key = "test 123";
		$value = array( "foo 123", "baz", "bar" );
		$result = $a->aggregate($key, $value, $query);
		$this->assertEquals(array( "test 123" => "foo 123,baz,bar" ), $result);
	}
}
?>