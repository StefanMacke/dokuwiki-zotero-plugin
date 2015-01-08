<?php
require_once('ZoteroConfig.php');

class IniConfig extends ZoteroConfig
{
	public function __construct()
	{
		$this->config = parse_ini_file(DOKU_PLUGIN . 'zotero/config.ini', true);
		parent::__construct();
	}
}
