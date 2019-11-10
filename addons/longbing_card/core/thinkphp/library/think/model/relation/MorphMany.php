<?php  namespace think\model\relation;
class MorphMany extends \think\model\Relation 
{
	protected $morphKey = NULL;
	protected $morphType = NULL;
	protected $type = NULL;
	public function __construct(\think\Model $parent, $model, $morphKey, $morphType, $type) 
	{
		$this->parent = $parent;
		$this->model = $model;
		$this->type = $type;
		$this->morphKey = $morphKey;
		$this->morphType = $morphType;
		$this->query = (new $model())->db();
	}
	public function getRelation($subRelation = "", $closure = NULL) 
	{
		if( $closure ) 
		{
			call_user_func_array($closure, array( $this->query ));
		}
		$list = $this->relation($subRelation)->select();
		$parent = clone $this->parent;
		foreach( $list as &$model ) 
		{
			$model->setParent($parent);
		}
		return $list;
	}
	public function has($operator = ">=", $count = 1, $id = "*", $joinType = "INNER") 
	{
		throw new \think\Exception("relation not support: has");
	}
	public function hasWhere($where = array( ), $fields = NULL) 
	{
		throw new \think\Exception("relation not support: hasWhere");
	}
	public function eagerlyResultSet(&$resultSet, $relation, $subRelation, $closure) 
	{
		$morphType = $this->morphType;
		$morphKey = $this->morphKey;
		$type = $this->type;
		$range = array( );
		foreach( $resultSet as $result ) 
		{
			$pk = $result->getPk();
			if( isset($result->$pk) ) 
			{
				$range[] = $result->$pk;
			}
		}
		if( !empty($range) ) 
		{
			$data = $this->eagerlyMorphToMany(array( $morphKey => array( "in", $range ), $morphType => $type ), $relation, $subRelation, $closure);
			$attr = \think\Loader::parseName($relation);
			foreach( $resultSet as $result ) 
			{
				if( !isset($data[$result->$pk]) ) 
				{
					$data[$result->$pk] = array( );
				}
				foreach( $data[$result->$pk] as &$relationModel ) 
				{
					$relationModel->setParent(clone $result);
					$relationModel->isUpdate(true);
				}
				$result->setRelation($attr, $this->resultSetBuild($data[$result->$pk]));
			}
		}
	}
	public function eagerlyResult(&$result, $relation, $subRelation, $closure) 
	{
		$pk = $result->getPk();
		if( isset($result->$pk) ) 
		{
			$data = $this->eagerlyMorphToMany(array( $this->morphKey => $result->$pk, $this->morphType => $this->type ), $relation, $subRelation, $closure);
			if( !isset($data[$result->$pk]) ) 
			{
				$data[$result->$pk] = array( );
			}
			foreach( $data[$result->$pk] as &$relationModel ) 
			{
				$relationModel->setParent(clone $result);
				$relationModel->isUpdate(true);
			}
			$result->setRelation(\think\Loader::parseName($relation), $this->resultSetBuild($data[$result->$pk]));
		}
	}
	public function relationCount($result, $closure) 
	{
		$pk = $result->getPk();
		$count = 0;
		if( isset($result->$pk) ) 
		{
			if( $closure ) 
			{
				call_user_func_array($closure, array( $this->query ));
			}
			$count = $this->query->where(array( $this->morphKey => $result->$pk, $this->morphType => $this->type ))->count();
		}
		return $count;
	}
	public function getRelationCountQuery($closure) 
	{
		if( $closure ) 
		{
			call_user_func_array($closure, array( $this->query ));
		}
		return $this->query->where(array( $this->morphKey => array( "exp", \think\Db::raw("=" . $this->parent->getTable() . "." . $this->parent->getPk()) ), $this->morphType => $this->type ))->fetchSql()->count();
	}
	protected function eagerlyMorphToMany($where, $relation, $subRelation = "", $closure = false) 
	{
		if( $closure ) 
		{
			call_user_func_array($closure, array( $this ));
		}
		$list = $this->query->where($where)->with($subRelation)->select();
		$morphKey = $this->morphKey;
		$data = array( );
		foreach( $list as $set ) 
		{
			$data[$set->$morphKey][] = $set;
		}
		return $data;
	}
	public function save($data) 
	{
		if( $data instanceof \think\Model ) 
		{
			$data = $data->getData();
		}
		$pk = $this->parent->getPk();
		$model = new $this->model();
		$data[$this->morphKey] = $this->parent->$pk;
		$data[$this->morphType] = $this->type;
		return ($model->save($data) ? $model : false);
	}
	public function saveAll(array $dataSet) 
	{
		$result = false;
		foreach( $dataSet as $key => $data ) 
		{
			$result = $this->save($data);
		}
		return $result;
	}
	protected function baseQuery() 
	{
		if( empty($this->baseQuery) && $this->parent->getData() ) 
		{
			$pk = $this->parent->getPk();
			$map[$this->morphKey] = $this->parent->$pk;
			$map[$this->morphType] = $this->type;
			$this->query->where($map);
			$this->baseQuery = true;
		}
	}
}
?>