<?php  namespace Guzzle\Tests\Parsers\UriTemplate;
class UriTemplateTest extends AbstractUriTemplateTest 
{
	public function testExpandsUriTemplates($template, $expansion, $params) 
	{
		$uri = new \Guzzle\Parser\UriTemplate\UriTemplate($template);
		$this->assertEquals($expansion, $uri->expand($template, $params));
	}
	public function expressionProvider() 
	{
		return array( array( "{+var*}", array( "operator" => "+", "values" => array( array( "value" => "var", "modifier" => "*" ) ) ) ), array( "{?keys,var,val}", array( "operator" => "?", "values" => array( array( "value" => "keys", "modifier" => "" ), array( "value" => "var", "modifier" => "" ), array( "value" => "val", "modifier" => "" ) ) ) ), array( "{+x,hello,y}", array( "operator" => "+", "values" => array( array( "value" => "x", "modifier" => "" ), array( "value" => "hello", "modifier" => "" ), array( "value" => "y", "modifier" => "" ) ) ) ) );
	}
	public function testParsesExpressions($exp, $data) 
	{
		$template = new \Guzzle\Parser\UriTemplate\UriTemplate($exp);
		$class = new \ReflectionClass($template);
		$method = $class->getMethod("parseExpression");
		$method->setAccessible(true);
		$exp = substr($exp, 1, -1);
		$this->assertEquals($data, $method->invokeArgs($template, array( $exp )));
	}
	public function testAllowsNestedArrayExpansion() 
	{
		$template = new \Guzzle\Parser\UriTemplate\UriTemplate();
		$result = $template->expand("http://example.com{+path}{/segments}{?query,data*,foo*}", array( "path" => "/foo/bar", "segments" => array( "one", "two" ), "query" => "test", "data" => array( "more" => array( "fun", "ice cream" ) ), "foo" => array( "baz" => array( "bar" => "fizz", "test" => "buzz" ), "bam" => "boo" ) ));
		$this->assertEquals("http://example.com/foo/bar/one,two?query=test&more%5B0%5D=fun&more%5B1%5D=ice%20cream&baz%5Bbar%5D=fizz&baz%5Btest%5D=buzz&bam=boo", $result);
	}
	public function testSetRegex() 
	{
		$template = new \Guzzle\Parser\UriTemplate\UriTemplate();
		$template->setRegex("/\\<\\\$(.+)\\>/");
		$this->assertSame("/foo", $template->expand("/<\$a>", array( "a" => "foo" )));
	}
}
?>