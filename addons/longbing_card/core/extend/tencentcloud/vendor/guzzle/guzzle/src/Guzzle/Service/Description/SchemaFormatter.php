<?php  namespace Guzzle\Service\Description;
class SchemaFormatter 
{
	protected static $utcTimeZone = NULL;
	public static function format($format, $value) 
	{
		switch( $format ) 
		{
			case "date-time": return self::formatDateTime($value);
			case "date-time-http": return self::formatDateTimeHttp($value);
			case "date": return self::formatDate($value);
			case "time": return self::formatTime($value);
			case "timestamp": return self::formatTimestamp($value);
			case "boolean-string": return self::formatBooleanAsString($value);
		}
		return $value;
	}
	public static function formatDateTime($value) 
	{
		return self::dateFormatter($value, "Y-m-d\\TH:i:s\\Z");
	}
	public static function formatDateTimeHttp($value) 
	{
		return self::dateFormatter($value, "D, d M Y H:i:s \\G\\M\\T");
	}
	public static function formatDate($value) 
	{
		return self::dateFormatter($value, "Y-m-d");
	}
	public static function formatTime($value) 
	{
		return self::dateFormatter($value, "H:i:s");
	}
	public static function formatBooleanAsString($value) 
	{
		return (filter_var($value, FILTER_VALIDATE_BOOLEAN) ? "true" : "false");
	}
	public static function formatTimestamp($value) 
	{
		return (int) self::dateFormatter($value, "U");
	}
	protected static function getUtcTimeZone() 
	{
		if( !self::$utcTimeZone ) 
		{
			self::$utcTimeZone = new \DateTimeZone("UTC");
		}
		return self::$utcTimeZone;
	}
	protected static function dateFormatter($dateTime, $format) 
	{
		if( is_numeric($dateTime) ) 
		{
			return gmdate($format, (int) $dateTime);
		}
		if( is_string($dateTime) ) 
		{
			$dateTime = new \DateTime($dateTime);
		}
		if( $dateTime instanceof \DateTime ) 
		{
			return $dateTime->setTimezone(self::getUtcTimeZone())->format($format);
		}
		throw new \Guzzle\Common\Exception\InvalidArgumentException("Date/Time values must be either a string, integer, or DateTime object");
	}
}
?>