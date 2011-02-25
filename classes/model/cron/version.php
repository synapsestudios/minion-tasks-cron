<?php defined('SYSPATH') or die('No direct script access.');

class Model_Cron_Version {

	public static function create_table()
	{
		$query = DB::query(Database::SELECT, "SHOW TABLES like 'minion_crons'")
			->execute();

		if ( ! count($query))
		{
			$sql = '
				CREATE  TABLE IF NOT EXISTS `minion_crons`
				(
					`id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
					`timestamp` varchar(14) NOT NULL,
					PRIMARY KEY (`id`)
				)
				ENGINE = InnoDB
			';

			DB::query(NULL, $sql)
				->execute();

			DB::insert('minion_crons', array('timestamp'))
				->values(array(0))
				->execute();
		}
	}

	public static function get_current()
	{
		return DB::select('timestamp')
			->from('minion_crons')
			->limit(1)
			->execute()
			->get('timestamp');
	}

	public static function set_current($timestamp)
	{
		DB::update('minion_crons')
			->set(array('timestamp' => $timestamp))
			->execute();
	}
}