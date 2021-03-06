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
	public $trunc = array();// путь от  корня каталога
	public $item_data = array();//данные по  найденному предмету каталога промежуточные
	public $category_data = array();//данные по найденной категории каталога промежуточные
	public $brand_scope_data = false;//данные бренда если работаем в режиме  брендапоиска
	public $other_scopes_found = false ;//если литенеры налли в SRCH_URI что-то свое то они сюда выставят true
	public $cleaned_srch_uri = '';//сюда листенеры пропишут очищеный от своих кусков srch_uri по которым искать категорию
	
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

		$parent = $this->get_args('parent',0);
		if($parent >0)
		{
			$data = $this->get_childs();
		}
		else
		{
			$data = $this->get_level(2);
		}

		return $this->parse_tmpl($template,$data);
	}

	public function get_childs()
	{
		$parent = $this->get_args('parent',0);
		$di =  data_interface::get_instance('m2_category');
		$data['records'] = $di->get_descendants($parent);
		return $data;
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
		if($this->args['with_manufacturers'])
		{
			foreach($data['records'] as $key=>$value)
			{
				$ids[] = $value['id'];
				$index[$value['id']] = $key;
			}
			$di = data_interface::get_instance('m2_category_manufacturers');
			$dt = $di->get_manufacturers_for_category_list_simple($ids);
			foreach($dt as $key=>$value)
			{
				if(!$data['records'][$index[$value->category_id]]['manufacturers'])
				{
					$data['records'][$index[$value->category_id]]['manufacturers'] = array();
				}
				$data['records'][$index[$value->category_id]]['manufacturers'][] = $value;
			}
		}
		if(preg_match('/brand.+/',SRCH_URI))// если у нас страница брендов на корню каталога отметим этот момент
		{
			$data['root_brand_page'] = 1;
		}

		$data['req'] = request::get();
		$data['records'] = $this->set_tree($data['records']);
		return $this->parse_tmpl($template,$data);

	}

	public	function pub_submenu_parametric()
	{
		$template = $this->get_args('template', 'submenu.html');
		$parent = $this->get_args('parent',1);
		$get_txts = $this->get_args('get_texts',false);
		$get_chars = $this->get_args('get_chars',false);
		$di = data_interface::get_instance('m2_category');
		$level = $this->get_args('level',0);
		$data = $di->get_level_down($parent,$level);
		$ids =  array();
		foreach($data['childs'] as $key=>$value)
		{	
			if($value['link_id']>0)
			{
				$ids[] = $value['link_id'];	
			}
			else
			{
				$ids[] = $value['id'];
			}
		}
		$di2 = data_interface::get_instance('m2_category_file');
		$di2->_flush();
		$di2->push_args(array('_sm2_category_id'=>$ids));
		$fls = $di2->extjs_grid_json(false,false);
		$data['files'] = $fls['records'];
		if($get_txts)
		{
			$di2 = data_interface::get_instance('m2_category_tabs');
			$di2->_flush();
			$di2->push_args(array('_sm2_category_id'=>$ids));
			$data2 = $di2->_get()->get_results();
			$data['texts'] = $data2;
		}
		if($get_chars)
		{
			$di2 = data_interface::get_instance('m2_category_chars');
			$di2->_flush();
			$di2->push_args(array('_sm2_id'=>$ids));
			$data2 = $di2->_get()->get_results();
			$data['chars'] = $data2;
		}
		return $this->parse_tmpl($template,$data);
	}

	public function pub_all()
	{
		$data =  array();
		$params = array();
		$di = data_interface::get_instance('m2_category');
		$parent = $this->get_args('parent','1');
		$hidden = $this->get_args('hidden',0);
		$template = $this->get_args('template','all.html');
		$di->set_args(array(
				'parent'=>$parent,
				'hidden'=>$hidden,
				));
		$with_manufacturers = $this->get_args('with_manufacturers','false');
		if($with_manufacturers == 'true')
		{
			$params['with_manufacturers'] = true;
		}
		if(!$this->data_all)
		{
			$this->data_all = $di->get_all_simple($params);
		}
		$data['records'] = $this->data_all['childs'];
		$path = array();
		foreach($this->trunc as $key=>$value)
		{
			$path[$value['id']] = 1;
			$path_seq[] = $value['id'];
		}
		$data['trunc'] = $path;
		$data['trunc_seq'] = $path_seq;
		$data['current_node'] = $this->trunc[count($this->trunc)-1]['id'];
		$data['args'] = $this->get_args();
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
		$from_level = $this->get_args('from_level', '');//2020-02-07 коммент позже отменен теперь субменю по дефолту от текущей ноды и неебет 
		//было ло последнегоизменения верхней строки  По дефолту выключено в случаях фиксировванного список подразделов начиная с определенного левлеа. Что бы список не менялся при перехде  в низлежаший.
		$di = data_interface::get_instance('m2_category');
		if(count($this->trunc)<1)
		{
			$this->trunc = $di->get_trunc_menu($this->category_id);
		}
		$trunc = $this->trunc; 
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
		if($this->category_id == 0)
		{
			$cat = 1;
		}else{
			$cat = $this->category_id;
		}
		$res = $di->get_level_down($root_node);
		$scope = $di->get_level_down($cat,100);
		if($cat > 0)
		{
			if($trunc_assoc[$cat]['link_id'] > 0)
			{
				$this->catalogue_scope[$trunc_assoc[$cat]['link_id']] = 1;
			}
			else
			{
				$this->catalogue_scope[$cat] = 1;
			}
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
				//if($value['level'] == 2 && $value['visible'] == 1) //было до 22112018
				if($value['level'] > 1 && $value['visible'] == 1)
				{
					$tmp[] = $value;
				}
			}
			$data['records'] = $tmp;
			$data['root_node'] = $res['childs'][0];
		}
		if($this->args['with_subdata'] != '')
		{
			$ids = array_keys($this->catalogue_scope);
			$di = data_interface::get_instance('m2_category_file');
			$r = $di->_flush()->set_args(array('_sm2_category_id'=>$ids))->_get()->get_results();
			//$res = $di->_flush()->_get()->get_results();
			foreach($r as $key=>$value){
				foreach($data['records'] as $key2=>$value2)
				{
					if($value2['id'] == $value->m2_category_id)
					{
						if(!$data['records'][$key2]['files'])
						{
							$data['records'][$key2]['files'] = array();
						}
						$data['records'][$key2]['files'][] = $value;
					}
				}
					
			}
		}
		$this->location_data = $data;
	}

	public function pub_locator()
	{
		$di = data_interface::get_instance('m2_url_indexer');
		$parts = explode('?',SRCH_URI); //режем куски потому что иногда на некоторых хостингах SRCH_URI содержит GET 

		$uri_parts = explode('/',substr(URI,1));
		// если в uri содержится стопворд /brand/ пытаепся установить какой брэн имелся ввиду
		if(strpos(SRCH_URI, 'brand/') !== false)
		{
			$stop_word_found = 0;
			foreach($uri_parts as $k=>$v)
			{
				if($stop_word_found == 1)
				{
					$this->args['brand_scope'] = 1;	
					$brand_name = $v;
					break;
				}
				if($v == 'brand')
				{
					$stop_word_found = 1;
				}
			}

		}
		if($this->args['brand_scope'])
		{
			$brand_id = $this->search_brand(array('name'=>$brand_name));
			if($brand_id)
			{
				$this->brand_id = $this->brand_scope_data->id; 
			}
			//return; // закомменчено потому, что брэнд может быть и в категории потому продолжаем копать что за категория например
		}
		// тут отрежем то что касается бренда от всего остального и по нему будем пытаться установить категорию или айтем
		if($this->args['brand_scope'])
		{
			$srch_uri_parts = explode("/brand/",SRCH_URI);
			$srch_uri = $srch_uri_parts[0].'/';
		}
		else
		{
			// тут если помимо бренда чтото еще по урлу мы скопим
			$this->fire_event('check_scopes', array());
			if($this->other_scopes_found == true)
			{
				$srch_uri = $this->cleaned_srch_uri;
			}
			else
			{
				$srch_uri = SRCH_URI;
			}
		}	
		$res = $di->search_by_uri('/'.$srch_uri);
		if($res['item_id']>0)
		{
			$this->location = 'item';
			$this->item_id = $res['item_id'];
			$this->category_id = $res['category_id'];
			$di_i = data_interface::get_instance('mf2_catalogue_list');
			$item = $di_i ->get_item($this->item_id);
			if($item->item_id >0)
			{
				$this->item_data = $item;
			}
		}

		if($res['item_id']==0 && $res['category_id'] >0)
		{
			$this->location = 'category';
			$this->category_id = $res['category_id'];
			$di = data_interface::get_instance('m2_category');
			$this->trunc = $di->get_trunc_menu($this->category_id);
			$this->category_data = $this->trunc[count($this->trunc) - 1];
		}

	}

	public function pub_current_category()
	{
		$template = $this->get_args('template', 'current_category.html');
		return $this->parse_tmpl($template, $this->category_data);
	}

	public function pub_current_item()
	{
		$template = $this->get_args('template', 'current_item.html');
		return $this->parse_tmpl($template, $this->item_data);
	}

	public function pub_trunc_menu()
	{
		$st = data_interface::get_instance('structure');
		$template = $this->get_args('template', 'trunc_menu.html');
		$data['records'] = $st->get_trunc_menu();
		//9* $this->trunc  заполняется раньше при запуске  метода  локатор в начале страницы
//		$catalog_root = $data['records'][count($data['records'])-1]['uri'];
		$catalog_root = '/catalog/';
		$site_root = $data['records'][0];
		if($this->category_id > 0 || $this->item_id > 0)
		{
			if($this->item_id>0)
			{
				$data['records'] = array();
				$data['records'][0] = $site_root;
			}
			foreach($this->trunc as $key=>$value)
			{
				if($value['id'] >1)
				{
					$value['uri'] = substr($catalog_root,0,-1).$value['uri'];
					$data['records'][] = $value;
				}
			}
		}
		if($this->item_id>0)
		{
			$di = data_interface::get_instance('m2_item_indexer');
			$di->_flush();
			$di->set_args(array('_sitem_id'=>$this->item_id));
			$di->what = array('title','name'=>'uri','category_list');
			$res = $di->_get()->get_results();
			if($res[0]->category_list != '')
			{
				$category_list = json_decode($res[0]->category_list);
				if(count($category_list)>0)
				{
					$di = data_interface::get_instance('m2_category');
					if($category_list[0]->category_id > 0)
					{
						$trunc = $di->get_trunc_menu($category_list[0]->category_id);
						foreach($trunc as $key=>$value)
						{
							if($value['id'] >1)
							{
								$value['uri'] = substr($catalog_root,0,-1).$value['uri'];
								$data['records'][] = $value;
							}
						}

					}
				}
			}
				$data['records'][] = array('title'=>$res[0]->title,'uri'=>'/catalog/items/'.$res[0]->uri.'/','hidden'=>0,'id'=>$res[0]->item_id);
		}
		if($this->brand_id >0)
		{
			$in = $this->brand_scope_data;
			$in->uri = URI;
			$data['records'][] = $this->brand_scope_data;
		}
		$data['args'] = $this->get_args();
		return $this->parse_tmpl('trunc_menu.html', $data);
	}

	public function search_brand($params = array())
	{
//		$name = str_replace('/','',SRCH_URI);
		$name = $params['name'];
		$di =  data_interface::get_instance('m2_manufacturers');
		$res = $di->search_by_name($name);
		if($res)
		{
			$this->brand_scope_data = $res;
			return $res->id;
		}
		else
		{
			return false;
		}
	}

	public function get_trunc()
	{
		return $this->trunc;
	}

	public function pub_brothers_menu()
	{
		if(!($this->category_id > 0))
		{
			return;
		}
		$c = $this->trunc[count($this->trunc)-1];
		if($c['level'] == 4)
		{
			$current = $this->trunc[count($this->trunc) - 2]['id'];
		}
		else if($c['level'] == 3)
		{
			$current = $this->trunc[count($this->trunc)- 2]['id'];
		}
		else if($c['level'] == 2)
		{
			$current = $this->trunc[count($this->trunc)-1]['id'];
		}
		if(!$this->data_all)
		{
			$di = data_interface::get_instance('m2_category');
			$data_r = $di->get_all_simple();
			$this->data_all = $data_r;
		}
		else
		{
			$data_r = $this->data_all;
		}
		foreach($this->data_all['childs'] as $key=>$value)
		{
			if($current == $value['id'])
			{
				$finded = $value;
				break;
			}
			else
			{
				if(count($value['childs']) > 0)
				{
					if($finded = $this->search_node($value['childs'],$current))
					{
						break;
					}
				}
			}
		}
		if($current == 1)
		{
			$finded = $this->data_all;
		}
		return $this->parse_tmpl('brothers.html',$finded);
	}

	public function search_node($c,$id)
	{
		foreach($c as $key=>$value)
		{
			if($value['id'] == $id)
			{
				$finded = $value;
				break;
			}
			else
			{
				if(count($value['childs']) > 0)
				{
					if($finded = $this->search_node($value['childs'],$id))
					{
						break;
					}
				}
			}
		}
		return $finded;
	}

	// Выдает список категорий для заданного id свойства товара которые входят в искомую категорию. То есть тупо найти категории в которых свойсто 123 имеется у входящий туда товаров.
	public function pub_categories_for_char()
	{
		$template = $this->get_args('template', 'cat_for_char.html');
		$type_id = $this->get_args('type_id',0);
		if($type_id == 0)
		{
			return;
		}
		$di = data_interface::get_instance('m2_chars_in_category');
		$data['records'] = $di->get_cats_for_char($type_id);
//		dbg::show($data);
		return $this->parse_tmpl($template,$data);
	}

	// Сканирует линейную выборку нод из дерева разделов запихивая чайлдов к парентам
	public function set_tree($data)
	{
		foreach($data as $k=>$v)
		{
			$tmp = array();
			foreach($data as $k2=>$v2)
			{
				if($v2['left']>$v['left'] && $v2['right'] < $v['right'] && $v2['level'] == ($v['level'] + 1))
				{
					$tmp[] = $v2;
				}
			}
			$data[$k]['childs'] = $tmp;
		}
		return $data;
	}

}
?>
