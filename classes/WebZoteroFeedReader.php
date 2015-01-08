<?php
require_once('ZoteroFeedReader.php');

class WebZoteroFeedReader implements ZoteroFeedReader
{
	/**
	 * @var ZoteroConfig
	 */
	private $config;
	
	public function __construct(ZoteroConfig $config)
	{
		$this->config = $config;
	}
	
	function getFeed()
	{
		if (!function_exists('curl_init'))
		{
			throw new Exception("CURL functions are not available.");
		}
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $this->config->getUrlForEntries());
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$feed = curl_exec($ch);
		curl_close($ch);
		return $feed;
	}
}
?>