<?php  namespace Guzzle\Tests\Service\Command;
class MapFactoryTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function mapProvider() 
	{
		return array( array( "foo", null ), array( "test", "Guzzle\\Tests\\Service\\Mock\\Command\\MockCommand" ), array( "test1", "Guzzle\\Tests\\Service\\Mock\\Command\\OtherCommand" ) );
	}
	public function testCreatesCommandsUsingMappings($key, $result) 
	{
		$factory = new \Guzzle\Service\Command\Factory\MapFactory(array( "test" => "Guzzle\\Tests\\Service\\Mock\\Command\\MockCommand", "test1" => "Guzzle\\Tests\\Service\\Mock\\Command\\OtherCommand" ));
		if( is_null($result) ) 
		{
			$this->assertNull($factory->factory($key));
		}
		else 
		{
			$this->assertInstanceof($result, $factory->factory($key));
		}
	}
}
?>