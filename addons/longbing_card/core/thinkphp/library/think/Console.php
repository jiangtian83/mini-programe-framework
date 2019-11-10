<?php  namespace think;
class Console 
{
	private $name = NULL;
	private $version = NULL;
	private $commands = array( );
	private $wantHelps = false;
	private $catchExceptions = true;
	private $autoExit = true;
	private $definition = NULL;
	private $defaultCommand = NULL;
	private static $defaultCommands = array( "think\\console\\command\\Help", "think\\console\\command\\Lists", "think\\console\\command\\Build", "think\\console\\command\\Clear", "think\\console\\command\\make\\Controller", "think\\console\\command\\make\\Model", "think\\console\\command\\optimize\\Autoload", "think\\console\\command\\optimize\\Config", "think\\console\\command\\optimize\\Route", "think\\console\\command\\optimize\\Schema" );
	public function __construct($name = "UNKNOWN", $version = "UNKNOWN", $user = NULL) 
	{
		$this->name = $name;
		$this->version = $version;
		if( $user ) 
		{
			$this->setUser($user);
		}
		$this->defaultCommand = "list";
		$this->definition = $this->getDefaultInputDefinition();
		foreach( $this->getDefaultCommands() as $command ) 
		{
			$this->add($command);
		}
	}
	public function setUser($user) 
	{
		$user = posix_getpwnam($user);
		if( $user ) 
		{
			posix_setuid($user["uid"]);
			posix_setgid($user["gid"]);
		}
	}
	public static function init($run = true) 
	{
		static $console = NULL;
		if( !$console ) 
		{
			$config = Config::get("console");
			$console = new self($config["name"], $config["version"], $config["user"]);
			if( is_file(CONF_PATH . "command" . EXT) ) 
			{
				$commands = include(CONF_PATH . "command" . EXT);
				if( is_array($commands) ) 
				{
					foreach( $commands as $command ) 
					{
						class_exists($command) && is_subclass_of($command, "\\think\\console\\Command") and $console->add(new $command());
					}
				}
			}
		}
		return ($run ? $console->run() : $console);
	}
	public static function call($command, array $parameters = array( ), $driver = "buffer") 
	{
		$console = self::init(false);
		array_unshift($parameters, $command);
		$input = new console\Input($parameters);
		$output = new console\Output($driver);
		$console->setCatchExceptions(false);
		$console->find($command)->run($input, $output);
		return $output;
	}
	public function run() 
	{
		$input = new console\Input();
		$output = new console\Output();
		$this->configureIO($input, $output);
		try 
		{
			$exitCode = $this->doRun($input, $output);
		}
		catch( \Exception $e ) 
		{
			if( !$this->catchExceptions ) 
			{
				throw $e;
			}
			$output->renderException($e);
			$exitCode = $e->getCode();
			if( is_numeric($exitCode) ) 
			{
				$exitCode = ((int) $exitCode ?: 1);
			}
			else 
			{
				$exitCode = 1;
			}
		}
		if( $this->autoExit ) 
		{
			if( 255 < $exitCode ) 
			{
				$exitCode = 255;
			}
			exit( $exitCode );
		}
		return $exitCode;
	}
	public function doRun(console\Input $input, console\Output $output) 
	{
		if( true === $input->hasParameterOption(array( "--version", "-V" )) ) 
		{
			$output->writeln($this->getLongVersion());
			return 0;
		}
		$name = $this->getCommandName($input);
		if( true === $input->hasParameterOption(array( "--help", "-h" )) ) 
		{
			if( !$name ) 
			{
				$name = "help";
				$input = new console\Input(array( "help" ));
			}
			else 
			{
				$this->wantHelps = true;
			}
		}
		if( !$name ) 
		{
			$name = $this->defaultCommand;
			$input = new console\Input(array( $this->defaultCommand ));
		}
		return $this->doRunCommand($this->find($name), $input, $output);
	}
	public function setDefinition(console\input\Definition $definition) 
	{
		$this->definition = $definition;
		return $this;
	}
	public function getDefinition() 
	{
		return $this->definition;
	}
	public function getHelp() 
	{
		return $this->getLongVersion();
	}
	public function setCatchExceptions($boolean) 
	{
		$this->catchExceptions = (bool) $boolean;
		return $this;
	}
	public function setAutoExit($boolean) 
	{
		$this->autoExit = (bool) $boolean;
		return $this;
	}
	public function getName() 
	{
		return $this->name;
	}
	public function setName($name) 
	{
		$this->name = $name;
		return $this;
	}
	public function getVersion() 
	{
		return $this->version;
	}
	public function setVersion($version) 
	{
		$this->version = $version;
		return $this;
	}
	public function getLongVersion() 
	{
		if( "UNKNOWN" !== $this->getName() && "UNKNOWN" !== $this->getVersion() ) 
		{
			return sprintf("<info>%s</info> version <comment>%s</comment>", $this->getName(), $this->getVersion());
		}
		return "<info>Console Tool</info>";
	}
	public function register($name) 
	{
		return $this->add(new console\Command($name));
	}
	public function addCommands(array $commands) 
	{
		foreach( $commands as $command ) 
		{
			$this->add($command);
		}
		return $this;
	}
	public function add(console\Command $command) 
	{
		if( !$command->isEnabled() ) 
		{
			$command->setConsole(null);
			return false;
		}
		$command->setConsole($this);
		if( null === $command->getDefinition() ) 
		{
			throw new \LogicException(sprintf("Command class \"%s\" is not correctly initialized. You probably forgot to call the parent constructor.", get_class($command)));
		}
		$this->commands[$command->getName()] = $command;
		foreach( $command->getAliases() as $alias ) 
		{
			$this->commands[$alias] = $command;
		}
		return $command;
	}
	public function get($name) 
	{
		if( !isset($this->commands[$name]) ) 
		{
			throw new \InvalidArgumentException(sprintf("The command \"%s\" does not exist.", $name));
		}
		$command = $this->commands[$name];
		if( $this->wantHelps ) 
		{
			$this->wantHelps = false;
			$helpCommand = $this->get("help");
			$helpCommand->setCommand($command);
			return $helpCommand;
		}
		return $command;
	}
	public function has($name) 
	{
		return isset($this->commands[$name]);
	}
	public function getNamespaces() 
	{
		$namespaces = array( );
		foreach( $this->commands as $command ) 
		{
			$namespaces = array_merge($namespaces, $this->extractAllNamespaces($command->getName()));
			foreach( $command->getAliases() as $alias ) 
			{
				$namespaces = array_merge($namespaces, $this->extractAllNamespaces($alias));
			}
		}
		return array_values(array_unique(array_filter($namespaces)));
	}
	public function findNamespace($namespace) 
	{
		$expr = preg_replace_callback("{([^:]+|)}", function($matches) 
		{
			return preg_quote($matches[1]) . "[^:]*";
		}
		, $namespace);
		$allNamespaces = $this->getNamespaces();
		$namespaces = preg_grep("{^" . $expr . "}", $allNamespaces);
		if( empty($namespaces) ) 
		{
			$message = sprintf("There are no commands defined in the \"%s\" namespace.", $namespace);
			if( $alternatives = $this->findAlternatives($namespace, $allNamespaces) ) 
			{
				if( 1 == count($alternatives) ) 
				{
					$message .= "\n\nDid you mean this?\n    ";
				}
				else 
				{
					$message .= "\n\nDid you mean one of these?\n    ";
				}
				$message .= implode("\n    ", $alternatives);
			}
			throw new \InvalidArgumentException($message);
		}
		$exact = in_array($namespace, $namespaces, true);
		if( 1 < count($namespaces) && !$exact ) 
		{
			throw new \InvalidArgumentException(sprintf("The namespace \"%s\" is ambiguous (%s).", $namespace, $this->getAbbreviationSuggestions(array_values($namespaces))));
		}
		return ($exact ? $namespace : reset($namespaces));
	}
	public function find($name) 
	{
		$expr = preg_replace_callback("{([^:]+|)}", function($matches) 
		{
			return preg_quote($matches[1]) . "[^:]*";
		}
		, $name);
		$allCommands = array_keys($this->commands);
		$commands = preg_grep("{^" . $expr . "}", $allCommands);
		if( empty($commands) || count(preg_grep("{^" . $expr . "\$}", $commands)) < 1 ) 
		{
			if( false !== ($pos = strrpos($name, ":")) ) 
			{
				$this->findNamespace(substr($name, 0, $pos));
			}
			$message = sprintf("Command \"%s\" is not defined.", $name);
			if( $alternatives = $this->findAlternatives($name, $allCommands) ) 
			{
				if( 1 == count($alternatives) ) 
				{
					$message .= "\n\nDid you mean this?\n    ";
				}
				else 
				{
					$message .= "\n\nDid you mean one of these?\n    ";
				}
				$message .= implode("\n    ", $alternatives);
			}
			throw new \InvalidArgumentException($message);
		}
		if( 1 < count($commands) ) 
		{
			$commandList = $this->commands;
			$commands = array_filter($commands, function($nameOrAlias) use ($commandList, $commands) 
			{
				$commandName = $commandList[$nameOrAlias]->getName();
				return $commandName === $nameOrAlias || !in_array($commandName, $commands);
			}
			);
		}
		$exact = in_array($name, $commands, true);
		if( 1 < count($commands) && !$exact ) 
		{
			$suggestions = $this->getAbbreviationSuggestions(array_values($commands));
			throw new \InvalidArgumentException(sprintf("Command \"%s\" is ambiguous (%s).", $name, $suggestions));
		}
		return $this->get(($exact ? $name : reset($commands)));
	}
	public function all($namespace = NULL) 
	{
		if( null === $namespace ) 
		{
			return $this->commands;
		}
		$commands = array( );
		foreach( $this->commands as $name => $command ) 
		{
			$ext = $this->extractNamespace($name, substr_count($namespace, ":") + 1);
			if( $ext === $namespace ) 
			{
				$commands[$name] = $command;
			}
		}
		return $commands;
	}
	public static function getAbbreviations($names) 
	{
		$abbrevs = array( );
		foreach( $names as $name ) 
		{
			for( $len = strlen($name);
			0 < $len;
			$len-- ) 
			{
				$abbrev = substr($name, 0, $len);
				$abbrevs[$abbrev][] = $name;
			}
		}
		return $abbrevs;
	}
	protected function configureIO(console\Input $input, console\Output $output) 
	{
		if( true === $input->hasParameterOption(array( "--ansi" )) ) 
		{
			$output->setDecorated(true);
		}
		else 
		{
			if( true === $input->hasParameterOption(array( "--no-ansi" )) ) 
			{
				$output->setDecorated(false);
			}
		}
		if( true === $input->hasParameterOption(array( "--no-interaction", "-n" )) ) 
		{
			$input->setInteractive(false);
		}
		if( true === $input->hasParameterOption(array( "--quiet", "-q" )) ) 
		{
			$output->setVerbosity(console\Output::VERBOSITY_QUIET);
		}
		else 
		{
			if( $input->hasParameterOption("-vvv") || $input->hasParameterOption("--verbose=3") || $input->getParameterOption("--verbose") === 3 ) 
			{
				$output->setVerbosity(console\Output::VERBOSITY_DEBUG);
			}
			else 
			{
				if( $input->hasParameterOption("-vv") || $input->hasParameterOption("--verbose=2") || $input->getParameterOption("--verbose") === 2 ) 
				{
					$output->setVerbosity(console\Output::VERBOSITY_VERY_VERBOSE);
				}
				else 
				{
					if( $input->hasParameterOption("-v") || $input->hasParameterOption("--verbose=1") || $input->hasParameterOption("--verbose") || $input->getParameterOption("--verbose") ) 
					{
						$output->setVerbosity(console\Output::VERBOSITY_VERBOSE);
					}
				}
			}
		}
	}
	protected function doRunCommand(console\Command $command, console\Input $input, console\Output $output) 
	{
		return $command->run($input, $output);
	}
	protected function getCommandName(console\Input $input) 
	{
		return $input->getFirstArgument();
	}
	protected function getDefaultInputDefinition() 
	{
		return new console\input\Definition(array( new console\input\Argument("command", console\input\Argument::REQUIRED, "The command to execute"), new console\input\Option("--help", "-h", console\input\Option::VALUE_NONE, "Display this help message"), new console\input\Option("--version", "-V", console\input\Option::VALUE_NONE, "Display this console version"), new console\input\Option("--quiet", "-q", console\input\Option::VALUE_NONE, "Do not output any message"), new console\input\Option("--verbose", "-v|vv|vvv", console\input\Option::VALUE_NONE, "Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug"), new console\input\Option("--ansi", "", console\input\Option::VALUE_NONE, "Force ANSI output"), new console\input\Option("--no-ansi", "", console\input\Option::VALUE_NONE, "Disable ANSI output"), new console\input\Option("--no-interaction", "-n", console\input\Option::VALUE_NONE, "Do not ask any interactive question") ));
	}
	protected function getDefaultCommands() 
	{
		$defaultCommands = array( );
		foreach( self::$defaultCommands as $class ) 
		{
			if( class_exists($class) && is_subclass_of($class, "think\\console\\Command") ) 
			{
				$defaultCommands[] = new $class();
			}
		}
		return $defaultCommands;
	}
	public static function addDefaultCommands(array $classes) 
	{
		self::$defaultCommands = array_merge(self::$defaultCommands, $classes);
	}
	private function getAbbreviationSuggestions($abbrevs) 
	{
		return sprintf("%s, %s%s", $abbrevs[0], $abbrevs[1], (2 < count($abbrevs) ? sprintf(" and %d more", count($abbrevs) - 2) : ""));
	}
	public function extractNamespace($name, $limit = NULL) 
	{
		$parts = explode(":", $name);
		array_pop($parts);
		return implode(":", (null === $limit ? $parts : array_slice($parts, 0, $limit)));
	}
	private function findAlternatives($name, $collection) 
	{
		$threshold = 1000;
		$alternatives = array( );
		$collectionParts = array( );
		foreach( $collection as $item ) 
		{
			$collectionParts[$item] = explode(":", $item);
		}
		foreach( explode(":", $name) as $i => $subname ) 
		{
			foreach( $collectionParts as $collectionName => $parts ) 
			{
				$exists = isset($alternatives[$collectionName]);
				if( !isset($parts[$i]) && $exists ) 
				{
					$alternatives[$collectionName] += $threshold;
					continue;
				}
				if( !isset($parts[$i]) ) 
				{
					continue;
				}
				$lev = levenshtein($subname, $parts[$i]);
				if( $lev <= strlen($subname) / 3 || "" !== $subname && false !== strpos($parts[$i], $subname) ) 
				{
					$alternatives[$collectionName] = ($exists ? $alternatives[$collectionName] + $lev : $lev);
				}
				else 
				{
					if( $exists ) 
					{
						$alternatives[$collectionName] += $threshold;
					}
				}
			}
		}
		foreach( $collection as $item ) 
		{
			$lev = levenshtein($name, $item);
			if( $lev <= strlen($name) / 3 || false !== strpos($item, $name) ) 
			{
				$alternatives[$item] = (isset($alternatives[$item]) ? $alternatives[$item] - $lev : $lev);
			}
		}
		$alternatives = array_filter($alternatives, function($lev) use ($threshold) 
		{
			return $lev < 2 * $threshold;
		}
		);
		asort($alternatives);
		return array_keys($alternatives);
	}
	public function setDefaultCommand($commandName) 
	{
		$this->defaultCommand = $commandName;
		return $this;
	}
	private function extractAllNamespaces($name) 
	{
		$namespaces = array( );
		foreach( explode(":", $name, -1) as $part ) 
		{
			if( count($namespaces) ) 
			{
				$namespaces[] = end($namespaces) . ":" . $part;
			}
			else 
			{
				$namespaces[] = $part;
			}
		}
		return $namespaces;
	}
}
?>