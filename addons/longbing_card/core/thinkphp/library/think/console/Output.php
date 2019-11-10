<?php  namespace think\console;
class Output 
{
	private $verbosity = self::VERBOSITY_NORMAL;
	private $handle = NULL;
	protected $styles = array( "info", "error", "comment", "question", "highlight", "warning" );
	const VERBOSITY_QUIET = 0;
	const VERBOSITY_NORMAL = 1;
	const VERBOSITY_VERBOSE = 2;
	const VERBOSITY_VERY_VERBOSE = 3;
	const VERBOSITY_DEBUG = 4;
	const OUTPUT_NORMAL = 0;
	const OUTPUT_RAW = 1;
	const OUTPUT_PLAIN = 2;
	public function __construct($driver = "console") 
	{
		$class = "\\think\\console\\output\\driver\\" . ucwords($driver);
		$this->handle = new $class($this);
	}
	public function ask(Input $input, $question, $default = NULL, $validator = NULL) 
	{
		$question = new output\Question($question, $default);
		$question->setValidator($validator);
		return $this->askQuestion($input, $question);
	}
	public function askHidden(Input $input, $question, $validator = NULL) 
	{
		$question = new output\Question($question);
		$question->setHidden(true);
		$question->setValidator($validator);
		return $this->askQuestion($input, $question);
	}
	public function confirm(Input $input, $question, $default = true) 
	{
		return $this->askQuestion($input, new output\question\Confirmation($question, $default));
	}
	public function choice(Input $input, $question, array $choices, $default = NULL) 
	{
		if( null !== $default ) 
		{
			$values = array_flip($choices);
			$default = $values[$default];
		}
		return $this->askQuestion($input, new output\question\Choice($question, $choices, $default));
	}
	protected function askQuestion(Input $input, output\Question $question) 
	{
		$ask = new output\Ask($input, $this, $question);
		$answer = $ask->run();
		if( $input->isInteractive() ) 
		{
			$this->newLine();
		}
		return $answer;
	}
	protected function block($style, $message) 
	{
		$this->writeln("<" . $style . ">" . $message . "</" . $style . ">");
	}
	public function newLine($count = 1) 
	{
		$this->write(str_repeat(PHP_EOL, $count));
	}
	public function writeln($messages, $type = self::OUTPUT_NORMAL) 
	{
		$this->write($messages, true, $type);
	}
	public function write($messages, $newline = false, $type = self::OUTPUT_NORMAL) 
	{
		$this->handle->write($messages, $newline, $type);
	}
	public function renderException(\Exception $e) 
	{
		$this->handle->renderException($e);
	}
	public function setVerbosity($level) 
	{
		$this->verbosity = (int) $level;
	}
	public function getVerbosity() 
	{
		return $this->verbosity;
	}
	public function isQuiet() 
	{
		return self::VERBOSITY_QUIET === $this->verbosity;
	}
	public function isVerbose() 
	{
		return self::VERBOSITY_VERBOSE <= $this->verbosity;
	}
	public function isVeryVerbose() 
	{
		return self::VERBOSITY_VERY_VERBOSE <= $this->verbosity;
	}
	public function isDebug() 
	{
		return self::VERBOSITY_DEBUG <= $this->verbosity;
	}
	public function describe($object, array $options = array( )) 
	{
		$descriptor = new output\Descriptor();
		$options = array_merge(array( "raw_text" => false ), $options);
		$descriptor->describe($this, $object, $options);
	}
	public function __call($method, $args) 
	{
		if( in_array($method, $this->styles) ) 
		{
			array_unshift($args, $method);
			return call_user_func_array(array( $this, "block" ), $args);
		}
		if( $this->handle && method_exists($this->handle, $method) ) 
		{
			return call_user_func_array(array( $this->handle, $method ), $args);
		}
		throw new \Exception("method not exists:" . "think\\console\\Output" . "->" . $method);
	}
}
?>