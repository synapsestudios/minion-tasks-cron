<?php defined('SYSPATH') or die('No direct script access.');

class View_Minion_Task_App_Cron_Update extends Kostache {

	public function start_section()
	{
		return '## PROJECT-NAME '.Kohana::$environment.' CRONS';
	}

	public function end_section()
	{
		return '## END PROJECT-NAME '.Kohana::$environment.' CRONS';
	}

}