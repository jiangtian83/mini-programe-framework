<?php  namespace Guzzle\Tests\Service\Command;
class ServiceDescriptionFactoryTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testProvider() 
	{
		return array( array( "foo", null ), array( "jar_jar", "Guzzle\\Tests\\Service\\Mock\\Command\\MockCommand" ), array( "binks", "Guzzle\\Tests\\Service\\Mock\\Command\\OtherCommand" ) );
	}
	public function testCreatesCommandsUsingServiceDescriptions($key, $result) 
	{
		$d = $this->getDescription();
		$factory = new \Guzzle\Service\Command\Factory\ServiceDescriptionFactory($d);
		$this->assertSame($d, $factory->getServiceDescription());
		if( is_null($result) ) 
		{
			$this->assertNull($factory->factory($key));
		}
		else 
		{
			$this->assertInstanceof($result, $factory->factory($key));
		}
	}
	public function testUsesUcFirstIfNoExactMatch() 
	{
		$d = $this->getDescription();
		$factory = new \Guzzle\Service\Command\Factory\ServiceDescriptionFactory($d, new \Guzzle\Inflection\Inflector());
		$this->assertInstanceof("Guzzle\\Tests\\Service\\Mock\\Command\\OtherCommand", $factory->factory("Test"));
		$this->assertInstanceof("Guzzle\\Tests\\Service\\Mock\\Command\\OtherCommand", $factory->factory("test"));
	}
	public function testUsesInflectionIfNoExactMatch() 
	{
		$d = $this->getDescription();
		$factory = new \Guzzle\Service\Command\Factory\ServiceDescriptionFactory($d, new \Guzzle\Inflection\Inflector());
		$this->assertInstanceof("Guzzle\\Tests\\Service\\Mock\\Command\\OtherCommand", $factory->factory("Binks"));
		$this->assertInstanceof("Guzzle\\Tests\\Service\\Mock\\Command\\OtherCommand", $factory->factory("binks"));
		$this->assertInstanceof("Guzzle\\Tests\\Service\\Mock\\Command\\MockCommand", $factory->factory("JarJar"));
		$this->assertInstanceof("Guzzle\\Tests\\Service\\Mock\\Command\\MockCommand", $factory->factory("jar_jar"));
	}
	protected function getDescription() 
	{
		return \Guzzle\Service\Description\ServiceDescription::factory(array( "operations" => array( "jar_jar" => array( "class" => "Guzzle\\Tests\\Service\\Mock\\Command\\MockCommand" ), "binks" => array( "class" => "Guzzle\\Tests\\Service\\Mock\\Command\\OtherCommand" ), "Test" => array( "class" => "Guzzle\\Tests\\Service\\Mock\\Command\\OtherCommand" ) ) ));
	}
}
?>