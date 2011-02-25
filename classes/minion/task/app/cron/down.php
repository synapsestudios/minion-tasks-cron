<?php defined('SYSPATH') or die('No direct script access.');

class Minion_Task_App_Cron_Down extends Minion_Task_App_Cron_Base {

	/**
	 * Migrates the database to the version specified
	 *
	 * @param array Configuration to use
	 */
	public function execute(array $config)
	{
		throw new Kohana_Exception('app:cron:down not yet implemented.');
		
		$upgrades = array();
		$files = array_reverse(Kohana::list_files('crons'));
		$current = Model_Cron_Version::get_current();

		foreach ($files as $file)
		{
			$timestamp = pathinfo($file, PATHINFO_FILENAME);
			$upgrades[$timestamp] = $file;
		}

		foreach ($upgrades as $timestamp => $path)
		{
			include_once $path;
			$class_name = 'Cron_'.$timestamp;
			$cron = new $class_name;
			$lines = $cron->execute();

			self::remove_lines($lines);
			//Model_Cron_Version::set_current($timestamp);
		}
		//Model_Cron_Version::set_current(0);
		
		return "Ok\n";
	}
}