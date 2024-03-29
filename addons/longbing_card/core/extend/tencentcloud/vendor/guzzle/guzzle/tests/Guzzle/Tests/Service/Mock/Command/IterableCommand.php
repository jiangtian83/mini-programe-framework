<?php  namespace Guzzle\Tests\Service\Mock\Command;
class IterableCommand extends MockCommand 
{
	protected function createOperation() 
	{
		return new \Guzzle\Service\Description\Operation(array( "name" => "iterable_command", "parameters" => array( "page_size" => array( "type" => "integer" ), "next_token" => array( "type" => "string" ) ) ));
	}
	protected function build() 
	{
		$this->request = $this->client->createRequest("GET");
		$this->request->getQuery()->set("next_token", $this->get("next_token"));
		if( $this->get("page_size") ) 
		{
			$this->request->getQuery()->set("page_size", $this->get("page_size"));
		}
	}
}
?>