<?php  namespace think;
class Error 
{
	public static function register() 
	{
		error_reporting(E_ALL);
		set_error_handler(array( "think\\Error", "appError" ));
		set_exception_handler(array( "think\\Error", "appException" ));
		register_shutdown_function(array( "think\\Error", "appShutdown" ));
	}
	public static function appException($e) 
	{
		if( !$e instanceof \Exception ) 
		{
			$e = new exception\ThrowableError($e);
		}
		$handler = self::getExceptionHandler();
		$handler->report($e);
		if( IS_CLI ) 
		{
			$handler->renderForConsole(new console\Output(), $e);
		}
		else 
		{
			$handler->render($e)->send();
		}
	}
	public static function appError($errno, $errstr, $errfile = "", $errline = 0) 
	{
		$exception = new exception\ErrorException($errno, $errstr, $errfile, $errline);
		if( error_reporting() & $errno ) 
		{
			throw $exception;
		}
		self::getExceptionHandler()->report($exception);
	}
	public static function appShutdown() 
	{
		if( !is_null($error = error_get_last()) && self::isFatal($error["type"]) ) 
		{
			self::appException(new exception\ErrorException($error["type"], $error["message"], $error["file"], $error["line"]));
		}
		Log::save();
	}
	protected static function isFatal($type) 
	{
		return in_array($type, array( E_ERROR, E_CORE_ERROR, E_COMPILE_ERROR, E_PARSE ));
	}
	public static function getExceptionHandler() 
	{
		static $handle = NULL;
		if( !$handle ) 
		{
			$class = Config::get("exception_handle");
			if( $class && is_string($class) && class_exists($class) && is_subclass_of($class, "\\think\\exception\\Handle") ) 
			{
				$handle = new $class();
			}
			else 
			{
				$handle = new exception\Handle();
				if( $class instanceof \Closure ) 
				{
					$handle->setRender($class);
				}
			}
		}
		return $handle;
	}
}
?>