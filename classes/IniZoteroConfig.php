<?php
require_once('ZoteroConfig.php');

class IniConfig extends ZoteroConfig
{
	public function __construct()
	{	
		$this->config = parse_ini_file(dirname(__FILE__) . DIRECTORY_SEPARATOR . "config.ini", true);
		parent::__construct();
	}
}	
?>
