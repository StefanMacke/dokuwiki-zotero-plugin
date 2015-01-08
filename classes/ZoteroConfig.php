<?php
require_once("ZoteroConfigException.php");

/**
 * Access setted configuration options
 */
abstract class ZoteroConfig extends DokuWiki_Plugin
{
	const FILE_ZOTERO_IDS = "ZoteroIds.txt";
	const FILE_ZOTERO_ENTRIES = "zotero_entries";
	const ENTRY_URL = "http://www.zotero.org/USERNAME/items/itemKey/ENTRYID";
	const ENTRIES_URL = "https://api.zotero.org/users/USERID/items?content=json&itemType=-attachment&key=ZOTEROKEY";
	
	protected $config = array();

    /**
     * Checks for config validility
     */
    public function __construct()
	{
		if (!$this->usernameIsValid())
		{
			throw new ZoteroConfigException($this->getLang('configinvalidusername'));
		}

		if ($this->autoupdateIsActivated() && !$this->keyIsValid())
		{
			throw new ZoteroConfigException($this->getLang('configinvalidzoterokey'));
		}

		if (!isset($this->config['WikiOutput']['parentheses']) || $this->config['WikiOutput']['parentheses'] == "")
		{
			$this->config['WikiOutput']['parentheses'] = ",";
		}
        $parentheses = explode(",", $this->config['WikiOutput']['parentheses']);
        if (count($parentheses) != 2)
        {
            throw new ZoteroConfigException($this->getLang('configinvalidparentheses'));
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

    /**
     * Is valid username provided
     *
     * @return bool
     */
    private function usernameIsValid()
	{
		return isset($this->config['ZoteroAccess']['username'])
            && $this->config['ZoteroAccess']['username'] != ""
            && $this->config['ZoteroAccess']['username'] != "YOURUSERNAME";
	}

    /**
     * Is valid username provided
     *
     * @return bool
     */
    private function keyIsValid()
	{
		return isset($this->config['ZoteroAccess']['key'])
            && $this->config['ZoteroAccess']['key'] != ""
            && $this->config['ZoteroAccess']['key'] != "YOURZOTEROKEY";
	}

    /**
     * @return bool
     */
    private function autoupdateIsActivated()
	{
		return isset($this->config['ZoteroAccess']['autoupdate'])
            && $this->config['ZoteroAccess']['autoupdate'] == true;
	}

    /**
     * @param $category
     * @param $key
     * @return null
     */
    public function getConfig($category, $key)
	{
		$value = null;
        if(isset($this->config[$category][$key])) {
            $value = $this->config[$category][$key];
        }
		return $value;
	}

    /**
     * @return string
     */
    public function getCachePage()
	{
		$cachePage = $this->config['SourceEntries']['cachePage'];
		if ($cachePage != "")
		{
			$cachePage = wikiFN($cachePage);
		}
		else
		{
			$cachePage = metaFN(self::FILE_ZOTERO_ENTRIES, '.txt');
		}
		return $cachePage;
	}

    /**
     * @param ZoteroEntry $entry
     * @return mixed
     */
    public function getUrlForEntry(ZoteroEntry $entry)
	{
        $url = self::ENTRY_URL;
		$url = str_replace("USERNAME", $this->getConfig("ZoteroAccess", "username"), $url);
		$url = str_replace("ENTRYID", $entry->getZoteroId(), $url);
		return $url;
	}

    /**
     * @return mixed
     */
    public function getUrlForEntries()
	{
        $url = self::ENTRIES_URL;
		$url = str_replace("USERID",    $this->getConfig("ZoteroAccess", "userid"), $url);
		$url = str_replace("ZOTEROKEY", $this->getConfig("ZoteroAccess", "key"), $url);
		return $url;
	}
}
