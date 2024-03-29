<?php  namespace think\console\input;
class Definition 
{
	private $arguments = NULL;
	private $requiredCount = NULL;
	private $hasAnArrayArgument = false;
	private $hasOptional = NULL;
	private $options = NULL;
	private $shortcuts = NULL;
	public function __construct(array $definition = array( )) 
	{
		$this->setDefinition($definition);
	}
	public function setDefinition(array $definition) 
	{
		$arguments = array( );
		$options = array( );
		foreach( $definition as $item ) 
		{
			if( $item instanceof Option ) 
			{
				$options[] = $item;
			}
			else 
			{
				$arguments[] = $item;
			}
		}
		$this->setArguments($arguments);
		$this->setOptions($options);
	}
	public function setArguments($arguments = array( )) 
	{
		$this->arguments = array( );
		$this->requiredCount = 0;
		$this->hasOptional = false;
		$this->hasAnArrayArgument = false;
		$this->addArguments($arguments);
	}
	public function addArguments($arguments = array( )) 
	{
		if( null !== $arguments ) 
		{
			foreach( $arguments as $argument ) 
			{
				$this->addArgument($argument);
			}
		}
	}
	public function addArgument(Argument $argument) 
	{
		if( isset($this->arguments[$argument->getName()]) ) 
		{
			throw new \LogicException(sprintf("An argument with name \"%s\" already exists.", $argument->getName()));
		}
		if( $this->hasAnArrayArgument ) 
		{
			throw new \LogicException("Cannot add an argument after an array argument.");
		}
		if( $argument->isRequired() && $this->hasOptional ) 
		{
			throw new \LogicException("Cannot add a required argument after an optional one.");
		}
		if( $argument->isArray() ) 
		{
			$this->hasAnArrayArgument = true;
		}
		if( $argument->isRequired() ) 
		{
			$this->requiredCount++;
		}
		else 
		{
			$this->hasOptional = true;
		}
		$this->arguments[$argument->getName()] = $argument;
	}
	public function getArgument($name) 
	{
		if( !$this->hasArgument($name) ) 
		{
			throw new \InvalidArgumentException(sprintf("The \"%s\" argument does not exist.", $name));
		}
		$arguments = (is_int($name) ? array_values($this->arguments) : $this->arguments);
		return $arguments[$name];
	}
	public function hasArgument($name) 
	{
		$arguments = (is_int($name) ? array_values($this->arguments) : $this->arguments);
		return isset($arguments[$name]);
	}
	public function getArguments() 
	{
		return $this->arguments;
	}
	public function getArgumentCount() 
	{
		return ($this->hasAnArrayArgument ? PHP_INT_MAX : count($this->arguments));
	}
	public function getArgumentRequiredCount() 
	{
		return $this->requiredCount;
	}
	public function getArgumentDefaults() 
	{
		$values = array( );
		foreach( $this->arguments as $argument ) 
		{
			$values[$argument->getName()] = $argument->getDefault();
		}
		return $values;
	}
	public function setOptions($options = array( )) 
	{
		$this->options = array( );
		$this->shortcuts = array( );
		$this->addOptions($options);
	}
	public function addOptions($options = array( )) 
	{
		foreach( $options as $option ) 
		{
			$this->addOption($option);
		}
	}
	public function addOption(Option $option) 
	{
		if( isset($this->options[$option->getName()]) && !$option->equals($this->options[$option->getName()]) ) 
		{
			throw new \LogicException(sprintf("An option named \"%s\" already exists.", $option->getName()));
		}
		if( $option->getShortcut() ) 
		{
			foreach( explode("|", $option->getShortcut()) as $shortcut ) 
			{
				if( isset($this->shortcuts[$shortcut]) && !$option->equals($this->options[$this->shortcuts[$shortcut]]) ) 
				{
					throw new \LogicException(sprintf("An option with shortcut \"%s\" already exists.", $shortcut));
				}
			}
		}
		$this->options[$option->getName()] = $option;
		if( $option->getShortcut() ) 
		{
			foreach( explode("|", $option->getShortcut()) as $shortcut ) 
			{
				$this->shortcuts[$shortcut] = $option->getName();
			}
		}
	}
	public function getOption($name) 
	{
		if( !$this->hasOption($name) ) 
		{
			throw new \InvalidArgumentException(sprintf("The \"--%s\" option does not exist.", $name));
		}
		return $this->options[$name];
	}
	public function hasOption($name) 
	{
		return isset($this->options[$name]);
	}
	public function getOptions() 
	{
		return $this->options;
	}
	public function hasShortcut($name) 
	{
		return isset($this->shortcuts[$name]);
	}
	public function getOptionForShortcut($shortcut) 
	{
		return $this->getOption($this->shortcutToName($shortcut));
	}
	public function getOptionDefaults() 
	{
		$values = array( );
		foreach( $this->options as $option ) 
		{
			$values[$option->getName()] = $option->getDefault();
		}
		return $values;
	}
	private function shortcutToName($shortcut) 
	{
		if( !isset($this->shortcuts[$shortcut]) ) 
		{
			throw new \InvalidArgumentException(sprintf("The \"-%s\" option does not exist.", $shortcut));
		}
		return $this->shortcuts[$shortcut];
	}
	public function getSynopsis($short = false) 
	{
		$elements = array( );
		if( $short && $this->getOptions() ) 
		{
			$elements[] = "[options]";
		}
		else 
		{
			if( !$short ) 
			{
				foreach( $this->getOptions() as $option ) 
				{
					$value = "";
					if( $option->acceptValue() ) 
					{
						$value = sprintf(" %s%s%s", ($option->isValueOptional() ? "[" : ""), strtoupper($option->getName()), ($option->isValueOptional() ? "]" : ""));
					}
					$shortcut = ($option->getShortcut() ? sprintf("-%s|", $option->getShortcut()) : "");
					$elements[] = sprintf("[%s--%s%s]", $shortcut, $option->getName(), $value);
				}
			}
		}
		if( count($elements) && $this->getArguments() ) 
		{
			$elements[] = "[--]";
		}
		foreach( $this->getArguments() as $argument ) 
		{
			$element = "<" . $argument->getName() . ">";
			if( !$argument->isRequired() ) 
			{
				$element = "[" . $element . "]";
			}
			else 
			{
				if( $argument->isArray() ) 
				{
					$element .= " (" . $element . ")";
				}
			}
			if( $argument->isArray() ) 
			{
				$element .= "...";
			}
			$elements[] = $element;
		}
		return implode(" ", $elements);
	}
}
?>