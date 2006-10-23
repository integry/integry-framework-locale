<?php

class LCInterfaceTranslator implements LCiTranslator
{
	/**
	 * Locale_MakeText handler instance for the particular locale
	 */
	private $makeTextInstance = false;
	
	/**
	 * Translation manager object instance
	 */
	private $translationManager;
	
	/**
	 * Current locale code
	 */
	private $localeCode;
	
	/**
	 * Initializes the interface translator handler object and assigns translation manager object
	 * @param string $localeCode, LCInterfaceTranslationManager $manager
	 */
	public function __construct($localeCode, LCInterfaceTranslationManager $manager)
	{
		$this->localeCode = $localeCode;
		$this->translationManager = $manager;
	}
	
	/**
	 * Translates text to current locale.
	 * @param string $key
	 * @return string
	 */
	public function translate($key) 
	{	 
		$def = $this->translationManager->getDefinition($key);

		if (FALSE !== $def)
		{
		  	return $def;
		} 	
		else
		{
		  	return $key;
		}
	}
	
	/**
	 * Performs MakeText translation
	 * @param string $key
	 * @param array $params
	 * @return string
	 * @todo document its working principles and probably refactor as well
	 */
	public function makeText($key, $params) 
	{	  	  		  
		$def = $this->translationManager->getDefinition($key);
		if ($def)
		{			
			$lh = $this->getLocaleMakeTextInstance();
			$list = array();
			$list[] = $this->translate($key);		
						
			$list = array_merge($list, split(",", $params));						
			return call_user_func_array(array($lh, "_"), $list);
		} 
		else 
		{
		  	return $key;
		}
	}
	
	/**
	 * Creates MakeText handler for current locale
	 * @return LocaleMakeText
	 */	
	private function getLocaleMakeTextInstance() 
	{	  
	  	if (!$this->makeTextInstance) 
		{
			require_once('maketext/LCMakeTextFactory.php');
			$inst = LCMakeTextFactory::create($this->localeCode);

			if ($inst)
			{
			  	$this->makeTextInstance = $inst;
			} 
			else 
			{
			  	return false;
			}
		}
		
		return $this->makeTextInstance;
	}	
	  	  
}

?>