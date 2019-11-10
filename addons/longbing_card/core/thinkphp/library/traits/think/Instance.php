<?php  namespace traits\think;
trait Instance 
{
	protected static $instance = NULL;
	public static function instance($options = array( )) 
	{
		if( is_null(self::$instance) ) 
		{
			self::$instance = new self($options);
		}
		return self::$instance;
	}
	public static function __callStatic($method, array $params) 
	{
		if( is_null(self::$instance) ) 
		{
			self::$instance = new self();
		}
		$call = substr($method, 1);
		if( 0 !== strpos($method, "_") || !is_callable(array( self::$instance, $call )) ) 
		{
			throw new \think\Exception("method not exists:" . $method);
		}
		return call_user_func_array(array( self::$instance, $call ), $params);
	}
}
?>