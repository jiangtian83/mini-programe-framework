<?php  namespace Guzzle\Tests\Service\Resource;
class MapResourceIteratorFactoryTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testEnsuresIteratorClassExists() 
	{
		$factory = new \Guzzle\Service\Resource\MapResourceIteratorFactory(array( "Foo", "Bar" ));
		$factory->build(new \Guzzle\Tests\Service\Mock\Command\MockCommand());
	}
	public function testBuildsResourceIterators() 
	{
		$factory = new \Guzzle\Service\Resource\MapResourceIteratorFactory(array( "mock_command" => "Guzzle\\Tests\\Service\\Mock\\Model\\MockCommandIterator" ));
		$iterator = $factory->build(new \Guzzle\Tests\Service\Mock\Command\MockCommand());
		$this->assertInstanceOf("Guzzle\\Tests\\Service\\Mock\\Model\\MockCommandIterator", $iterator);
	}
	public function testUsesWildcardMappings() 
	{
		$factory = new \Guzzle\Service\Resource\MapResourceIteratorFactory(array( "*" => "Guzzle\\Tests\\Service\\Mock\\Model\\MockCommandIterator" ));
		$iterator = $factory->build(new \Guzzle\Tests\Service\Mock\Command\MockCommand());
		$this->assertInstanceOf("Guzzle\\Tests\\Service\\Mock\\Model\\MockCommandIterator", $iterator);
	}
}
?>