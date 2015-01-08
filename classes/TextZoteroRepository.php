<?php
require_once('ZoteroEntryNotFoundException.php');

class TextZoteroRepository extends ZoteroRepository
{
	const COLUMN_ID = 1;
	const COLUMN_SHORT_TITLE = 2;
	const COLUMN_TITLE = 3;
	const COLUMN_AUTHOR = 4;
	const COLUMN_DATE = 5;

	const HEADER = "|**Zotero ID**|**Short Name**|**Title**|**Author**|**Date**|**Info**|";

	private $fileName;
	private $config;

	public function __construct($fileName, ZoteroConfig $config)
	{
		$this->fileName = $fileName;
		$this->config = $config;
		$fileContents = file_get_contents($fileName);
		$this->parseEntries($fileContents);
	}
		
	private function parseEntries($fileContents)
	{
		foreach (explode("\n", $fileContents) as $line)
		{
			$e = $this->parseLine($line);
			if ($e != null)
			{
				$this->entries[$e->getZoteroId()] = $e;
			}
		}
	}
		
	/**
	 * @return ZoteroEntry
	 */
	private function parseLine($line)
	{
		$line = trim($line);
		
		if ($line == "" || $line == self::HEADER)
		{
			return null;
		}

		$line = $this->extractZoteroKey($line);
		$columns = explode("|", $line);
		
		if (count($columns) != 8)
		{
			return null;
		}
		
		$e = new ZoteroEntry($columns[self::COLUMN_ID]);
		$e->setAuthor($columns[self::COLUMN_AUTHOR]);
		$e->setCiteKey($columns[self::COLUMN_SHORT_TITLE]);
		$e->setDate($columns[self::COLUMN_DATE]);
		$e->setTitle($columns[self::COLUMN_TITLE]);
		return $e;
	}
	
	private function extractZoteroKey($text)
	{
		$matches = array();
		if (preg_match("/\[\[.*\|(.*)\]\]/", $text, $matches))
		{
			$text = str_replace($matches[0], $matches[1], $text);
		}
		return $text;
	}
		
	public function updateAndSaveEntries(array $newEntries)
	{
		foreach ($newEntries as $id => $newEntry)
		{
			$this->entries[$id] = $newEntry;
		}
		
		$allEntries = array();
		foreach ($this->entries as $entry)
		{
			$allEntries[$entry->getCiteKey()] = $entry;
		}
		ksort($allEntries);
		$this->saveAllEntriesToFile($allEntries);
	}
	
	private function saveAllEntriesToFile($entries)
	{
		$text = self::HEADER . "\n";
		foreach ($entries as $e)
		{
			$text .= $this->serializeEntry($e);
		}
		file_put_contents($this->fileName, $text);
	}
	
	private function serializeEntry(ZoteroEntry $e)
	{
		$problem = "";
		$title = preg_replace("/[\n\r\t ]{2,}/", " ", $e->getTitle());
		if ($title != $e->getTitle()) { $problem .= "White space in title. "; }
		$citeKey = trim($e->getCiteKey()) . ($e->getCiteKey() == "" ? " " : "");
		if ($citeKey === "") { $problem .= "Empty short title/cite key. "; }
		$citeKeyCount = $this->countCiteKey($e);
		if ($citeKeyCount > 1) { $problem .= "Multiple usages of short title/cite key (" . $citeKeyCount . "). "; }
		
		if ($problem != "") 
		{
			 $problem = "Problem(s): " . $problem; 
		}
		else 
		{
			$problem = " ";
		}
		$entryUrl = $this->config->getUrlForEntry($e);

		return 
			"|" .
			"[[" . $entryUrl . "|" . $e->getZoteroId() . "]]" . "|" .
			$citeKey . "|" .
			$title . "|" .
			$e->getAuthor() . "|" .
			$e->getDate() . ($e->getDate() == "" ? " " : "") . "|" .
			$problem .
			"|\n";
	}
	
	private function countCiteKey(ZoteroEntry $e)
	{
		$count = 0;
		foreach ($this->entries as $entry)
		{
			if ($entry->getCiteKey() === $e->getCiteKey())
			{
				$count++;
			}
		}
		return $count;
	}
}
?>
