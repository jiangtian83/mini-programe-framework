<?php  namespace Qcloud\Cos;
class Command extends \Guzzle\Service\Command\OperationCommand 
{
	public function createPresignedUrl($expires) 
	{
		return $this->client->createPresignedUrl($this->prepare(), $expires);
	}
	public function createAuthorization($expires) 
	{
		return $this->client->createAuthorization($this->prepare(), $expires);
	}
	protected function process() 
	{
		parent::process();
		if( $this->result instanceof \Guzzle\Service\Resource\Model && $this->getName() == "PutObject" ) 
		{
			$request = $this->getRequest();
			$this->result->set("ObjectURL", $request->getUrl());
		}
	}
}
?>