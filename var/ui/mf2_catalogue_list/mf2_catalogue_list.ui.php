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
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/';
	}
//9* просто список с автоматическим определением  категории по URL
	public function pub_content()
	{
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
		if(SRCH_URI != '' && !($category_id >0))
		{
			$st = user_interface::get_instance('structure');
			$st->do_404();
		}
		$scope = $ui->get_scope();
		$di = data_interface::get_instance('mf2_catalogue_list');
		$params = $this->prepare_input();
		$params['scope'] = $scope;
		$di->set_args($params);
		if($params['search'] != '')
		{
			$search_mode = true;
		}
		$res = $di->get_list($search_mode);
		$data['records'] = $res['records'];
		$data['basket'] = $_SESSION['mf2_cart'];
		$data['filters'] = $params['return_to_tmpl'];

		$title =  $trunc[count($trunc) -1]['title'].'  '.$trunc[count($trunc) -1]['meta_title'];
		$st = user_interface::get_instance('structure');
		$st->add_title($title);


		$di = data_interface::get_instance('m2_category_tabs');
		if($category_texts)
		{
			$data['current_node_texts'] = $di->get_text_for($data['current_node']['id']);
		}
		$data['current_node'] = $ui->location_data['current_node'];
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
		$data['args'] = $args;
		return $this->parse_tmpl($template,$data);
	}

	public function prepare_input()
	{
		$possible = array(
			'sort'=>array(
					'1'=>'order',
					'2'=>'price',
					'3'=>'price',
				),
			'dir'=>array(
					'1'=>'asc',
					'2'=>'asc',
					'3'=>'desc',
				),
			'limit'=>array(
					'1'=>'12',
					'2'=>'24',
					'3'=>'48',
			),
		);

		$search = request::get('search');
		$cat = request::get('cat');

		$sort_saved = session::get('sort','1',$this->name);
		$limit_saved = session::get('limit','1',$this->name);

		$sort = request::get('sort',0);
		$limit = request::get('limit',0);
		$page = request::get('page', 1);
		$pstart = request::get('pstart', 1);
		$pend = request::get('pend', 70000);
		if($pstart == 0)
		{
			$pstart = 1;
		}
		if($sort_saved != $sort && $sort > 0)
		{
			session::set('sort',$sort,$this->name);
		}
		if($limit_saved != $limit && $limit > 0)
		{
			session::set('limit',$limit,$this->name);
		}
		if($sort_saved != '' && $sort == 0)
		{
			$sort = $sort_saved;
		}
		if($limit_saved != '' && $limit == 0)
		{
			$limit = $limit_saved;
		}
		$params['pstart'] = $pstart;
		$params['pend'] = $pend;
		$params['price_type'] = '7';
		$params['sort'] = $possible['sort'][$sort];
		$params['dir'] = $possible['dir'][$sort];
		$params['limit'] = $possible['limit'][$limit];
		$params['return_to_tmpl'] = array('sort'=>$sort,'limit'=>$limit,'pstart'=>$pstart,'pend'=>$pend);
		if($params['sort'] == '')
		{
			$params['sort'] = 'order';
			$params['dir'] = 'ASC';
		}

		if($params['sort'] == 'price')
		{
			$params['price_type'] = '7';
		}
		$params['start'] = ($page - 1) * $params['limit'];
		$params['page'] = $page;
		if($search != '')
		{
			$params['search'] = $search;
		}
		if($cat != '')
		{
			$params['cat'] = $cat;
		}

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
		$di->set_args(array('scope'=>$scope,'sort'=>$sort,'dir'=>$dir));
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
		$template = $this->get_args('template', 'filter.html');
		return $this->parse_tmpl($template,$data);
	}
}
?>
