<?php  namespace Guzzle\Tests\Common;
class CollectionTest extends \Guzzle\Tests\GuzzleTestCase 
{
	protected $coll = NULL;
	protected function setUp() 
	{
		$this->coll = new \Guzzle\Common\Collection();
	}
	public function testConstructorCanBeCalledWithNoParams() 
	{
		$this->coll = new \Guzzle\Common\Collection();
		$p = $this->coll->getAll();
		$this->assertEmpty($p, "-> Collection must be empty when no data is passed");
	}
	public function testConstructorCanBeCalledWithParams() 
	{
		$testData = array( "test" => "value", "test_2" => "value2" );
		$this->coll = new \Guzzle\Common\Collection($testData);
		$this->assertEquals($this->coll->getAll(), $testData, "-> getAll() must return the data passed in the constructor");
		$this->assertEquals($this->coll->getAll(), $this->coll->toArray());
	}
	public function testImplementsIteratorAggregate() 
	{
		$this->coll->set("key", "value");
		$this->assertInstanceOf("ArrayIterator", $this->coll->getIterator());
		$this->assertEquals(1, count($this->coll));
		$total = 0;
		foreach( $this->coll as $key => $value ) 
		{
			$this->assertEquals("key", $key);
			$this->assertEquals("value", $value);
			$total++;
		}
		$this->assertEquals(1, $total);
	}
	public function testCanAddValuesToExistingKeysByUsingArray() 
	{
		$this->coll->add("test", "value1");
		$this->assertEquals($this->coll->getAll(), array( "test" => "value1" ));
		$this->coll->add("test", "value2");
		$this->assertEquals($this->coll->getAll(), array( "test" => array( "value1", "value2" ) ));
		$this->coll->add("test", "value3");
		$this->assertEquals($this->coll->getAll(), array( "test" => array( "value1", "value2", "value3" ) ));
	}
	public function testHandlesMergingInDisparateDataSources() 
	{
		$params = array( "test" => "value1", "test2" => "value2", "test3" => array( "value3", "value4" ) );
		$this->coll->merge($params);
		$this->assertEquals($this->coll->getAll(), $params);
		$this->assertEquals($this->coll->merge($this->coll), $this->coll);
	}
	public function testCanClearAllDataOrSpecificKeys() 
	{
		$this->coll->merge(array( "test" => "value1", "test2" => "value2" ));
		$this->coll->remove("test");
		$this->assertEquals($this->coll->getAll(), array( "test2" => "value2" ));
		$this->coll->clear();
		$this->assertEquals($this->coll->getAll(), array( ));
	}
	public function testGetsValuesByKey() 
	{
		$this->assertNull($this->coll->get("test"));
		$this->coll->add("test", "value");
		$this->assertEquals("value", $this->coll->get("test"));
		$this->coll->set("test2", "v2");
		$this->coll->set("test3", "v3");
		$this->assertEquals(array( "test" => "value", "test2" => "v2" ), $this->coll->getAll(array( "test", "test2" )));
	}
	public function testProvidesKeys() 
	{
		$this->assertEquals(array( ), $this->coll->getKeys());
		$this->coll->merge(array( "test1" => "value1", "test2" => "value2" ));
		$this->assertEquals(array( "test1", "test2" ), $this->coll->getKeys());
		$this->assertEquals(array( "test1", "test2" ), $this->coll->getKeys());
		$this->coll->remove("test1");
		$this->assertEquals(array( "test2" ), $this->coll->getKeys());
		$this->coll->add("test3", "value3");
		$this->assertEquals(array( "test2", "test3" ), $this->coll->getKeys());
	}
	public function testChecksIfHasKey() 
	{
		$this->assertFalse($this->coll->hasKey("test"));
		$this->coll->add("test", "value");
		$this->assertEquals(true, $this->coll->hasKey("test"));
		$this->coll->add("test2", "value2");
		$this->assertEquals(true, $this->coll->hasKey("test"));
		$this->assertEquals(true, $this->coll->hasKey("test2"));
		$this->assertFalse($this->coll->hasKey("testing"));
		$this->assertEquals(false, $this->coll->hasKey("AB-C", "junk"));
	}
	public function testChecksIfHasValue() 
	{
		$this->assertFalse($this->coll->hasValue("value"));
		$this->coll->add("test", "value");
		$this->assertEquals("test", $this->coll->hasValue("value"));
		$this->coll->add("test2", "value2");
		$this->assertEquals("test", $this->coll->hasValue("value"));
		$this->assertEquals("test2", $this->coll->hasValue("value2"));
		$this->assertFalse($this->coll->hasValue("val"));
	}
	public function testCanGetAllValuesByArray() 
	{
		$this->coll->add("foo", "bar");
		$this->coll->add("tEsT", "value");
		$this->coll->add("tesTing", "v2");
		$this->coll->add("key", "v3");
		$this->assertNull($this->coll->get("test"));
		$this->assertEquals(array( "foo" => "bar", "tEsT" => "value", "tesTing" => "v2" ), $this->coll->getAll(array( "foo", "tesTing", "tEsT" )));
	}
	public function testImplementsCount() 
	{
		$data = new \Guzzle\Common\Collection();
		$this->assertEquals(0, $data->count());
		$data->add("key", "value");
		$this->assertEquals(1, count($data));
		$data->add("key", "value2");
		$this->assertEquals(1, count($data));
		$data->add("key_2", "value3");
		$this->assertEquals(2, count($data));
	}
	public function testAddParamsByMerging() 
	{
		$params = array( "test" => "value1", "test2" => "value2", "test3" => array( "value3", "value4" ) );
		$this->coll->merge($params);
		$this->coll->merge(array( "test" => "another", "different_key" => "new value" ));
		$this->assertEquals(array( "test" => array( "value1", "another" ), "test2" => "value2", "test3" => array( "value3", "value4" ), "different_key" => "new value" ), $this->coll->getAll());
	}
	public function testAllowsFunctionalFilter() 
	{
		$this->coll->merge(array( "fruit" => "apple", "number" => "ten", "prepositions" => array( "about", "above", "across", "after" ), "same_number" => "ten" ));
		$filtered = $this->coll->filter(function($key, $value) 
		{
			return $value == "ten";
		}
		);
		$this->assertNotEquals($filtered, $this->coll);
		$this->assertEquals(array( "number" => "ten", "same_number" => "ten" ), $filtered->getAll());
	}
	public function testAllowsFunctionalMapping() 
	{
		$this->coll->merge(array( "number_1" => 1, "number_2" => 2, "number_3" => 3 ));
		$mapped = $this->coll->map(function($key, $value) 
		{
			return $value * $value;
		}
		);
		$this->assertNotEquals($mapped, $this->coll);
		$this->assertEquals(array( "number_1" => 1, "number_2" => 4, "number_3" => 9 ), $mapped->getAll());
	}
	public function testImplementsArrayAccess() 
	{
		$this->coll->merge(array( "k1" => "v1", "k2" => "v2" ));
		$this->assertTrue($this->coll->offsetExists("k1"));
		$this->assertFalse($this->coll->offsetExists("Krull"));
		$this->coll->offsetSet("k3", "v3");
		$this->assertEquals("v3", $this->coll->offsetGet("k3"));
		$this->assertEquals("v3", $this->coll->get("k3"));
		$this->coll->offsetUnset("k1");
		$this->assertFalse($this->coll->offsetExists("k1"));
	}
	public function testUsesStaticWhenCreatingNew() 
	{
		$qs = new \Guzzle\Http\QueryString(array( "a" => "b", "c" => "d" ));
		$this->assertInstanceOf("Guzzle\\Http\\QueryString", $qs->map(function($a, $b) 
		{
		}
		));
		$this->assertInstanceOf("Guzzle\\Common\\Collection", $qs->map(function($a, $b) 
		{
		}
		, array( ), false));
		$this->assertInstanceOf("Guzzle\\Http\\QueryString", $qs->filter(function($a, $b) 
		{
		}
		));
		$this->assertInstanceOf("Guzzle\\Common\\Collection", $qs->filter(function($a, $b) 
		{
		}
		, false));
	}
	public function testCanReplaceAllData() 
	{
		$this->assertSame($this->coll, $this->coll->replace(array( "a" => "123" )));
		$this->assertEquals(array( "a" => "123" ), $this->coll->getAll());
	}
	public function dataProvider() 
	{
		return array( array( "this_is_a_test", "{a}_is_a_{b}", array( "a" => "this", "b" => "test" ) ), array( "this_is_a_test", "{abc}_is_a_{0}", array( "abc" => "this", 0 => "test" ) ), array( "this_is_a_test", "{abc}_is_a_{0}", array( "abc" => "this", 0 => "test" ) ), array( "this_is_a_test", "this_is_a_test", array( "abc" => "this" ) ), array( "{abc}_is_{not_found}a_{0}", "{abc}_is_{not_found}a_{0}", array( ) ) );
	}
	public function testInjectsConfigData($output, $input, $config) 
	{
		$collection = new \Guzzle\Common\Collection($config);
		$this->assertEquals($output, $collection->inject($input));
	}
	public function testCanSearchByKey() 
	{
		$collection = new \Guzzle\Common\Collection(array( "foo" => "bar", "BaZ" => "pho" ));
		$this->assertEquals("foo", $collection->keySearch("FOO"));
		$this->assertEquals("BaZ", $collection->keySearch("baz"));
		$this->assertEquals(false, $collection->keySearch("Bar"));
	}
	public function testPreparesFromConfig() 
	{
		$c = \Guzzle\Common\Collection::fromConfig(array( "a" => "123", "base_url" => "http://www.test.com/" ), array( "a" => "xyz", "b" => "lol" ), array( "a" ));
		$this->assertInstanceOf("Guzzle\\Common\\Collection", $c);
		$this->assertEquals(array( "a" => "123", "b" => "lol", "base_url" => "http://www.test.com/" ), $c->getAll());
		try 
		{
			$c = \Guzzle\Common\Collection::fromConfig(array( ), array( ), array( "a" ));
			$this->fail("Exception not throw when missing config");
		}
		catch( \Guzzle\Common\Exception\InvalidArgumentException $e ) 
		{
		}
	}
	public function falseyDataProvider() 
	{
		return array( array( false, false ), array( null, null ), array( "", "" ), array( array( ), array( ) ), array( 0, 0 ) );
	}
	public function testReturnsCorrectData($a, $b) 
	{
		$c = new \Guzzle\Common\Collection(array( "value" => $a ));
		$this->assertSame($b, $c->get("value"));
	}
	public function testRetrievesNestedKeysUsingPath() 
	{
		$data = array( "foo" => "bar", "baz" => array( "mesa" => array( "jar" => "jar" ) ) );
		$collection = new \Guzzle\Common\Collection($data);
		$this->assertEquals("bar", $collection->getPath("foo"));
		$this->assertEquals("jar", $collection->getPath("baz/mesa/jar"));
		$this->assertNull($collection->getPath("wewewf"));
		$this->assertNull($collection->getPath("baz/mesa/jar/jar"));
	}
	public function testFalseyKeysStillDescend() 
	{
		$collection = new \Guzzle\Common\Collection(array( array( "a" => "jar" ), "other" ));
		$this->assertEquals("jar", $collection->getPath("0/a"));
		$this->assertEquals("other", $collection->getPath("1"));
	}
	public function getPathProvider() 
	{
		$data = array( "foo" => "bar", "baz" => array( "mesa" => array( "jar" => "jar", "array" => array( "a", "b", "c" ) ), "bar" => array( "baz" => "bam", "array" => array( "d", "e", "f" ) ) ), "bam" => array( array( "foo" => 1 ), array( "foo" => 2 ), array( "array" => array( "h", "i" ) ) ) );
		$c = new \Guzzle\Common\Collection($data);
		return array( array( $c, "foo", "bar" ), array( $c, "baz", $data["baz"] ), array( $c, "bam", $data["bam"] ), array( $c, "baz/mesa", $data["baz"]["mesa"] ), array( $c, "baz/mesa/jar", "jar" ), array( $c, "baz/*", array( "jar" => "jar", "array" => array_merge($data["baz"]["mesa"]["array"], $data["baz"]["bar"]["array"]), "baz" => "bam" ) ), array( $c, "fefwfw", null ), array( $c, "*/*/*/*/*/wefwfe", array( ) ), array( $c, "*|mesa", $data["baz"]["mesa"], "|" ), array( $c, "baz/*/array/*", array_merge($data["baz"]["mesa"]["array"], $data["baz"]["bar"]["array"]) ), array( $c, "baz/*/array", array_merge($data["baz"]["mesa"]["array"], $data["baz"]["bar"]["array"]) ), array( $c, "baz/mesa/array", $data["baz"]["mesa"]["array"] ), array( $c, "baz/mesa/array/*", $data["baz"]["mesa"]["array"] ), array( $c, "*", array_merge(array( "bar" ), $data["baz"], $data["bam"]) ), array( $c, "*/*", array( "jar" => "jar", "array" => array( "a", "b", "c", "d", "e", "f", "h", "i" ), "baz" => "bam", "foo" => array( 1, 2 ) ) ), array( $c, "*/*/array", array( "a", "b", "c", "d", "e", "f", "h", "i" ) ) );
	}
	public function testGetPath(\Guzzle\Common\Collection $c, $path, $expected, $separator = "/") 
	{
		$this->assertEquals($expected, $c->getPath($path, $separator));
	}
	public function testOverridesSettings() 
	{
		$c = new \Guzzle\Common\Collection(array( "foo" => 1, "baz" => 2, "bar" => 3 ));
		$c->overwriteWith(array( "foo" => 10, "bar" => 300 ));
		$this->assertEquals(array( "foo" => 10, "baz" => 2, "bar" => 300 ), $c->getAll());
	}
	public function testOverwriteWithCollection() 
	{
		$c = new \Guzzle\Common\Collection(array( "foo" => 1, "baz" => 2, "bar" => 3 ));
		$b = new \Guzzle\Common\Collection(array( "foo" => 10, "bar" => 300 ));
		$c->overwriteWith($b);
		$this->assertEquals(array( "foo" => 10, "baz" => 2, "bar" => 300 ), $c->getAll());
	}
	public function testOverwriteWithTraversable() 
	{
		$c = new \Guzzle\Common\Collection(array( "foo" => 1, "baz" => 2, "bar" => 3 ));
		$b = new \Guzzle\Common\Collection(array( "foo" => 10, "bar" => 300 ));
		$c->overwriteWith($b->getIterator());
		$this->assertEquals(array( "foo" => 10, "baz" => 2, "bar" => 300 ), $c->getAll());
	}
	public function testCanSetNestedPathValueThatDoesNotExist() 
	{
		$c = new \Guzzle\Common\Collection(array( ));
		$c->setPath("foo/bar/baz/123", "hi");
		$this->assertEquals("hi", $c["foo"]["bar"]["baz"][123]);
	}
	public function testCanSetNestedPathValueThatExists() 
	{
		$c = new \Guzzle\Common\Collection(array( "foo" => array( "bar" => "test" ) ));
		$c->setPath("foo/bar", "hi");
		$this->assertEquals("hi", $c["foo"]["bar"]);
	}
	public function testVerifiesNestedPathIsValidAtExactLevel() 
	{
		$c = new \Guzzle\Common\Collection(array( "foo" => "bar" ));
		$c->setPath("foo/bar", "hi");
		$this->assertEquals("hi", $c["foo"]["bar"]);
	}
	public function testVerifiesThatNestedPathIsValidAtAnyLevel() 
	{
		$c = new \Guzzle\Common\Collection(array( "foo" => "bar" ));
		$c->setPath("foo/bar/baz", "test");
	}
}
?>