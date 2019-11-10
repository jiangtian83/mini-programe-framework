<?php  namespace Guzzle\Tests\Service\Command\LocationVisitor\Response;
abstract class AbstractResponseVisitorTest extends \Guzzle\Tests\GuzzleTestCase 
{
	protected $response = NULL;
	protected $command = NULL;
	protected $value = NULL;
	public function setUp() 
	{
		$this->value = array( );
		$this->command = new \Guzzle\Tests\Service\Mock\Command\MockCommand();
		$this->response = new \Guzzle\Http\Message\Response(200, array( "X-Foo" => "bar", "Content-Length" => 3, "Content-Type" => "text/plain" ), "Foo");
	}
}
?>