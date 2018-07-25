<?php
/**
*
* @author	Fedot B Pozdnyakov 9@u9.ru 07092017	
* @package	SBIN Diesel
*/
class ui_mf2_catalogue_filters extends user_interface
{
	public $title = 'mf2: Маркет 2  Фронт -  Фильтры';
	private $conditions = array();

	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/';
	}


	public function pub_content()
	{
		$data = array();
		$ui = user_interface::get_instance('mf2_catalogue_nav');
		$data['current_node'] = $ui->category_data;
		// Шаблон
		$template = $this->get_args('template', 'default.html');
		return $this->parse_tmpl($template,$data);
	}


	public function prepare_filters($eObj, $in = array())
	{
		$di = data_interface::get_instance('mf2_catalogue_filters');
		$di->prepare_current_ids($in);
	}

	public function _listeners()
	{
		return array(
			array('ui' => 'mf2_catalogue_list', 'event' => 'data_ready', 'handler' => 'prepare_filters'),
		);
	}

	public function set_condition($filter,$condition,$add_condition = false)
	{
		$di = data_interface::get_instance('mf2_catalogue_filters');
		$di->conditions[$filter] = array('condition'=>$condition,"include"=>$add_condition);
	}


	public function get_others($filter)
	{
		$di = data_interface::get_instance('mf2_catalogue_filters');
		$a = $di->conditions;
		unset($a[$filter]);
		return $a;
	}
}
?>
