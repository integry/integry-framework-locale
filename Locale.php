<?php

namespace locale;

/**
 *
 * @package library/locale
 * @author Integry Systems
 */
class Locale
{
	const FORMAT_TIME_FULL = 0;
	const FORMAT_TIME_LONG = 1;
	const FORMAT_TIME_MEDIUM = 2;
	const FORMAT_TIME_SHORT = 3;

	const FORMAT_DATE_FULL = 4;
	const FORMAT_DATE_LONG = 5;
	const FORMAT_DATE_MEDIUM = 6;
	const FORMAT_DATE_SHORT = 7;

  	private $translationManagerInstance;

	private $translatorInstance;

	private $localeInfoInstance;

	private $localeCode;

	private $timeFormat;

	private static $currentLocale;

	private static $instanceMap = array();

	/**
	 * Initialize new locale instance
	 * @param string $localeCode.
	 */
	private function __construct($localeCode)
  	{
	 	include_once(dirname(__file__) . '/LCLocaleInfo.php');
		$this->localeCode = $localeCode;
		$this->localeInfoInstance = new LCLocaleInfo($localeCode);
	}

	/**
	 * Sets current locale
	 * @param string $localeCode.
	 */
	public static function setCurrentLocale($localeCode)
	{
		self::$currentLocale = $localeCode;
	}

	/**
	 * Returns locale code
	 * @return String
	 */
	public function getLocaleCode()
	{
	  	return $this->localeCode;
	}

	/**
	 * Gets locale, which is defined as current {@see Locale::setCurrentLocale}
	 * @return Locale
	 */
	public static function getCurrentLocale()
	{
	  	return Locale::getInstance(self::$currentLocale);
	}

	/**
	 * Gets locale instance by its code. Flyweight pattern is used, to load data just once.
	 * @param $localeCode
	 */
	public static function getInstance($localeCode)
	{
	  	if(!isset(self::$instanceMap[$localeCode]))
		{
		  	$instance = self::createInstance($localeCode);
		  	if (!$instance)
		  	{
				return false;
			}

			self::$instanceMap[$localeCode] = $instance;
		}

		return self::$instanceMap[$localeCode];
	}

	/**
	 * Returns LCLocaleInfo instance
	 *
	 * @return LCLocaleInfo
	 */
	public function info()
	{
	  	return $this->localeInfoInstance;
	}

	/**
	 * Returns LCInterfaceTranslationManager instance
	 */
	public function translationManager()
	{
	  	return $this->translationManagerInstance;
	}

	/**
	 * Returns LCInterfaceTranslator instance
	 */
	public function translator()
	{
	  	return $this->translatorInstance;
	}

	/**
	 * Returns LCInterfaceTranslator instance
	 *
	 * @param string $format   Format name (for example, 'date_short')
	 */
	public function getFormattedTime($time, $format = null)
	{
		if (!$this->timeFormat)
		{
			$this->loadFormatConfig();
		}

		$map = $this->getTimeMap($time);

		// day names
		if (isset($this->timeFormat['dayNames']['format']))
		{
			$names = $this->timeFormat['dayNames']['format'];
			$index = $map['%N'] < 7 ? $map['%N'] : 0;

			foreach (array('D' => 'abbreviated', 'l' => 'wide') as $php => $loc)
			{
				if (isset($names[$loc][$index]))
				{
					$map['%' . $php] = $names[$loc][$index];
				}
			}
		}

		// month names
		if (isset($this->timeFormat['monthNames']['format']))
		{
			$names = $this->timeFormat['monthNames']['format'];
			$index = $map['%n'] - 1;

			foreach (array('M' => 'abbreviated', 'F' => 'wide') as $php => $loc)
			{
				if (isset($names[$loc][$index]))
				{
					$map['%' . $php] = $names[$loc][$index];
				}
			}
		}

		// AM/PM
		if (isset($this->timeFormat['AmPmMarkers']))
		{
			$index = (int)($map['%a'] == 'pm');
			$map['%X'] = $this->timeFormat['AmPmMarkers'][$index];
		}

		$res = $this->getFormattedData($map);

		return !is_null($format) ? $res[$format] : $res;
	}

