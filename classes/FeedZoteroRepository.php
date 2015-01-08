<?php
require_once('ZoteroRepository.php');
require_once('ZoteroEntryNotFoundException.php');
require_once('ZoteroFeedReader.php');
require_once('ZoteroConfig.php');

/**
 * Retrieve and temporary stores entries from the online zotero repository
 */
class FeedZoteroRepository extends ZoteroRepository
{
	/**
	 * @var DomDocument
	 */
	private $dom;
	
	/**
	 * @var DomXPath
	 */
	private $xpath;

    /**
     * @param ZoteroConfig     $config
     * @param ZoteroFeedReader $feedReader
     */
    public function __construct(ZoteroConfig $config, ZoteroFeedReader $feedReader)
	{
        parent::__construct($config);

		$this->parseEntries($feedReader->getFeed());
	}

    /**
     * Parse given feed and store complete entries
     *
     * @param string $feed
     */
    private function parseEntries($feed)
	{                                   dbglog($feed);
		$this->dom = new DomDocument();
    	$this->dom->loadXml($feed);
		$this->createXPath();

		$domnodelist = $this->xpath->query('//atom:feed/atom:entry');
		foreach ($domnodelist as $domnode)
		{
			$itemType = $this->parseItemType($domnode);
			if ($itemType == "note" || $itemType == "attachment")
			{
				continue;
			}
			$e = $this->createEntry($domnode);
			if ($e->getZoteroId() !== "" && $e->getAuthor() !== "" && $e->getTitle() !== "")
			{
				$this->entries[$e->getZoteroId()] = $e;
			}
		}
	}

	private function createXPath()
	{
		$this->xpath = new DomXPath($this->dom);
		$this->xpath->registerNameSpace("atom", "http://www.w3.org/2005/Atom");
		$this->xpath->registerNameSpace("zapi", "http://zotero.org/ns/api");
	}

    /**
     * Return new ZoteroEntry with content from given node
     *
     * @param DOMNode $node
     * @return ZoteroEntry
     */
	private function createEntry($node)
	{
		$zoteroId = $this->parseId($node);
		$e = new ZoteroEntry($zoteroId);
		$this->parseData($node, $e);
		return $e;
	}

    /**
     * Return item type
     *
     * @param DOMNode $node
     * @return string
     * @throws ZoteroParserException
     */
    private function parseItemType($node)
	{
		$itemtype = $this->xpath->query("./zapi:itemType", $node)->item(0);
		if ($itemtype == null)
		{
			throw new ZoteroParserException(sprintf($this->config->getLang('feeditemtypenotfound'), hsc($node)));
		}
		return $itemtype->nodeValue;
	}

    /**
     * Return item id
     *
     * @param DOMNode $node
     * @return string
     * @throws ZoteroParserException
     */
    private function parseId($node)
	{
		$item = $this->xpath->query("./zapi:key", $node)->item(0);
		if ($item == null)
		{
			throw new ZoteroParserException(sprintf($this->config->getLang('feedidnotfound'), hsc($node)));
		}
		return $item->nodeValue;
	}

    /**
     * Parse item content and store at given ZoteroEntry
     *
     * @param DOMNode     $node
     * @param ZoteroEntry $entry
     * @throws ZoteroParserException
     */
    private function parseData($node, ZoteroEntry $entry)
	{

		$item = $this->xpath->query("./atom:content", $node)->item(0);
		if ($item == null)
		{
			throw new ZoteroParserException(sprintf($this->config->getLang('feedcontentnotfound'), hsc($node)));
		}
		$json = $item->nodeValue;
		$data = json_decode($json);
        print_r($data);
		$entry->setAuthor($this->parseAuthor($data));
		if (isset($data->title))        { $entry->setTitle(hsc($data->title)); }
		if (isset($data->shortTitle))   { $entry->setCiteKey(hsc($data->shortTitle)); }
		if (isset($data->date))         { $entry->setDate(hsc($data->date)); }
        if (isset($data->pages))        { $entry->setPages(hsc($data->pages)); }
	}

    /**
     * Return author name (only first)
     *
     * @param $data
     * @return mixed|string
     */
    private function parseAuthor($data)
	{
		if (count($data->creators) == 0)
		{
			return $this->config->getLang('feednoauthor');
		}
		$authorName = "";
		$author = $data->creators[0];
		if (isset($author->firstName) && isset($author->lastName))
		{
			$firstName = $author->firstName;
			$lastName =$author->lastName;
			$authorFormat = $this->config->getConfig("SourceEntries", "authorFormat");
			$authorName = str_replace("FIRSTNAME", $firstName, $authorFormat);
			$authorName = str_replace("LASTNAME", $lastName, $authorName);
		}
		elseif (isset($author->name))
		{
			$authorName = $author->name;
		}
		if ($authorName == "")
		{
			return $this->config->getLang('feednoauthor');
		}
		if (count($data->creators) > 1)
		{
			$authorName .= $this->config->getLang('feedmultiauthorabbr');
		}
		return $authorName;
	}
}
