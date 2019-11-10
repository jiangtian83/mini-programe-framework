<?php  namespace think\db\connector;
class Sqlsrv extends \think\db\Connection 
{
	protected $params = NULL;
	protected $builder = "\\think\\db\\builder\\Sqlsrv";
	protected function parseDsn($config) 
	{
		$dsn = "sqlsrv:Database=" . $config["database"] . ";Server=" . $config["hostname"];
		if( !empty($config["hostport"]) ) 
		{
			$dsn .= "," . $config["hostport"];
		}
		return $dsn;
	}
	public function getFields($tableName) 
	{
		list($tableName) = explode(" ", $tableName);
		$sql = "SELECT   column_name,   data_type,   column_default,   is_nullable\r\n        FROM    information_schema.tables AS t\r\n        JOIN    information_schema.columns AS c\r\n        ON  t.table_catalog = c.table_catalog\r\n        AND t.table_schema  = c.table_schema\r\n        AND t.table_name    = c.table_name\r\n        WHERE   t.table_name = '" . $tableName . "'";
		$pdo = $this->query($sql, array( ), false, true);
		$result = $pdo->fetchAll(\PDO::FETCH_ASSOC);
		$info = array( );
		if( $result ) 
		{
			foreach( $result as $key => $val ) 
			{
				$val = array_change_key_case($val);
				$info[$val["column_name"]] = array( "name" => $val["column_name"], "type" => $val["data_type"], "notnull" => (bool) ("" === $val["is_nullable"]), "default" => $val["column_default"], "primary" => false, "autoinc" => false );
			}
		}
		$sql = "SELECT column_name FROM information_schema.key_column_usage WHERE table_name='" . $tableName . "'";
		$this->debug(true);
		$pdo = $this->linkID->query($sql);
		$this->debug(false, $sql);
		$result = $pdo->fetch(\PDO::FETCH_ASSOC);
		if( $result ) 
		{
			$info[$result["column_name"]]["primary"] = true;
		}
		return $this->fieldCase($info);
	}
	public function getTables($dbName = "") 
	{
		$sql = "SELECT TABLE_NAME\r\n            FROM INFORMATION_SCHEMA.TABLES\r\n            WHERE TABLE_TYPE = 'BASE TABLE'\r\n            ";
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
}
?>