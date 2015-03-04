<?php
/**
*
* @author	Fedot B Pozdnyakov 9@u9.ru 04032015	
* @package	SBIN Diesel
*/
class ui_mf2_catalogue_brand_list extends user_interface
{
	public $title = 'mf2: Маркет 2  Фронт -  списки брэндов для разных целей';
	
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
		$sort = $this->get_args('sort','title');
		$dir = $this->get_args('dir','asc');
		$template = $this->get_args('template', 'default.html');
		$di = data_interface::get_instance('m2_manufacturers');
		$di->_flush();
		$di->push_args(array('scope'=>$scope,'sort'=>$sort,'dir'=>$dir,'not_available'=>0));
		$data = $di->extjs_grid_json(false,false);
		$di->pop_args();
		return $this->parse_tmpl($template,$data);
	}
}
?>
