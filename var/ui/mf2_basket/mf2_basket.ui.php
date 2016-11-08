<?php
/**
*
* @author       9* 9@u9.ru	
* @access	public
* @package	SBIN Diesel 	
*/
class ui_mf2_basket extends user_interface
{
	public $title = 'mf2: корзина';

	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

        public function pub_content()
        {
		$data = array();
		$cart = data_interface::get_instance('mf2_cart');
		$this->records = $cart->get_records();
		$this->cart_data = $cart->get_cart_data();
		$data['payload']= $this->prepare_basket_body();
		$data['basket_list']= $this->basket_list();
		return $this->parse_tmpl('default.html',$data);
	}

	private function prepare_basket_body()
	{
		$data = array();
		$data = array(
			'records' => $this->records,
		);
		return $this->parse_tmpl('basket_body.html',$data);
	}

	private function basket_list()
	{
		$data = array();
		$data = array(
			'records' => $this->cart_data,
		);
		return $this->parse_tmpl('basket_list.html',$data);
	
	}
	private function prepare_upsale()
	{
		
	}

	public function pub_basket_json()
	{
		$cart = data_interface::get_instance('mf2_cart');
		$this->records = $cart->get_records();
		$this->cart_data = $cart->get_cart_data();
		$resp['payload'] = $this->prepare_basket_body();
		$resp['basket_list']= $this->basket_list();
		$resp['success'] = true;
		response::send($resp,'json');
	}
}
?>
