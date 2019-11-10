<?php  namespace think\model\relation;
class MorphOne extends \think\model\Relation 
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
		$relationModel = $this->relation($subRelation)->find();
		if( $relationModel ) 
		{
			$relationModel->setParent(clone $this->parent);
		}
		return $relationModel;
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
			$data = $this->eagerlyMorphToOne(array( $morphKey => array( "in", $range ), $morphType => $type ), $relation, $subRelation, $closure);
			$attr = \think\Loader::parseName($relation);
			foreach( $resultSet as $result ) 
			{
				if( !isset($data[$result->$pk]) ) 
				{
					$relationModel = null;
				}
				else 
				{
					$relationModel = $data[$result->$pk];
					$relationModel->setParent(clone $result);
					$relationModel->isUpdate(true);
				}
				$result->setRelation($attr, $relationModel);
			}
		}
	}
	public function eagerlyResult(&$result, $relation, $subRelation, $closure) 
	{
		$pk = $result->getPk();
		if( isset($result->$pk) ) 
		{
			$pk = $result->$pk;
			$data = $this->eagerlyMorphToOne(array( $this->morphKey => $pk, $this->morphType => $this->type ), $relation, $subRelation, $closure);
			if( isset($data[$pk]) ) 
			{
				$relationModel = $data[$pk];
				$relationModel->setParent(clone $result);
				$relationModel->isUpdate(true);
			}
			else 
			{
				$relationModel = null;
			}
			$result->setRelation(\think\Loader::parseName($relation), $relationModel);
		}
	}
	protected function eagerlyMorphToOne($where, $relation, $subRelation = "", $closure = false) 
	{
		if( $closure ) 
		{
			call_user_func_array($closure, array( $this ));
		}
		$list = $this->query->where($where)->with($subRelation)->find();
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