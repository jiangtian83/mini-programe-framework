<?php  namespace Guzzle\Inflection;
class Inflector implements InflectorInterface 
{
	protected static $default = NULL;
	public static function getDefault() 
	{
		if( !self::$default ) 
		{
			self::$default = new MemoizingInflector(new self());
		}
		return self::$default;
	}
	public function snake($word) 
	{
		return (ctype_lower($word) ? $word : strtolower(preg_replace("/(.)([A-Z])/", "\$1_\$2", $word)));
	}
	public function camel($word) 
	{
		return str_replace(" ", "", ucwords(strtr($word, "_-", "  ")));
	}
}
?>