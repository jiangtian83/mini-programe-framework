<?php  namespace Guzzle\Service;
class Client extends \Guzzle\Http\Client implements ClientInterface 
{
	protected $serviceDescription = NULL;
	protected $commandFactory = NULL;
	protected $resourceIteratorFactory = NULL;
	protected $inflector = NULL;
	const COMMAND_PARAMS = "command.params";
	public static function factory($config = array( )) 
	{
		return new static((isset($config["base_url"]) ? $config["base_url"] : null), $config);
	}
	public static function getAllEvents() 
	{
		return array_merge(\Guzzle\Http\Client::getAllEvents(), array( "client.command.create", "command.before_prepare", "command.after_prepare", "command.before_send", "command.after_send", "command.parse_response" ));
	}
	public function __call($method, $args) 
	{
		return $this->getCommand($method, (isset($args[0]) ? $args[0] : array( )))->getResult();
	}
	public function getCommand($name, array $args = array( )) 
	{
		if( $options = $this->getConfig(self::COMMAND_PARAMS) ) 
		{
			$args += $options;
		}
		if( !($command = $this->getCommandFactory()->factory($name, $args)) ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("Command was not found matching " . $name);
		}
		$command->setClient($this);
		$this->dispatch("client.command.create", array( "client" => $this, "command" => $command ));
		return $command;
	}
	public function setCommandFactory(Command\Factory\FactoryInterface $factory) 
	{
		$this->commandFactory = $factory;
		return $this;
	}
	public function setResourceIteratorFactory(Resource\ResourceIteratorFactoryInterface $factory) 
	{
		$this->resourceIteratorFactory = $factory;
		return $this;
	}
	public function getIterator($command, array $commandOptions = NULL, array $iteratorOptions = array( )) 
	{
		if( !$command instanceof Command\CommandInterface ) 
		{
			$command = $this->getCommand($command, ($commandOptions ?: array( )));
		}
		return $this->getResourceIteratorFactory()->build($command, $iteratorOptions);
	}
	public function execute($command) 
	{
		if( $command instanceof Command\CommandInterface ) 
		{
			$this->send($this->prepareCommand($command));
			$this->dispatch("command.after_send", array( "command" => $command ));
			return $command->getResult();
		}
		if( is_array($command) || $command instanceof \Traversable ) 
		{
			return $this->executeMultiple($command);
		}
		throw new \Guzzle\Common\Exception\InvalidArgumentException("Command must be a command or array of commands");
	}
	public function setDescription(Description\ServiceDescriptionInterface $service) 
	{
		$this->serviceDescription = $service;
		if( $this->getCommandFactory() && $this->getCommandFactory() instanceof Command\Factory\CompositeFactory ) 
		{
			$this->commandFactory->add(new Command\Factory\ServiceDescriptionFactory($service));
		}
		if( $baseUrl = $service->getBaseUrl() ) 
		{
			$this->setBaseUrl($baseUrl);
		}
		return $this;
	}
	public function getDescription() 
	{
		return $this->serviceDescription;
	}
	public function setInflector(\Guzzle\Inflection\InflectorInterface $inflector) 
	{
		$this->inflector = $inflector;
		return $this;
	}
	public function getInflector() 
	{
		if( !$this->inflector ) 
		{
			$this->inflector = \Guzzle\Inflection\Inflector::getDefault();
		}
		return $this->inflector;
	}
	protected function prepareCommand(Command\CommandInterface $command) 
	{
		$request = $command->setClient($this)->prepare();
		$request->setState(\Guzzle\Http\Message\RequestInterface::STATE_NEW);
		$this->dispatch("command.before_send", array( "command" => $command ));
		return $request;
	}
	protected function executeMultiple($commands) 
	{
		$requests = array( );
		$commandRequests = new \SplObjectStorage();
		foreach( $commands as $command ) 
		{
			$request = $this->prepareCommand($command);
			$commandRequests[$request] = $command;
			$requests[] = $request;
		}
		try 
		{
			$this->send($requests);
			foreach( $commands as $command ) 
			{
				$this->dispatch("command.after_send", array( "command" => $command ));
			}
			return $commands;
		}
		catch( \Guzzle\Http\Exception\MultiTransferException $failureException ) 
		{
			$e = Exception\CommandTransferException::fromMultiTransferException($failureException);
			foreach( $failureException->getFailedRequests() as $request ) 
			{
				if( isset($commandRequests[$request]) ) 
				{
					$e->addFailedCommand($commandRequests[$request]);
					unset($commandRequests[$request]);
				}
			}
			foreach( $commandRequests as $success ) 
			{
				$e->addSuccessfulCommand($commandRequests[$success]);
				$this->dispatch("command.after_send", array( "command" => $commandRequests[$success] ));
			}
			throw $e;
		}
	}
	protected function getResourceIteratorFactory() 
	{
		if( !$this->resourceIteratorFactory ) 
		{
			$clientClass = get_class($this);
			$prefix = substr($clientClass, 0, strrpos($clientClass, "\\"));
			$this->resourceIteratorFactory = new Resource\ResourceIteratorClassFactory(array( (string) $prefix . "\\Iterator", (string) $prefix . "\\Model" ));
		}
		return $this->resourceIteratorFactory;
	}
	protected function getCommandFactory() 
	{
		if( !$this->commandFactory ) 
		{
			$this->commandFactory = Command\Factory\CompositeFactory::getDefaultChain($this);
		}
		return $this->commandFactory;
	}
	public function enableMagicMethods($isEnabled) 
	{
		\Guzzle\Common\Version::warn("Guzzle\\Service\\Client::enableMagicMethods" . " is deprecated");
	}
}
?>