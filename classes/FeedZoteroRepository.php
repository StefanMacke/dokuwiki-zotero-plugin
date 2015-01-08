<?php
require_once('ZoteroRepository.php');
require_once('ZoteroEntryNotFoundException.php');
require_once('ZoteroFeedReader.php');
require_once('ZoteroConfig.php');

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
	 * @var ZoteroConfig
	 */
	private $config;
		
	public function __construct(ZoteroFeedReader $feedReader, ZoteroConfig $config)
	{
		$this->config = $config;
		$this->parseEntries($feedReader->getFeed());
	}
		
	private function parseEntries($feed)
	{
		$this->dom = new DomDocument();
    	$this->dom->loadXml($feed);
		$this->createXPath();

		$r = $this->xpath->query('//atom:feed/atom:entry');
		foreach ($r as $node)
		{
			$itemType = $this->parseItemType($node);
			if ($itemType == "note" || $itemType == "attachment")
			{
				continue;
			}
			$e = $this->createEntry($node);
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
	 * @return ZoteroEntry
	 */
	private function createEntry($node)
	{
		$zoteroId = $this->parseId($node);
		$e = new ZoteroEntry($zoteroId);
		$this->parseData($node, $e);
		return $e;
	}

	private function parseItemType($node)
	{
		$item = $this->xpath->query("./zapi:itemType", $node)->item(0);
		if ($item == null)
		{
			throw new ZoteroParserException("Zotero item type could not be found in node " . $node);
		}
		return $item->nodeValue;
	}

	private function parseId($node)
	{
		$item = $this->xpath->query("./zapi:key", $node)->item(0);
		if ($item == null)
		{
			throw new ZoteroParserException("Zotero ID could not be found in node " . $node);
		}
		return $item->nodeValue;
	}
	
	private function parseData($node, ZoteroEntry $e)
	{
		$item = $this->xpath->query("./atom:content", $node)->item(0);
		if ($item == null)
		{
			throw new ZoteroParserException("Entry content could not be found in node " . $node);
		}
		$json = $item->nodeValue;
		$data = json_decode($json);
		
		$e->setAuthor($this->parseAuthor($data));
		if (isset($data->title)) { $e->setTitle(html_entity_decode($data->title)); }
		if (isset($data->shortTitle)) { $e->setCiteKey(html_entity_decode($data->shortTitle)); }
		if (isset($data->date)) { $e->setDate(html_entity_decode($data->date)); }
	}
	
	private function parseAuthor($data)
	{
		if (count($data->creators) == 0)
		{
			return "Author not specified";
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
			return "Author not specified";
		}
		if (count($data->creators) > 1)
		{
			$authorName .= " et.al.";
		}
		return $authorName;
	}
}
?>
