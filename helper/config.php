<?php
require_once(DOKU_PLUGIN . 'zotero/classes/ZoteroConfig.php');

/**
 * Singleton configuration instance to access Zotero configuration settings
 */
class helper_plugin_zotero_config extends ZoteroConfig
{
    /**
     * Load and checks zotero configuration
     */
    public function __construct()
    {
        $configfile = DOKU_PLUGIN . 'zotero/config.ini';

        if(!file_exists($configfile)) {
            msg($this->getLang('confignotfound'), -1);
            return;
        }

        $this->config = parse_ini_file($configfile, true);

        //validate
        try {
            parent::__construct();
        } catch (Exception $e) {
            msg($e->getMessage(), -1);
        }
    }
}
