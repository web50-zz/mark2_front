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
		$args = array();
		$sw = '';
		$scope = $this->get_args('scope');
		$this->_flush();
//		$this->where = " MATCH (`category_list`) AGAINST ('".'"(:135)"'."' IN BOOLEAN MODE)>0 ";
		if($search_mode == true)
		{
			$search = $this->get_args('search');
			if($search == '')
			{
				return array();
			}
			$where[] = " `title` like '%$search%' ";
			$where[] = " `article` like '%$search%' ";
			$where[] = " `text_list` like '%$search%' ";
			$sw = implode('OR',$where);
		}
		else
		{
			foreach($scope as $key=>$value)
			{
				$scope_where[] = " `category_list` like '%\"category_id\":\"".$key."\",%' ";
			}
			$sw = implode('OR',$scope_where);
		}
		$this->where = $sw;
		$this->push_args($args);
		$res = $this->extjs_grid_json(false,false);
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
