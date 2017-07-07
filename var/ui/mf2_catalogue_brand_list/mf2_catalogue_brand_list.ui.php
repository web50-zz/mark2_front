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
		$sql = "select m.*,f.real_name,f.file_type from m2_manufacturers m left join m2_manufacturer_files f on f.item_id = m.id order by $sort $dir";
		$data['records'] = $di->_get($sql)->get_results();;
		$data['PAGE_ID'] = PAGE_ID;
		$data['req'] = request::get();
		return $this->parse_tmpl($template,$data);
	}

	//производители по заданной группе
	public function pub_by_group()
	{
		$data = array();
		// Шаблон
		$args = $this->get_args();
		$sort = $this->get_args('sort','title');
		$dir = $this->get_args('dir','asc');
		$template = $this->get_args('template', 'default.html');
		$group = $this->get_args('group',0);
		if(!($group > 0))
		{
			return false;
		}
		$di = data_interface::get_instance('m2_manufacturers');
		$sql = "select m.*,f.real_name,f.file_type from m2_manufacturer_in_groups g left join m2_manufacturers m on g.item_id = m.id left join m2_manufacturer_files f on f.item_id = m.id where g.group_id = $group group by m.id";
		$data['records'] = $di->_get($sql)->get_results();
		$data['PAGE_ID'] = PAGE_ID;
		$data['req'] = request::get();
		return $this->parse_tmpl($template,$data);
	}

	public function pub_char_list()
	{
		$char_id =  $this->get_args('char_id',0);
		if(!($char_id >0))
		{
			return '';
		}
		$template = $this->get_args('template', 'char_list.html');
		$sort = $this->get_args('sort','title');
		$dir = $this->get_args('dir','asc');
		$di = data_interface::get_instance('m2_chars_types');
		$ns = new nested_sets($di);
		$data['records'] = $ns->get_childs($char_id);
		$data['req'] = request::get();
		return $this->parse_tmpl($template,$data);
	}

}
?>
