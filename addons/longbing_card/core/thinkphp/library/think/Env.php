<?php  namespace think;
class Env 
{
	public static function get($name, $default = NULL) 
	{
		$result = getenv(ENV_PREFIX . strtoupper(str_replace(".", "_", $name)));
		if( false !== $result ) 
		{
			if( "false" === $result ) 
			{
				$result = false;
			}
			else 
			{
				if( "true" === $result ) 
				{
					$result = true;
				}
			}
			return $result;
		}
		return $default;
	}
}
?>