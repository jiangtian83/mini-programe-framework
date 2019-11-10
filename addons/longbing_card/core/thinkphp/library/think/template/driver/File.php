<?php  namespace think\template\driver;
class File 
{
	protected $cacheFile = NULL;
	public function write($cacheFile, $content) 
	{
		$dir = dirname($cacheFile);
		if( !is_dir($dir) ) 
		{
			mkdir($dir, 493, true);
		}
		if( false === file_put_contents($cacheFile, $content) ) 
		{
			throw new \think\Exception("cache write error:" . $cacheFile, 11602);
		}
	}
	public function read($cacheFile, $vars = array( )) 
	{
		$this->cacheFile = $cacheFile;
		if( !empty($vars) && is_array($vars) ) 
		{
			extract($vars, EXTR_OVERWRITE);
		}
		include($this->cacheFile);
	}
	public function check($cacheFile, $cacheTime) 
	{
		if( !file_exists($cacheFile) ) 
		{
			return false;
		}
		if( 0 != $cacheTime && filemtime($cacheFile) + $cacheTime < $_SERVER["REQUEST_TIME"] ) 
		{
			return false;
		}
		return true;
	}
}
?>