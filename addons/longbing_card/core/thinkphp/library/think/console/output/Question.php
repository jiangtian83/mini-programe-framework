<?php  namespace think\console\output;
class Question 
{
	private $question = NULL;
	private $attempts = NULL;
	private $hidden = false;
	private $hiddenFallback = true;
	private $autocompleterValues = NULL;
	private $validator = NULL;
	private $default = NULL;
	private $normalizer = NULL;
	public function __construct($question, $default = NULL) 
	{
		$this->question = $question;
		$this->default = $default;
	}
	public function getQuestion() 
	{
		return $this->question;
	}
	public function getDefault() 
	{
		return $this->default;
	}
	public function isHidden() 
	{
		return $this->hidden;
	}
	public function setHidden($hidden) 
	{
		if( $this->autocompleterValues ) 
		{
			throw new \LogicException("A hidden question cannot use the autocompleter.");
		}
		$this->hidden = (bool) $hidden;
		return $this;
	}
	public function isHiddenFallback() 
	{
		return $this->hiddenFallback;
	}
	public function setHiddenFallback($fallback) 
	{
		$this->hiddenFallback = (bool) $fallback;
		return $this;
	}
	public function getAutocompleterValues() 
	{
		return $this->autocompleterValues;
	}
	public function setAutocompleterValues($values) 
	{
		if( is_array($values) && $this->isAssoc($values) ) 
		{
			$values = array_merge(array_keys($values), array_values($values));
		}
		if( null !== $values && !is_array($values) && (!$values instanceof \Traversable || $values instanceof \Countable) ) 
		{
			throw new \InvalidArgumentException("Autocompleter values can be either an array, `null` or an object implementing both `Countable` and `Traversable` interfaces.");
		}
		if( $this->hidden ) 
		{
			throw new \LogicException("A hidden question cannot use the autocompleter.");
		}
		$this->autocompleterValues = $values;
		return $this;
	}
	public function setValidator($validator) 
	{
		$this->validator = $validator;
		return $this;
	}
	public function getValidator() 
	{
		return $this->validator;
	}
	public function setMaxAttempts($attempts) 
	{
		if( null !== $attempts && $attempts < 1 ) 
		{
			throw new \InvalidArgumentException("Maximum number of attempts must be a positive value.");
		}
		$this->attempts = $attempts;
		return $this;
	}
	public function getMaxAttempts() 
	{
		return $this->attempts;
	}
	public function setNormalizer($normalizer) 
	{
		$this->normalizer = $normalizer;
		return $this;
	}
	public function getNormalizer() 
	{
		return $this->normalizer;
	}
	protected function isAssoc($array) 
	{
		return (bool) count(array_filter(array_keys($array), "is_string"));
	}
}
?>