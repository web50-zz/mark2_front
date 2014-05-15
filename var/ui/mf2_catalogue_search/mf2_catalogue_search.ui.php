<?php
/**
*
* @author	Fedot B Pozdnyakov 9@u9.ru 10052014	
* @package	SBIN Diesel
*/
class ui_mf2_catalogue_search extends user_interface
{
	public $title = 'mf2: Маркет 2  Фронт -  поиск';
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/';
	}


// Форма поиска  
	public function pub_form()
	{
		$template = $this->get_args('template', 'search.html');
		$data['search'] = request::get('search');
		return $this->parse_tmpl($template,$data);
	}


	//9* метод пока не алё
	public function pub_content()
	{
		$data = array();
		// Шаблон
		$template = $this->get_args('template', 'default.html');
		return $this->parse_tmpl($template,$data);
	}

}
?>
