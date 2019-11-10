<?php  namespace Guzzle\Tests\Service\Command;
abstract class AbstractCommandTest extends \Guzzle\Tests\GuzzleTestCase 
{
	protected function getClient() 
	{
		$client = new \Guzzle\Service\Client("http://www.google.com/");
		return $client->setDescription(\Guzzle\Service\Description\ServiceDescription::factory(__DIR__ . "/../../TestData/test_service.json"));
	}
}
?>