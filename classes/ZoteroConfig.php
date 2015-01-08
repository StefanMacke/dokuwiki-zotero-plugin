<?php
require_once("ZoteroConfigException.php");

abstract class ZoteroConfig
{
	const FILE_ZOTERO_IDS = "ZoteroIds.txt";
	const FILE_ZOTERO_ENTRIES = "ZoteroEntries.txt";
	const ENTRY_URL = "http://www.zotero.org/USERNAME/items/ENTRYID";
	const ENTRIES_URL = "https://api.zotero.org/users/USERID/items?content=json&itemType=-attachment&key=ZOTEROKEY";
	
	protected $config = array();
	
	public function __construct()
	{	
		if (!$this->usernameIsValid())
		{
			throw new ZoteroConfigException("Invalid Zotero username in config file.");
		}

		if ($this->autoupdateIsActivated() && !$this->keyIsValid())
		{
			throw new ZoteroConfigException("Invalid Zotero key in config file.");
		}

		if (!isset($this->config['WikiOutput']['parentheses']) || $this->config['WikiOutput']['parentheses'] == "")
		{
			$this->config['WikiOutput']['parentheses'] = ",";
		}

		if (!isset($this->config['SourceEntries']['authorFormat']) || $this->config['SourceEntries']['authorFormat'] == "")
		{
			$this->config['SourceEntries']['authorFormat'] = "LASTNAME, FIRSTNAME";
		}
		if (!isset($this->config['WikiOutput']['titleFormat']) || $this->config['WikiOutput']['titleFormat'] == "")
		{
			$this->config['WikiOutput']['titleFormat'] = "AUTHOR: TITLE (DATE)";
		}
	}
	
	private function usernameIsValid()
	{
		return isset($this->config['ZoteroAccess']['username']) && $this->config['ZoteroAccess']['username'] != "" && $this->config['ZoteroAccess']['username'] != "YOURUSERNAME";
	}

	private function keyIsValid()
	{
		return isset($this->config['ZoteroAccess']['key']) && $this->config['ZoteroAccess']['key'] != "" && $this->config['ZoteroAccess']['key'] != "YOURZOTEROKEY";
	}

	private function autoupdateIsActivated()
	{
		return isset($this->config['ZoteroAccess']['autoupdate']) && $this->config['ZoteroAccess']['autoupdate'] == true;
	}

	public function getConfig($category, $key)
	{
		$value = @$this->config[$category][$key];
		return $value;
	}
	
	public function getCachePage()
	{
		$cachePage = $this->config['SourceEntries']['cachePage'];
		if ($cachePage != "")
		{
			$wikiPagesDir = dirname(__FILE__) . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . ".." . DIRECTORY_SEPARATOR . "data" . DIRECTORY_SEPARATOR . "pages" . DIRECTORY_SEPARATOR;
			if (strstr($cachePage, ":"))
			{
				$parts = explode(":", $cachePage);
				$cachePage = $parts[0] . DIRECTORY_SEPARATOR . $parts[1];
			}
			$cachePage = realpath($wikiPagesDir) . DIRECTORY_SEPARATOR . $cachePage . ".txt";
		}
		else
		{
			$cachePage = self::FILE_ZOTERO_ENTRIES;
		}
		return $cachePage;
	}

	public function getUrlForEntry(ZoteroEntry $entry)
	{
		$url = str_replace("USERNAME", $this->getConfig("ZoteroAccess", "username"), self::ENTRY_URL);
		$url = str_replace("ENTRYID", $entry->getZoteroId(), $url);
		return $url;
	}

	public function getUrlForEntries()
	{
		$url = str_replace("USERID", $this->getConfig("ZoteroAccess", "userid"), self::ENTRIES_URL);
		$url = str_replace("ZOTEROKEY", $this->getConfig("ZoteroAccess", "key"), $url);
		return $url;
	}
}
?>
