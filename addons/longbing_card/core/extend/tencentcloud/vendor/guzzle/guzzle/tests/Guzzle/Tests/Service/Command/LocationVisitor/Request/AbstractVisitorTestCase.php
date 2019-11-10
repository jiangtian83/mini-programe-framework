<?php  namespace Guzzle\Tests\Service\Command\LocationVisitor\Request;
abstract class AbstractVisitorTestCase extends \Guzzle\Tests\GuzzleTestCase 
{
	protected $command = NULL;
	protected $request = NULL;
	protected $param = NULL;
	protected $validator = NULL;
	public function setUp() 
	{
		$this->command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$this->request = new \Guzzle\Http\Message\EntityEnclosingRequest("POST", "http://www.test.com/some/path.php");
		$this->validator = new \Guzzle\Service\Description\SchemaValidator();
	}
	protected function getCommand($location) 
	{
		$command = new \Guzzle\Service\Command\OperationCommand(array( ), $this->getNestedCommand($location));
		$command->setClient(new \Guzzle\Tests\Service\Mock\MockClient());
		return $command;
	}
	protected function getNestedCommand($location) 
	{
		return new \Guzzle\Service\Description\Operation(array( "httpMethod" => "POST", "parameters" => array( "foo" => new \Guzzle\Service\Description\Parameter(array( "type" => "object", "location" => $location, "sentAs" => "Foo", "required" => true, "properties" => array( "test" => array( "type" => "object", "required" => true, "properties" => array( "baz" => array( "type" => "boolean", "default" => true ), "jenga" => array( "type" => "string", "default" => "hello", "sentAs" => "Jenga_Yall!", "filters" => array( "strtoupper" ) ) ) ), "bar" => array( "default" => 123 ) ), "additionalProperties" => array( "type" => "string", "filters" => array( "strtoupper" ), "location" => $location ) )), "arr" => new \Guzzle\Service\Description\Parameter(array( "type" => "array", "location" => $location, "items" => array( "type" => "string", "filters" => array( "strtoupper" ) ) )) ) ));
	}
	protected function getCommandWithArrayParamAndFilters() 
	{
		$operation = new \Guzzle\Service\Description\Operation(array( "httpMethod" => "POST", "parameters" => array( "foo" => new \Guzzle\Service\Description\Parameter(array( "type" => "string", "location" => "query", "sentAs" => "Foo", "required" => true, "default" => "bar", "filters" => array( "strtoupper" ) )), "arr" => new \Guzzle\Service\Description\Parameter(array( "type" => "array", "location" => "query", "sentAs" => "Arr", "required" => true, "default" => array( 123, 456, 789 ), "filters" => array( array( "method" => "implode", "args" => array( ",", "@value" ) ) ) )) ) ));
		$command = new \Guzzle\Service\Command\OperationCommand(array( ), $operation);
		$command->setClient(new \Guzzle\Tests\Service\Mock\MockClient());
		return $command;
	}
}
?>