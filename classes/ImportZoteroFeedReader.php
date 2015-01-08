<?php
require_once('ZoteroFeedReader.php');
require_once('ZoteroParserException.php');

class ImportZoteroFeedReader implements ZoteroFeedReader
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

	private function download($url)
	{
        #return file_get_contents("ZoteroImport.xml");
		echo "Downloading " . $url . "\n";
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		$download = curl_exec($ch);
		curl_close($ch);
		return $download;
	}
	
    private function getDocument($xml)
    {
		$dom = new DomDocument();
        $dom->loadXml($xml);
        return $dom;
    }

	private function getXPath($xml)
	{
		$dom = $this->getDocument($xml);
		$xpath = $this->getXPathForDocument($dom);
		return $xpath;
	}

	private function getXPathForDocument(DOMDocument $dom)
    {
		$xpath = new DomXPath($dom);
		$xpath->registerNameSpace("atom", "http://www.w3.org/2005/Atom");
		$xpath->registerNameSpace("zapi", "http://zotero.org/ns/api");
		return $xpath;
    }
	
	private function getNextUrl(DOMXPath $xpath)
	{
		$next = $xpath->query("//atom:feed/atom:link[@rel='next']")->item(0);
		if ($next != null)
		{
			return $next->getAttribute("href") . "&key=" . $this->config->getConfig("ZoteroAccess", "key");
		}
		return null;
	}
	
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
?>
