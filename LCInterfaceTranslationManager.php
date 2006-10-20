<?php

class LCInterfaceTranslationManager
{
	/**
	 * Current locale code (en, lt, ru)
	 */
  	private $localeCode;
  	
	/**
	 * Interface translation definitions
	 */
	private $definitions = array();  	

	/**
	 * Definition file storage directory
	 */
	private $defFileDir;
	  
	private function __construct($localeCode)
	{
		$this->localeCode = $localeCode;		
	}
	
	public static function create($localeCode)
	{
		try 
		{
			$definitions = self::getTranslatedDefinitions($localeCode);	
		} 
		catch (Exception $ex) 
		{
			// no definitions defined for this locale, so the translation manager cannot be created
			return false;
		}
				
		$instance =& new self($localeCode);
		$instance->setDefinitions($definitions);

		return $instance;			
	}  
	
	public function setDefinitions($definitions)
	{
	  	$this->definitions = $definitions;
	}
	
	/**
	 * Returns a single definition value
	 * @param string $localeCode Locale code (E.g. - en, lt, ru)
	 * @return array Definition values (returns false if the definition is not defined)
	 */
	public static function getTranslatedDefinitions($localeCode, $useEnglishDefs = true)
	{
		try 
		{
			$defs = ActiveRecord::getInstanceById('InterfaceTranslation', array('ID' => $localeCode), true, true);		
		} 
		catch (Exception $ex) 
		{
			throw $ex;
		}
	  	
		$definitions = unserialize((string)$defs->interfaceData->get());					

		if (!is_array($definitions))
		{
		  	$definitions = array();
		}
		
		// use English definitions for missing translations
		if ($localeCode != 'en')
		{
		  	$english = self::getTranslatedDefinitions('en');

			foreach ($english as $key => $value)
			{
			  	if (!isset($definitions[$key]) || $definitions[$key] == '')
			  	{
				 	$definitions[$key] = ($useEnglishDefs ? $value : '');
				}
			}		  	
		}

		return $definitions;
	}

	/**
	 * Returns a single definition value
	 * @param string $key Definition key
	 * @return string Definition value (returns false if the definition is not defined)
	 */
	public function getDefinition($key)
	{
		if (!empty($this->definitions[$key])) 
		{		  
			return $this->definitions[$key];
		} 
		else 
		{		  
		  	return false;
		}	  		
	}
	
	/**
	 * Checks for newly added default translation definitions and adds them to translations
	 */	
	public function updateDefinitions()
	{
		// get translated definitions
		$translated = self::getTranslatedDefinitions($this->localeCode);
		
		// get default definitions
		$default = $this->getDefaultDefinitions($this->defFileDir);

		// add non-translated definitions
		foreach ($default as $key => $value)
		{
		 	if (!array_key_exists($key, $translated))
			{
			  	$translated[$key] = $value;
			} 	
		}

		$this->saveDefinitions($translated);
	}
	
	/**
	 * Sets translation file directory
	 * @param string $dir Directory path
	 * @return bool Status
	 */
	public function setDefinitionFileDir($dir)
	{
		if (!is_dir($dir))
		{
		  	return false;
		}  	
		else 
		{
		  	$this->defFileDir = realpath($dir);
		  	return true;
		}
	}	
	
	/**
	 * Save definitions to database
	 * @todo delegate to a separate strategy class - as this data can be stored in file/shared memory/etc as well
	 */	
	public function saveDefinitions($defs)
	{
		$defInst = ActiveRecord::getInstanceById("InterfaceTranslation", $this->localeCode, true, true);
		$defInst->interfaceData->set(serialize($defs));
		$defInst->save();	  		  
	}
	
	/**
	 * Gets all translations.
	 * @return array
	 * @todo strtolower
	 */	
	public static function getDefaultDefinitions($dir) 
	{	  
	  	$files = self::getDefinitionFiles($dir);
	  	$defs = array();
		foreach ($files as $file)
	  	{
		 	$defs = array_merge($defs, self::getFileDefs($dir .'/' . $file));   
		}
		
		return $defs;
	}	
	
	/**
	 * Gets all translation definition files
	 * @param string $dir File directory
	 * @return array List of files
	 */
	public function getDefinitionFiles($dir) 
	{	  
	  	$files = array();

		$iter = new DirectoryIterator($dir);
	  	foreach ($iter as $value) 
		{		    
		    if ($value->isFile() && '.lng' == (substr($name = $value->GetFileName(), -4))) 
			{			 	
			 	$files[] = $name;
			}
		}

	 	return $files; 	
	}
	
	/**
	 * Gets all translations from file
	 * @param string $file File path
	 * @return array Definitions (key => value)
	 */
	public function getFileDefs($file) 
	{
        if (!file_exists($file))
        {          
            return false;
        }

        $defs = array();

        $f = fopen($file,'r');
        while (!feof($f)) 
		{          
            $s = chop(fgets($f));

            if (strlen($s) == 0 || $s{0} == '#')
            {
			 	continue; 
			}

            list($key, $value) = explode('=', $s, 2);
            $defs[$key] = $value;
        }
        fclose($f);
        
        return $defs;
    }	   
}

?>