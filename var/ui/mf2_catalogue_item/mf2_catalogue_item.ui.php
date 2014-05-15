<?php
/**
*
* @author	Fedot B Pozdnyakov 9@u9.ru 31032014	
* @package	SBIN Diesel
*/
class ui_mf2_catalogue_item extends user_interface
{
	public $title = 'mf2: Маркет 2  Фронт -  карточка товара';
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/';
	}

	public function pub_content()
	{
		$args = request::get();
		$data = array();
		// Шаблон
		$template = $this->get_args('template', 'default.html');
		$ui = user_interface::get_instance('mf2_catalogue_nav');
		$item_id = $ui->item_id;
		$di = data_interface::get_instance('mf2_catalogue_list');
		$data = $di->get_item($item_id);
		$data->basket = $_SESSION['mf2_cart'];
		if($args['mode'] == 'ajax')
		{
			$data->show_link = 1;
		}
		$ret = $this->parse_tmpl($template,$data);
		if($args['mode'] == 'ajax')
		{
			$resp['success'] = 'true';
			$resp['data'] = $ret;
			response::send($resp,'json');
		}

		return 	$ret;
	}

}
?>
