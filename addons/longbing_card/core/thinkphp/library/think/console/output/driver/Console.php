<?php  namespace think\console\output\driver;
class Console 
{
	private $stdout = NULL;
	private $formatter = NULL;
	private $terminalDimensions = NULL;
	private $output = NULL;
	public function __construct(\think\console\Output $output) 
	{
		$this->output = $output;
		$this->formatter = new \think\console\output\Formatter();
		$this->stdout = $this->openOutputStream();
		$decorated = $this->hasColorSupport($this->stdout);
		$this->formatter->setDecorated($decorated);
	}
	public function getFormatter() 
	{
		return $this->formatter;
	}
	public function setDecorated($decorated) 
	{
		$this->formatter->setDecorated($decorated);
	}
	public function write($messages, $newline = false, $type = \think\console\Output::OUTPUT_NORMAL, $stream = NULL) 
	{
		if( \think\console\Output::VERBOSITY_QUIET === $this->output->getVerbosity() ) 
		{
			return NULL;
		}
		$messages = (array) $messages;
		foreach( $messages as $message ) 
		{
			switch( $type ) 
			{
				case \think\console\Output::OUTPUT_NORMAL: $message = $this->formatter->format($message);
				break;
				case \think\console\Output::OUTPUT_RAW: break;
				case \think\console\Output::OUTPUT_PLAIN: $message = strip_tags($this->formatter->format($message));
				break;
				default: throw new \InvalidArgumentException(sprintf("Unknown output type given (%s)", $type));
			}
			$this->doWrite($message, $newline, $stream);
		}
	}
	public function renderException(\Exception $e) 
	{
		$stderr = $this->openErrorStream();
		$decorated = $this->hasColorSupport($stderr);
		$this->formatter->setDecorated($decorated);
		$title = sprintf("  [%s]  ", get_class($e));
		$len = $this->stringWidth($title);
		$width = ($this->getTerminalWidth() ? $this->getTerminalWidth() - 1 : PHP_INT_MAX);
		if( defined("HHVM_VERSION") && 1 << 31 < $width ) 
		{
			$width = 1 << 31;
		}
		$lines = array( );
		foreach( preg_split("/\\r?\\n/", $e->getMessage()) as $line ) 
		{
			foreach( $this->splitStringByWidth($line, $width - 4) as $line ) 
			{
				$lineLength = $this->stringWidth(preg_replace("/\x1B\\[[^m]*m/", "", $line)) + 4;
				$lines[] = array( $line, $lineLength );
				$len = max($lineLength, $len);
			}
		}
		$messages = array( "", "" );
		$messages[] = $emptyLine = sprintf("<error>%s</error>", str_repeat(" ", $len));
		$messages[] = sprintf("<error>%s%s</error>", $title, str_repeat(" ", max(0, $len - $this->stringWidth($title))));
		foreach( $lines as $line ) 
		{
			$messages[] = sprintf("<error>  %s  %s</error>", $line[0], str_repeat(" ", $len - $line[1]));
		}
		$messages[] = $emptyLine;
		$messages[] = "";
		$messages[] = "";
		$this->write($messages, true, \think\console\Output::OUTPUT_NORMAL, $stderr);
		if( \think\console\Output::VERBOSITY_VERBOSE <= $this->output->getVerbosity() ) 
		{
			$this->write("<comment>Exception trace:</comment>", true, \think\console\Output::OUTPUT_NORMAL, $stderr);
			$trace = $e->getTrace();
			array_unshift($trace, array( "function" => "", "file" => ($e->getFile() !== null ? $e->getFile() : "n/a"), "line" => ($e->getLine() !== null ? $e->getLine() : "n/a"), "args" => array( ) ));
			$i = 0;
			for( $count = count($trace);
			$i < $count;
			$i++ ) 
			{
				$class = (isset($trace[$i]["class"]) ? $trace[$i]["class"] : "");
				$type = (isset($trace[$i]["type"]) ? $trace[$i]["type"] : "");
				$function = $trace[$i]["function"];
				$file = (isset($trace[$i]["file"]) ? $trace[$i]["file"] : "n/a");
				$line = (isset($trace[$i]["line"]) ? $trace[$i]["line"] : "n/a");
				$this->write(sprintf(" %s%s%s() at <info>%s:%s</info>", $class, $type, $function, $file, $line), true, \think\console\Output::OUTPUT_NORMAL, $stderr);
			}
			$this->write("", true, \think\console\Output::OUTPUT_NORMAL, $stderr);
			$this->write("", true, \think\console\Output::OUTPUT_NORMAL, $stderr);
		}
		if( !($e = $e->getPrevious()) ) 
		{
		}
	}
	protected function getTerminalWidth() 
	{
		$dimensions = $this->getTerminalDimensions();
		return $dimensions[0];
	}
	protected function getTerminalHeight() 
	{
		$dimensions = $this->getTerminalDimensions();
		return $dimensions[1];
	}
	public function getTerminalDimensions() 
	{
		if( $this->terminalDimensions ) 
		{
			return $this->terminalDimensions;
		}
		if( "\\" === DS ) 
		{
			if( preg_match("/^(\\d+)x\\d+ \\(\\d+x(\\d+)\\)\$/", trim(getenv("ANSICON")), $matches) ) 
			{
				return array( (int) $matches[1], (int) $matches[2] );
			}
			if( preg_match("/^(\\d+)x(\\d+)\$/", $this->getMode(), $matches) ) 
			{
				return array( (int) $matches[1], (int) $matches[2] );
			}
		}
		if( $sttyString = $this->getSttyColumns() ) 
		{
			if( preg_match("/rows.(\\d+);.columns.(\\d+);/i", $sttyString, $matches) ) 
			{
				return array( (int) $matches[2], (int) $matches[1] );
			}
			if( preg_match("/;.(\\d+).rows;.(\\d+).columns/i", $sttyString, $matches) ) 
			{
				return array( (int) $matches[2], (int) $matches[1] );
			}
		}
		return array( null, null );
	}
	private function getSttyColumns() 
	{
		if( !function_exists("proc_open") ) 
		{
			return NULL;
		}
		$descriptorspec = array( 1 => array( "pipe", "w" ), 2 => array( "pipe", "w" ) );
		$process = proc_open("stty -a | grep columns", $descriptorspec, $pipes, null, null, array( "suppress_errors" => true ));
		if( is_resource($process) ) 
		{
			$info = stream_get_contents($pipes[1]);
			fclose($pipes[1]);
			fclose($pipes[2]);
			proc_close($process);
			return $info;
		}
	}
	private function getMode() 
	{
		if( !function_exists("proc_open") ) 
		{
			return NULL;
		}
		$descriptorspec = array( 1 => array( "pipe", "w" ), 2 => array( "pipe", "w" ) );
		$process = proc_open("mode CON", $descriptorspec, $pipes, null, null, array( "suppress_errors" => true ));
		if( is_resource($process) ) 
		{
			$info = stream_get_contents($pipes[1]);
			fclose($pipes[1]);
			fclose($pipes[2]);
			proc_close($process);
			if( preg_match("/--------+\\r?\\n.+?(\\d+)\\r?\\n.+?(\\d+)\\r?\\n/", $info, $matches) ) 
			{
				return $matches[2] . "x" . $matches[1];
			}
		}
	}
	private function stringWidth($string) 
	{
		if( !function_exists("mb_strwidth") ) 
		{
			return strlen($string);
		}
		if( false === ($encoding = mb_detect_encoding($string)) ) 
		{
			return strlen($string);
		}
		return mb_strwidth($string, $encoding);
	}
	private function splitStringByWidth($string, $width) 
	{
		if( !function_exists("mb_strwidth") ) 
		{
			return str_split($string, $width);
		}
		if( false === ($encoding = mb_detect_encoding($string)) ) 
		{
			return str_split($string, $width);
		}
		$utf8String = mb_convert_encoding($string, "utf8", $encoding);
		$lines = array( );
		$line = "";
		foreach( preg_split("//u", $utf8String) as $char ) 
		{
			if( mb_strwidth($line . $char, "utf8") <= $width ) 
			{
				$line .= $char;
				continue;
			}
			$lines[] = str_pad($line, $width);
			$line = $char;
		}
		if( strlen($line) ) 
		{
			$lines[] = (count($lines) ? str_pad($line, $width) : $line);
		}
		mb_convert_variables($encoding, "utf8", $lines);
		return $lines;
	}
	private function isRunningOS400() 
	{
		$checks = array( (function_exists("php_uname") ? php_uname("s") : ""), getenv("OSTYPE"), PHP_OS );
		return false !== stripos(implode(";", $checks), "OS400");
	}
	protected function hasStdoutSupport() 
	{
		return false === $this->isRunningOS400();
	}
	protected function hasStderrSupport() 
	{
		return false === $this->isRunningOS400();
	}
	private function openOutputStream() 
	{
		if( !$this->hasStdoutSupport() ) 
		{
			return fopen("php://output", "w");
		}
		return (@fopen("php://stdout", "w") ?: fopen("php://output", "w"));
	}
	private function openErrorStream() 
	{
		return fopen(($this->hasStderrSupport() ? "php://stderr" : "php://output"), "w");
	}
	protected function doWrite($message, $newline, $stream = NULL) 
	{
		if( null === $stream ) 
		{
			$stream = $this->stdout;
		}
		if( false === @fwrite($stream, $message . (($newline ? PHP_EOL : ""))) ) 
		{
			throw new \RuntimeException("Unable to write output.");
		}
		fflush($stream);
	}
	protected function hasColorSupport($stream) 
	{
		if( DIRECTORY_SEPARATOR === "\\" ) 
		{
			return "10.0.10586" === PHP_WINDOWS_VERSION_MAJOR . "." . PHP_WINDOWS_VERSION_MINOR . "." . PHP_WINDOWS_VERSION_BUILD || false !== getenv("ANSICON") || "ON" === getenv("ConEmuANSI") || "xterm" === getenv("TERM");
		}
		return function_exists("posix_isatty") && @posix_isatty($stream);
	}
}
?>