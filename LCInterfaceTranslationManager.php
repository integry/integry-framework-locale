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
		$definitions = self::getDefinitions($localeCode);
		
		// no definitions defined for this locale, so the translation manager cannot be created
		if (!$definitions)
		{
		  	return false;
		}
		
		$instance = new self();
		$instance->setDefinitions($definitions);
				
		return $instance;			
	}  
	
	public function setDefinitions($definitions)
	{
	  	$this->definitions = $definitions;
	}
	
	public static function getTranslatedDefinitions($localeCode)
	{
		try 
		{
			$defs = ActiveRecord::getInstanceById("InterfaceTranslation", $localeCode, true, true);		
		} 
		catch (Exception $ex) 
		{
			return false;
		}
	  	
		$definitions = unserialize((string)$defs->interfaceData->get());					
	
		return $definitions;
	}

	/**
	 * Returns a single definition value
	 * @param string $key Definition key
	 * @return string Definition value (returns false if the definition is not defined)
	 */
	public function getDefinition($key)
	{
		if (!empty($this->definitions[$key][Locale::value])) 
		{		  
			return $this->definitions[$key][Locale::value];
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
		$default = $this->getDefaultDefinitions($this->getFileDir);
		
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
	  	$iter = new DirectoryIterator($dir);
	  	
	  	$defs = array();
	  	foreach ($iter as $value) 
		{		    
		    if ($value->isFile() && '.lng' == (substr($name = $value->GetFileName(), -4))) 
			{			 	
			 	$short = substr($name, 0, -4);			 	
				$defs += self::GetFileDefs($dir.$name, $short);			 				 									
			}
		}
		return $defs;
	}	
	
	/**
	 * Gets all translations from file (almost copied from k-rates)
	 * @param string $file File name
	 * @return array 
	 */
	private static function getFileDefs($file, $short = '') 
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
            $defs[$key][Locale::value] = $value;
            $defs[$key][Locale::file] = $short;
        }
        fclose($f);
        
        return $defs;
    }	   
    

/* *  *   *    *     *      *       *        *         *          *           *            *             *
	REFACTOR FROM HERE 
* *  *   *    *     *      *       *        *         *          *           *            *             */


	/**
	 * Gets all defition files
	 * @param string $ext
	 * @return array
	 */
	public function getDefinitionFiles($ext = '') 
	{	  
	  	$files = array();
	  	foreach ($this->definitions as $key => $value) {
		    		    
		    if (empty($files[$value[Locale::file]])) {
			  
				$files[$value[Locale::file]] = $value[Locale::file].$ext;
			}
		}
	  	
	 	return $files; 	
	}
	 
	/**
	 * Gets all definitions.
	 */
	public function /*&*/getFullDefinitionsArray() 
	{	  
	  	return $this->definitions;
	}
	 
	 
	public function getDefinitionsFromFile($file) 
	{	  
	  	$defs = array();
	  	foreach ($this->definitions as $key => $value)  {
		    
		    if ($value[Locale::file] == $file) {
	
			    $defs[$key] = $value[Locale::value];
			}
		}
		
		return $defs;
	} 	
		
	public function getAllDefinitions() 
	{
	  	  
	  	$defs = array();
	  	foreach ($this->definitions as $key => $value)  {
		    
		    $defs[$key] = $value[Locale::value];
		}
		
		return $defs;
	}
	
}

?>