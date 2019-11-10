<?php  namespace Guzzle\Tests\Service\Mock\Command;
class OtherCommand extends MockCommand 
{
	protected function createOperation() 
	{
		return new \Guzzle\Service\Description\Operation(array( "name" => "other_command", "parameters" => array( "test" => array( "default" => "123", "required" => true, "doc" => "Test argument" ), "other" => array( ), "arg" => array( "type" => "string" ), "static" => array( "static" => true, "default" => "this is static" ) ) ));
	}
	protected function build() 
	{
		$this->request = $this->client->getRequest("HEAD");
	}
}
?>