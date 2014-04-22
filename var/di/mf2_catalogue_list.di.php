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



	public function get_list()
	{
		$args = array();
		$scope = $this->get_args('scope');
		$this->_flush();
//		$this->where = " MATCH (`category_list`) AGAINST ('".'"(:135)"'."' IN BOOLEAN MODE)>0 ";
		foreach($scope as $key=>$value)
		{
			$scope_where[] = " `category_list` like '%\"category_id\":\"".$key."\",%' ";
		}
		$sw = implode('OR',$scope_where);
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

//9* оверлоадим метод чтобы от парентов листенореов не  приходило
	public function _listeners()
	{
		return array();
	}
}
?>
