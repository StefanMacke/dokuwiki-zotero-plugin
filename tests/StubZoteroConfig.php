<?php
require_once('..' . DIRECTORY_SEPARATOR . 'ZoteroConfig.php');

class StubZoteroConfig extends ZoteroConfig
{
	public function __construct()
	{}
	
	public function setConfig($category, $key, $value)
	{
		$this->config[$category][$key] = $value;
	}
}	
?>