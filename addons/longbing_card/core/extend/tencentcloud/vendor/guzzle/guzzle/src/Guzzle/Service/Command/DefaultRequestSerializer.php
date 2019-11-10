<?php  namespace Guzzle\Service\Command;
class DefaultRequestSerializer implements RequestSerializerInterface 
{
	protected $factory = NULL;
	protected static $instance = NULL;
	public static function getInstance() 
	{
		if( !self::$instance ) 
		{
			self::$instance = new self(LocationVisitor\VisitorFlyweight::getInstance());
		}
		return self::$instance;
	}
	public function __construct(LocationVisitor\VisitorFlyweight $factory) 
	{
		$this->factory = $factory;
	}
	public function addVisitor($location, LocationVisitor\Request\RequestVisitorInterface $visitor) 
	{
		$this->factory->addRequestVisitor($location, $visitor);
		return $this;
	}
	public function prepare(CommandInterface $command) 
	{
		$request = $this->createRequest($command);
		$foundVisitors = array( );
		$operation = $command->getOperation();
		foreach( $operation->getParams() as $name => $arg ) 
		{
			$location = $arg->getLocation();
			if( $location && $location != "uri" ) 
			{
				if( !isset($foundVisitors[$location]) ) 
				{
					$foundVisitors[$location] = $this->factory->getRequestVisitor($location);
				}
				$value = $command[$name];
				if( $value !== null ) 
				{
					$foundVisitors[$location]->visit($command, $request, $arg, $value);
				}
			}
		}
		if( ($additional = $operation->getAdditionalParameters()) && ($visitor = $this->prepareAdditionalParameters($operation, $command, $request, $additional)) ) 
		{
			$foundVisitors[$additional->getLocation()] = $visitor;
		}
		foreach( $foundVisitors as $visitor ) 
		{
			$visitor->after($command, $request);
		}
		return $request;
	}
	protected function prepareAdditionalParameters(\Guzzle\Service\Description\OperationInterface $operation, CommandInterface $command, \Guzzle\Http\Message\RequestInterface $request, \Guzzle\Service\Description\Parameter $additional) 
	{
		if( !($location = $additional->getLocation()) ) 
		{
			return NULL;
		}
		$visitor = $this->factory->getRequestVisitor($location);
		$hidden = $command[$command::HIDDEN_PARAMS];
		foreach( $command->toArray() as $key => $value ) 
		{
			if( $value !== null && !in_array($key, $hidden) && !$operation->hasParam($key) ) 
			{
				$additional->setName($key);
				$visitor->visit($command, $request, $additional, $value);
			}
		}
		return $visitor;
	}
	protected function createRequest(CommandInterface $command) 
	{
		$operation = $command->getOperation();
		$client = $command->getClient();
		$options = ($command[AbstractCommand::REQUEST_OPTIONS] ?: array( ));
		if( !($uri = $operation->getUri()) ) 
		{
			return $client->createRequest($operation->getHttpMethod(), $client->getBaseUrl(), null, null, $options);
		}
		$variables = array( );
		foreach( $operation->getParams() as $name => $arg ) 
		{
			if( $arg->getLocation() == "uri" && isset($command[$name]) ) 
			{
				$variables[$name] = $arg->filter($command[$name]);
				if( !is_array($variables[$name]) ) 
				{
					$variables[$name] = (string) $variables[$name];
				}
			}
		}
		return $client->createRequest($operation->getHttpMethod(), array( $uri, $variables ), null, null, $options);
	}
}
?>