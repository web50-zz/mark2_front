<?php
/**
*
* @author	Fedot B Pozdnyakov <9@u9.ru>  &  Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class di_mf2_cart extends data_interface
{
	public $title = 'Корзина';

	/**
	* @var	string	$cfg	Имя конфигурации БД
	*/
	protected $cfg = 'session';
	
	/**
	* @var	string	$name	Имя таблицы
	*/
	protected $name = 'mf2_cart';

	protected $cart_data = array();
	/**
	* @var	array	$fields	Конфигурация таблицы
	*/
	public $fields = array(
		'id' => array('type' => 'integer', 'serial' => TRUE, 'readonly' => TRUE),
	);
	
	public function __construct ()
	{
	    // Call Base Constructor
	    parent::__construct(__CLASS__);
	}
	
	public function get_records($method_of_payment = 0)
	{
		$records = array();
		$ids = array_keys($this->get_list());
		if (!empty($ids))
		{
			$records = $ids;
			$this->fire_event('collect_data', array($ids));
		}
		return (array)$records;
	}

	/**
	*	Список записей
	*/
	public function get_list()
	{
		//return (array)session::get(null, array(), $this->name);
		return (array)session::get(null, array(), $this->name);
	}
	
	public function get($id)
	{
		return session::get($id, 0, $this->name);
	}
	
	/**
	*	Сохранить данные и вернуть JSON-пакет для ExtJS
	* @access protected
	*/
	public function set($id, $count = 0)
	{
		$count = (!$count) ? $this->get($id) + 1 : $count;
		return session::set($id, $count, $this->name);
	}
	
	/**
	*	Удалить данные и вернуть JSON-пакет для ExtJS
	* @access protected
	*/
	public function do_unset($id)
	{
		return session::del($id, $this->name);
	}

	public function add_data($data)
	{
		$this->cart_data = $data;
	}

	public function get_cart_data()
	{
		$this->get_records();
		$list = $this->get_list();
		foreach($this->cart_data as $key=>$value)
		{
			foreach($list[$value['item_id']] as $key2=>$value2)
			{
				$this->cart_data[$key][$key2] = $value2;
			}
		}
		return $this->cart_data;
	}
}
?>
