<?php
/**
*
* @author	9@u9.ru Anthon S. Litvinenko <a.litvinenko@web50.ru>
* @package	SBIN Diesel
*/
class ui_mf2_cart extends user_interface
{
	public $title = 'mf2: Заказ';
	
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
		if (request::get('z') == 1 && ($check = $this->check()) === FALSE)
		{
			$this->send_order();
			return '<br><br><br><br><center>Спасибо, ваш заказ отправлен. Наши менеджеры свяжутся с вами.</center>';
		}
		else
		{
			return $this->prepare_form($check);
		}
        }

	private function prepare_form($check)
	{
		$data = $this->get_data();
		$data['check'] = $check;
		return $this->parse_tmpl('default.html', $data);
	}

	private function get_data()
	{
		$cart = data_interface::get_instance('mf2_cart');
		$data = array(
			'records' => $cart->get_cart_data(),
		);
		return $data;
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
		//Create the Transport: Mail
		$transport = Swift_MailTransport::newInstance();

		//Create the Mailer using your created Transport
		$mailer = Swift_Mailer::newInstance($transport);

		//Create a message
		$message = Swift_Message::newInstance(registry::get('ORDER_MAIL_TITLE'));
		$message->setFrom(array("noreply@web50.ru" => "Site Order Form"));
		$message->setTo(array(registry::get('ORDER_MAIL')));
		$message->setBody($this->parse_tmpl('order_mail.html', $this->get_data()), 'text/html');

		//Send the message
		$result = $mailer->send($message);
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
			$di->_set($id, $count);
		}
		list($count, $summ) = $this->calculate();
		response::send(array(
			'success' => true,
			'count' => $count,
			'summ' => $summ,
		), 'json');
	}

	/**
	*	Добавить элемент в корзину
	*/
	protected function pub_add()
	{
		$record = request::get(array('id', 'count'));
		if ($record['id'] > 0)
		{
			$di = data_interface::get_instance('mf2_cart');
			$di->_set($record['id'], $record);
			list($count, $summ) = $this->calculate();
			response::send(array(
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

	/**
	*	Добавить элемент в корзину
	*/
	protected function pub_del()
	{
		$id = request::get('id');
		$di = data_interface::get_instance('mf2_cart');
		$di->_unset($id);
		list($count, $summ) = $this->calculate();
		response::send(array(
			'success' => true,
				'count' => $count,
				'summ' => $summ,
		), 'json');
	}
}
?>
