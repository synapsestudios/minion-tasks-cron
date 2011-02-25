<?php defined('SYSPATH') or die('No direct script access.');

class Minion_Task_App_Cron extends Minion_Task {

	public $_config = array('help');

	public function execute(array $config)
	{
		$help = Arr::get($config, 'help', FALSE);

		if ($help)
		{
			echo "Available commands:\n";
			echo "\tapp:cron          - Runs app:cron:upgrade\n";
			echo "\tapp:cron --help   - Displays this help text\n";
			echo "\tapp:cron:generate - Generates new cron upgrade template\n";
			echo "\tapp:cron:upgrade  - Updates cron to newest version\n";
			//echo "\tapp:cron:status   - Shows all current crontab entries for this application\n";
			return;
		}

		$upgrade = new Minion_Task_App_Cron_Upgrade();
		return $upgrade->execute($config);
	}

}