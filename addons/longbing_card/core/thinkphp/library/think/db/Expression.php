<?php  namespace think\db;
class Expression 
{
	protected $value = NULL;
	public function __construct($value) 
	{
		$this->value = $value;
	}
	public function getValue() 
	{
		return $this->value;
	}
	public function __toString() 
	{
		return (string) $this->value;
	}
}
?>