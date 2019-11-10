<?php  namespace think\db;
abstract class Connection 
{
	protected $PDOStatement = NULL;
	protected $queryStr = "";
	protected $numRows = 0;
	protected $transTimes = 0;
	protected $error = "";
	protected $links = array( );
	protected $linkID = NULL;
	protected $linkRead = NULL;
	protected $linkWrite = NULL;
	protected $fetchType = \PDO::FETCH_ASSOC;
	protected $attrCase = \PDO::CASE_LOWER;
	protected static $event = array( );
	protected $builder = NULL;
	protected $config = NULL;
	protected $params = NULL;
	protected $bind = array( );
	public function __construct(array $config = array( )) 
	{
		if( !empty($config) ) 
		{
			$this->config = array_merge($this->config, $config);
		}
	}
	protected function getQuery() 
	{
		$class = $this->config["query"];
		return new $class($this);
	}
	public function getBuilder() 
	{
		if( !empty($this->builder) ) 
		{
			return $this->builder;
		}
		return ($this->getConfig("builder") ?: "\\think\\db\\builder\\" . ucfirst($this->getConfig("type")));
	}
	public function __call($method, $args) 
	{
		return call_user_func_array(array( $this->getQuery(), $method ), $args);
	}
	abstract protected function parseDsn($config);
	abstract public function getFields($tableName);
	abstract public function getTables($dbName);
	abstract protected function getExplain($sql);
	public function fieldCase($info) 
	{
		switch( $this->attrCase ) 
		{
			case \PDO::CASE_LOWER: $info = array_change_key_case($info);
			break;
			case \PDO::CASE_UPPER: $info = array_change_key_case($info, CASE_UPPER);
			break;
			case \PDO::CASE_NATURAL: }
		return $info;
	}
	public function getConfig($config = "") 
	{
		return ($config ? $this->config[$config] : $this->config);
	}
	public function setConfig($config, $value = "") 
	{
		if( is_array($config) ) 
		{
			$this->config = array_merge($this->config, $config);
		}
		else 
		{
			$this->config[$config] = $value;
		}
	}
	public function connect(array $config = array( ), $linkNum = 0, $autoConnection = false) 
	{
		if( !isset($this->links[$linkNum]) ) 
		{
			if( !$config ) 
			{
				$config = $this->config;
			}
			else 
			{
				$config = array_merge($this->config, $config);
			}
			if( isset($config["params"]) && is_array($config["params"]) ) 
			{
				$params = $config["params"] + $this->params;
			}
			else 
			{
				$params = $this->params;
			}
			$this->attrCase = $params[\PDO::ATTR_CASE];
			if( isset($config["result_type"]) ) 
			{
				$this->fetchType = $config["result_type"];
			}
			try 
			{
				if( empty($config["dsn"]) ) 
				{
					$config["dsn"] = $this->parseDsn($config);
				}
				if( $config["debug"] ) 
				{
					$startTime = microtime(true);
				}
				$this->links[$linkNum] = new \PDO($config["dsn"], $config["username"], $config["password"], $params);
				if( $config["debug"] ) 
				{
					\think\Log::record("[ DB ] CONNECT:[ UseTime:" . number_format(microtime(true) - $startTime, 6) . "s ] " . $config["dsn"], "sql");
				}
			}
			catch( \PDOException $e ) 
			{
				if( $autoConnection ) 
				{
					\think\Log::record($e->getMessage(), "error");
					return $this->connect($autoConnection, $linkNum);
				}
				throw $e;
			}
		}
		return $this->links[$linkNum];
	}
	public function free() 
	{
		$this->PDOStatement = null;
	}
	public function getPdo() 
	{
		if( !$this->linkID ) 
		{
			return false;
		}
		return $this->linkID;
	}
	public function query($sql, $bind = array( ), $master = false, $pdo = false) 
	{
		$this->initConnect($master);
		if( !$this->linkID ) 
		{
			return false;
		}
		$this->queryStr = $sql;
		if( $bind ) 
		{
			$this->bind = $bind;
		}
		\think\Db::$queryTimes++;
		try 
		{
			$this->debug(true);
			if( !empty($this->PDOStatement) ) 
			{
				$this->free();
			}
			if( empty($this->PDOStatement) ) 
			{
				$this->PDOStatement = $this->linkID->prepare($sql);
			}
			$procedure = in_array(strtolower(substr(trim($sql), 0, 4)), array( "call", "exec" ));
			if( $procedure ) 
			{
				$this->bindParam($bind);
			}
			else 
			{
				$this->bindValue($bind);
			}
			$this->PDOStatement->execute();
			$this->debug(false, "", $master);
			return $this->getResult($pdo, $procedure);
		}
		catch( \PDOException $e ) 
		{
			if( $this->isBreak($e) ) 
			{
				return $this->close()->query($sql, $bind, $master, $pdo);
			}
			throw new \think\exception\PDOException($e, $this->config, $this->getLastsql());
		}
		catch( \Throwable $e ) 
		{
			if( $this->isBreak($e) ) 
			{
				return $this->close()->query($sql, $bind, $master, $pdo);
			}
			throw $e;
		}
		catch( \Exception $e ) 
		{
			if( $this->isBreak($e) ) 
			{
				return $this->close()->query($sql, $bind, $master, $pdo);
			}
			throw $e;
		}
	}
	public function execute($sql, $bind = array( ), Query $query = NULL) 
	{
		$this->initConnect(true);
		if( !$this->linkID ) 
		{
			return false;
		}
		$this->queryStr = $sql;
		if( $bind ) 
		{
			$this->bind = $bind;
		}
		\think\Db::$executeTimes++;
		try 
		{
			$this->debug(true);
			if( !empty($this->PDOStatement) && $this->PDOStatement->queryString != $sql ) 
			{
				$this->free();
			}
			if( empty($this->PDOStatement) ) 
			{
				$this->PDOStatement = $this->linkID->prepare($sql);
			}
			$procedure = in_array(strtolower(substr(trim($sql), 0, 4)), array( "call", "exec" ));
			if( $procedure ) 
			{
				$this->bindParam($bind);
			}
			else 
			{
				$this->bindValue($bind);
			}
			$this->PDOStatement->execute();
			$this->debug(false, "", true);
			if( $query && !empty($this->config["deploy"]) && !empty($this->config["read_master"]) ) 
			{
				$query->readMaster();
			}
			$this->numRows = $this->PDOStatement->rowCount();
			return $this->numRows;
		}
		catch( \PDOException $e ) 
		{
			if( $this->isBreak($e) ) 
			{
				return $this->close()->execute($sql, $bind, $query);
			}
			throw new \think\exception\PDOException($e, $this->config, $this->getLastsql());
		}
		catch( \Throwable $e ) 
		{
			if( $this->isBreak($e) ) 
			{
				return $this->close()->execute($sql, $bind, $query);
			}
			throw $e;
		}
		catch( \Exception $e ) 
		{
			if( $this->isBreak($e) ) 
			{
				return $this->close()->execute($sql, $bind, $query);
			}
			throw $e;
		}
	}
	public function getRealSql($sql, array $bind = array( )) 
	{
		if( is_array($sql) ) 
		{
			$sql = implode(";", $sql);
		}
		foreach( $bind as $key => $val ) 
		{
			$value = (is_array($val) ? $val[0] : $val);
			$type = (is_array($val) ? $val[1] : \PDO::PARAM_STR);
			if( \PDO::PARAM_STR == $type ) 
			{
				$value = $this->quote($value);
			}
			else 
			{
				if( \PDO::PARAM_INT == $type ) 
				{
					$value = (double) $value;
				}
			}
			$sql = (is_numeric($key) ? substr_replace($sql, $value, strpos($sql, "?"), 1) : str_replace(array( ":" . $key . ")", ":" . $key . ",", ":" . $key . " ", ":" . $key . PHP_EOL ), array( $value . ")", $value . ",", $value . " ", $value . PHP_EOL ), $sql . " "));
		}
		return rtrim($sql);
	}
	protected function bindValue(array $bind = array( )) 
	{
		foreach( $bind as $key => $val ) 
		{
			$param = (is_numeric($key) ? $key + 1 : ":" . $key);
			if( is_array($val) ) 
			{
				if( \PDO::PARAM_INT == $val[1] && "" === $val[0] ) 
				{
					$val[0] = 0;
				}
				$result = $this->PDOStatement->bindValue($param, $val[0], $val[1]);
			}
			else 
			{
				$result = $this->PDOStatement->bindValue($param, $val);
			}
			if( !$result ) 
			{
				throw new exception\BindParamException("Error occurred  when binding parameters '" . $param . "'", $this->config, $this->getLastsql(), $bind);
			}
		}
	}
	protected function bindParam($bind) 
	{
		foreach( $bind as $key => $val ) 
		{
			$param = (is_numeric($key) ? $key + 1 : ":" . $key);
			if( is_array($val) ) 
			{
				array_unshift($val, $param);
				$result = call_user_func_array(array( $this->PDOStatement, "bindParam" ), $val);
			}
			else 
			{
				$result = $this->PDOStatement->bindValue($param, $val);
			}
			if( !$result ) 
			{
				$param = array_shift($val);
				throw new exception\BindParamException("Error occurred  when binding parameters '" . $param . "'", $this->config, $this->getLastsql(), $bind);
			}
		}
	}
	protected function getResult($pdo = false, $procedure = false) 
	{
		if( $pdo ) 
		{
			return $this->PDOStatement;
		}
		if( $procedure ) 
		{
			return $this->procedure();
		}
		$result = $this->PDOStatement->fetchAll($this->fetchType);
		$this->numRows = count($result);
		return $result;
	}
	protected function procedure() 
	{
		$item = array( );
		do 
		{
			$result = $this->getResult();
			if( $result ) 
			{
				$item[] = $result;
			}
		}
		while( $this->PDOStatement->nextRowset() );
		$this->numRows = count($item);
		return $item;
	}
	public function transaction($callback) 
	{
		$this->startTrans();
		try 
		{
			$result = null;
			if( is_callable($callback) ) 
			{
				$result = call_user_func_array($callback, array( $this ));
			}
			$this->commit();
			return $result;
		}
		catch( \Exception $e ) 
		{
			$this->rollback();
			throw $e;
		}
		catch( \Throwable $e ) 
		{
			$this->rollback();
			throw $e;
		}
	}
	public function startTrans() 
	{
		$this->initConnect(true);
		if( !$this->linkID ) 
		{
			return false;
		}
		$this->transTimes++;
		try 
		{
			if( 1 == $this->transTimes ) 
			{
				$this->linkID->beginTransaction();
			}
			else 
			{
				if( 1 < $this->transTimes && $this->supportSavepoint() ) 
				{
					$this->linkID->exec($this->parseSavepoint("trans" . $this->transTimes));
				}
			}
		}
		catch( \PDOException $e ) 
		{
			if( $this->isBreak($e) ) 
			{
				return $this->close()->startTrans();
			}
			throw $e;
		}
		catch( \Exception $e ) 
		{
			if( $this->isBreak($e) ) 
			{
				return $this->close()->startTrans();
			}
			throw $e;
		}
		catch( \Error $e ) 
		{
			if( $this->isBreak($e) ) 
			{
				return $this->close()->startTrans();
			}
			throw $e;
		}
	}
	public function commit() 
	{
		$this->initConnect(true);
		if( 1 == $this->transTimes ) 
		{
			$this->linkID->commit();
		}
		$this->transTimes--;
	}
	public function rollback() 
	{
		$this->initConnect(true);
		if( 1 == $this->transTimes ) 
		{
			$this->linkID->rollBack();
		}
		else 
		{
			if( 1 < $this->transTimes && $this->supportSavepoint() ) 
			{
				$this->linkID->exec($this->parseSavepointRollBack("trans" . $this->transTimes));
			}
		}
		$this->transTimes = max(0, $this->transTimes - 1);
	}
	protected function supportSavepoint() 
	{
		return false;
	}
	protected function parseSavepoint($name) 
	{
		return "SAVEPOINT " . $name;
	}
	protected function parseSavepointRollBack($name) 
	{
		return "ROLLBACK TO SAVEPOINT " . $name;
	}
	public function batchQuery($sqlArray = array( ), $bind = array( ), Query $query = NULL) 
	{
		if( !is_array($sqlArray) ) 
		{
			return false;
		}
		$this->startTrans();
		try 
		{
			foreach( $sqlArray as $sql ) 
			{
				$this->execute($sql, $bind, $query);
			}
			$this->commit();
		}
		catch( \Exception $e ) 
		{
			$this->rollback();
			throw $e;
		}
		return true;
	}
	public function getQueryTimes($execute = false) 
	{
		return ($execute ? \think\Db::$queryTimes + \think\Db::$executeTimes : \think\Db::$queryTimes);
	}
	public function getExecuteTimes() 
	{
		return \think\Db::$executeTimes;
	}
	public function close() 
	{
		$this->linkID = null;
		$this->linkWrite = null;
		$this->linkRead = null;
		$this->links = array( );
		return $this;
	}
	protected function isBreak($e) 
	{
		if( !$this->config["break_reconnect"] ) 
		{
			return false;
		}
		$info = array( "server has gone away", "no connection to the server", "Lost connection", "is dead or not enabled", "Error while sending", "decryption failed or bad record mac", "server closed the connection unexpectedly", "SSL connection has been closed unexpectedly", "Error writing data to the connection", "Resource deadlock avoided", "failed with errno" );
		$error = $e->getMessage();
		foreach( $info as $msg ) 
		{
			if( false !== stripos($error, $msg) ) 
			{
				return true;
			}
		}
		return false;
	}
	public function getLastSql() 
	{
		return $this->getRealSql($this->queryStr, $this->bind);
	}
	public function getLastInsID($sequence = NULL) 
	{
		return $this->linkID->lastInsertId($sequence);
	}
	public function getNumRows() 
	{
		return $this->numRows;
	}
	public function getError() 
	{
		if( $this->PDOStatement ) 
		{
			$error = $this->PDOStatement->errorInfo();
			$error = $error[1] . ":" . $error[2];
		}
		else 
		{
			$error = "";
		}
		if( "" != $this->queryStr ) 
		{
			$error .= "\n [ SQL语句 ] : " . $this->getLastsql();
		}
		return $error;
	}
	public function quote($str, $master = true) 
	{
		$this->initConnect($master);
		return ($this->linkID ? $this->linkID->quote($str) : $str);
	}
	protected function debug($start, $sql = "", $master = false) 
	{
		if( !empty($this->config["debug"]) ) 
		{
			if( $start ) 
			{
				\think\Debug::remark("queryStartTime", "time");
			}
			else 
			{
				\think\Debug::remark("queryEndTime", "time");
				$runtime = \think\Debug::getRangeTime("queryStartTime", "queryEndTime");
				$sql = ($sql ?: $this->getLastsql());
				$result = array( );
				if( $this->config["sql_explain"] && 0 === stripos(trim($sql), "select") ) 
				{
					$result = $this->getExplain($sql);
				}
				$this->trigger($sql, $runtime, $result, $master);
			}
		}
	}
	public function listen($callback) 
	{
		self::$event[] = $callback;
	}
	protected function trigger($sql, $runtime, $explain = array( ), $master = false) 
	{
		if( !empty($event) ) 
		{
			foreach( self::$event as $callback ) 
			{
				if( is_callable($callback) ) 
				{
					call_user_func_array($callback, array( $sql, $runtime, $explain, $master ));
				}
			}
		}
		else 
		{
			if( $this->config["deploy"] ) 
			{
				$master = ($master ? "master|" : "slave|");
			}
			else 
			{
				$master = "";
			}
			\think\Log::record("[ SQL ] " . $sql . " [ " . $master . "RunTime:" . $runtime . "s ]", "sql");
			if( !empty($explain) ) 
			{
				\think\Log::record("[ EXPLAIN : " . var_export($explain, true) . " ]", "sql");
			}
		}
	}
	protected function initConnect($master = true) 
	{
		if( !empty($this->config["deploy"]) ) 
		{
			if( $master || $this->transTimes ) 
			{
				if( !$this->linkWrite ) 
				{
					$this->linkWrite = $this->multiConnect(true);
				}
				$this->linkID = $this->linkWrite;
			}
			else 
			{
				if( !$this->linkRead ) 
				{
					$this->linkRead = $this->multiConnect(false);
				}
				$this->linkID = $this->linkRead;
			}
		}
		else 
		{
			if( !$this->linkID ) 
			{
				$this->linkID = $this->connect();
			}
		}
	}
	protected function multiConnect($master = false) 
	{
		$_config = array( );
		foreach( array( "username", "password", "hostname", "hostport", "database", "dsn", "charset" ) as $name ) 
		{
			$_config[$name] = explode(",", $this->config[$name]);
		}
		$m = floor(mt_rand(0, $this->config["master_num"] - 1));
		if( $this->config["rw_separate"] ) 
		{
			if( $master ) 
			{
				$r = $m;
			}
			else 
			{
				if( is_numeric($this->config["slave_no"]) ) 
				{
					$r = $this->config["slave_no"];
				}
				else 
				{
					$r = floor(mt_rand($this->config["master_num"], count($_config["hostname"]) - 1));
				}
			}
		}
		else 
		{
			$r = floor(mt_rand(0, count($_config["hostname"]) - 1));
		}
		$dbMaster = false;
		if( $m != $r ) 
		{
			$dbMaster = array( );
			foreach( array( "username", "password", "hostname", "hostport", "database", "dsn", "charset" ) as $name ) 
			{
				$dbMaster[$name] = (isset($_config[$name][$m]) ? $_config[$name][$m] : $_config[$name][0]);
			}
		}
		$dbConfig = array( );
		foreach( array( "username", "password", "hostname", "hostport", "database", "dsn", "charset" ) as $name ) 
		{
			$dbConfig[$name] = (isset($_config[$name][$r]) ? $_config[$name][$r] : $_config[$name][0]);
		}
		return $this->connect($dbConfig, $r, ($r == $m ? false : $dbMaster));
	}
	public function __destruct() 
	{
		if( $this->PDOStatement ) 
		{
			$this->free();
		}
		$this->close();
	}
}
?>