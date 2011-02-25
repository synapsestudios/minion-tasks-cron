<?php defined('SYSPATH') or die('No direct script access.');

class View_Minion_Task_App_Cron_Generate extends Kostache {

	public function timestamp()
	{
		return date('YmdHis');
	}
}