<?php  namespace Guzzle\Tests\Common;
class VersionTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function testEmitsWarnings() 
	{
		\Guzzle\Common\Version::$emitWarnings = true;
		\Guzzle\Common\Version::warn("testing!");
	}
	public function testCanSilenceWarnings() 
	{
		\Guzzle\Common\Version::$emitWarnings = false;
		\Guzzle\Common\Version::warn("testing!");
		\Guzzle\Common\Version::$emitWarnings = true;
	}
}
?>