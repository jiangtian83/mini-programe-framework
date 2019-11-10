<?php  namespace Guzzle\Tests\Service\Exception;
class ValidationExceptionTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testCanSetAndRetrieveErrors() 
	{
		$errors = array( "foo", "bar" );
		$e = new \Guzzle\Service\Exception\ValidationException("Foo");
		$e->setErrors($errors);
		$this->assertEquals($errors, $e->getErrors());
	}
}
?>