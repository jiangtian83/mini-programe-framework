<?php  namespace Guzzle\Tests\Message;
class HeaderComparisonTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function filterProvider() 
	{
		return array( array( array( "Content-Length" => "Foo" ), array( "Content-Length" => "Foo" ), false ), array( array( "X-Foo" => "Bar" ), array( ), array( "- X-Foo" => "Bar" ) ), array( array( "X-Foo" => "Bar" ), array( "X-Foo" => "Bar", "X-Baz" => "Jar" ), array( "+ X-Baz" => "Jar" ) ), array( array( "!X-Foo" => "*" ), array( "X-Foo" => "Bar" ), array( "++ X-Foo" => "Bar" ) ), array( array( "X-Foo" => "Bar" ), array( "X-Foo" => "Baz" ), array( "X-Foo" => "Baz != Bar" ) ), array( array( "X-Foo" => "*" ), array( "X-Foo" => "Bar" ), false ), array( array( "X-Foo" => "*" ), array( ), array( "- X-Foo" => "*" ) ), array( array( "X-Foo" => "*", "_X-Bar" => "*" ), array( "X-Foo" => "Baz", "X-Bar" => "Jar" ), false ), array( array( "X-Foo" => "*", "_X-Bar" => "*" ), array( "X-Foo" => "Baz" ), false ), array( array( "X-Foo" => "*", "_X-Bar" => "*" ), array( "x-foo" => "Baz", "x-BAR" => "baz" ), false ), array( array( "X-Foo" => "*", "_X-Bar" => "*" ), new \Guzzle\Common\Collection(array( "x-foo" => "Baz", "x-BAR" => "baz" )), false ) );
	}
	public function testComparesHeaders($filters, $headers, $result) 
	{
		$compare = new \Guzzle\Tests\Http\Message\HeaderComparison();
		$this->assertEquals($result, $compare->compare($filters, $headers));
	}
}
?>