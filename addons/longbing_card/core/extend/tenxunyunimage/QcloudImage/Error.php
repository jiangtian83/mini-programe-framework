<?php  namespace QcloudImage;
class Error 
{
	public static $Param = -1;
	public static $Network = -2;
	public static $FilePath = -3;
	public static $Unknown = -4;
	public static function json($code, $message, $httpcode = 0) 
	{
		return json_encode(array( "code" => $code, "message" => $message, "httpcode" => $httpcode, "data" => json_decode("{}", true) ));
	}
}
?>