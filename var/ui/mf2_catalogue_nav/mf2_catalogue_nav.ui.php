<?php
/**
*
* @author	Fedot B Pozdnyakov 9@u9.ru 02022014	
* @package	SBIN Diesel
*/
class ui_mf2_catalogue_nav extends user_interface
{
	public $title = 'mf2: Маркет 2  Фронт -  категории навигация';
	public $location = false;
	public $location_data = false;
	public $catalogue_scope = array();// тут все ид разделов  каталога по которым  мы  работаем в текущем запросе
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/';
	}

	public function pub_content()
	{
		$data = array();
		// Шаблон
		$template = $this->get_args('template', 'default.html');

		// Родитель (по умолчанию родителем является корневая нода - Home)
	//	$parent = $this->get_args('parent', 1);
		// Глубина вложенности (если передаётся NULL, то до бесконечности)
	//	$deep = $this->get_args('deep', null);

		$data = $this->get_level(2);
		return $this->parse_tmpl($template,$data);
	}

	public function get_level($level = 2)
	{
		$di = data_interface::get_instance('m2_category');
		$di->_flush();
		$di->push_args(array('_svisible'=>'1','_slevel'=>$level));
		$di->set_order('left','ASC');
		$res = $di->extjs_grid_json(false,false);
		$di->pop_args();
		return $res;
	}

	public function pub_submenu()
	{
		$template = $this->get_args('template', 'submenu.html');
		if($this->location_data == false)
		{
			$this->pub_collect_scope_data();
		}
		$data = $this->location_data;
		return $this->parse_tmpl($template,$data);

	}
	
	public function get_scope()
	{
		if($this->location_data == false)
		{
			$this->pub_collect_scope_data();
		}
		return $this->catalogue_scope;
	}
	public function  pub_collect_scope_data()
	{
		$from_level = $this->get_args('from_level', '1');// По дефолту выключено в случаях фиксировванного список подразделов начиная с определенного левлеа. Что бы список не менялся при перехде  в низлежаший.
		$di = data_interface::get_instance('m2_category');
		$trunc = $di->get_trunc_menu($this->category_id);
		foreach($trunc as $key=>$value)
		{
			$trunc_assoc[$value['id']] = $value;
			if($value['level'] == $from_level)
			{
				$mod_root_node = $value['id'];
			}
		}

		if($from_level > 0)
		{
			$root_node = $mod_root_node;
		}
		else{
			$root_node = $this->category_id;
		}
		$res = $di->get_level_down($root_node);
		$scope = $di->get_level_down($this->category_id,100);
		if($trunc_assoc[$this->category_id]['link_id'] > 0)
		{
			$this->catalogue_scope[$trunc_assoc[$this->category_id]['link_id']] = 1;
		}
		else
		{
			$this->catalogue_scope[$this->category_id] = 1;
		}
		foreach($scope['childs'] as $key =>$value)
		{	
			if($value['link_id']>0)
			{
				$this->catalogue_scope[$value['link_id']] = 1;
			}
			else
			{
				$this->catalogue_scope[$value['id']] = 1;
			}
		}
		$data['records'] = $res['childs'];
		$data['root_node'] = $trunc_assoc[$root_node];
		$data['current_node'] = $trunc_assoc[$this->category_id];
		if(!($data['current_node']['id'] >0))
		{
			$data['current_node'] = $res['childs'][0];
			$tmp = array();
			foreach($res['childs'] as $key=>$value)
			{
				if($value['level'] == 2 && $value['visible'] == 1)
				{
					$tmp[] = $value;
				}
			}
			$data['records'] = $tmp;
			$data['root_node'] = $res['childs'][0];
		}
		$this->location_data = $data;
	}

	public function pub_locator()
	{
		$di = data_interface::get_instance('m2_url_indexer');
		$res = $di->search_by_uri('/'.SRCH_URI);
		if($res['item_id']>0)
		{
			$this->location = 'item';
			$this->item_id = $res['item_id'];
			$this->category_id = $res['category_id'];
		}

		if($res['item_id']==0 && $res['category_id'] >0)
		{
			$this->location = 'category';
			$this->category_id = $res['category_id'];
		}
	}
}
?>
