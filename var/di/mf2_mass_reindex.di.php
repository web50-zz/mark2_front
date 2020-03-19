<?php
/**
*
* @author	Fedot B Pozdnyakov <9@u9.ru>  
* @package	SBIN Diesel
*/
class di_mf2_mass_reindex extends data_interface
{
	public $title = 'реиндекс';
	/**
	* @var	string	$cfg	Имя конфигурации БД
	*/
	protected $cfg = 'localhost';
	
	/**
	* @var	string	$db	Имя БД
	*/
	protected $db = 'db1';


	protected $cart_data = array();
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
	);
	
	public function __construct ()
	{
	    // Call Base Constructor
	    parent::__construct(__CLASS__);
	}
	
	public function reindex()
	{
		$where = "where manufacturers_list != '[]' && category_list != '[]' and not_available = 0";
		$sql = "select * from m2_item_indexer $where";
		$res = $this->_get($sql)->get_results();
		$di = data_interface::get_instance('m2_chars_in_category');
		$di->recache($res);
		$di = data_interface::get_instance('m2_category_manufacturers');
		$di->recache($res);
		$di = data_interface::get_instance('m2_category_price');
		$di->recache($res);
		unset($res);
		return ;
	}
	public function _listeners()
	{
		return array(
			array('di' => 'm2_item_indexer', 'event' => 'after_reindex', 'handler' => 'reindex'),
		);
	}

}
?>
