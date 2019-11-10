<?php  namespace Guzzle\Tests\Service\Description;
class SchemaFormatterTest extends \Guzzle\Tests\GuzzleTestCase 
{
	public function dateTimeProvider() 
	{
		$dateUtc = "October 13, 2012 16:15:46 UTC";
		$dateOffset = "October 13, 2012 10:15:46 -06:00";
		$expectedDateTime = "2012-10-13T16:15:46Z";
		return array( array( "foo", "does-not-exist", "foo" ), array( $dateUtc, "date-time", $expectedDateTime ), array( $dateUtc, "date-time-http", "Sat, 13 Oct 2012 16:15:46 GMT" ), array( $dateUtc, "date", "2012-10-13" ), array( $dateUtc, "timestamp", strtotime($dateUtc) ), array( new \DateTime($dateUtc), "timestamp", strtotime($dateUtc) ), array( $dateUtc, "time", "16:15:46" ), array( strtotime($dateUtc), "time", "16:15:46" ), array( strtotime($dateUtc), "timestamp", strtotime($dateUtc) ), array( "true", "boolean-string", "true" ), array( true, "boolean-string", "true" ), array( "false", "boolean-string", "false" ), array( false, "boolean-string", "false" ), array( "1350144946", "date-time", $expectedDateTime ), array( 1350144946, "date-time", $expectedDateTime ), array( $dateOffset, "date-time", $expectedDateTime ) );
	}
	public function testFilters($value, $format, $result) 
	{
		$this->assertEquals($result, \Guzzle\Service\Description\SchemaFormatter::format($format, $value));
	}
	public function testValidatesDateTimeInput() 
	{
		\Guzzle\Service\Description\SchemaFormatter::format("date-time", false);
	}
	public function testEnsuresTimestampsAreIntegers() 
	{
		$t = time();
		$result = \Guzzle\Service\Description\SchemaFormatter::format("timestamp", $t);
		$this->assertSame($t, $result);
		$this->assertInternalType("int", $result);
	}
}
?>