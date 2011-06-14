<?php

/**
 * Builds an array of original language names
**/

class BuildOriginalNameArray 
{

	private $codes = array();
	
	public $names = array();
	
	function __construct()
	{
		$dir = new DirectoryIterator(dirname(__file__));
		
		foreach ($dir as $file) 
		{
			if (!$dir->isFile()) 
			{
				continue;
			}
		   
			list($code, $bs) = explode('.', $file);
		   
			if (strlen($code) != 2)
			{
				continue;     	
			}
		   
			include $file;		   
		   
		   	$this->names[$code] = $this->codes[$code];		   	
		}
	}
}

$build = new BuildOriginalNameArray();
//print_r($build->names);

$f = fopen('original.php', 'w');
fwrite($f, '<?php $names = ' . var_export($build->names, true) . ';');
fclose($f);

?>