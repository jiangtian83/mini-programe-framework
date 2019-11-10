<?php  namespace Guzzle\Service\Command;
abstract class AbstractCommand extends \Guzzle\Common\Collection implements CommandInterface 
{
	protected $client = NULL;
	protected $request = NULL;
	protected $result = NULL;
	protected $operation = NULL;
	protected $onComplete = NULL;
	protected $validator = NULL;
	const HEADERS_OPTION = "command.headers";
	const ON_COMPLETE = "command.on_complete";
	const RESPONSE_BODY = "command.response_body";
	const REQUEST_OPTIONS = "command.request_options";
	const HIDDEN_PARAMS = "command.hidden_params";
	const DISABLE_VALIDATION = "command.disable_validation";
	const RESPONSE_PROCESSING = "command.response_processing";
	const TYPE_RAW = "raw";
	const TYPE_MODEL = "model";
	const TYPE_NO_TRANSLATION = "no_translation";
	public function __construct($parameters = array( ), \Guzzle\Service\Description\OperationInterface $operation = NULL) 
	{
		parent::__construct($parameters);
		$this->operation = ($operation ?: $this->createOperation());
		foreach( $this->operation->getParams() as $name => $arg ) 
		{
			$currentValue = $this[$name];
			$configValue = $arg->getValue($currentValue);
			if( $currentValue !== $configValue ) 
			{
				$this[$name] = $configValue;
			}
		}
		$headers = $this[self::HEADERS_OPTION];
		if( !$headers instanceof \Guzzle\Common\Collection ) 
		{
			$this[self::HEADERS_OPTION] = new \Guzzle\Common\Collection((array) $headers);
		}
		if( $onComplete = $this["command.on_complete"] ) 
		{
			unset($this["command.on_complete"]);
			$this->setOnComplete($onComplete);
		}
		if( !$this[self::HIDDEN_PARAMS] ) 
		{
			$this[self::HIDDEN_PARAMS] = array( self::HEADERS_OPTION, self::RESPONSE_PROCESSING, self::HIDDEN_PARAMS, self::REQUEST_OPTIONS );
		}
		$this->init();
	}
	public function __clone() 
	{
		$this->request = null;
		$this->result = null;
	}
	public function __invoke() 
	{
		return $this->execute();
	}
	public function getName() 
	{
		return $this->operation->getName();
	}
	public function getOperation() 
	{
		return $this->operation;
	}
	public function setOnComplete($callable) 
	{
		if( !is_callable($callable) ) 
		{
			throw new \Guzzle\Common\Exception\InvalidArgumentException("The onComplete function must be callable");
		}
		$this->onComplete = $callable;
		return $this;
	}
	public function execute() 
	{
		if( !$this->client ) 
		{
			throw new \Guzzle\Service\Exception\CommandException("A client must be associated with the command before it can be executed.");
		}
		return $this->client->execute($this);
	}
	public function getClient() 
	{
		return $this->client;
	}
	public function setClient(\Guzzle\Service\ClientInterface $client) 
	{
		$this->client = $client;
		return $this;
	}
	public function getRequest() 
	{
		if( !$this->request ) 
		{
			throw new \Guzzle\Service\Exception\CommandException("The command must be prepared before retrieving the request");
		}
		return $this->request;
	}
	public function getResponse() 
	{
		if( !$this->isExecuted() ) 
		{
			$this->execute();
		}
		return $this->request->getResponse();
	}
	public function getResult() 
	{
		if( !$this->isExecuted() ) 
		{
			$this->execute();
		}
		if( null === $this->result ) 
		{
			$this->process();
			if( $this->onComplete ) 
			{
				call_user_func($this->onComplete, $this);
			}
		}
		return $this->result;
	}
	public function setResult($result) 
	{
		$this->result = $result;
		return $this;
	}
	public function isPrepared() 
	{
		return $this->request !== null;
	}
	public function isExecuted() 
	{
		return $this->request !== null && $this->request->getState() == "complete";
	}
	public function prepare() 
	{
		if( !$this->isPrepared() ) 
		{
			if( !$this->client ) 
			{
				throw new \Guzzle\Service\Exception\CommandException("A client must be associated with the command before it can be prepared.");
			}
			if( !isset($this[self::RESPONSE_PROCESSING]) ) 
			{
				$this[self::RESPONSE_PROCESSING] = self::TYPE_MODEL;
			}
			$this->client->dispatch("command.before_prepare", array( "command" => $this ));
			$this->validate();
			$this->build();
			if( $headers = $this[self::HEADERS_OPTION] ) 
			{
				foreach( $headers as $key => $value ) 
				{
					$this->request->setHeader($key, $value);
				}
			}
			if( $options = $this[\Guzzle\Service\Client::CURL_OPTIONS] ) 
			{
				$this->request->getCurlOptions()->overwriteWith(\Guzzle\Http\Curl\CurlHandle::parseCurlConfig($options));
			}
			if( $responseBody = $this[self::RESPONSE_BODY] ) 
			{
				$this->request->setResponseBody($responseBody);
			}
			$this->client->dispatch("command.after_prepare", array( "command" => $this ));
		}
		return $this->request;
	}
	public function setValidator(\Guzzle\Service\Description\ValidatorInterface $validator) 
	{
		$this->validator = $validator;
		return $this;
	}
	public function getRequestHeaders() 
	{
		return $this[self::HEADERS_OPTION];
	}
	protected function init() 
	{
	}
	abstract protected function build();
	protected function createOperation() 
	{
		return new \Guzzle\Service\Description\Operation(array( "name" => get_class($this) ));
	}
	protected function process() 
	{
		$this->result = ($this[self::RESPONSE_PROCESSING] != self::TYPE_RAW ? DefaultResponseParser::getInstance()->parse($this) : $this->request->getResponse());
	}
	protected function validate() 
	{
		if( $this[self::DISABLE_VALIDATION] ) 
		{
			return NULL;
		}
		$errors = array( );
		$validator = $this->getValidator();
		foreach( $this->operation->getParams() as $name => $schema ) 
		{
			$value = $this[$name];
			if( !$validator->validate($schema, $value) ) 
			{
				$errors = array_merge($errors, $validator->getErrors());
			}
			else 
			{
				if( $value !== $this[$name] ) 
				{
					$this->data[$name] = $value;
				}
			}
		}
		$hidden = $this[self::HIDDEN_PARAMS];
		if( $properties = $this->operation->getAdditionalParameters() ) 
		{
			foreach( $this->toArray() as $name => $value ) 
			{
				if( !$this->operation->hasParam($name) && !in_array($name, $hidden) ) 
				{
					$properties->setName($name);
					if( !$validator->validate($properties, $value) ) 
					{
						$errors = array_merge($errors, $validator->getErrors());
					}
					else 
					{
						if( $value !== $this[$name] ) 
						{
							$this->data[$name] = $value;
						}
					}
				}
			}
		}
		if( !empty($errors) ) 
		{
			$e = new \Guzzle\Service\Exception\ValidationException("Validation errors: " . implode("\n", $errors));
			$e->setErrors($errors);
			throw $e;
		}
	}
	protected function getValidator() 
	{
		if( !$this->validator ) 
		{
			$this->validator = \Guzzle\Service\Description\SchemaValidator::getInstance();
		}
		return $this->validator;
	}
	public function getValidationErrors() 
	{
		if( !$this->validator ) 
		{
			return false;
		}
		return $this->validator->getErrors();
	}
}
?>