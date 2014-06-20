<?php
/**
*
* @author	Fedot B Pozdnyakov 9@u9.ru 18022014	
* @package	SBIN Diesel
*/
class ui_mf2_catalogue_list extends user_interface
{
	public $title = 'mf2: Маркет 2  Фронт -  списки товаров';
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
		$data = array();
		// Шаблон
		$args = $this->get_args();
		$template = $this->get_args('template', 'default.html');
		$ui = user_interface::get_instance('mf2_catalogue_nav');
		$scope = $ui->get_scope();
		$item_id = $ui->item_id;
		$di = data_interface::get_instance('mf2_catalogue_list');
		$di->push_args(array('scope'=>$scope));
		$data['records'] = $di->get_list();
		$data['basket'] = $_SESSION['mf2_cart'];
		$di->pop_args();
		return $this->parse_tmpl($template,$data);
	}
//9*  списко реагирующий на ходяий параметр search  для поиска по каталогу
	public function pub_search()
	{
		$template = $this->get_args('template', 'default.html');
		$di = data_interface::get_instance('mf2_catalogue_list');
		$search = request::get('search');
		$cat = request::get('cat');
		$di->push_args(array('search'=>$search,'cat'=>$cat));
		$data['records'] = $di->get_list(true);
		$data['basket'] = $_SESSION['mf2_cart'];
		$di->pop_args();
		return $this->parse_tmpl($template,$data);
	}

}
?>
