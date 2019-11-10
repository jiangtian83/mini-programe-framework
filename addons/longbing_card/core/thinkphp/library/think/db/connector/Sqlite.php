<?php  namespace think\db\connector;
class Sqlite extends \think\db\Connection 
{
	protected $builder = "\\think\\db\\builder\\Sqlite";
	protected function parseDsn($config) 
	{
		$dsn = "sqlite:" . $config["database"];
		return $dsn;
	}
	public function getFields($tableName) 
	{
		list($tableName) = explode(" ", $tableName);
		$sql = "PRAGMA table_info( " . $tableName . " )";
		$pdo = $this->query($sql, array( ), false, true);
		$result = $pdo->fetchAll(\PDO::FETCH_ASSOC);
		$info = array( );
		if( $result ) 
		{
			foreach( $result as $key => $val ) 
			{
				$val = array_change_key_case($val);
				$info[$val["name"]] = array( "name" => $val["name"], "type" => $val["type"], "notnull" => 1 === $val["notnull"], "default" => $val["dflt_value"], "primary" => "1" == $val["pk"], "autoinc" => "1" == $val["pk"] );
			}
		}
		return $this->fieldCase($info);
	}
	public function getTables($dbName = "") 
	{
		$sql = "SELECT name FROM sqlite_master WHERE type='table' " . "UNION ALL SELECT name FROM sqlite_temp_master " . "WHERE type='table' ORDER BY name";
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
		return array( );
	}
	protected function supportSavepoint() 
	{
		return true;
	}
}
?>