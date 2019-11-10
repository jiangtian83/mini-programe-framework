<?php  namespace Guzzle\Tests\Service\Mock;
class MockClient extends \Guzzle\Service\Client 
{
	public static function factory($config = array( )) 
	{
		$config = \Guzzle\Common\Collection::fromConfig($config, array( "base_url" => "{scheme}://127.0.0.1:8124/{api_version}/{subdomain}", "scheme" => "http", "api_version" => "v1" ), array( "username", "password", "subdomain" ));
		return new self($config->get("base_url"), $config);
	}
}
?>