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
	protected $cfg_path = 'mark2_front/etc/filters_config.php';
	public $conf = array();


	public function __construct ()
	{
		if(!glob::get('filters_conf'))
		{
			if(file_exists(INSTANCES_PATH.$this->cfg_path))
			{
				require_once(INSTANCES_PATH.$this->cfg_path);
				glob::set('filters_conf',$filters_conf);
			}
		}

		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/';
	}

	public function pub_content()
	{

		$data = array();
		$ui = user_interface::get_instance('mf2_catalogue_nav');
		$category_id = $ui->category_id;
		$scope = $ui->get_scope();
		$di = data_interface::get_instance('m2_category_manufacturers');
		if($category_id > 0)
		{
			$data['records'] = $di->get_manufacturers_for_category();
		}
		else	
		{
			$cat_in = request::get('category');
			if(is_array($cat_in) && count($cat_in)>0)
			{
				$scope = array();
				foreach($cat_in as $key=>$value)
				{
					$scope[$value] = 1;
				}
				$data['records'] = $di->get_manufacturers_for_category_list(array_keys($scope));
			}
			else
			{
				$ui = user_interface::get_instance('mf2_catalogue_nav');
			}
		}
		if(!count($data['records']) > 0)
		{
			return ;
		}

		$di2 = data_interface::get_instance('mf2_catalogue_filters');
		$ids = $di2->get_parts();
		$others = $di2->get_others_ids('mf2_catalogue_filter_brand');
		/*
		$current = $di2->get_current_ids();
		$possible  = array();
		foreach($current as $key=>$value)
		{
			$possible[] = $value;
		}
		foreach($others as $key=>$value)
		{
			$possible[] = $value;
		}
		*/
			$cat_ids = implode(',',array_keys($scope));
			$i_ids = implode(',',$others);
			if($i_ids == '')
			{
				$i_ids = "''";
			}
			$sql = "SELECT m.title,im.manufacturer_id,COUNT(im.item_id) as cnt 
					FROM m2_item_manufacturer im 
					LEFT JOIN m2_item_category ic ON im.item_id = ic.item_id 
					LEFT JOIN m2_manufacturers m ON im.manufacturer_id = m.id 
					WHERE ic.category_id IN($cat_ids) and im.item_id in ($i_ids)
					GROUP BY manufacturer_id 
					order by m.title ASC ";
			$counts = $di->_get($sql)->get_results();
			if(count($data['records'])>0)
			{
				foreach($data['records'] as $key2=>$value2)
				{
					$cnt  = 0;
					foreach($counts as $key=>$value)
					{
						$id = $value->manufacturer_id;
						if($value2->manufacturer_id == $id)
						{
							$cnt = $value->cnt;
						}
					}
						if($cnt == 0)
						{
							unset($data['records'][$key2]);
						}else
						{
							$data['records'][$key2]->cnt = $cnt;
						}
				}
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
		$mans = request::get('mans');
		if($mans)
		{
			$mans = json_decode($mans);
			$tmp = array();
			foreach($mans  as $key=>$value)
			{
				$tmp[] =  " `manufacturers_list` like '%\"manufacturer_id\":\"".$value."\"%'";

			}
			if(count($tmp)>0)
			{
				$sw = '('.implode(' OR ',$tmp).')';
				$eObj->where .= "and ($sw)"; 
				$ui = user_interface::get_instance('mf2_catalogue_filters');
				$ui->set_condition('mf2_catalogue_filter_brand',$sw);
			}
		}
	}

	public function _listeners()
	{
		if($conf = glob::get('filters_conf'))
		{
			if(array_key_exists('ignored',$conf))
			{
				if(array_key_exists('mf2_catalogue_filter_brand',$conf['ignored']))
				{
					return array();
				}
			}
		}
		return array(
			array('di' => 'mf2_catalogue_list', 'event' => 'conditions_done', 'handler' => 'apply_filter'),
		);
	}

}
?>
