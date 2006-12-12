<?php

class LCLocaleInfo
{
	/**
	 * Current locale code (en, lt, ru, ...)
	 */	
	private $localeCode;
	
	/**
	 * Country data handler instance
	 */	
	private $countryInstance;

	/**
	 * Language data handler instance
	 */	
	private $languageInstance;

	/**
	 * Currency data handler instance
	 */	
	private $currencyInstance;
	
	/**
	 * Language original names (ex: lietuviu, Deutsch, eesti)
	 */	
	private $originalNames = false;
	
	public function __construct($localeCode)
	{
		$this->localeCode = $localeCode;  	
	}
	
	/**
	 * Gets country name by code.
	 * $param string $code
	 * @return string
	 */
	public function getCountryName($code) 
	{	  
	  	return $this->getCountryInstance()->getName($code);
	}
	
	/**
	 * Gets array of country names.
	 * @return array
	 */
	public function getAllCountries() 
	{		  	
	  	return $this->getCountryInstance()->getAllCodes();	
	}
		
	/**
	 * Gets array of language names.
	 * @return array
	 */		
	public function getAllLanguages() 
	{	  
	  	return $this->getLanguageInstance()->getAllCodes();	
	}
	
	/**
	 * Gets language name by code
	 * $param string $code
	 * @return string
	 */
	public function getLanguageName($code) 
	{	  	  	
	  	return $this->getLanguageInstance()->getName($code);
	}
	
	/**
	 * Gets language name in its language by code
	 * ex: "lt" will always return "Lietuviu" regardless of the current locale
	 * $param string $code
	 * @return string
	 */
	public function getOriginalLanguageName($code) 
	{	  	  	
	  	if (!$this->originalNames)
	  	{
			include 'I18Nv2/Language/original.php';
			$this->originalNames = $names;
		}
		
		if (isset($this->originalNames[$code]))
		{
		  	return $this->originalNames[$code];
		}
	}

	/**
	 * Gets array of currency names.
	 * @return array
	 */
	public function getAllCurrencies() 
	{	  
	  	return $this->getCurrencyInstance()->getAllCodes();	
	}
	
	/**
	 * Gets currency name by code.
	 * @param string $code
	 * $return string
	 */	 
	public function getCurrencyName($code) 
	{	  
		return $this->getCurrencyInstance()->getName($code);
	}  	  
	
	/**
	 * Returns language data handler instance
	 * $return I18Nv2_Language
	 */	 
	private function getLanguageInstance() {
	  
	  	if (!$this->languageInstance) 
		{	  	  
	  	 	require_once('I18Nv2/Language.php');
			$this->languageInstance = new I18Nv2_Language($this->localeCode);		    
		}
		
		return $this->languageInstance;
	}	
	
	/**
	 * Returns country data handler instance
	 * $return I18Nv2_Country
	 */	 
	private function getCountryInstance() 
	{	  
	  	if (!$this->countryInstance) 
		{	  	  
	  	 	require_once('I18Nv2/Country.php');
			$this->countryInstance = new I18Nv2_Country($this->localeCode);		    
		}
		
		return $this->countryInstance;
	}	
	
	/**
	 * Returns currency data handler instance
	 * $return I18Nv2_Country
	 */	 
	private function getCurrencyInstance() 
	{	  
	  	if (!$this->currencyInstance) 
		{	  	  
	  	 	require_once('I18Nv2/Currency.php');
			$this->currencyInstance = new I18Nv2_Currency($this->localeCode);		    
		}
		
		return $this->currencyInstance;
	}
}

?>