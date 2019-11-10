<?php  namespace think\console\output\question;
class Choice extends \think\console\output\Question 
{
	private $choices = NULL;
	private $multiselect = false;
	private $prompt = " > ";
	private $errorMessage = "Value \"%s\" is invalid";
	public function __construct($question, array $choices, $default = NULL) 
	{
		parent::__construct($question, $default);
		$this->choices = $choices;
		$this->setValidator($this->getDefaultValidator());
		$this->setAutocompleterValues($choices);
	}
	public function getChoices() 
	{
		return $this->choices;
	}
	public function setMultiselect($multiselect) 
	{
		$this->multiselect = $multiselect;
		$this->setValidator($this->getDefaultValidator());
		return $this;
	}
	public function isMultiselect() 
	{
		return $this->multiselect;
	}
	public function getPrompt() 
	{
		return $this->prompt;
	}
	public function setPrompt($prompt) 
	{
		$this->prompt = $prompt;
		return $this;
	}
	public function setErrorMessage($errorMessage) 
	{
		$this->errorMessage = $errorMessage;
		$this->setValidator($this->getDefaultValidator());
		return $this;
	}
	private function getDefaultValidator() 
	{
		$choices = $this->choices;
		$errorMessage = $this->errorMessage;
		$multiselect = $this->multiselect;
		$isAssoc = $this->isAssoc($choices);
		return function($selected) use ($choices, $errorMessage, $multiselect, $isAssoc) 
		{
			$selectedChoices = str_replace(" ", "", $selected);
			if( $multiselect ) 
			{
				if( !preg_match("/^[a-zA-Z0-9_-]+(?:,[a-zA-Z0-9_-]+)*\$/", $selectedChoices, $matches) ) 
				{
					throw new \InvalidArgumentException(sprintf($errorMessage, $selected));
				}
				$selectedChoices = explode(",", $selectedChoices);
			}
			else 
			{
				$selectedChoices = array( $selected );
			}
			$multiselectChoices = array( );
			foreach( $selectedChoices as $value ) 
			{
				$results = array( );
				foreach( $choices as $key => $choice ) 
				{
					if( $choice === $value ) 
					{
						$results[] = $key;
					}
				}
				if( 1 < count($results) ) 
				{
					throw new \InvalidArgumentException(sprintf("The provided answer is ambiguous. Value should be one of %s.", implode(" or ", $results)));
				}
				$result = array_search($value, $choices);
				if( !$isAssoc ) 
				{
					if( !empty($result) ) 
					{
						$result = $choices[$result];
					}
					else 
					{
						if( isset($choices[$value]) ) 
						{
							$result = $choices[$value];
						}
					}
				}
				else 
				{
					if( empty($result) && array_key_exists($value, $choices) ) 
					{
						$result = $value;
					}
				}
				if( empty($result) ) 
				{
					throw new \InvalidArgumentException(sprintf($errorMessage, $value));
				}
				array_push($multiselectChoices, $result);
			}
			if( $multiselect ) 
			{
				return $multiselectChoices;
			}
			return current($multiselectChoices);
		}
		;
	}
}
?>