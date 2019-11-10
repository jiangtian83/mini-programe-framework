<?php  namespace think\db\connector;
class Mysql extends \think\db\Connection 
{
	protected $builder = "\\think\\db\\builder\\Mysql";
	protected function parseDsn($config) 
	{
		if( !empty($config["socket"]) ) 
		{
			$dsn = "mysql:unix_socket=" . $config["socket"];
		}
		else 
		{
			if( !empty($config["hostport"]) ) 
			{
				$dsn = "mysql:host=" . $config["hostname"] . ";port=" . $config["hostport"];
			}
			else 
			{
				$dsn = "mysql:host=" . $config["hostname"];
			}
		}
		$dsn .= ";dbname=" . $config["database"];
		if( !empty($config["charset"]) ) 
		{
			$dsn .= ";charset=" . $config["charset"];
		}
		return $dsn;
	}
	public function getFields($tableName) 
	{
		list($tableName) = explode(" ", $tableName);
		if( false === strpos($tableName, "`") ) 
		{
			if( strpos($tableName, ".") ) 
			{
				$tableName = str_replace(".", "`.`", $tableName);
			}
			$tableName = "`" . $tableName . "`";
		}
		$sql = "SHOW COLUMNS FROM " . $tableName;
		$pdo = $this->query($sql, array( ), false, true);
		$result = $pdo->fetchAll(\PDO::FETCH_ASSOC);
		$info = array( );
		if( $result ) 
		{
			foreach( $result as $key => $val ) 
			{
				$val = array_change_key_case($val);
				$info[$val["field"]] = array( "name" => $val["field"], "type" => $val["type"], "notnull" => (bool) ("" === $val["null"]), "default" => $val["default"], "primary" => strtolower($val["key"]) == "pri", "autoinc" => strtolower($val["extra"]) == "auto_increment" );
			}
		}
		return $this->fieldCase($info);
	}
	public function getTables($dbName = "") 
	{
		$sql = (!empty($dbName) ? "SHOW TABLES FROM " . $dbName : "SHOW TABLES ");
		$pdo = $this->query($sql, array( ), false, true);
		$result = $pdo->fetchAll(\PDO::FETCH_ASSOC);
		$info = array( );
		foreach( $result as $key => $val ) 
		{
			$info[$key] = current($val);
		}
		return $info;
	}
	protected function getExplain($sql) 
	{
		$pdo = $this->linkID->query("EXPLAIN " . $sql);
		$result = $pdo->fetch(\PDO::FETCH_ASSOC);
		$result = array_change_key_case($result);
		if( isset($result["extra"]) && (strpos($result["extra"], "filesort") || strpos($result["extra"], "temporary")) ) 
		{
			\think\Log::record("SQL:" . $this->queryStr . "[" . $result["extra"] . "]", "warn");
		}
		return $result;
	}
	protected function supportSavepoint() 
	{
		return true;
	}
}
?>