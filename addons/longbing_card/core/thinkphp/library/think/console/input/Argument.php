<?php  namespace think\console\input;
class Argument 
{
	private $name = NULL;
	private $mode = NULL;
	private $default = NULL;
	private $description = NULL;
	const REQUIRED = 1;
	const OPTIONAL = 2;
	const IS_ARRAY = 4;
	public function __construct($name, $mode = NULL, $description = "", $default = NULL) 
	{
		if( null === $mode ) 
		{
			$mode = self::OPTIONAL;
		}
		else 
		{
			if( !is_int($mode) || 7 < $mode || $mode < 1 ) 
			{
				throw new \InvalidArgumentException(sprintf("Argument mode \"%s\" is not valid.", $mode));
			}
		}
		$this->name = $name;
		$this->mode = $mode;
		$this->description = $description;
		$this->setDefault($default);
	}
	public function getName() 
	{
		return $this->name;
	}
	public function isRequired() 
	{
		return self::REQUIRED === (self::REQUIRED & $this->mode);
	}
	public function isArray() 
	{
		return self::IS_ARRAY === (self::IS_ARRAY & $this->mode);
	}
	public function setDefault($default = NULL) 
	{
		if( self::REQUIRED === $this->mode && null !== $default ) 
		{
			throw new \LogicException("Cannot set a default value except for InputArgument::OPTIONAL mode.");
		}
		if( $this->isArray() ) 
		{
			if( null === $default ) 
			{
				$default = array( );
			}
			else 
			{
				if( !is_array($default) ) 
				{
					throw new \LogicException("A default value for an array argument must be an array.");
				}
			}
		}
		$this->default = $default;
	}
	public function getDefault() 
	{
		return $this->default;
	}
	public function getDescription() 
	{
		return $this->description;
	}
}
?>