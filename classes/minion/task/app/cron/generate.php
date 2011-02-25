<?php defined('SYSPATH') or die('No direct script access.');

class Minion_Task_App_Cron_Generate extends Minion_Task {

	public function execute(array $config)
	{
		$view = Kostache::factory('minion/task/app/cron/generate');
		$timestamp = $view->timestamp();

		file_put_contents(APPPATH.'crons'.DIRECTORY_SEPARATOR.$timestamp.'.php', $view->render());
	}
}