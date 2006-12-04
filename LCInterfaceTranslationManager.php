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
	 * Definition key => File name mapping
	 */
	private $definitionValueFileMap = array();  	

	/**
	 * Definition file storage directory
	 */
	private static $defFileDir;
	  
	/**
	 * Cache file storage directory (changed translations)
	 */
	private static $cacheFileDir;
	  
	private function __construct($localeCode)
	{
		$this->localeCode = $localeCode;		
	}
	
	public static function create($localeCode)
	{		
		 return new self($localeCode);
	}  
	
	public function setDefinitions($definitions)
	{
	  	$this->definitions = $definitions;
	}
	
	public function loadDefinitions($definitions)
	{
		if (!is_array($definitions))
		{
		  	return false;
		}

		$this->definitions = array_merge($this->definitions, $definitions);	  
		return true;
	}
	
	/**
	 * Returns a single definition value
	 * @param string $localeCode Locale code (E.g. - en, lt, ru)
	 * @return array Definition values (returns false if the definition is not defined)
	 */
	public function getTranslatedDefinitions($localeCode, $useEnglishDefs = true)
	{
		$dir = self::$cacheFileDir . $localeCode;
		$files = $this->getCachedFiles($dir);		
	  	$definitions = array();
		foreach ($files as $file)
		{
		  	$defs = $this->getCacheDefs($file);
		  	$definitions = array_merge($definitions, $defs);
		}
	  	
		// use English definitions for missing translations
		if ($localeCode != 'en')
		{
		  	$english = $this->getTranslatedDefinitions('en');

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
		// get all language definition files

		$files = $this->getDefinitionFiles(self::$defFileDir . $this->localeCode);		
		
		// update each respective cache file
		foreach ($files as $file)
		{
		  	$relPath = substr($file, strlen(self::$defFileDir));
		  	$this->updateCacheFile($relPath);
		}		
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
		  	self::$defFileDir = realpath($dir) . '/';
		  	return true;
		}
	}	
	
	/**
	 * Sets translation cache file directory
	 * @param string $dir Directory path
	 * @return bool Status
	 */
	public function setCacheFileDir($dir)
	{
		if (!is_dir($dir))
		{
		  	return false;
		}  	
		else 
		{
		  	self::$cacheFileDir = realpath($dir) . '/'; 
		  	return true;
		}
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
	  	$dir = realpath($dir) . '/';
		$files = array();

		$iter = new DirectoryIterator($dir);
	  	foreach ($iter as $value) 
		{		    
		    $name = $value->GetFileName();

			if ($value->isFile() && '.lng' == (substr($name, -4))) 
			{			 	
			 	$files[] = $dir . $name;
			}
			else if($value->isDir() && ($name[0] != '.'))
			{
				$files = array_merge($files, $this->getDefinitionFiles($dir . $name));  	
			}
		}

		// fix backslashes
		foreach ($files as $key => $value)
		{
		  	$files[$key] = str_replace(chr(92), '/', $value);
		}

	 	return $files; 	
	}
	
	/**
	 * Gets all translations from file
	 * @param string $file File path
	 * @param bool $relPath Relative path
	 * @return array Definitions (key => value)
	 */
	public function getFileDefs($file, $relPath = false) 
	{
        if ($relPath)
        {
		  	$file = self::$defFileDir . $this->localeCode . '/' . $file;
		}
		
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
    
	/**
	 * Adds language definitions from file
	 * @param string $file File path
	 * @return bool Status
	 */
    public function loadFile($file, $english = false)
    {
	  	// load English definitions first
	  	if (!$english && ('en' != $this->localeCode))
	  	{
			$this->loadFile($file, true);    
		}
		 
		// add locale code to file path
		$locale = $english ? 'en' : $this->localeCode;
		$file = $locale . '/' . $file;
							  
		// check if cached file exists
		if (!$this->isFileCached($file))
		{
		  	// check if language file exists
		  	if (!$this->getLangFilePath($file))
		  	{
			    return false;
			}
		  	
			$this->cacheFile($file);	  
		}

		$this->loadCachedFile($file);
		return true;
	}
	
	/**
	 * Load language definitions from cached file
	 * @param string $filePath File path
	 * @param bool $relativePath File path is relative (e.g. backend/Language.php)
	 * @return array Definitions
	 */
	public function getCacheDefs($filePath, $relativePath = false)
	{
		if ($relativePath)
		{
		  	$filePath = self::$cacheFileDir . $this->localeCode . '/' . $filePath;
		}
			
		if ('.lng' == substr($filePath, -4))
		{
		  	$filePath = substr($filePath, 0, -4) . '.php';
		}

		if ($filePath && file_exists($filePath))
		{				
			include $filePath;
			
			$this->definitionValueFileMap[$this->getRelCachePath($filePath)] = $languageDefs;
			
			return $languageDefs;	
		} 
		else 
		{
		  	return array();
		}
	}
	
	/**
	 * Appends language definitions from cached file
	 * @param string $langFile Relative file path (e.g. - en/Base)
	 */
	public function loadCachedFile($langFile)
	{
		return $this->loadDefinitions( $this->getCacheDefs($this->getCachedFilePath($langFile)) );
	}
		
	/**
	 * Returns relative language file path
	 * @param string $langFile Full language file path
	 * @return string Relative file path
	private function getRelativePath($langFile)
	{
		$path = substr($langFile, strlen(self::$defFileDir));
		return $path;
	}
	 */
	 
	/**
	 * Returns relative language file path by cache file path
	 * @param string $langFile Full cache file path
	 * @return string Relative file path
	 */
	private function getRelCachePath($langFile)
	{
		return substr($langFile, strlen(self::$cacheFileDir) + 3, -4);		
	}			    	

	private function updateCacheFile($file)
	{
		$defFile = self::$defFileDir . $file;
		$cacheFile = substr($file, 0, -4) . '.php';
			
		$cacheDefs = $this->getCacheDefs(self::$cacheFileDir . $cacheFile);
		$defs = $this->getFileDefs($defFile);		
		
		if (is_array($cacheDefs))
		{
			$defs = array_merge($defs, $cacheDefs);  
		}		
		$this->saveCacheData($cacheFile, $defs);		
	}	
	
	/**
	 * Creates initial cache file from default definitions file
	 * @param string $langFile Language definitions cache file path
	 */
	private function cacheFile($langFile)
	{
		$defs = $this->getFileDefs($this->getLangFilePath($langFile));
		$this->saveCacheData($langFile, $defs);
	}

	/**
	 * Saves translation data to cache file
	 * @param string $langFile Language definitions cache file relative path
	 * @param array  $defs Language definitions
	 * @todo The mkdir stuff looks ugly
	 */
	public function saveCacheData($langFile, $defs)
	{
		// remove empty values
		foreach ($defs as $key => $value)
		{
		  	if ('' == $value)
		  	{
			    unset($defs[$key]);
			}
		}
		
		// save
		$dump = '<?php $languageDefs = ' . var_export($defs, true) . '; ?>';
		$path = $this->getCachedFilePath($langFile);

		if (!file_exists(dirname($path)))
		{
			$dir = dirname($path);
			$dir = str_replace(chr(92), '/', $dir);
			$dir = str_replace('/', DIRECTORY_SEPARATOR, $dir);
			mkdir($dir, 0, true);
		}
		
		file_put_contents($path, $dump);				  		  
	}
	
	/**
	 * Return a single translation value by file name and translation key
	 * @param string $langFile File path
	 * @param string $key Translation key
	 * @return string Translation value
	 */
	public function getValue($langFile, $key)
	{
		// load language file
		if (!isset($this->definitionValueFileMap[$langFile]))
		{			
			$this->loadCachedFile($this->localeCode . '/' . $langFile);  
		}

		if (isset($this->definitionValueFileMap[$langFile]) && isset($this->definitionValueFileMap[$langFile][$key]))
		{
		  	return $this->definitionValueFileMap[$langFile][$key];
		}  
	}
	
	/**
	 * Update a single translation value by file name and translation key
	 * @param string $langFile Relative file path (without locale code)
	 * @param string $key Translation key
	 * @param string $value Translation value
	 * @return bool Status 
	 */
	public function updateValue($langFile, $key, $value)
	{
		if (!isset($this->definitionValueFileMap[$langFile]) || !isset($this->definitionValueFileMap[$langFile][$key]))
		{
		  	return false;
		}

		$this->definitionValueFileMap[$langFile][$key] = $value;	  
		$langFilePath = $this->localeCode . '/' . $langFile;
		$this->saveCacheData($langFilePath, $this->definitionValueFileMap[$langFile]);
		return true;
	}

	/**
	 * Determine which file the particular definition key belongs to
	 * @param string $key Translation key
	 * @param string $value Translation key
	 * @return string File path
	 */
	public function getFileByDefKey($key, $value = '')
	{
		foreach ($this->definitionValueFileMap as $file => $values)
		{
		  	if (isset($values[$key]) && (('' == $value) || ($values[$key] == $value)))
		  	{
				return $file;
			}
		}
		return false;	  
	}

	/**
	 * Determines if the language file has been cached already
	 * @param string $langFile File path
	 * @return bool Status
	 */
	private function isFileCached($langFile)
	{
		$path = $this->getCachedFilePath($langFile); 
		if (!file_exists($path))
		{
		  	return false;
		} else 
		{
			return true;  
		}
	}

	/**
	 * Returns full language file path
	 * @param string $langFile Relative language file path
	 * @return string File path
	 */
	private function getLangFilePath($langFile)
	{
		$path = self::$defFileDir . $langFile . '.lng';
		return file_exists($path) ? $path : false;
	}

	/**
	 * Returns full cached file path
	 * @param string $langFile Relative language file path
	 * @return bool Status
	 */
	private function getCachedFilePath($langFile)
	{
		return self::$cacheFileDir . $langFile . '.php';
	}

	/**
	 * Returns all cache files in given directory (recursive)
	 * @param string $dir 
	 * @return array Files
	 */
	private function getCachedFiles($dir)
	{
	  	if (!$dir || !file_exists($dir))
	  	{
		    return array();
		}
		
		$dir = realpath($dir) . '/';
		$files = array();
		$iter = new DirectoryIterator($dir);
	  	foreach ($iter as $value) 
		{		    
		    $name = $value->GetFileName();
			if ($value->isFile() && '.php' == (substr($name, -4))) 
			{			 	
			 	$files[] = $dir . $name;
			}
			else if($value->isDir() && ($name[0] != '.'))
			{
				$files = array_merge($files, $this->getCachedFiles($dir . $name));  	
			}
		}

		return $files;
	}

}

?>