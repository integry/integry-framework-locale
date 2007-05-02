<?php

ClassLoader::import('library.*');
ClassLoader::import('library.I18Nv2.*');

class Locale
{
	const value = 'v';
  	const file = 'f';
	   
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
	 	include_once('LCLocaleInfo.php');
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
	 */
    public function getFormattedTime($time, $format = self::FORMAT_TIME_LONG)
    {
        if (!$this->timeFormat)
        {            
            if (!@include('I18Nv2/time/' . $this->localeCode . '.php'))
            {
                include('I18Nv2/time/en.php');   
            }
        }       
    }

	/**
	 * Creates locale by locale ID.
	 * @param string $localeCode E.g. "en", "lt", "ru"
	 */
	private static function createInstance($localeCode) 
	{		
		// verify that such locale exists
		include_once('LCInterfaceTranslationManager.php');
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
		include_once('LCInterfaceTranslator.php');
	 	$this->translationManagerInstance = $manager;
	 	$this->translatorInstance = new LCInterfaceTranslator($this->localeCode, $this->translationManagerInstance);  
	}
}
?>