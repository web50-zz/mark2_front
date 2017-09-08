<?php
/**
*
* @author	Fedot B Pozdnyakov 9@u9.ru 07092017	
* @package	SBIN Diesel
*/
class ui_mf2_catalogue_filter_brand extends user_interface
{
	public $title = 'mf2: Маркет фильтр по брэндам';
	public $item_data = array();

	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/';
	}

	public function pub_content()
	{	
		$data = array();
		$ui = user_interface::get_instance('mf2_catalogue_nav');
		$category_id = $ui->category_id;
		if($category_id > 0)
		{
			$di = data_interface::get_instance('m2_category_manufacturers');
			$data['records'] = $di->get_manufacturers_for_category();
		}
		$in = request::get('mans','[]');
		if($in != '[]')
		{
			$colors = json_decode($in);
			if(count($colors)>0)
			{
				foreach($colors as $key=>$value)
				{
					$data['mans'][$value] = 1;
				}
			}
		}
		return 	$this->parse_tmpl('default.html',$data);
	}

	public function apply_filter($eObj, $ids = array())
	{
		if(request::get('color_nothing_to_get'))
		{
			$colj = request::get('color','[]');
			$colors = json_decode($colj);
			$w = array();
			foreach($colors as $key=>$value)
			{
				$w[] = "m2_item_indexer.chars_list like '%\"str_title\":\"Цвет\",\"variable_value\":\"".$value."\"%'";
			}
			$s =  " AND (".implode(' or ',$w).") ";
			$eObj->where .= $s; 
		}
	}

	public function _listeners()
	{
		return array(
			array('di' => 'mf2_catalogue_list', 'event' => 'conditions_done', 'handler' => 'apply_filter'),
		);
	}

}
?>
