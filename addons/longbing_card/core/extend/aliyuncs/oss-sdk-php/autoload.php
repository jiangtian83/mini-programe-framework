<?php  spl_autoload_register("classLoader");
function classLoader($class) 
{
	$path = str_replace("\\", DIRECTORY_SEPARATOR, $class);
	$file = __DIR__ . DIRECTORY_SEPARATOR . "src" . DIRECTORY_SEPARATOR . $path . ".php";
	if( file_exists($file) ) 
	{
		require_once($file);
	}
}
?>