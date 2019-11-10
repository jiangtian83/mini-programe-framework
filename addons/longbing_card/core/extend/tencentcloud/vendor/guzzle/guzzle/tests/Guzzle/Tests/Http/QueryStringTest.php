<?php  namespace Guzzle\Tests\Http;
class QueryStringTest extends \Guzzle\Tests\GuzzleTestCase 
{
	protected $q = NULL;
	public function setup() 
	{
		$this->q = new \Guzzle\Http\QueryString();
	}
	public function testGetFieldSeparator() 
	{
		$this->assertEquals("&", $this->q->getFieldSeparator());
	}
	public function testGetValueSeparator() 
	{
		$this->assertEquals("=", $this->q->getValueSeparator());
	}
	public function testIsUrlEncoding() 
	{
		$this->assertEquals("RFC 3986", $this->q->getUrlEncoding());
		$this->assertTrue($this->q->isUrlEncoding());
		$this->assertEquals("foo%20bar", $this->q->encodeValue("foo bar"));
		$this->q->useUrlEncoding(\Guzzle\Http\QueryString::FORM_URLENCODED);
		$this->assertTrue($this->q->isUrlEncoding());
		$this->assertEquals(\Guzzle\Http\QueryString::FORM_URLENCODED, $this->q->getUrlEncoding());
		$this->assertEquals("foo+bar", $this->q->encodeValue("foo bar"));
		$this->assertSame($this->q, $this->q->useUrlEncoding(false));
		$this->assertFalse($this->q->isUrlEncoding());
		$this->assertFalse($this->q->isUrlEncoding());
	}
	public function testSetFieldSeparator() 
	{
		$this->assertEquals($this->q, $this->q->setFieldSeparator("/"));
		$this->assertEquals("/", $this->q->getFieldSeparator());
	}
	public function testSetValueSeparator() 
	{
		$this->assertEquals($this->q, $this->q->setValueSeparator("/"));
		$this->assertEquals("/", $this->q->getValueSeparator());
	}
	public function testUrlEncode() 
	{
		$params = array( "test" => "value", "test 2" => "this is a test?", "test3" => array( "v1", "v2", "v3" ), "ሴ" => "bar" );
		$encoded = array( "test" => "value", "test%202" => rawurlencode("this is a test?"), "test3%5B0%5D" => "v1", "test3%5B1%5D" => "v2", "test3%5B2%5D" => "v3", "%E1%88%B4" => "bar" );
		$this->q->replace($params);
		$this->assertEquals($encoded, $this->q->urlEncode());
		$testData = array( "test 2" => "this is a test" );
		$this->q->replace($testData);
		$this->q->useUrlEncoding(false);
		$this->assertEquals($testData, $this->q->urlEncode());
	}
	public function testToString() 
	{
		$this->assertEquals("", $this->q->__toString());
		$params = array( "test" => "value", "test 2" => "this is a test?", "test3" => array( "v1", "v2", "v3" ), "test4" => null );
		$this->q->replace($params);
		$this->assertEquals("test=value&test%202=this%20is%20a%20test%3F&test3%5B0%5D=v1&test3%5B1%5D=v2&test3%5B2%5D=v3&test4", $this->q->__toString());
		$this->q->useUrlEncoding(false);
		$this->assertEquals("test=value&test 2=this is a test?&test3[0]=v1&test3[1]=v2&test3[2]=v3&test4", $this->q->__toString());
		$this->q->setAggregator(new \Guzzle\Http\QueryAggregator\CommaAggregator());
		$this->assertEquals("test=value&test 2=this is a test?&test3=v1,v2,v3&test4", $this->q->__toString());
	}
	public function testAllowsMultipleValuesPerKey() 
	{
		$q = new \Guzzle\Http\QueryString();
		$q->add("facet", "size");
		$q->add("facet", "width");
		$q->add("facet.field", "foo");
		$q->setAggregator(new \Guzzle\Http\QueryAggregator\DuplicateAggregator());
		$this->assertEquals("facet=size&facet=width&facet.field=foo", $q->__toString());
	}
	public function testAllowsNestedQueryData() 
	{
		$this->q->replace(array( "test" => "value", "t" => array( "v1" => "a", "v2" => "b", "v3" => array( "v4" => "c", "v5" => "d" ) ) ));
		$this->q->useUrlEncoding(false);
		$this->assertEquals("test=value&t[v1]=a&t[v2]=b&t[v3][v4]=c&t[v3][v5]=d", $this->q->__toString());
	}
	public function parseQueryProvider() 
	{
		return array( array( "q=a&q=b", array( "q" => array( "a", "b" ) ) ), array( "q[]=a&q[]=b", array( "q" => array( "a", "b" ) ) ), array( "q[]=a", array( "q" => array( "a" ) ) ), array( "q.a=a&q.b=b", array( "q.a" => "a", "q.b" => "b" ) ), array( "q%20a=a%20b", array( "q a" => "a b" ) ), array( "q&a", array( "q" => false, "a" => false ) ) );
	}
	public function testParsesQueryStrings($query, $data) 
	{
		$query = \Guzzle\Http\QueryString::fromString($query);
		$this->assertEquals($data, $query->getAll());
	}
	public function testProperlyDealsWithDuplicateQueryStringValues() 
	{
		$query = \Guzzle\Http\QueryString::fromString("foo=a&foo=b&?µ=c");
		$this->assertEquals(array( "a", "b" ), $query->get("foo"));
		$this->assertEquals("c", $query->get("?µ"));
	}
	public function testAllowsBlankQueryStringValues() 
	{
		$query = \Guzzle\Http\QueryString::fromString("foo");
		$this->assertEquals("foo", (string) $query);
		$query->set("foo", \Guzzle\Http\QueryString::BLANK);
		$this->assertEquals("foo", (string) $query);
	}
	public function testAllowsFalsyQueryStringValues() 
	{
		$query = \Guzzle\Http\QueryString::fromString("0");
		$this->assertEquals("0", (string) $query);
		$query->set("0", \Guzzle\Http\QueryString::BLANK);
		$this->assertSame("0", (string) $query);
	}
	public function testFromStringIgnoresQuestionMark() 
	{
		$query = \Guzzle\Http\QueryString::fromString("foo=baz&bar=boo");
		$this->assertEquals("foo=baz&bar=boo", (string) $query);
	}
	public function testConvertsPlusSymbolsToSpaces() 
	{
		$query = \Guzzle\Http\QueryString::fromString("var=foo+bar");
		$this->assertEquals("foo bar", $query->get("var"));
	}
	public function testFromStringDoesntMangleZeroes() 
	{
		$query = \Guzzle\Http\QueryString::fromString("var=0");
		$this->assertSame("0", $query->get("var"));
	}
	public function testAllowsZeroValues() 
	{
		$query = new \Guzzle\Http\QueryString(array( "foo" => 0, "baz" => "0", "bar" => null, "boo" => false, "bam" => "" ));
		$this->assertEquals("foo=0&baz=0&bar&boo&bam=", (string) $query);
	}
	public function testFromStringDoesntStripTrailingEquals() 
	{
		$query = \Guzzle\Http\QueryString::fromString("data=mF0b3IiLCJUZWFtIERldiJdfX0=");
		$this->assertEquals("mF0b3IiLCJUZWFtIERldiJdfX0=", $query->get("data"));
	}
	public function testGuessesIfDuplicateAggregatorShouldBeUsed() 
	{
		$query = \Guzzle\Http\QueryString::fromString("test=a&test=b");
		$this->assertEquals("test=a&test=b", (string) $query);
	}
	public function testGuessesIfDuplicateAggregatorShouldBeUsedAndChecksForPhpStyle() 
	{
		$query = \Guzzle\Http\QueryString::fromString("test[]=a&test[]=b");
		$this->assertEquals("test%5B0%5D=a&test%5B1%5D=b", (string) $query);
	}
}
?>