	public function getMonthName($index, $type = 'abbreviated')
	{
		if (!$this->timeFormat)
		{
			$this->loadFormatConfig();
		}

		if (isset($this->timeFormat['monthNames'][$type][$index]))
		{
			return $this->timeFormat['monthNames'][$type][$index];
		}
		else if (isset($this->timeFormat['monthNames']['format'][$type][$index]))
		{
			return $this->timeFormat['monthNames']['format'][$type][$index];
		}
		else
		{
			return date('M', mktime(1, 1, 1, $index, 1, 2000));
		}
	}

	private function getTimeMap($time)
	{
		$f = 'Y|y|F|M|m|n|d|j|l|D|h|g|G|H|i|s|N|a|T';
		$values = explode('|', date($f, $time));
		$keys = explode('|', '%' . str_replace('|', '|%', $f));
		return array_combine($keys, $values);
	}

	private function getFormattedData($map)
	{
		$res = array();
		foreach (self::getDateFormats() as $name => $code)
		{
			$res[$name] = trim(strtr($this->timeFormat['DateTimePatterns'][$code], $map));
		}
		return $res;
	}

	private function loadFormatConfig()
	{
		$path = $this->config->getPath('library/locale.I18Nv2.time/' . $this->localeCode) . '.php';

		if (!file_exists($path) || !include($path))
		{
			include('I18Nv2/time/en.php');
		}
		else
		{
			// load sub-locale (first available, this will have to be extended in future)
			if (!isset($data['DateTimePatterns']))
			{
				$d = $data;
				foreach (new DirectoryIterator(dirname(__file__) . '/I18Nv2/time/') as $file)
				{
					if (substr($file->getFileName(), 0, 3) == $this->localeCode . '_')
					{
						include $file->getPath() . '/' . $file->getFileName();
						$data = array_merge($d, $data);
					}
				}
			}
		}

		if (!isset($data['DateTimePatterns']))
		{
			include dirname(__file__) . '/I18Nv2/time/en.php';
		}

		$this->timeFormat = $data;
	}

	private static function getDateFormats()
	{
		static $dateTransform = null;

		if (!$dateTransform)
		{
			$dateTransform = array
			(
				'time_full' => Locale::FORMAT_TIME_FULL,
				'time_long' => Locale::FORMAT_TIME_LONG,
				'time_medium' => Locale::FORMAT_TIME_MEDIUM,
				'time_short' => Locale::FORMAT_TIME_SHORT,
				'date_full' => Locale::FORMAT_DATE_FULL,
				'date_long' => Locale::FORMAT_DATE_LONG,
				'date_medium' => Locale::FORMAT_DATE_MEDIUM,
				'date_short' => Locale::FORMAT_DATE_SHORT,
			);
		}

		return $dateTransform;
	}

	/**
	 * Creates locale by locale ID.
	 * @param string $localeCode E.g. "en", "lt", "ru"
	 */
	private static function createInstance($localeCode)
	{
		// verify that such locale exists
		include_once(dirname(__file__) . '/LCInterfaceTranslationManager.php');
		$translationManager = LCInterfaceTranslationManager::create($localeCode);
		if (!$translationManager)
		{
			return false;
		}

		$instance = new Locale($localeCode);

		$instance->setTranslationManager($translationManager);

		return $instance;
	}

	/**
	 * Assigns interface translation manager object
	 * @param LCInterfaceTranslationManager $manager
	 */
	private function setTranslationManager(LCInterfaceTranslationManager $manager)
	{
		include_once(dirname(__file__) . '/LCInterfaceTranslator.php');
	 	$this->translationManagerInstance = $manager;
	 	$this->translatorInstance = new LCInterfaceTranslator($this->localeCode, $this->translationManagerInstance);
	}
}
?>
