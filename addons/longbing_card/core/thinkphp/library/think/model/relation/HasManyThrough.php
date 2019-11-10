<?php  namespace think\model\relation;
class HasManyThrough extends \think\model\Relation 
{
	protected $throughKey = NULL;
	protected $through = NULL;
	public function __construct(\think\Model $parent, $model, $through, $foreignKey, $throughKey, $localKey) 
	{
		$this->parent = $parent;
		$this->model = $model;
		$this->through = $through;
		$this->foreignKey = $foreignKey;
		$this->throughKey = $throughKey;
		$this->localKey = $localKey;
		$this->query = (new $model())->db();
	}
	public function getRelation($subRelation = "", $closure = NULL) 
	{
		if( $closure ) 
		{
			call_user_func_array($closure, array( $this->query ));
		}
		return $this->relation($subRelation)->select();
	}
	public function has($operator = ">=", $count = 1, $id = "*", $joinType = "INNER") 
	{
		return $this->parent;
	}
	public function hasWhere($where = array( ), $fields = NULL) 
	{
		throw new \think\Exception("relation not support: hasWhere");
	}
	public function eagerlyResultSet(&$resultSet, $relation, $subRelation, $closure) 
	{
	}
	public function eagerlyResult(&$result, $relation, $subRelation, $closure) 
	{
	}
	public function relationCount($result, $closure) 
	{
	}
	protected function baseQuery() 
	{
		if( empty($this->baseQuery) && $this->parent->getData() ) 
		{
			$through = $this->through;
			$alias = \think\Loader::parseName(basename(str_replace("\\", "/", $this->model)));
			$throughTable = $through::getTable();
			$pk = (new $through())->getPk();
			$throughKey = $this->throughKey;
			$modelTable = $this->parent->getTable();
			$this->query->field($alias . ".*")->alias($alias)->join($throughTable, $throughTable . "." . $pk . "=" . $alias . "." . $throughKey)->join($modelTable, $modelTable . "." . $this->localKey . "=" . $throughTable . "." . $this->foreignKey)->where($throughTable . "." . $this->foreignKey, $this->parent->
			{
				$this->localKey}
			);
			$this->baseQuery = true;
		}
	}
}
?>