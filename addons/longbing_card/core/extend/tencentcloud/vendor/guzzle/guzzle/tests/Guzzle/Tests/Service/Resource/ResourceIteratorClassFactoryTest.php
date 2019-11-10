<?php  namespace Guzzle\Tests\Service\Resource;
class ResourceIteratorClassFactoryTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testEnsuresIteratorClassExists() 
	{
		$factory = new \Guzzle\Service\Resource\ResourceIteratorClassFactory(array( "Foo", "Bar" ));
		$factory->registerNamespace("Baz");
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$factory->build($command);
	}
	public function testBuildsResourceIterators() 
	{
		$factory = new \Guzzle\Service\Resource\ResourceIteratorClassFactory("Guzzle\\Tests\\Service\\Mock\\Model");
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$iterator = $factory->build($command, array( "client.namespace" => "Guzzle\\Tests\\Service\\Mock" ));
		$this->assertInstanceOf("Guzzle\\Tests\\Service\\Mock\\Model\\MockCommandIterator", $iterator);
	}
	public function testChecksIfCanBuild() 
	{
		$factory = new \Guzzle\Service\Resource\ResourceIteratorClassFactory("Guzzle\\Tests\\Service");
		$this->assertFalse($factory->canBuild(new \Guzzle\Tests\Service\Mock\Command\MockCommand()));
		$factory = new \Guzzle\Service\Resource\ResourceIteratorClassFactory("Guzzle\\Tests\\Service\\Mock\\Model");
		$this->assertTrue($factory->canBuild(new \Guzzle\Tests\Service\Mock\Command\MockCommand()));
	}
}
?>