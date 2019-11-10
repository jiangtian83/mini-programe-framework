<?php  namespace Guzzle\Tests\Service\Resource;
class ModelTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testOwnsStructure() 
	{
		$param = new \Guzzle\Service\Description\Parameter(array( "type" => "object" ));
		$model = new \Guzzle\Service\Resource\Model(array( "foo" => "bar" ), $param);
		$this->assertSame($param, $model->getStructure());
		$this->assertEquals("bar", $model->get("foo"));
		$this->assertEquals("bar", $model["foo"]);
	}
	public function testCanBeUsedWithoutStructure() 
	{
		$model = new \Guzzle\Service\Resource\Model(array( "Foo" => "baz", "Bar" => array( "Boo" => "Bam" ) ));
		$transform = function($key, $value) 
		{
			return ($value && is_array($value) ? new \Guzzle\Common\Collection($value) : $value);
		}
		;
		$model = $model->map($transform);
		$this->assertInstanceOf("Guzzle\\Common\\Collection", $model->getPath("Bar"));
	}
	public function testAllowsFiltering() 
	{
		$model = new \Guzzle\Service\Resource\Model(array( "Foo" => "baz", "Bar" => "a" ));
		$model = $model->filter(function($i, $v) 
		{
			return $v[0] == "a";
		}
		);
		$this->assertEquals(array( "Bar" => "a" ), $model->toArray());
	}
	public function testDoesNotIncludeEmptyStructureInString() 
	{
		$model = new \Guzzle\Service\Resource\Model(array( "Foo" => "baz" ));
		$str = (string) $model;
		$this->assertContains("Debug output of model", $str);
		$this->assertNotContains("Model structure", $str);
	}
	public function testDoesIncludeModelStructureInString() 
	{
		$model = new \Guzzle\Service\Resource\Model(array( "Foo" => "baz" ), new \Guzzle\Service\Description\Parameter(array( "name" => "Foo" )));
		$str = (string) $model;
		$this->assertContains("Debug output of Foo model", $str);
		$this->assertContains("Model structure", $str);
	}
}
?>