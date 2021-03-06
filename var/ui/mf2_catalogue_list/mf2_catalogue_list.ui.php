<?php
/**
*
* @author	Fedot B Pozdnyakov 9@u9.ru 18022014	
* @package	SBIN Diesel
*/
class ui_mf2_catalogue_list extends user_interface
{
	public $title = 'mf2: Маркет 2  Фронт -  списки товаров';
	protected $name = 'mf2_catalogue_list';
	public $location = false;
	public $location_data = false;
	public $catalogue_scope = array();// тут все ид разделов  каталога по которым  мы  работаем в текущем запросе
	public $params = array(); //тут параметры запроса по prepare_params  
	public $data_buffer = array(); //сюда складываем чтото из внешних уи ди для парсинга в шаблон

	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/';
	}
//9* просто список с автоматическим определением  категории по URL
	public function pub_content()
	{
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

		$grid_mode_changed = request::get('gridMode',false);
		if($grid_mode_changed)
		{
			session::set('grid_mode',$grid_mode_changed,$this->name);
			response::send(array('code'=>'200'),'json');
		}


		$data = array();
		$search_mode = false;
		$args = $this->get_args();

		$template = $this->get_args('template', 'default.html');
		$category_texts = $this->get_args('category_texts',false); // true  если надо выдернуть тексты от текущей категории

		$ui = user_interface::get_instance('mf2_catalogue_nav');
		$category_id = $ui->category_id;
		$trunc = $ui->trunc;
		$location_type = $ui->location;
		if($this->args['item_detect'] == 'true')// если это включено  то будет детектится и  айтем по полному URL от категории в которую входит. Хотя обычно это делается через /item/{__name__}
		{
			if($location_type == 'item')
			{
				$ui = user_interface::get_instance('mf2_catalogue_item');
				$st = user_interface::get_instance('structure');
				$st->collect_resources($ui,'mf2_catalogue_item');
				return $ui->pub_content();
			}
		}
		if(!$this->args['brand_scope'])
		{
			if($location_type != 'category' && $this->args['lock_global_list'] == 'true')
			{
				if(request::get('search','') == '')
				{
					//Если нет параметров на вход и приэтом не определена кактегория мы не выводим ничего ибо выборка может быть велика по всему каталогу
		//			$st = user_interface::get_instance('structure');
		//			$st->do_404();//пока не ясно что с этим делать
				}
			}
		}
		$title =  $trunc[count($trunc) -1]['title'];
		if($trunc[count($trunc) -1]['meta_title'] != '')
		{
			$title =  $trunc[count($trunc) -1]['meta_title'];
		}
		$st = user_interface::get_instance('structure');
		$st->add_title($title);
		if($ignore_category = $this->get_args('ignore_category'))// входной параметр ignore_category - массив со списком категорий для которых не надо выводить листинг товаров
		{
			foreach($ignore_category as $key=>$value)
			{
				if($value == $category_id)
				{
					return '';
				}
			}
		}
		$scope = $ui->get_scope();
		$di = data_interface::get_instance('mf2_catalogue_list');
		$params = $this->prepare_input();

		if($params['brand_scope'])
		{
			if(!($ui->brand_id>0))
			{
				$brand_id = $this->search_brand(array('name'=>$brand_name));
			}
			else{
				$brand_id = $ui->brand_id;
				$this->brand_scope_data = $ui->brand_scope_data;
			}
			if($brand_id >0)
			{
				$params['brand_scope_ids'][] = $brand_id;
			}
			else
			{
				if(SRCH_URI != '')
				{
					$st = user_interface::get_instance('structure');
					$st->do_404();
				}
				else
				{	
					$uib = user_interface::get_instance('mf2_catalogue_brand_list');
					$uib->set_args(array('template'=>'all_brands.html'));
					return $uib -> pub_content();
				}
			}

		}
		if(SRCH_URI != '' && !($category_id >0)&& !$params['brand_scope'])
		{
			$st = user_interface::get_instance('structure');
			$st->do_404();
		}

		$params['scope'] = $scope;
		$di->set_args($params);
		if($params['search'] != '')
		{
			$search_mode = true;
		}
		$res = $di->get_list($search_mode);
		$this->fire_event('data_ready', array($di));
		$data['records'] = $res['records'];
		$data['basket'] = $_SESSION['mf2_cart'];
		$data['filters'] = $params['return_to_tmpl'];
		if($params['brand_scope'])
		{
			$st->add_title($this->brand_scope_data->title);
		}


		$data['current_node'] = $ui->location_data['current_node'];
		if($params['brand_scope'])
		{
			$data['current_node']['title'] = $this->brand_scope_data->title;
		}

		$data['grid_mode'] = session::get('grid_mode','',$this->name);
		$enable_pager = true;
		if($enable_pager == true)
		{
			$par = explode('&',$_SERVER['QUERY_STRING']);
			foreach($par as $key=>$value)
			{
				$line = explode('=',$value);
				if($line[0] == 'page')
				{
					unset($par[$key]);
				}
			}
			$query =  implode('&',$par);
			$data['custom_pager'] = array('page' => $params['page'], 'total' => $res['total'], 'limit' => $params['limit'], 'prefix' => $query);
		}
		//СОбираем данные по текущей категории
		if($category_texts)
		{
			$di = data_interface::get_instance('m2_category_tabs');
			$sql = 'select * from m2_category_tabs where m2_category_id = '.$data['current_node']['id'];
			$data['current_node_texts'] = $di->_get($sql)->get_results();
		}
		$data['args'] = $args;
		$data['req'] = request::get();
		$this->fire_event('before_parse', array($this));
		$data = array_merge($data,$this->data_buffer);
		return $this->parse_tmpl($template,$data);
	}

	public function prepare_input()
	{
		$possible_limits  = $this->get_args('possible_limits',array( '1'=>'12', '2'=>'24', '3'=>'48',));// это можно задать через аргументы   "possible_limits":[0,50,100]
		$default_sort = $this->get_args('default_sort',0);  // чeрез аргументы "default_sort":"4" 
		$possible = array(
			'sort'=>array(
					'1'=>'order',
					'2'=>'price',
					'3'=>'price',
					'4'=>'title',
				),
			'dir'=>array(
					'1'=>'asc',
					'2'=>'asc',
					'3'=>'desc',
					'4'=>'asc',
				),
			'limit'=>$possible_limits,
			);
		$search = request::get('search');
		$cat = request::get('cat');

		$sort_saved = session::get('sort','',$this->name);
		$limit_saved = session::get('limit','2',$this->name);
		$sort = request::get('sort',0);
		$limit = request::get('limit');
		$page = request::get('page', 1);
		$mans = request::get('mans',0);
		if($sort_saved != '' && $sort == 0)
		{
			$sort = $sort_saved;
		}
		if($sort_saved != $sort && $sort > 0)
		{
			session::set('sort',$sort,$this->name);
		}
		if($sort_saved == '' && $sort == 0)
		{
			$sort = $default_sort;
			session::set('sort',$default_sort,$this->name);
		}
		if(($limit>0) && $limit_saved != $limit) // если что-то пришло на вход и это не похоже на ранее сохраненное то сохраняем это в сессию
		{
			session::set('limit',$limit,$this->name);
		}
		if($limit_saved != '' && $limit == '')// если на вход ничего нет но было что-то сохранено то выставляем сохраненное
		{
			$limit = $limit_saved;
		}
		$params['sort'] = $possible['sort'][$sort];
		$params['dir'] = $possible['dir'][$sort];
		$params['limit'] = $possible['limit'][$limit];
		$params['return_to_tmpl'] = array('sort'=>$sort,'limit'=>$limit,'pstart'=>$pstart,'pend'=>$pend);
		if($mans)
		{
			$t = json_decode($mans);
			foreach($t as $key=>$value)
			{
				$t[$value] = 1;
				$params['return_to_tmpl']['mans'][$value] = 1;
			}
		}
		if($params['sort'] == '')
		{
			$params['sort'] = 'order';
			$params['dir'] = 'ASC';
		}

		if($params['sort'] == 'price')
		{
			$params['price_type'] = registry::get('MAIN_PRICE_TYPE');
		}
		$params['start'] = ($page - 1) * $params['limit'];
		$params['page'] = $page;
		$brand_scope  =  $this->get_args('brand_scope');
		if($brand_scope != '')
		{
			$params['brand_scope'] = $brand_scope;
		}

		if($search != '')
		{
			$params['search'] = $search;
		}
		if($cat != '')
		{
			$params['cat'] = $cat;
		}
		if($mans)
		{
			$params['mans'] = $mans;
		}
		$this->params = $params;
		$this->fire_event('params_ready', array($params));
		return $params;
	}
	public function pub_parametric()
	{
		$template = $this->get_args('template', 'default.html');
		$data = array();
		// Шаблон
		$args = $this->get_args();
		$sort = $this->get_args('sort','id');
		$dir = $this->get_args('dir','asc');
		$template = $this->get_args('template', 'default.html');
		$category = $this->get_args('category', '1');
		$scope[$category] = 1;
		$di = data_interface::get_instance('mf2_catalogue_list');
		$di->set_args(array('scope'=>$scope,'sort'=>$sort,'dir'=>$dir,'no_conditions_done' => 1));
		$res =  $di->get_list();
		$data['records'] = $res['records'];
		$data['args'] = $args;
		$data['basket'] = $_SESSION['mf2_cart'];
		$di->pop_args();
		return $this->parse_tmpl($template,$data);

	}
