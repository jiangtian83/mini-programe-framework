<?php  namespace think\db\connector;
class Pgsql extends \think\db\Connection 
{
	protected $builder = "\\think\\db\\builder\\Pgsql";
	protected function parseDsn($config) 
	{
		$dsn = "pgsql:dbname=" . $config["database"] . ";host=" . $config["hostname"];
		if( !empty($config["hostport"]) ) 
		{
			$dsn .= ";port=" . $config["hostport"];
		}
		return $dsn;
	}
	public function getFields($tableName) 
	{
		list($tableName) = explode(" ", $tableName);
		$sql = "select fields_name as \"field\",fields_type as \"type\",fields_not_null as \"null\",fields_key_name as \"key\",fields_default as \"default\",fields_default as \"extra\" from table_msg('" . $tableName . "');";
		$pdo = $this->query($sql, array( ), false, true);
		$result = $pdo->fetchAll(\PDO::FETCH_ASSOC);
		$info = array( );
		if( $result ) 
		{
			foreach( $result as $key => $val ) 
			{
				$val = array_change_key_case($val);
				$info[$val["field"]] = array( "name" => $val["field"], "type" => $val["type"], "notnull" => (bool) ("" !== $val["null"]), "default" => $val["default"], "primary" => !empty($val["key"]), "autoinc" => 0 === strpos($val["extra"], "nextval(") );
			}
		}
		return $this->fieldCase($info);
	}
	public function getTables($dbName = "") 
	{
		$sql = "select tablename as Tables_in_test from pg_tables where  schemaname ='public'";
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