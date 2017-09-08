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


/*
	sort:  поле по которому сортировать  default id
	dir: направление   default ASC
	car: Id категории в которой и в одкатегории которой искать
	price_floor: нижний предел по цене
	price_ceil: верхний предел по цене
	price_type: тип цены по которому сортировать или отбирать
	limit: показывать на странице
	start: стартовая позиция лимита

*/
	public function get_list($search_mode = false)
	{
		$args = $this->get_args();
		$sw = '';
		$scope = $this->get_args('scope');
//		$this->where = " MATCH (`category_list`) AGAINST ('".'"(:135)"'."' IN BOOLEAN MODE)>0 ";
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
			$sw = '('.implode('OR',$where).')';
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
		$mans = $this->get_args('mans');
		if($mans)
		{
			$mans = json_decode($mans);
			$tmp = array();
			foreach($mans  as $key=>$value)
			{
				$tmp[] =  " `manufacturers_list` like '%\"manufacturer_id\":\"".$value."\"%'";

			}
			$sw = '('.implode(' OR ',$tmp).')';
		}
		$sw .= ' AND '.$this->get_alias().'.`not_available` = 0 ';


		if(count($args['brand_scope_ids']) >0)
		{
			$tmp = array();
			foreach($args['brand_scope_ids']  as $key=>$value)
			{
				$tmp[] =  $this->get_alias().".`manufacturers_list` like '%\"manufacturer_id\":\"".$value."\"%'";

			}
			$sw .= ' AND ('.implode(' OR ',$tmp).')';
		}


		$flds = array(
			'id',
			'title',
			'name',
			'item_id',
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
		);
		$this->_flush();
		$this->push_args(array());// нам не надо чтобы напрямую параметры залетали
		$this->set_limit($args['start'],$args['limit']);
		if($args['sort'] == 'order')
		{
			$dj = $this->join_with_di('m2_item',array('item_id'=>'id'),array('order'=>'order'));
			$flds[] = array('di'=>$dj,'name'=>'order');
			$this->set_order($args['sort'],$args['dir'],$dj);
		}
		if($args['sort'] == 'title')
		{
			$this->set_order($args['sort'],$args['dir'],$dj);
		}

		if($args['sort'] == 'price' || $args['pstart']|| $args['pend'])
		{
			$dj2 = $this->join_with_di('m2_item_price',array('item_id'=>'item_id'),array('price_value'=>'price_value','type'=>'price_type'));
			$flds[] = array('di'=>$dj2,'name'=>'price_value');
			$flds[] = array('di'=>$dj2,'name'=>'type');
			$a = $dj2->get_alias();
			if($args['sort'] == 'price')
			{
				$this->set_order('price_value',$args['dir'],$dj2);
			}
			$t1 = ' '.$a.'.`type` = '.$args['price_type'].' ';
			if($args['pstart'] && $args['pend'])
			{
				$t2 .= " and ($a.`price_value`<".$args['pend']." and $a.`price_value` >".$args['pstart'].') ';
			}
			$sw .= " and ($t1 $t2) ";
		}
		$this->where = $sw;
		$this->fire_event('conditions_done', array());
		$res = $this->extjs_grid_json($flds,false);
		$this->pop_args();
		return $res;
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
