<?php
/**
*
* @author	Fedot B Pozdnyakov 9@u9.ru 22042014	
* @package	SBIN Diesel
*/
class ui_mf2_catalogue_item_ajax extends user_interface
{
	public $title = 'mf2: Маркет 2  Фронт -  AJAX карточка товара';
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/';
	}

	public function pub_content()
	{
		$st = user_interface::get_instance('structure');
		$ui = user_interface::get_instance('mf2_catalogue_item');
		$st->collect_resources($ui,'mf2_catalogue_item');
		return '';
	}

}
?>
