<?php

/**
 *
 * @package library.locale
 * @author Integry Systems 
 */
interface LCiTranslator
{
	public function translate($key);  
	public function makeText($key, $params);
}

?>