<?php  namespace think;
class Process 
{
	private $callback = NULL;
	private $commandline = NULL;
	private $cwd = NULL;
	private $env = NULL;
	private $input = NULL;
	private $starttime = NULL;
	private $lastOutputTime = NULL;
	private $timeout = NULL;
	private $idleTimeout = NULL;
	private $options = NULL;
	private $exitcode = NULL;
	private $fallbackExitcode = NULL;
	private $processInformation = NULL;
	private $outputDisabled = false;
	private $stdout = NULL;
	private $stderr = NULL;
	private $enhanceWindowsCompatibility = true;
	private $enhanceSigchildCompatibility = NULL;
	private $process = NULL;
	private $status = self::STATUS_READY;
	private $incrementalOutputOffset = 0;
	private $incrementalErrorOutputOffset = 0;
	private $tty = NULL;
	private $pty = NULL;
	private $useFileHandles = false;
	private $processPipes = NULL;
	private $latestSignal = NULL;
	private static $sigchild = NULL;
	public static $exitCodes = array( "OK", "General error", "Misuse of shell builtins", "126" => "Invoked command cannot execute", "127" => "Command not found", "128" => "Invalid exit argument", "129" => "Hangup", "130" => "Interrupt", "131" => "Quit and dump core", "132" => "Illegal instruction", "133" => "Trace/breakpoint trap", "134" => "Process aborted", "135" => "Bus error: \"access to undefined portion of memory object\"", "136" => "Floating point exception: \"erroneous arithmetic operation\"", "137" => "Kill (terminate immediately)", "138" => "User-defined 1", "139" => "Segmentation violation", "140" => "User-defined 2", "141" => "Write to pipe with no one reading", "142" => "Signal raised by alarm", "143" => "Termination (request to terminate)", "145" => "Child process terminated, stopped (or continued*)", "146" => "Continue if stopped", "147" => "Stop executing temporarily", "148" => "Terminal stop signal", "149" => "Background process attempting to read from tty (\"in\")", "150" => "Background process attempting to write to tty (\"out\")", "151" => "Urgent data available on socket", "152" => "CPU time limit exceeded", "153" => "File size limit exceeded", "154" => "Signal raised by timer counting virtual time: \"virtual timer expired\"", "155" => "Profiling timer expired", "157" => "Pollable event", "159" => "Bad syscall" );
	const ERR = "err";
	const OUT = "out";
	const STATUS_READY = "ready";
	const STATUS_STARTED = "started";
	const STATUS_TERMINATED = "terminated";
	const STDIN = 0;
	const STDOUT = 1;
	const STDERR = 2;
	const TIMEOUT_PRECISION = 0.2;
	public function __construct($commandline, $cwd = NULL, array $env = NULL, $input = NULL, $timeout = 60, array $options = array( )) 
	{
		if( !function_exists("proc_open") ) 
		{
			throw new \RuntimeException("The Process class relies on proc_open, which is not available on your PHP installation.");
		}
		$this->commandline = $commandline;
		$this->cwd = $cwd;
		if( null === $this->cwd && (defined("ZEND_THREAD_SAFE") || "\\" === DS) ) 
		{
			$this->cwd = getcwd();
		}
		if( null !== $env ) 
		{
			$this->setEnv($env);
		}
		$this->input = $input;
		$this->setTimeout($timeout);
		$this->useFileHandles = "\\" === DS;
		$this->pty = false;
		$this->enhanceWindowsCompatibility = true;
		$this->enhanceSigchildCompatibility = "\\" !== DS && $this->isSigchildEnabled();
		$this->options = array_replace(array( "suppress_errors" => true, "binary_pipes" => true ), $options);
	}
	public function __destruct() 
	{
		$this->stop();
	}
	public function __clone() 
	{
		$this->resetProcessData();
	}
	public function run($callback = NULL) 
	{
		$this->start($callback);
		return $this->wait();
	}
	public function mustRun($callback = NULL) 
	{
		if( $this->isSigchildEnabled() && !$this->enhanceSigchildCompatibility ) 
		{
			throw new \RuntimeException("This PHP has been compiled with --enable-sigchild. You must use setEnhanceSigchildCompatibility() to use this method.");
		}
		if( 0 !== $this->run($callback) ) 
		{
			throw new process\exception\Failed($this);
		}
		return $this;
	}
	public function start($callback = NULL) 
	{
		if( $this->isRunning() ) 
		{
			throw new \RuntimeException("Process is already running");
		}
		if( $this->outputDisabled && null !== $callback ) 
		{
			throw new \LogicException("Output has been disabled, enable it to allow the use of a callback.");
		}
		$this->resetProcessData();
		$this->starttime = $this->lastOutputTime = microtime(true);
		$this->callback = $this->buildCallback($callback);
		$descriptors = $this->getDescriptors();
		$commandline = $this->commandline;
		if( "\\" === DS && $this->enhanceWindowsCompatibility ) 
		{
			$commandline = "cmd /V:ON /E:ON /C \"(" . $commandline . ")";
			foreach( $this->processPipes->getFiles() as $offset => $filename ) 
			{
				$commandline .= " " . $offset . ">" . process\Utils::escapeArgument($filename);
			}
			$commandline .= "\"";
			if( !isset($this->options["bypass_shell"]) ) 
			{
				$this->options["bypass_shell"] = true;
			}
		}
		$this->process = proc_open($commandline, $descriptors, $this->processPipes->pipes, $this->cwd, $this->env, $this->options);
		if( !is_resource($this->process) ) 
		{
			throw new \RuntimeException("Unable to launch a new process.");
		}
		$this->status = self::STATUS_STARTED;
		if( $this->tty ) 
		{
			return NULL;
		}
		$this->updateStatus(false);
		$this->checkTimeout();
	}
	public function restart($callback = NULL) 
	{
		if( $this->isRunning() ) 
		{
			throw new \RuntimeException("Process is already running");
		}
		$process = clone $this;
		$process->start($callback);
		return $process;
	}
	public function wait($callback = NULL) 
	{
		$this->requireProcessIsStarted("wait");
		$this->updateStatus(false);
		if( null !== $callback ) 
		{
			$this->callback = $this->buildCallback($callback);
		}
		do 
		{
			$this->checkTimeout();
			$running = ("\\" === DS ? $this->isRunning() : $this->processPipes->areOpen());
			$close = "\\" !== DS || !$running;
			$this->readPipes(true, $close);
		}
		while( $running );
		while( $this->isRunning() ) 
		{
			usleep(1000);
		}
		if( $this->processInformation["signaled"] && $this->processInformation["termsig"] !== $this->latestSignal ) 
		{
			throw new \RuntimeException(sprintf("The process has been signaled with signal \"%s\".", $this->processInformation["termsig"]));
		}
		return $this->exitcode;
	}
	public function getPid() 
	{
		if( $this->isSigchildEnabled() ) 
		{
			throw new \RuntimeException("This PHP has been compiled with --enable-sigchild. The process identifier can not be retrieved.");
		}
		$this->updateStatus(false);
		return ($this->isRunning() ? $this->processInformation["pid"] : null);
	}
	public function signal($signal) 
	{
		$this->doSignal($signal, true);
		return $this;
	}
	public function disableOutput() 
	{
		if( $this->isRunning() ) 
		{
			throw new \RuntimeException("Disabling output while the process is running is not possible.");
		}
		if( null !== $this->idleTimeout ) 
		{
			throw new \LogicException("Output can not be disabled while an idle timeout is set.");
		}
		$this->outputDisabled = true;
		return $this;
	}
	public function enableOutput() 
	{
		if( $this->isRunning() ) 
		{
			throw new \RuntimeException("Enabling output while the process is running is not possible.");
		}
		$this->outputDisabled = false;
		return $this;
	}
	public function isOutputDisabled() 
	{
		return $this->outputDisabled;
	}
	public function getOutput() 
	{
		if( $this->outputDisabled ) 
		{
			throw new \LogicException("Output has been disabled.");
		}
		$this->requireProcessIsStarted("getOutput");
		$this->readPipes(false, ("\\" === DS ? !$this->processInformation["running"] : true));
		return $this->stdout;
	}
	public function getIncrementalOutput() 
	{
		$this->requireProcessIsStarted("getIncrementalOutput");
		$data = $this->getOutput();
		$latest = substr($data, $this->incrementalOutputOffset);
		if( false === $latest ) 
		{
			return "";
		}
		$this->incrementalOutputOffset = strlen($data);
		return $latest;
	}
	public function clearOutput() 
	{
		$this->stdout = "";
		$this->incrementalOutputOffset = 0;
		return $this;
	}
	public function getErrorOutput() 
	{
		if( $this->outputDisabled ) 
		{
			throw new \LogicException("Output has been disabled.");
		}
		$this->requireProcessIsStarted("getErrorOutput");
		$this->readPipes(false, ("\\" === DS ? !$this->processInformation["running"] : true));
		return $this->stderr;
	}
	public function getIncrementalErrorOutput() 
	{
		$this->requireProcessIsStarted("getIncrementalErrorOutput");
		$data = $this->getErrorOutput();
		$latest = substr($data, $this->incrementalErrorOutputOffset);
		if( false === $latest ) 
		{
			return "";
		}
		$this->incrementalErrorOutputOffset = strlen($data);
		return $latest;
	}
	public function clearErrorOutput() 
	{
		$this->stderr = "";
		$this->incrementalErrorOutputOffset = 0;
		return $this;
	}
	public function getExitCode() 
	{
		if( $this->isSigchildEnabled() && !$this->enhanceSigchildCompatibility ) 
		{
			throw new \RuntimeException("This PHP has been compiled with --enable-sigchild. You must use setEnhanceSigchildCompatibility() to use this method.");
		}
		$this->updateStatus(false);
		return $this->exitcode;
	}
	public function getExitCodeText() 
	{
		if( null === ($exitcode = $this->getExitCode()) ) 
		{
			return NULL;
		}
		return (isset(self::$exitCodes[$exitcode]) ? self::$exitCodes[$exitcode] : "Unknown error");
	}
	public function isSuccessful() 
	{
		return 0 === $this->getExitCode();
	}
	public function hasBeenSignaled() 
	{
		$this->requireProcessIsTerminated("hasBeenSignaled");
		if( $this->isSigchildEnabled() ) 
		{
			throw new \RuntimeException("This PHP has been compiled with --enable-sigchild. Term signal can not be retrieved.");
		}
		$this->updateStatus(false);
		return $this->processInformation["signaled"];
	}
	public function getTermSignal() 
	{
		$this->requireProcessIsTerminated("getTermSignal");
		if( $this->isSigchildEnabled() ) 
		{
			throw new \RuntimeException("This PHP has been compiled with --enable-sigchild. Term signal can not be retrieved.");
		}
		$this->updateStatus(false);
		return $this->processInformation["termsig"];
	}
	public function hasBeenStopped() 
	{
		$this->requireProcessIsTerminated("hasBeenStopped");
		$this->updateStatus(false);
		return $this->processInformation["stopped"];
	}
	public function getStopSignal() 
	{
		$this->requireProcessIsTerminated("getStopSignal");
		$this->updateStatus(false);
		return $this->processInformation["stopsig"];
	}
	public function isRunning() 
	{
		if( self::STATUS_STARTED !== $this->status ) 
		{
			return false;
		}
		$this->updateStatus(false);
		return $this->processInformation["running"];
	}
	public function isStarted() 
	{
		return self::STATUS_READY != $this->status;
	}
	public function isTerminated() 
	{
		$this->updateStatus(false);
		return self::STATUS_TERMINATED == $this->status;
	}
	public function getStatus() 
	{
		$this->updateStatus(false);
		return $this->status;
	}
	public function stop() 
	{
		if( $this->isRunning() ) 
		{
			if( "\\" === DS && !$this->isSigchildEnabled() ) 
			{
				exec(sprintf("taskkill /F /T /PID %d 2>&1", $this->getPid()), $output, $exitCode);
				if( 0 < $exitCode ) 
				{
					throw new \RuntimeException("Unable to kill the process");
				}
			}
			else 
			{
				$pids = preg_split("/\\s+/", shell_exec("ps -o pid --no-heading --ppid " . $this->getPid()));
				foreach( $pids as $pid ) 
				{
					if( is_numeric($pid) ) 
					{
						posix_kill($pid, 9);
					}
				}
			}
		}
		$this->updateStatus(false);
		if( $this->processInformation["running"] ) 
		{
			$this->close();
		}
		return $this->exitcode;
	}
	public function addOutput($line) 
	{
		$this->lastOutputTime = microtime(true);
		$this->stdout .= $line;
	}
	public function addErrorOutput($line) 
	{
		$this->lastOutputTime = microtime(true);
		$this->stderr .= $line;
	}
	public function getCommandLine() 
	{
		return $this->commandline;
	}
	public function setCommandLine($commandline) 
	{
		$this->commandline = $commandline;
		return $this;
	}
	public function getTimeout() 
	{
		return $this->timeout;
	}
	public function getIdleTimeout() 
	{
		return $this->idleTimeout;
	}
	public function setTimeout($timeout) 
	{
		$this->timeout = $this->validateTimeout($timeout);
		return $this;
	}
	public function setIdleTimeout($timeout) 
	{
		if( null !== $timeout && $this->outputDisabled ) 
		{
			throw new \LogicException("Idle timeout can not be set while the output is disabled.");
		}
		$this->idleTimeout = $this->validateTimeout($timeout);
		return $this;
	}
	public function setTty($tty) 
	{
		if( "\\" === DS && $tty ) 
		{
			throw new \RuntimeException("TTY mode is not supported on Windows platform.");
		}
		if( $tty && (!file_exists("/dev/tty") || !is_readable("/dev/tty")) ) 
		{
			throw new \RuntimeException("TTY mode requires /dev/tty to be readable.");
		}
		$this->tty = (bool) $tty;
		return $this;
	}
	public function isTty() 
	{
		return $this->tty;
	}
	public function setPty($bool) 
	{
		$this->pty = (bool) $bool;
		return $this;
	}
	public function isPty() 
	{
		return $this->pty;
	}
	public function getWorkingDirectory() 
	{
		if( null === $this->cwd ) 
		{
			return (getcwd() ?: null);
		}
		return $this->cwd;
	}
	public function setWorkingDirectory($cwd) 
	{
		$this->cwd = $cwd;
		return $this;
	}
	public function getEnv() 
	{
		return $this->env;
	}
	public function setEnv(array $env) 
	{
		$env = array_filter($env, function($value) 
		{
			return !is_array($value);
		}
		);
		$this->env = array( );
		foreach( $env as $key => $value ) 
		{
			$this->env[(string) $key] = (string) $value;
		}
		return $this;
	}
	public function getInput() 
	{
		return $this->input;
	}
	public function setInput($input) 
	{
		if( $this->isRunning() ) 
		{
			throw new \LogicException("Input can not be set while the process is running.");
		}
		$this->input = process\Utils::validateInput(sprintf("%s::%s", "think\\Process", "setInput"), $input);
		return $this;
	}
	public function getOptions() 
	{
		return $this->options;
	}
	public function setOptions(array $options) 
	{
		$this->options = $options;
		return $this;
	}
	public function getEnhanceWindowsCompatibility() 
	{
		return $this->enhanceWindowsCompatibility;
	}
	public function setEnhanceWindowsCompatibility($enhance) 
	{
		$this->enhanceWindowsCompatibility = (bool) $enhance;
		return $this;
	}
	public function getEnhanceSigchildCompatibility() 
	{
		return $this->enhanceSigchildCompatibility;
	}
	public function setEnhanceSigchildCompatibility($enhance) 
	{
		$this->enhanceSigchildCompatibility = (bool) $enhance;
		return $this;
	}
	public function checkTimeout() 
	{
		if( self::STATUS_STARTED !== $this->status ) 
		{
			return NULL;
		}
		if( null !== $this->timeout && $this->timeout < microtime(true) - $this->starttime ) 
		{
			$this->stop();
			throw new process\exception\Timeout($this, process\exception\Timeout::TYPE_GENERAL);
		}
		if( null !== $this->idleTimeout && $this->idleTimeout < microtime(true) - $this->lastOutputTime ) 
		{
			$this->stop();
			throw new process\exception\Timeout($this, process\exception\Timeout::TYPE_IDLE);
		}
	}
	public static function isPtySupported() 
	{
		static $result = NULL;
		if( null !== $result ) 
		{
			return $result;
		}
		if( "\\" === DS ) 
		{
			return $result = false;
		}
		$proc = @proc_open("echo 1", array( array( "pty" ), array( "pty" ), array( "pty" ) ), $pipes);
		if( is_resource($proc) ) 
		{
			proc_close($proc);
			return $result = true;
		}
		return $result = false;
	}
	private function getDescriptors() 
	{
		if( "\\" === DS ) 
		{
			$this->processPipes = process\pipes\Windows::create($this, $this->input);
		}
		else 
		{
			$this->processPipes = process\pipes\Unix::create($this, $this->input);
		}
		$descriptors = $this->processPipes->getDescriptors($this->outputDisabled);
		if( !$this->useFileHandles && $this->enhanceSigchildCompatibility && $this->isSigchildEnabled() ) 
		{
			$descriptors = array_merge($descriptors, array( array( "pipe", "w" ) ));
			$this->commandline = "(" . $this->commandline . ") 3>/dev/null; code=\$?; echo \$code >&3; exit \$code";
		}
		return $descriptors;
	}
	protected function buildCallback($callback) 
	{
		$out = self::OUT;
		$callback = function($type, $data) use ($callback, $out) 
		{
			if( $out == $type ) 
			{
				$this->addOutput($data);
			}
			else 
			{
				$this->addErrorOutput($data);
			}
			if( null !== $callback ) 
			{
				call_user_func($callback, $type, $data);
			}
		}
		;
		return $callback;
	}
	protected function updateStatus($blocking) 
	{
		if( self::STATUS_STARTED !== $this->status ) 
		{
			return NULL;
		}
		$this->processInformation = proc_get_status($this->process);
		$this->captureExitCode();
		$this->readPipes($blocking, ("\\" === DS ? !$this->processInformation["running"] : true));
		if( !$this->processInformation["running"] ) 
		{
			$this->close();
		}
	}
	protected function isSigchildEnabled() 
	{
		if( null !== self::$sigchild ) 
		{
			return self::$sigchild;
		}
		if( !function_exists("phpinfo") ) 
		{
			return self::$sigchild = false;
		}
		ob_start();
		phpinfo(INFO_GENERAL);
		return self::$sigchild = false !== strpos(ob_get_clean(), "--enable-sigchild");
	}
	private function validateTimeout($timeout) 
	{
		$timeout = (double) $timeout;
		if( 0 === $timeout ) 
		{
			$timeout = null;
		}
		else 
		{
			if( $timeout < 0 ) 
			{
				throw new \InvalidArgumentException("The timeout value must be a valid positive integer or float number.");
			}
		}
		return $timeout;
	}
	private function readPipes($blocking, $close) 
	{
		$result = $this->processPipes->readAndWrite($blocking, $close);
		$callback = $this->callback;
		foreach( $result as $type => $data ) 
		{
			if( 3 == $type ) 
			{
				$this->fallbackExitcode = (int) $data;
			}
			else 
			{
				$callback((self::STDOUT === $type ? self::OUT : self::ERR), $data);
			}
		}
	}
	private function captureExitCode() 
	{
		if( isset($this->processInformation["exitcode"]) && -1 != $this->processInformation["exitcode"] ) 
		{
			$this->exitcode = $this->processInformation["exitcode"];
		}
	}
	private function close() 
	{
		$this->processPipes->close();
		if( is_resource($this->process) ) 
		{
			$exitcode = proc_close($this->process);
		}
		else 
		{
			$exitcode = -1;
		}
		$this->exitcode = (-1 !== $exitcode ? $exitcode : (null !== $this->exitcode ? $this->exitcode : -1));
		$this->status = self::STATUS_TERMINATED;
		if( -1 === $this->exitcode && null !== $this->fallbackExitcode ) 
		{
			$this->exitcode = $this->fallbackExitcode;
		}
		else 
		{
			if( -1 === $this->exitcode && $this->processInformation["signaled"] && 0 < $this->processInformation["termsig"] ) 
			{
				$this->exitcode = 128 + $this->processInformation["termsig"];
			}
		}
		return $this->exitcode;
	}
	private function resetProcessData() 
	{
		$this->starttime = null;
		$this->callback = null;
		$this->exitcode = null;
		$this->fallbackExitcode = null;
		$this->processInformation = null;
		$this->stdout = null;
		$this->stderr = null;
		$this->process = null;
		$this->latestSignal = null;
		$this->status = self::STATUS_READY;
		$this->incrementalOutputOffset = 0;
		$this->incrementalErrorOutputOffset = 0;
	}
	private function doSignal($signal, $throwException) 
	{
		if( !$this->isRunning() ) 
		{
			if( $throwException ) 
			{
				throw new \LogicException("Can not send signal on a non running process.");
			}
			return false;
		}
		if( $this->isSigchildEnabled() ) 
		{
			if( $throwException ) 
			{
				throw new \RuntimeException("This PHP has been compiled with --enable-sigchild. The process can not be signaled.");
			}
			return false;
		}
		if( true !== @proc_terminate($this->process, $signal) ) 
		{
			if( $throwException ) 
			{
				throw new \RuntimeException(sprintf("Error while sending signal `%s`.", $signal));
			}
			return false;
		}
		$this->latestSignal = $signal;
		return true;
	}
	private function requireProcessIsStarted($functionName) 
	{
		if( !$this->isStarted() ) 
		{
			throw new \LogicException(sprintf("Process must be started before calling %s.", $functionName));
		}
	}
	private function requireProcessIsTerminated($functionName) 
	{
		if( !$this->isTerminated() ) 
		{
			throw new \LogicException(sprintf("Process must be terminated before calling %s.", $functionName));
		}
	}
}
?>