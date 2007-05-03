<?php

$keyMap = array(

	'yyyy' => '%Y',
	'yy' => '%y',
	
	'MMMM' => '%F',
	'MMM' => '%M',
	'MM' => '%m',
	'M' => '%n',
	
    'dd' => '%d',
	'd' => '%j',            

	'EEEE' => '%l',
	'EEE' => '%D',
	
	'hh' => '%h',
	'h' => '%g',
	
    'HH' => '%G',
    'H'	=> '%H',			

	'mm' => '%i',
	'm' => '%i',
	'ss' => '%s',
	's' => '%s',
    	
	'G' => '%E',
	'a' => '%X',
	
	'z' => '%T',
                		
);


foreach (new DirectoryIterator(getcwd()) as $file)
{    
    $name = $file->getFileName();
    
    unset($data);
    
    if (substr($name, -4) == '.php' && $name != 'test.php')
    {
        include $name; 
    }
    
    if (!isset($data['DateTimePatterns']))
    {
        continue;
    }
    
    foreach ($data['DateTimePatterns'] as &$s)
    {
        $s = str_replace("''", "'`", $s);
        $p = explode("'", $s);
        
        foreach ($p as $key => $value)
        {
            if (!($key % 2))
            {
                $value = strtr($value, $keyMap);
            }
        
            $p[$key] = $value;
        }
    
        $s = implode('', $p);    
        $s = str_replace("`", "'", $s);
    }
    
    /*
    file_put_contents($name, '<?php $data = ' . var_export($data, true) . '; ?>');
    */
    
    var_dump($name);
    var_dump(var_export($data['DateTimePatterns'], true));
}

?>