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
		$data['payload']= $this->prepare_basket_body();
		return $this->parse_tmpl('default.html',$data);
	}

	private function prepare_basket_body()
	{
		$data = array();
		$cart = data_interface::get_instance('mf2_cart');
		$data = array(
			'records' => $cart->get_records(),
		);
		return $this->parse_tmpl('basket_body.html',$data);
	}

	public function pub_basket_json()
	{
		$resp['payload'] = $this->prepare_basket_body();
		$resp['success'] = true;
		response::send($resp,'json');
	}
}
?>
