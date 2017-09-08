<?php
/**
*
* @author	Fedot B Pozdnyakov 9@u9.ru 07092017	
* @package	SBIN Diesel
*/
class ui_mf2_catalogue_filter_sort extends user_interface
{
	public $title = 'mf2: фильтр по сортировка';
	public $request_key = 'sort';

	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/';
	}

	public function pub_content()
	{	
		$data = array();
		$data['records'] = array(
			array('title'=>'По порядку','id'=>1),
			array('title'=>'По увеличению стоимости','id'=>2),
			array('title'=>'По уменьшению стоимости','id'=>3),
			array('title'=>'от А до Я','id'=>4),
		);
		$in = request::get($this->request_key,'1');
		if($in != '0')
		{
			$data[$this->request_key][$in] = 1;
		}
		return 	$this->parse_tmpl('default.html',$data);
	}

	public function apply_filter($eObj, $ids = array())
	{
	/* тут это не надо реакцияна парамерты по ценам встроена в mf2_catalogue_list
		if(request::get($this->request_key))
		{
			$colj = request::get($this->request_key,'[]');
			$colors = json_decode($colj);
			$w = array();
			foreach($colors as $key=>$value)
			{
				$w[] = "m2_item_indexer.chars_list like '%\"type_value\":\"".$value."\",\"str_title\":\"".$this->char_type_title."\",%'";
			}
			$s =  " AND (".implode(' or ',$w).") ";
			$eObj->where .= $s; 
		}
	*/
	}

}
?>
