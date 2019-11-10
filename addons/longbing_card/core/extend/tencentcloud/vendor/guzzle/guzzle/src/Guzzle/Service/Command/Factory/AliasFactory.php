<?php  namespace Guzzle\Service\Command\Factory;
class AliasFactory implements FactoryInterface 
{
	protected $aliases = NULL;
	protected $client = NULL;
	public function __construct(\Guzzle\Service\ClientInterface $client, array $aliases) 
	{
		$this->client = $client;
		$this->aliases = $aliases;
	}
	public function factory($name, array $args = array( )) 
	{
		if( isset($this->aliases[$name]) ) 
		{
			try 
			{
				return $this->client->getCommand($this->aliases[$name], $args);
			}
			catch( \Guzzle\Common\Exception\InvalidArgumentException $e ) 
			{
				return null;
			}
		}
	}
}
?>