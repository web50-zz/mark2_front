<?php
/**
*
* @author	Fedot B Pozdnyakov 9@u9.ru 25072018	
* @package	SBIN Diesel
*/
class ui_mf2_category_tree extends user_interface
{
	public $title = 'mf2: Маркет 2  Фронт - категории по уровням';
/* Выводим списко категорий томбами по выбранному паренту */	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/';
	}

	public function pub_content()
	{
		$data = array();
		// Шаблон
		$args = $this->get_args();
		$sort = $this->get_args('sort','title');
		$dir = $this->get_args('dir','asc');
		$template = $this->get_args('template', 'default.html');
		$di = data_interface::get_instance('m2_url_indexer');
		$res = $di->search_by_uri('/'.SRCH_URI);
		if($res['item_id']==0 && $res['category_id'] >0)
		{
			$this->location = 'category';
			$this->category_id = $res['category_id'];
			$di = data_interface::get_instance('m2_category');
			$this->trunc = $di->get_trunc_menu($this->category_id);
			$this->category_data = $this->trunc[count($this->trunc) - 1];
		}

		$parent = $this->category_id;
		if($parent >0)
		{
			$data = $this->get_childs($parent);
		}
		else
		{
			$data = $this->get_childs(1);
		}
		$data['current'] = $this->category_data;
		$data['PAGE_ID'] = PAGE_ID;
		$data['req'] = request::get();
		return $this->parse_tmpl($template,$data);
	}

	public function get_childs($parent = 1)
	{
		$di =  data_interface::get_instance('m2_category');
		$di->set_args(array('parent'=>$parent));
		$d = $di->get_all_simple();
		$data['records'] = $d['childs'];

		foreach($data['records'] as $key=>$value)
		{
			$ids[] = $value['id'];
			$data['records'][$key]['files'] = array();
		}
		$di =  data_interface::get_instance('m2_category_file');
		$di->_flush()->set_args(array('_sm2_category_id'=>$ids));
		$res = $di->_get()->get_results();
		foreach($data['records'] as $key=>$value)
		{
			foreach($res as $k=>$v)
			{
				if($v->m2_category_id == $value['id'])
				{
					$data['records'][$key]['files'][]= $v;
				}
			}
		}
		return $data;
	}

}
?>
