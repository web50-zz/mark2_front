<?php
/**
*
* @author	Fedot B Pozdnyakov <9@u9.ru>  2018-07-09
* @package	SBIN Diesel
*/
class di_mf2_catalogue_filters extends data_interface
{
	public $title = 'mf2 фильтры';
	/**
	* @var	string	$cfg	Имя конфигурации БД
	*/
	protected $cfg = 'localhost';
	
	/**
	* @var	string	$db	Имя БД
	*/
	protected $db = 'db1';
	
	/**
	* @var	string	$name	Имя таблицы
	*/
	protected $name = '';

	protected $ids = array();	
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
	);
	protected $data = array();
	protected $parts = array();
	public $conditions = array();
	protected $current_done = false;
	protected $others_done = false;
	protected $current_ids = array();

	public function __construct () {
		// Call Base Constructor
		parent::__construct(__CLASS__);
	}
/*
	нужен для дергнаья sql через get для всяких нужд фильтров
*/

	public function prepare_current_ids($Obj)
	{
		$d = $Obj->connector->get_sql_parts();
		$this->parts = $d;
		$sql = "SELECT m2_item_indexer.item_id, m2_item_indexer.id  {$d['_from']} {$d['_where']} {$d['_group']} {$d['_having']}";
		$this->_flush();
		$data = $this->_get($sql)->get_results();
		foreach($data as $key=>$value)
		{
			$this->current_ids[] = $value->item_id;
		}
		$this->current_done = true;
		return $this->current_ids;
	}

	public function get_parts()
	{
		return $this->parts;
	}

	public function get_ids()
	{
		return $this->ids;
	}

	public function get_others($filter)
	{
		$a = $this->conditions;
		unset($a[$filter]);
		return $a;
	}

	public function get_current_ids()
	{
		return $this->current_ids;
	}
	public function get_others_ids($filter)
	{
	/*
		if($this->other_done == true)
		{
			return $this->other_ids;
		}
	*/
		$other_ids = array();
		$a = $this->conditions;
		unset($a[$filter]);
		foreach($a as $k=>$v)
		{
			if($v['include'] == true)
			{
				$inc[$k] = $v['condition'];
			}
			else
			{
				$opp[$k] = $v['condition'];
			}
		}
		$cond = implode('and',$opp);
		$cond2 = implode('and',$inc);
		if(strlen($cond)>0)
		{
			$cond = " and $cond ";
		}
		if(strlen($cond2)>0)
		{
			$cond .=" $cond2 ";
		}
		$d = $this->parts;
		$di = data_interface::get_instance('mf2_catalogue_list');
		$where = $di->get_where(true);
		$sql = "SELECT m2_item_indexer.item_id, m2_item_indexer.id  {$d['_from']} where {$where} {$cond} ";
		$res = $this->_get($sql)->get_results();
		foreach($res as $k=>$v)
		{
			$other_ids[] = $v->item_id;
		}
		$this->other_done = true;
		return $other_ids;
	}
}
?>
