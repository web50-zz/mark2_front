<?php
/**
*
* @author	Fedot B Pozdnyakov 9@u9.ru 10052014	
* @package	SBIN Diesel
*/
class ui_mf2_catalogue_search extends user_interface
{
	public $title = 'mf2: Маркет 2  Фронт -  поиск';
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/';
	}


// Форма поиска  
	public function pub_form()
	{
		$template = $this->get_args('template', 'search.html');
		$data['search'] = request::get('search');
		return $this->parse_tmpl($template,$data);
	}


	public function pub_content()
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

}
?>
