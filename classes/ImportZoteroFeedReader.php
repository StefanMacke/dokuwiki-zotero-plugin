<?php
require_once('ZoteroFeedReader.php');
require_once('ZoteroParserException.php');

/**
 * Class ImportZoteroFeedReader
 */
class ImportZoteroFeedReader implements ZoteroFeedReader
{
	/**
	 * @var ZoteroConfig
	 */
	private $config;

    /**
     * @param ZoteroConfig $config
     */
    public function __construct(ZoteroConfig $config)
	{
		$this->config = $config;
	}

    /**
     * Returns whole feed downloaded from url
     *
     * @return string the XML
     */
    function getFeed()
	{
		$feed = $this->download($this->config->getUrlForEntries());
		$dom = $this->getDocument($feed);

		$xpath = $this->getXPath($feed);
		$nextUrl = $this->getNextUrl($xpath);
		while ($nextUrl != null)
		{
			$additionalFeed = $this->download($nextUrl);
			$xpath = $this->getXPath($additionalFeed);
			$this->addEntriesToMainFeed($dom, $xpath);
			$xml = $dom->saveXML();
			file_put_contents("ZoteroImport.xml", $xml);
			$nextUrl = $this->getNextUrl($xpath);
		}

		$xml = $dom->saveXML();
		return $xml;
	}

    /**
     * Download and return the content from given link
     *
     * @param string $url
     * @return bool|string
     */
    private function download($url)
	{
        $http = new DokuHTTPClient();
        return $http->get($url);
	}

    /**
     * Returns DomDocument builded from XML string
     *
     * @param $xml
     * @return DomDocument
     */
    private function getDocument($xml)
    {
		$dom = new DomDocument();
        $dom->loadXml($xml);
        return $dom;
    }

    /**
     * Get XPath from given xml string
     *
     * @param string $xml
     * @return DomXPath
     */
    private function getXPath($xml)
	{
		$dom = $this->getDocument($xml);
		$xpath = $this->getXPathForDocument($dom);
		return $xpath;
	}

    /**
     * Get XPath from given DOM Document
     *
     * @param DOMDocument $dom
     * @return DomXPath
     */
    private function getXPathForDocument(DOMDocument $dom)
    {
		$xpath = new DomXPath($dom);
		$xpath->registerNameSpace("atom", "http://www.w3.org/2005/Atom");
		$xpath->registerNameSpace("zapi", "http://zotero.org/ns/api");
		return $xpath;
    }

    /**
     * Get url to next part of the feed
     *
     * @param DOMXPath $xpath
     * @return null|string
     */
    private function getNextUrl(DOMXPath $xpath)
	{
		$next = $xpath->query("//atom:feed/atom:link[@rel='next']")->item(0);
		if ($next != null)
		{
			return $next->getAttribute("href") . "&key=" . $this->config->getConfig("ZoteroAccess", "key");
		}
		return null;
	}

    /**
     * Stores entries from XPath in the DOMDocument
     *
     * @param DOMDocument $dom
     * @param DOMXPath    $xpath
     */
    private function addEntriesToMainFeed(DOMDocument $dom, DOMXPath $xpath)
	{
        $domXpath = $this->getXPathForDocument($dom);
		$root = $xpath->query('/atom:feed')->item(0);

		$r = $xpath->query('//atom:feed/atom:entry');
		foreach ($r as $node)
		{
			$newNode = $dom->importNode($node, true);
			$dom->documentElement->appendChild($newNode);
		}
	}
}
