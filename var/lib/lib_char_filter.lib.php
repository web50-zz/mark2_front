<?php
/**
*
* @author	FedotB Pozdnyakov 9@u9.ru 2018-10-04
* @package	SBIN Diesel
*/
class lib_char_filter extends user_interface
{
	public $title = '';
	public $char_id = 114;
	public $request_param = 'pack';
	public $filter_name = '';

	public function __construct ()
	{
		parent::__construct(__CLASS__);
	}
	
	public function pub_list(){
		$template = $this->get_args('template', 'default.html');
		$sort = $this->get_args('sort','title');
		$dir = $this->get_args('dir','asc');
		$di = data_interface::get_instance('m2_chars_types');
		$ns = new nested_sets($di);
		$data['records'] = $ns->get_childs($this->char_id);
		$data['req'] = request::get();
		$in = request::get($this->request_param,'[]');
		if($in != '[]')
		{
			$l = json_decode($in);
			if(count($l)>0)
			{
				foreach($l as $key=>$value)
				{
					$data[$this->request_param][$value] = 1;
				}
			}
		}

		return $this->parse_tmpl($template,$data);

	}


	public function pub_content()
	{	
		$data = array();
		$data['records'] = array();
		$ui = user_interface::get_instance('mf2_catalogue_nav');
		$category_id = $ui->category_id;
		$scope = $ui->get_scope();
		$di = data_interface::get_instance('m2_chars_in_category');
		if($category_id > 0)
		{
			$d = $di->get_chars_for($scope,$this->char_id);
		}
		else	
		{
			$cat_in = request::get('category');
			$scope = array();
			if(is_array($cat_in) && count($cat_in)>0)
			{
				foreach($cat_in as $key=>$value)
				{
					$scope[$value] = 1;
				}
			}
			$d = $di->get_chars_for($scope,$this->char_id);
		}
		if(!count($d) > 0)
		{
			return ;
		}
		foreach($d as $k=>$v)
		{
			$data['records'][] = array('title'=>$k,'id'=>$v);
		}
		$di2 = data_interface::get_instance('mf2_catalogue_filters');
		$ids = $di2->get_parts();
		$others = $di2->get_others_ids($this->filter_name);

		$cat_ids = implode(',',array_keys($scope));
		$i_ids = implode(',',$others);
			if($i_ids == '')
			{
				$i_ids = "''";
			}

		$sql = "SELECT mc.type_value,mc.type_id,count(*) as cnt FROM m2_chars mc WHERE mc.m2_id IN ($i_ids) and  mc.type_id = ".$this->char_id." group by type_value;";
		$counts = $di->_get($sql)->get_results();
		foreach($data['records'] as $key2=>$value2)
		{
			$cnt  = 0;
			foreach($counts as $key=>$value)
			{
				$id = $value->type_value;
				if($value2['id'] == $id)
				{
					$cnt = $value->cnt;
				}
			}
			if($cnt == 0)
			{
				unset($data['records'][$key2]);
			}else
			{
				$data['records'][$key2]['cnt'] = $cnt;
			}

		}
		$data['req'] = request::get();
		$in = request::get($this->request_param,'[]');
		if($in != '[]')
		{
			$l = json_decode($in);
			if(count($l)>0)
			{
				foreach($l as $key=>$value)
				{
					$data[$this->request_param][$value] = 1;
				}
			}
		}

		return 	$this->parse_tmpl('default.html',$data);
	}

	public function apply_filter($eObj, $ids = array())
	{
		if(request::get($this->request_param))
		{
			$pack = request::get($this->request_param);
			if($pack)
			{
				$mans = json_decode($pack);
				$tmp = array();
				foreach($mans  as $key=>$value)
				{
					$tmp[] =  ' `chars_list` like "%{\"type_id\":\"'.$this->char_id.'\",\"type_value\":\"'.$value.'\"%"';

				}
				if(count($tmp)>0)
				{
					$sw = '('.implode(' OR ',$tmp).')';
					$eObj->where .= "and ($sw)";
					$ui = user_interface::get_instance('mf2_catalogue_filters');
					$ui->set_condition($this->filter_name,$sw);
				}
			}
		}
	}
	//ниже для примера что надо вставить
	public function example_listeners()
	{
		return array(
			array('di' => 'mf2_catalogue_list', 'event' => 'conditions_done', 'handler' => 'apply_filter'),
		);
	}
}
?>
