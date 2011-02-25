<?php defined('SYSPATH') or die('No direct script access.');

class Minion_Task_App_Cron_Upgrade extends Minion_Task {

	/**
	 * Updates the cron to a new version
	 *
	 * @param array Configuration to use
	 */
	public function execute(array $config)
	{
		Model_Cron_Version::create_table();

		$upgrades = array();
		$files = Kohana::list_files('crons');
		$current = Model_Cron_Version::get_current();

		foreach ($files as $file)
		{
			$timestamp = pathinfo($file, PATHINFO_FILENAME);
			if ($timestamp > $current)
			{
				$upgrades[$timestamp] = $file;
			}
		}

		foreach ($upgrades as $timestamp => $path)
		{
			include_once $path;
			$class_name = 'Cron_'.$timestamp;
			
			if (class_exists($class_name))
			{
				$cron = new $class_name;
			}
			else
			{
				return 'Invalid Cron template file: '.$path."\nIncorrect class name\n";
			}
			$new_lines = $cron->execute();

			if (is_array($new_lines))
			{
				self::add_lines($new_lines);
			}
			else
			{
				return 'Invalid Cron template file: '.$path."\nTemplate must return array\n";
			}
			
			Model_Cron_Version::set_current($timestamp);
		}

		return "Cron upgraded to newest version.\n";
	}

	protected static function get_crontab()
	{
		ob_start();
		system('crontab -l');
		$crontab = ob_get_clean();

		return $crontab;
	}

	protected static function save_crontab($crontab)
	{
		if (substr($crontab, -1) != "\n")
		{
			$crontab .= "\n";
		}
		
		$filename = self::unique_filename();
		file_put_contents($filename, $crontab);

		ob_start();
		system('crontab '.escapeshellarg($filename));
		$output = ob_get_clean();

		unlink($filename);

		return ($output == '');
	}

	protected static function unique_filename($ext = 'tmp')
	{
		do
		{
			$unique_filename = realpath(sys_get_temp_dir()).DIRECTORY_SEPARATOR.Text::random().'.'.$ext;
		}
		while (file_exists($unique_filename));

		return $unique_filename;
	}
	
	protected static function get_tasks($section)
	{
		$lines = array();

		if (preg_match("/##\s.*\n.*\n/", $section, $matches))
		{
			$parts = explode("\n", $matches[0], 2);
			$split = explode(' ', $parts[0], 3);

			$lines[$split[1]] = array('command' => trim($parts[1]));
			if (array_key_exists('2', $split))
			{
				$lines[$split[1]]['description'] = trim($split[2]);
			}
		}
		return $lines;
	}

	protected static function add_lines(array $groups)
	{
		$crontab = self::get_crontab();
		foreach ($groups as $key => $group)
		{
			$hash = '## '.$key.' cron';
			$chunks = explode($hash, $crontab);

			$crontab = $chunks[0];

			// If there were previous entried for that group
			if (array_key_exists('1', $chunks))
			{
				$old_group = self::get_tasks($chunks[1]);
				$group = array_merge($old_group, $group);
			}

			$section = '';
			foreach ($group as $key => $line)
			{
				if ($line !== NULL)
				{
					$section .= '## '.$key;
					if (array_key_exists('description', $line))
					{
						$section .= ' '.$line['description']."\n";
					}
					else
					{
						$section .= "\n";
					}
					$section .= $line['command']."\n";
				}
			}

			if ($section != '')
			{
				$crontab .= $hash."\n";
				$crontab .= $section;
				$crontab .= $hash."\n";
			}

			if (array_key_exists('2', $chunks))
			{
				$crontab .= trim($chunks[2]);
			}
		}
		return self::save_crontab($crontab);
	}

	protected static function remove_lines(array $groups)
	{
		throw new Kohana_Exception('remove_lines not yet implemented.');
		
		$crontab = self::get_crontab();
		foreach ($groups as $key => $group)
		{
			$hash = '## '.$key.' cron';
			$chunks = explode($hash, $crontab);
			if (array_key_exists('2', $chunks))
			{
				$old_group = self::get_tasks($chunks[1]);
				$group = array_diff_assoc($old_group, $group);

				$crontab .= $chunks[0];
				if (count($group))
				{
					$crontab .= $hash."\n";
				}

				foreach ($group as $key => $line)
				{
					$crontab .= '## '.$key;
					if (array_key_exists('description', $line))
					{
						$crontab .= ' '.$line['description']."\n";
					}
					else
					{
						$crontab .= "\n";
					}
					$crontab .= $line['command']."\n";
				}

				if (count($group))
				{
					$crontab .= $hash."\n";
				}
				if (array_key_exists('2', $chunks))
				{
					$crontab .= trim($chunks[2]);
				}
			}

			return self::save_crontab($crontab);
		}
	}

}