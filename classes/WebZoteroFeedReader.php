<?php
require_once('ZoteroFeedReader.php');

/**
 * Retrieve Feed
 */
class WebZoteroFeedReader implements ZoteroFeedReader
{
	/**
	 * @var ZoteroConfig
	 */
	private $config;

    /**
     * Get config instance
     */
    public function __construct()
	{
        $this->config = plugin_load('helper', 'zotero_config');
	}

    /**
     * Retrieve content from url
     *
     * @return bool|string
     */
    function getFeed()
	{
        $http = new DokuHTTPClient();
        return $http->get($this->config->getUrlForEntries());
	}
}