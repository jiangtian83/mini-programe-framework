<?php  namespace Guzzle\Tests\Service\Builder;
class ServiceBuilderLoaderTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testBuildsServiceBuilders() 
	{
		$arrayFactory = new \Guzzle\Service\Builder\ServiceBuilderLoader();
		$data = array( "services" => array( "abstract" => array( "params" => array( "access_key" => "xyz", "secret" => "abc" ) ), "foo" => array( "extends" => "abstract", "params" => array( "baz" => "bar" ) ), "mock" => array( "extends" => "abstract", "params" => array( "username" => "foo", "password" => "baz", "subdomain" => "bar" ) ) ) );
		$builder = $arrayFactory->load($data);
		$this->assertTrue(isset($builder["mock"]));
		$this->assertTrue(isset($builder["abstract"]));
		$this->assertTrue(isset($builder["foo"]));
		$this->assertFalse(isset($builder["jimmy"]));
	}
	public function testThrowsExceptionWhenExtendingNonExistentService() 
	{
		$arrayFactory = new \Guzzle\Service\Builder\ServiceBuilderLoader();
		$data = array( "services" => array( "foo" => array( "extends" => "abstract" ) ) );
		$builder = $arrayFactory->load($data);
	}
	public function testAllowsGlobalParameterOverrides() 
	{
		$arrayFactory = new \Guzzle\Service\Builder\ServiceBuilderLoader();
		$data = array( "services" => array( "foo" => array( "params" => array( "foo" => "baz", "bar" => "boo" ) ) ) );
		$builder = $arrayFactory->load($data, array( "bar" => "jar", "far" => "car" ));
		$compiled = json_decode($builder->serialize(), true);
		$this->assertEquals(array( "foo" => "baz", "bar" => "jar", "far" => "car" ), $compiled["foo"]["params"]);
	}
	public function tstDoesNotErrorOnCircularReferences() 
	{
		$arrayFactory = new \Guzzle\Service\Builder\ServiceBuilderLoader();
		$arrayFactory->load(array( "services" => array( "too" => array( "extends" => "ball" ), "ball" => array( "extends" => "too" ) ) ));
	}
	public function configProvider() 
	{
		$foo = array( "extends" => "bar", "class" => "stdClass", "params" => array( "a" => "test", "b" => "456" ) );
		return array( array( array( "services" => array( "foo" => $foo, "bar" => array( "params" => array( "baz" => "123" ) ) ) ), array( "services" => array( "foo" => array( "class" => "Baz" ) ) ), array( "services" => array( "foo" => array( "class" => "Baz" ), "bar" => array( "params" => array( "baz" => "123" ) ) ) ) ), array( array( "services" => array( "foo" => $foo, "bar" => array( "params" => array( "baz" => "123" ) ) ) ), array( "services" => array( "foo" => array( "extends" => "foo", "params" => array( "b" => "123", "c" => "def" ) ) ) ), array( "services" => array( "foo" => array( "extends" => "bar", "class" => "stdClass", "params" => array( "a" => "test", "b" => "123", "c" => "def" ) ), "bar" => array( "params" => array( "baz" => "123" ) ) ) ) ) );
	}
	public function testCombinesConfigs($a, $b, $c) 
	{
		$l = new \Guzzle\Service\Builder\ServiceBuilderLoader();
		$m = new \ReflectionMethod($l, "mergeData");
		$m->setAccessible(true);
		$this->assertEquals($c, $m->invoke($l, $a, $b));
	}
}
?>