<?php

require_once('LocaleMaketext.php');

/**
 *
 * @package library.locale.maketext
 * @author Integry Systems 
 */
class LCMaketext_ru extends LocaleMaketext
{  
  	function quant($args)
	{
		$num   = $args[0];
		$forms = array_slice($args,1);

		$_return = "$num ";
	
		if ($num % 100 > 10 && $num % 100 < 20) 
		{
		  	$_return .= $forms[2];		  	
		} 
		else if ($num % 10 == 1) 
		{		  
		  	$_return .= $forms[0];	
		} 
		else if ($num % 10 == 0) 
		{		  
		  	$_return .= $forms[2];	
		} 
		else if ($num % 10 < 5)
		{		  
		  	$_return .= $forms[1];
		} 
		else 
		{		  
		  	$_return .= $forms[2];
		} 
 
		return $_return;
	}		
} 

?>