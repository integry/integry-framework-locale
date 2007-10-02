<?php

/**
 * Creates a MakeText translation handler instance for required locale
 *
 * @return LocaleMaketext
 * @author Integry Systems
 * @package library.locale.maketext
 */
class LCMakeTextFactory
{
	/**
	 * Creates a MakeText translation handler instance for required locale
	 * @return LCMakeText
	 */	
  	function create($locale)
    {    
      	$classname = 'LCMakeText_' . strtolower($locale);
		$classfile = dirname(__FILE__) . '/' . $classname . '.php';

	  	if (file_exists($classfile)) 
		{
		 	require_once($classfile);
			$instance = new $classname;
		}
		else
		{
		  	require_once('LocaleMaketext.php');
		  	$instance = new LocaleMaketext;
		}
		
		return $instance;
    }
} 
?>