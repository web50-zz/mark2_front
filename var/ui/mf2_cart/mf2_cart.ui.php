<?php
/**
*
* @author	9@u9.ru Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class ui_mf2_cart extends user_interface
{
	public $title = 'mf2: Заказ';
	public $cart_data = array();
	public $mail_data = array();
	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/'; 
	}

	protected function pub_empty()
	{
		return '';
	}
        
        /**
        *       Отрисовка контента для внешней части
        */
        protected function pub_content()
        {
		if (SRCH_URI == 'save/')
		{
			$this->pub_accept_order();
		}
		else
		{
			return $this->prepare_form($check);
		}
        }

	private function prepare_form($check)
	{
		$data = $this->get_data();
		$data['hash'] = md5(time().rand());
		$data['check'] = $check;
		return $this->parse_tmpl('default.html', $data);
	}

	private function get_data()
	{
		$cart = data_interface::get_instance('mf2_cart');
		$this->cart_data = array(
			'records' => $cart->get_cart_data(),
		);
		$this->fire_event('onCartDataReady',array($this->cart_data));
		return $this->cart_data;
	}

	private function check()
	{
		$check = array();
		$data = request::get();
		if (empty($data['name']))
			$check['name'] = 'Необходимо указать ваше Имя';
		if (empty($data['phone']))
			$check['phone'] = 'Необходимо указать ваш Телефон';
		if (empty($data['email']))
			$check['email'] = 'Необходимо указать ваш E-Mail';

		return (!empty($check)) ? $check : FALSE;
	}

	private function send_order()
	{
		$data = $this->mail_data;
		$body =  $this->parse_tmpl('order_mail.html', $data);
		$rcpt = registry::get('ORDER_MAIL_TO');
		$title = 'Заказ на сайте';
		$core_domain = $_SERVER['HTTP_HOST'];
		if(!$core_domain)
		{
			$core_domain = 'localhost';
		}
		require_once LIB_PATH.'Swift/swift_required.php';
		$transport = Swift_MailTransport::newInstance();
		$mailer = Swift_Mailer::newInstance($transport);
		$message = Swift_Message::newInstance($title)
			->setFrom(array('no-reply@'.$core_domain => 'no-reply'))
			->setTo($rcpt)
			->setBody($body);
		$message->setContentType("text/html");	
		$this->fire_event('onMessageReady',array($message,$data));
		$numSent = $mailer->batchSend($message);
		if(!empty($data['email']))
		{
	       		$this->send_followup($data);
		}
		$this->fire_event('onSent', array(request::get()));
	}
       
	private function send_followup($data = array())
	{
		$body =  $this->parse_tmpl('followup_mail.html', $data);
		$rcpt = $data['email'];
		$title = 'Ваш Заказ';
		$core_domain = $_SERVER['HTTP_HOST'];
		if(!$core_domain)
		{
			$core_domain = 'localhost';
		}
		require_once LIB_PATH.'Swift/swift_required.php';
		$transport = Swift_MailTransport::newInstance();
		$mailer = Swift_Mailer::newInstance($transport);
		$message = Swift_Message::newInstance($title)
			->setFrom(array('no-reply@'.$core_domain => 'no-reply'))
			->setTo($rcpt)
			->setBody($body);
		$message->setContentType("text/html");	
		$numSent = $mailer->batchSend($message);
		return;	
	}

        /**
        *       Отрисовка контента для внешней части
        */
        protected function pub_tray()
        {
		$cart = data_interface::get_instance('mf2_cart');
		$data = array(
			'records' => $cart->get_records(),
			//'records' => array(array('summ' => 12.34), array('summ' => 2.66),),
			//'is_logged' => authenticate::is_logged()
		);
                return $this->parse_tmpl('tray.html', $data);
        }

	/**
	*	Получить корзину в виде HTML
	*/
	public function get_html_cart($method_of_payment)
	{
                return $this->parse_tmpl('table.html', $this->prepare_data($method_of_payment));
	}

	/**
	*	Подготовить данные корзины
	*/
	private function prepare_data($method_of_payment)
	{
		$cart = data_interface::get_instance('mf2_cart');
		$records = $cart->get_records($method_of_payment);
		$total_items = 0;
		$total_summ = 0;
		foreach ($records as $i => $rec)
		{
			$total_summ+= $rec['summ'];
			$total_items+= $rec['count'];
		}
		$parcels = ceil($total_items / 6);
		$delivery_cost = ($method_of_payment == 2) ? 220 : $parcels * 200;
		return array(
			'records' => $records,
			'total_items' => $total_items,
			'total_summ' => sprintf("%0.2f", $total_summ),
			'parcels' => $parcels,
			'delivery_cost' => sprintf("%0.2f", $delivery_cost),
			'total_cost' => sprintf("%0.2f", $total_summ + $delivery_cost),
			'method_of_payment' => $method_of_payment,
		);
	}

	/**
	*	Получить корзину с описанием и HTML
	*/
	public function get_cart($method_of_payment)
	{
		$data = $this->prepare_data($method_of_payment);
		$data['html'] = $this->get_html_cart($method_of_payment);
		return $data;
	}

	/**
	*	Расчитать общую сумму корзины
	*/
	public function calculate()
	{
		$summ = 0;
		$cart = data_interface::get_instance('mf2_cart');
		$records = $cart->get_records();

		foreach ($records as $rec)
		{
			$summ+= $rec['summ'];
		}

		return array(count($records), $summ);
	}

	/**
	*	Пересчёт корзины
	*/
	protected function pub_recalc()
	{
		$counts = request::get('count', array());
		$di = data_interface::get_instance('mf2_cart');

		foreach ($counts as $id => $count)
		{
			$di->set($id, $count);
		}
		list($count, $summ) = $this->calculate();
		response::send(array(
			'success' => true,
			'count' => $count,
			'summ' => $summ,
		), 'json');
	}


	/**
	*	Добавить элемент в корзину c формы програмно
	*/
	public function add($record = array())
	{
		if ($record['id'] > 0)
		{
			$di = data_interface::get_instance('mf2_cart');
			$di->set($record['id'], $record);
			list($count, $summ) = $this->calculate();
		}
		else
		{
			throw new Exception('Ошибки в просчете корзины');
		}
	}



	/**
	*	Добавить элемент в корзину c формы 
	*/
	protected function pub_add()
	{
		$record = request::get(array('id', 'count'));
		if ($record['id'] > 0)
		{
			$di = data_interface::get_instance('mf2_cart');
			$di->set($record['id'], $record);
			list($count, $summ) = $this->calculate();
			response::send(array(
				'upsale' => $this->upsale($record),
				'success' => true,
				'count' => $count,
				'summ' => $summ,
			), 'json');
		}
		else
		{
			response::send(array(
				'success' => false,
			), 'json');
		}
	}

	protected function upsale($record = array())
	{
		$di = data_interface::get_instance('mf2_catalogue_list');
		$di->_flush();
		$di->set_args(array('_sitem_id'=>$record['id']));
		$data = $di-> _get()->get_results(0);
		return $this->parse_tmpl('upsale.html', $data);
	}
	/**
	*	Добавить элемент в корзину
	*/
	protected function pub_del()
	{
		$id = request::get('id');
		$di = data_interface::get_instance('mf2_cart');
		$di->do_unset($id);
		list($count, $summ) = $this->calculate();
		response::send(array(
			'success' => true,
				'count' => $count,
				'summ' => $summ,
		), 'json');
	}


	protected function pub_accept_order()
	{
		try{
			$data = request::get();
			$check =  array();
			if (empty($data['phone']))
			{
				$check['phone'] = 'Необходимо указать ваш Телефон';
			}
			if (empty($data['mail']))
			{
			//	$check['email'] = 'Необходимо указать ваш E-Mail';
			}
			if(count($check)>0)
			{
				throw new Exception(implode('',$check));
			}
			$cart = json_decode($data['cart_json']);
			foreach($cart->records as $key=>$value)
			{
				$ids[] = $value->id;
			}
			$di = data_interface::get_instance('m2_item_indexer');
			$di->_flush();
			$di->set_args(array('_sitem_id'=>$ids));
			$res = $di->_get()->get_results();
			foreach($cart->records as $key=>$value)
			{
				foreach($res as $key2=>$value2)
				{
					if($value2->item_id == $value->id)
					{
						$cart->records[$key]->index = $value2;
					}
				}
			}
			$data['cart'] = $cart;
			$this->mail_data = $data;
			$this->fire_event('onNewOrder',array($data));
			if(!registry::get('MF2_DISABLE_ORDER_MAILS') == '1')
			{
				$this->send_order();
			}
			if(registry::get('MF2_EMULATE_NOACCEPT') == '1')
			{
				response::send(array('success'=>false,'data'=>'ee'),'json');
			}
			$_SESSION['mf2_cart'] = array();//корзину опустошаем
			response::send(array('success'=>true,'data'=>'ee'),'json');
		
		}catch(Exception $e)
		{
			response::send(array('success'=>false,'message'=>$e->getMessage()),'json');
		}

	}
}
?>
