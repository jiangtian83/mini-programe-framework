<?php  namespace Guzzle\Tests\Inflection;
class InflectorTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testReturnsDefaultInstance() 
	{
		$this->assertSame(\Guzzle\Inflection\Inflector::getDefault(), \Guzzle\Inflection\Inflector::getDefault());
	}
	public function testSnake() 
	{
		$this->assertEquals("camel_case", \Guzzle\Inflection\Inflector::getDefault()->snake("camelCase"));
		$this->assertEquals("camel_case", \Guzzle\Inflection\Inflector::getDefault()->snake("CamelCase"));
		$this->assertEquals("camel_case_words", \Guzzle\Inflection\Inflector::getDefault()->snake("CamelCaseWords"));
		$this->assertEquals("camel_case_words", \Guzzle\Inflection\Inflector::getDefault()->snake("CamelCase_words"));
		$this->assertEquals("test", \Guzzle\Inflection\Inflector::getDefault()->snake("test"));
		$this->assertEquals("test", \Guzzle\Inflection\Inflector::getDefault()->snake("test"));
		$this->assertEquals("expect100_continue", \Guzzle\Inflection\Inflector::getDefault()->snake("Expect100Continue"));
	}
	public function testCamel() 
	{
		$this->assertEquals("CamelCase", \Guzzle\Inflection\Inflector::getDefault()->camel("camel_case"));
		$this->assertEquals("CamelCaseWords", \Guzzle\Inflection\Inflector::getDefault()->camel("camel_case_words"));
		$this->assertEquals("Test", \Guzzle\Inflection\Inflector::getDefault()->camel("test"));
		$this->assertEquals("Expect100Continue", ucfirst(\Guzzle\Inflection\Inflector::getDefault()->camel("expect100_continue")));
		$this->assertEquals("Test", \Guzzle\Inflection\Inflector::getDefault()->camel("test", false));
	}
}
?>