<?php  namespace Guzzle\Http\Message\Header;
class CacheControl extends \Guzzle\Http\Message\Header 
{
	protected $directives = NULL;
	public function add($value) 
	{
		parent::add($value);
		$this->directives = null;
	}
	public function removeValue($searchValue) 
	{
		parent::removeValue($searchValue);
		$this->directives = null;
	}
	public function hasDirective($param) 
	{
		$directives = $this->getDirectives();
		return isset($directives[$param]);
	}
	public function getDirective($param) 
	{
		$directives = $this->getDirectives();
		return (isset($directives[$param]) ? $directives[$param] : null);
	}
	public function addDirective($param, $value) 
	{
		$directives = $this->getDirectives();
		$directives[$param] = $value;
		$this->updateFromDirectives($directives);
		return $this;
	}
	public function removeDirective($param) 
	{
		$directives = $this->getDirectives();
		unset($directives[$param]);
		$this->updateFromDirectives($directives);
		return $this;
	}
	public function getDirectives() 
	{
		if( $this->directives === null ) 
		{
			$this->directives = array( );
			foreach( $this->parseParams() as $collection ) 
			{
				foreach( $collection as $key => $value ) 
				{
					$this->directives[$key] = ($value === "" ? true : $value);
				}
			}
		}
		return $this->directives;
	}
	protected function updateFromDirectives(array $directives) 
	{
		$this->directives = $directives;
		$this->values = array( );
		foreach( $directives as $key => $value ) 
		{
			$this->values[] = ($value === true ? $key : (string) $key . "=" . $value);
		}
	}
}
?>