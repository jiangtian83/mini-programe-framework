<?php  namespace Guzzle\Service\Command;
class OperationCommand extends AbstractCommand 
{
	protected $requestSerializer = NULL;
	protected $responseParser = NULL;
	public function setResponseParser(ResponseParserInterface $parser) 
	{
		$this->responseParser = $parser;
		return $this;
	}
	public function setRequestSerializer(RequestSerializerInterface $serializer) 
	{
		$this->requestSerializer = $serializer;
		return $this;
	}
	public function getRequestSerializer() 
	{
		if( !$this->requestSerializer ) 
		{
			$this->requestSerializer = DefaultRequestSerializer::getInstance();
		}
		return $this->requestSerializer;
	}
	public function getResponseParser() 
	{
		if( !$this->responseParser ) 
		{
			$this->responseParser = OperationResponseParser::getInstance();
		}
		return $this->responseParser;
	}
	protected function build() 
	{
		$this->request = $this->getRequestSerializer()->prepare($this);
	}
	protected function process() 
	{
		$this->result = ($this[self::RESPONSE_PROCESSING] == self::TYPE_RAW ? $this->request->getResponse() : $this->getResponseParser()->parse($this));
	}
}
?>