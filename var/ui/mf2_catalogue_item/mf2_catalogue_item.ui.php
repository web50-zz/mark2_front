<?php
/**
*
* @author	Fedot B Pozdnyakov 9@u9.ru 31032014	
* @package	SBIN Diesel
*/
class ui_mf2_catalogue_item extends user_interface
{
	public $title = 'mf2: Маркет 2  Фронт -  карточка товара';
	public $item_data = array();

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
		if(!($item_id > 0))
		{
			$st = user_interface::get_instance('structure');
			$st->do_404();
		}
		if($ui->item_data->item_id >0)
		{
			$data = $ui->item_data;
		}
		else
		{
			$di = data_interface::get_instance('mf2_catalogue_list');
			$data = $di->get_item($item_id);
		}
		if($data->not_available == 1)
		{
			$st = user_interface::get_instance('structure');
			$st->do_404();
		}

		if(request::get('gallery') == 'true')// это для отдачи галереей картинок  товара аяксом
		{
			$headers = getallheaders();
			if($headers['x-requested-with'] == 'XMLHttpRequest' ||$headers['X-Requested-With'] == 'XMLHttpRequest')
			{
				$this->gallery($data);
			}
			$st = user_interface::get_instance('structure');
			$st->do_404();
			return false;
		}
		$data->basket = $_SESSION['mf2_cart'];
		if($args['mode'] == 'ajax')
		{
			$data->show_link = 1;
			$da = user_interface::get_instance("mf2_catalogue_item_ajax");
			$da->pub_item($data);
		}
		$title =  $data->title.'  '.$data->meta_title;
		$st = user_interface::get_instance('structure');
		$st->add_title($title);

		$ret = $this->parse_tmpl($template,$data);
		return 	$ret;
	}

	public function pub_linked_items()
	{
		$data = array();
		$data['records'] = array();
		$link_type = $this->get_args('type','0');
		if($link_type > 0)
		{
			$ui = user_interface::get_instance('mf2_catalogue_nav');
			if($ui->item_data->item_id > 0)
			{
				$l = json_decode($ui->item_data->linked_items_list);
				if(count($l)>0)
				{
					$ids = array();
					foreach($l as $key=>$value)
					{
						if($value -> type == $link_type)
						{
							$ids[] = $value->linked_item_id;
						}
					}
					$di = data_interface::get_instance('mf2_catalogue_list');
					$di->_flush();
					$di->push_args(array('_sitem_id'=>$ids));
					$res = $di->_get()->get_results();
					$di->pop_args();
					foreach($ids as $key=>$value)
					{
						foreach($res as $key2=>$value2)
						{
							if($value2->item_id == $value)
							{
								$data['records'][] = $value2;
							}
						}
					}
				}
			}
		}
		$template = $this->get_args('template', 'linked.html');
		$data['args'] =  $this->get_args();
		return $this->parse_tmpl($template,$data);
	}

	public function gallery($data)
	{
			$template_gal = $this->get_args('template', 'gallery.html');
			$ret = $this->parse_tmpl($template_gal,$data);
			response::send($ret,'html');
	}
}
?>
