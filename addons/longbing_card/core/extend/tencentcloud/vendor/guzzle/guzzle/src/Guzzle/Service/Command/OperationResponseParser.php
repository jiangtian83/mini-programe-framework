<?php  namespace Guzzle\Service\Command;
class OperationResponseParser extends DefaultResponseParser 
{
	protected $factory = NULL;
	protected static $instance = NULL;
	private $schemaInModels = NULL;
	public static function getInstance() 
	{
		if( !static::$instance ) 
		{
			static::$instance = new static(LocationVisitor\VisitorFlyweight::getInstance());
		}
		return static::$instance;
	}
	public function __construct(LocationVisitor\VisitorFlyweight $factory, $schemaInModels = false) 
	{
		$this->factory = $factory;
		$this->schemaInModels = $schemaInModels;
	}
	public function addVisitor($location, LocationVisitor\Response\ResponseVisitorInterface $visitor) 
	{
		$this->factory->addResponseVisitor($location, $visitor);
		return $this;
	}
	protected function handleParsing(CommandInterface $command, \Guzzle\Http\Message\Response $response, $contentType) 
	{
		$operation = $command->getOperation();
		$type = $operation->getResponseType();
		$model = null;
		if( $type == \Guzzle\Service\Description\OperationInterface::TYPE_MODEL ) 
		{
			$model = $operation->getServiceDescription()->getModel($operation->getResponseClass());
		}
		else 
		{
			if( $type == \Guzzle\Service\Description\OperationInterface::TYPE_CLASS ) 
			{
				return $this->parseClass($command);
			}
		}
		if( !$model ) 
		{
			return parent::handleParsing($command, $response, $contentType);
		}
		if( $command[AbstractCommand::RESPONSE_PROCESSING] != AbstractCommand::TYPE_MODEL ) 
		{
			return new \Guzzle\Service\Resource\Model(parent::handleParsing($command, $response, $contentType));
		}
		return new \Guzzle\Service\Resource\Model($this->visitResult($model, $command, $response), ($this->schemaInModels ? $model : null));
	}
	protected function parseClass(CommandInterface $command) 
	{
		$event = new CreateResponseClassEvent(array( "command" => $command ));
		$command->getClient()->getEventDispatcher()->dispatch("command.parse_response", $event);
		if( $result = $event->getResult() ) 
		{
			return $result;
		}
		$className = $command->getOperation()->getResponseClass();
		if( !method_exists($className, "fromCommand") ) 
		{
			throw new \Guzzle\Service\Exception\ResponseClassException((string) $className . " must exist and implement a static fromCommand() method");
		}
		return $className::fromCommand($command);
	}
	protected function visitResult(\Guzzle\Service\Description\Parameter $model, CommandInterface $command, \Guzzle\Http\Message\Response $response) 
	{
		$foundVisitors = $result = $knownProps = array( );
		$props = $model->getProperties();
		foreach( $props as $schema ) 
		{
			if( ($location = $schema->getLocation()) && !isset($foundVisitors[$location]) ) 
			{
				$foundVisitors[$location] = $this->factory->getResponseVisitor($location);
				$foundVisitors[$location]->before($command, $result);
			}
		}
		if( ($additional = $model->getAdditionalProperties()) instanceof \Guzzle\Service\Description\Parameter ) 
		{
			$this->visitAdditionalProperties($model, $command, $response, $additional, $result, $foundVisitors);
		}
		foreach( $props as $schema ) 
		{
			$knownProps[$schema->getName()] = 1;
			if( $location = $schema->getLocation() ) 
			{
				$foundVisitors[$location]->visit($command, $response, $schema, $result);
			}
		}
		if( $additional === false ) 
		{
			$result = array_intersect_key($result, $knownProps);
		}
		foreach( $foundVisitors as $visitor ) 
		{
			$visitor->after($command);
		}
		return $result;
	}
	protected function visitAdditionalProperties(\Guzzle\Service\Description\Parameter $model, CommandInterface $command, \Guzzle\Http\Message\Response $response, \Guzzle\Service\Description\Parameter $additional, &$result, array &$foundVisitors) 
	{
		if( $location = $additional->getLocation() ) 
		{
			if( !isset($foundVisitors[$location]) ) 
			{
				$foundVisitors[$location] = $this->factory->getResponseVisitor($location);
				$foundVisitors[$location]->before($command, $result);
			}
			if( is_array($result) ) 
			{
				foreach( array_keys($result) as $key ) 
				{
					if( !$model->getProperty($key) ) 
					{
						$additional->setName($key);
						$foundVisitors[$location]->visit($command, $response, $additional, $result);
					}
				}
				$additional->setName(null);
			}
		}
	}
}
?>