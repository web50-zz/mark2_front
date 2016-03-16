<?php
/**
*
* @author	9@u9.ru 19022014	
* @package	SBIN Diesel
*/
class di_mf2_catalogue_list extends di_m2_item_indexer
{
	public $title = 'mf2: Item list';



	public function __construct () {
		// Call Base Constructor
		parent::__construct(__CLASS__);
	}



	public function get_list($search_mode = false)
	{
		$args = $this->get_args();
		$sw = '';
		$scope = $this->get_args('scope');
		$this->_flush();
		$this->push_args($args);
//		$this->where = " MATCH (`category_list`) AGAINST ('".'"(:135)"'."' IN BOOLEAN MODE)>0 ";
		$dj = $this->join_with_di('m2_item',array('item_id'=>'id'),array('order'=>'order'));
		if($search_mode == true)
		{
			$search = $this->get_args('search');
			$cat = $this->get_args('cat');
			
			if($search == '')
			{
				return array();
			}
			
			$where[] = ' '.$this->get_alias().".`title` like '%$search%' ";
			$where[] = ' '.$this->get_alias().".`article` like '%$search%' ";
			$where[] = ' '.$this->get_alias().".`text_list` like '%$search%' ";
			$sw = implode('OR',$where);
			if($cat >0)
			{
				$di = data_interface::get_instance('m2_category');
				$childs = $di->get_descendants($cat);
				$ids = array();
				$ids[] = $cat;
				foreach($childs as $key=>$value)
				{
					$ids[] = $value['id'];
				}
				$sw2 = array();
				foreach($ids as $key=>$value)
				{
					$sw2[] = " `category_list` like '%\"category_id\":\"".$value."\",%' ";
				}
				$sw3 = implode('OR',$sw2);
				$sw = "($sw) AND ($sw3)";
			}
		}
		else
		{
			foreach($scope as $key=>$value)
			{
				$scope_where[] = " `category_list` like '%\"category_id\":\"".$key."\",%' ";
			}
			$sw = '('.implode('OR',$scope_where).')';
			if($args['conditions'] != '')
			{
				$sw .= " AND ".$args['conditions'];
			}
		}
		$this->where = $sw;
		$this->set_order($dj->get_alias().'.'.$args['sort'],$args['dir']);
		$flds = array(
			'id',
			'title',
			'name',
			'article',
			'not_available',
			'files_list',
			'text_list',
			'prices_list',
			'manufacturers_list',
			'chars_list',
			'category_list',
			'last_changed',
			'meta_title',
			array('di'=>$dj,'name'=>'order')

		);
		$res = $this->extjs_grid_json($flds,false);
		$this->pop_args();
		return $res['records'];
	}


	public function get_item($item_id = 0)
	{
		$args = array();
		$this->_flush();
		$args['_sitem_id'] = $item_id;
		$this->push_args($args);
		$res = $this->_get()->get_results(0);
		$this->pop_args();
		return $res;

	}

	public function collect_data($eObj, $ids)
	{
		if (count($ids) > 0)
		{
			$this->_flush();
			$this->what = array('*');
			$this->push_args(array('_sitem_id' => $ids));
			$this->connector->fetchMethod = PDO::FETCH_ASSOC;
			$this->_get();
			$this->pop_args();
			$data= $this->get_results();
			$eObj->add_data($data);
		}
	}


	public function _listeners()
	{
		return array(
			array('di' => 'mf2_cart', 'event' => 'collect_data', 'handler' => 'collect_data'),
		);
	}

}
?>
