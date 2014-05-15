<?php
/**
*
* @author   9* <9@u9.ru>  15052013
* @package	SBIN Diesel
*/
class ui_mf2_catalogue_slider extends user_interface
{
	public $title = 'mf2: Маркет 2 Фронт - Слайдер для разделов';

	protected $deps = array(
	);
	
	public function __construct()
	{
		parent::__construct((func_num_args() > 0) ? func_get_arg(0) : __CLASS__);
		$this->files_path = dirname(__FILE__).'/';
	}

	public function  pub_top_parent()
	{
		$level = $this->get_args('level',2);
		$char_id = $this->get_args('char_id',0);//9*  это ID характеристики из справочника  в которой хранится  ид слайдера для раздела
		$ui = user_interface::get_instance('mf2_catalogue_nav');
		$trunc = $ui->get_trunc();
		$parent = $trunc[$level - 1];
		$di = data_interface::get_instance('m2_category_chars');
		$slider = $di->get_value_for_property($parent['id'],$char_id);
		$this->set_args(array('group'=>$slider));
		return $this->content();
	}

	public function content()
	{
		if (($gid = $this->get_args('group', false)) && $gid > 0)
		{
			$slider = data_interface::get_instance('www_slide_group')
				->_flush()
				->push_args(array("_sid" => $gid))
				->_get()
				->pop_args()
				->get_results(0);

			if (!empty($slider))
			{
				$slider->slides = data_interface::get_instance('www_slide')
					->_flush()
					->push_args(array("_sslide_group_id" => $gid))
					->_get()
					->pop_args()
					->get_results();

				$slider->path = data_interface::get_instance('www_slide')->get_url();

				$template = $this->get_args('template', 'default.html');

				return $this->parse_tmpl($template, $slider);
			}
		}

		return false;
	}
	
	/**
	*       Page configure form
	*/
	protected function sys_configure_form()
	{
		$tmpl = new tmpl($this->pwd() . 'configure_form.js');
		response::send($tmpl->parse($this), 'js');
	}
}
?>
