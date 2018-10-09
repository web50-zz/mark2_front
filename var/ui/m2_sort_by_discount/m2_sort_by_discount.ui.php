<?php
/**
*
* @author	FedotB Pozdnyakov 9@u9.ru 2018-10-08
* @package	SBIN Diesel
*/
class ui_m2_sort_by_discount extends user_interface
{
	public $title = 'mark2: Сортировка по скидке';

	
	public function __construct ()
	{
		parent::__construct(__CLASS__);
	}

	public function sort_it($eObj, $in = array())
	{
		$sort = session::get('sort','1','mf2_catalogue_list');
		if($sort == 5)
		{
				$di = $eObj->join_with_di('m2_chars',array('item_id'=>'m2_id','128'=>'type_id'),array('variable_value,integer'=>'variable_value'));
				$eObj->flds[] = array('di'=>$di,'name'=>'variable_value');
				$eObj->__order = array();
				$eObj->set_order('abs(variable_value)','DESC',$di);
		}
	}

	public function _listeners()
	{
		return array(
			array('di' => 'mf2_catalogue_list', 'event' => 'conditions_done', 'handler' => 'sort_it'),
		);
	}

}
?>
