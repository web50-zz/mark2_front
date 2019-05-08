<?php
/**
*
* @author	Fedot B Pozdnyakov 9@u9.ru 25052018	
* @package	SBIN Diesel
*/
class ui_mf2_catalogue_filter_price extends user_interface
{
	public $title = 'mf2: Маркет фильтр по цене';
	public $item_data = array();
	protected $cfg_path = 'mark2_front/etc/filters_config.php';
	public $conf = array();

	public function __construct ()
	{
		if(!glob::get('filters_conf'))
		{
			if(file_exists(INSTANCES_PATH.$this->cfg_path))
			{
				require_once(INSTANCES_PATH.$this->cfg_path);
				glob::set('filters_conf',$filters_conf);
			}
		}
		parent::__construct(__CLASS__);
		$this->files_path = dirname(__FILE__).'/';
	}

	public function pub_content()
	{	
		$data = array();
		$ui = user_interface::get_instance('mf2_catalogue_nav');
		$category_id = $ui->category_id;
		$di =  data_interface::get_instance('m2_category_price');
		$prc = $di->get_price_for_category();
		$data['min'] = $prc['min'];
		$data['max'] = $prc['max'];
		$data['pstart'] = request::get('pstart',$prc['min']);
		$data['pend'] = request::get('pend',$prc['max']);
		return 	$this->parse_tmpl('default.html',$data);
	}

	public function apply_filter($eObj, $ids = array())
	{
			$price_type = registry::get('SORT_PRICE_TYPE',6);
			$pend  = request::get('pend',80000);
			$pstart  = request::get('pstart',1);
			if($eObj->is_joined('m2_item_price'))
			{
				$dj2 = $eObj->get_joined('m2_item_price');
			}
			else
			{
			$dj2 = $eObj->join_with_di('m2_item_price',array('item_id'=>'item_id'),array('price_value'=>'price_value','type'=>'price_type'));
			$eObj->set_joined('m2_item_price',$dj2);
			}
			$a = $dj2->get_alias();
			if($pend == $pstart && $end == 0)
			{
				$pend = '2000000';
			}
			$t1 = ' '.$a.'.`type` = '.$price_type.' ';
			if($pstart && $pend)
			{
				$t2 .= " and ($a.`price_value`<=".$pend." and $a.`price_value` >=".$pstart.') ';
			}
			$sw .= " and ($t1 $t2) ";
			$eObj->where .= $sw; 
			$ui = user_interface::get_instance('mf2_catalogue_filters');
			$ui->set_condition('mf2_catalogue_filter_price',$sw,true);
	}

	public function _listeners()
	{
		if($conf = glob::get('filters_conf'))
		{
			if(array_key_exists('ignored',$conf))
			{
				if(array_key_exists('mf2_catalogue_filter_price',$conf['ignored']))
				{
					return array();
				}
			}
		}
		return array(
			array('di' => 'mf2_catalogue_list', 'event' => 'conditions_done', 'handler' => 'apply_filter'),
		);
	}

}
?>
