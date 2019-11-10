<?php  namespace Guzzle\Common;
class Version 
{
	public static $emitWarnings = false;
	const VERSION = "3.9.3";
	public static function warn($message) 
	{
		if( self::$emitWarnings ) 
		{
			trigger_error("Deprecation warning: " . $message, E_USER_DEPRECATED);
		}
	}
}
?>