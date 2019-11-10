<?php  namespace Guzzle\Tests\Service\Resource;
class CompositeResourceIteratorFactoryTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testEnsuresIteratorClassExists() 
	{
		$factory = new \Guzzle\Service\Resource\CompositeResourceIteratorFactory(array( new \Guzzle\Service\Resource\ResourceIteratorClassFactory(array( "Foo", "Bar" )) ));
		$cmd = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$this->assertFalse($factory->canBuild($cmd));
		$factory->build($cmd);
	}
	public function testBuildsResourceIterators() 
	{
		$f1 = new \Guzzle\Service\Resource\ResourceIteratorClassFactory("Guzzle\\Tests\\Service\\Mock\\Model");
		$factory = new \Guzzle\Service\Resource\CompositeResourceIteratorFactory(array( ));
		$factory->addFactory($f1);
		$command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$iterator = $factory->build($command, array( "client.namespace" => "Guzzle\\Tests\\Service\\Mock" ));
		$this->assertInstanceOf("Guzzle\\Tests\\Service\\Mock\\Model\\MockCommandIterator", $iterator);
	}
}
?>