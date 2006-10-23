<?php

interface LCiTranslator
{
	public function translate($key);  
	public function makeText($key, $params);
}

?>