//9*  списко реагирующий на ходяий параметр search  для поиска по каталогу
	public function pub_search()
	{
		$template = $this->get_args('template', 'default.html');
		$di = data_interface::get_instance('mf2_catalogue_list');
		$sort = $this->get_args('sort','id');
		$dir = $this->get_args('dir','asc');
		$search = request::get('search');
		$cat = request::get('cat');
		$di->push_args(array('search'=>$search,'cat'=>$cat,'sort'=>$sort,'dir'=>$dir));
		$data['records'] = $di->get_list(true);
		$data['basket'] = $_SESSION['mf2_cart'];
		$di->pop_args();
		return $this->parse_tmpl($template,$data);
	}

	public function pub_filter()
	{
		$data = array();
		$params = $this->prepare_input();
		$data = $params['return_to_tmpl'];
		$ui = user_interface::get_instance('mf2_catalogue_nav');
		$category_id = $ui->category_id;
		if($category_id>0)
		{
			$di = data_interface::get_instance('m2_category_manufacturers');
			$data['manufacturers'] = $di->get_manufacturers_for_category();
		}
		$brand_id = $ui->brand_id;
		if($brand_id > 0)
		{
			$data['categories'] = $this->get_categories_for_brand($brand_id);
		}
		$template = $this->get_args('template', 'filter.html');
		return $this->parse_tmpl($template,$data);
	}

	public function search_brand($params = array())
	{
		$name = str_replace('/','',SRCH_URI);
		$di =  data_interface::get_instance('m2_manufacturers');
		$res = $di->search_by_name($name);
		$this->brand_scope_title = $res->title;
		if($res)
		{
			return $res->id;
		}
		else
		{
			return false;
		}
	}

	public function get_categories_for_brand($brand_id = 0)
	{
		if($brand_id == 0)
		{
			return false;
		}
		$sql = "select * from m2_category_manufacturers where manufacturer_id = $brand_id group by category_id";
		$di = data_interface::get_instance('m2_category_manufacturers');
		$data = $di->_get($sql)->get_results();
		foreach($data as $key=>$value)
		{
			$ids[] = $value->category_id;
		}
		if(count($ids)>0)
		{
			$di =  data_interface::get_instance('m2_category');
			$sql = 'select * from m2_category where id in('.implode(',',$ids).')';
			$data2 = $di->_get($sql)->get_results();
			return $data2;
		}
		return false;
	}
}
?>
