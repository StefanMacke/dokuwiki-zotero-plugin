<?php
require_once('ZoteroEntryNotFoundException.php');

/**
 * Retrieve and stores entries from a wikipage
 */
class TextZoteroRepository extends ZoteroRepository
{
    const COLUMN_ID             = 1;
    const COLUMN_SHORT_TITLE    = 2;
    const COLUMN_TITLE          = 3;
    const COLUMN_AUTHOR         = 4;
    const COLUMN_DATE           = 5;
    const COLUMN_PAGES          = 6;

    //old header, backward compatible
    const ENGLISHHEADER         = "|**Zotero ID**|**Short Name**|**Title**|**Author**|**Date**|**Info**|";

	var $header                 = "^ %s ^ %s ^ %s ^ %s ^ %s ^ %s ^ %s ^";

    /**
     * @var string
     */
    private $fileName;

    /**
     * Load local repo page in this instance
     *
     * @param ZoteroConfig $config
     */
    public function __construct(ZoteroConfig $config)
	{
		parent::__construct($config);

        $this->header = sprintf(
            $this->header,
            $this->config->getLang('zoteroid'),
            $this->config->getLang('shortname'),
            $this->config->getLang('title'),
            $this->config->getLang('author'),
            $this->config->getLang('date'),
            $this->config->getLang('pages'),
            $this->config->getLang('info')
        );

        $this->fileName = $this->config->getCachePage();

        if(file_exists($this->fileName)) {
            $fileContents = file_get_contents($this->fileName);
            $this->parseEntries($fileContents);
        }
	}

    /**
     * Parse and add entries from local repository page
     *
     * @param string $fileContents
     */
    private function parseEntries($fileContents)
	{
		foreach (explode("\n", $fileContents) as $line)
		{
			$entry = $this->parseLine($line);
			if ($entry != null)
			{
				$this->entries[$entry->getZoteroId()] = $entry;
			}
		}
	}

    /**
     * Try to parse line to an entry
     *
     * @param string $line
     * @return null|ZoteroEntry
     */
	private function parseLine($line)
	{
		$line = trim($line);

		if ($line == "" || $line == self::ENGLISHHEADER || $line == $this->header)
		{
			return null;
		}

		$line = $this->extractZoteroKey($line);
		$columns = explode("|", $line);

        $count = count($columns);
		if (!($count == 8 || $count == 9))
		{
			return null;
		}

		$entry = new ZoteroEntry($columns[self::COLUMN_ID]);
		$entry->setAuthor($columns[self::COLUMN_AUTHOR]);
		$entry->setCiteKey($columns[self::COLUMN_SHORT_TITLE]);
		$entry->setDate($columns[self::COLUMN_DATE]);
		$entry->setTitle($columns[self::COLUMN_TITLE]);
        if($count == 9) {
            $entry->setPages($columns[self::COLUMN_PAGES]);
        }

        return $entry;
	}

    /**
     * Extract key from link title
     *
     * @param string $content
     * @return string updated line
     */
    private function extractZoteroKey($content)
	{
		$matches = array();
		if (preg_match("/\[\[.*\|(.*)\]\]/", $content, $matches))
		{
            $content = str_replace($matches[0], $matches[1], $content);
		}
		return $content;
	}

    /**
     * @param ZoteroEntry[] $newEntries
     */
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

    /**
     * @param ZoteroEntry[] $entries
     */
    private function saveAllEntriesToFile($entries)
	{
		$text = $this->header . "\n";
		foreach ($entries as $entry)
		{
			$text .= $this->buildTablerow($entry);
		}
        io_saveFile($this->fileName, $text);
	}

    /**
     * Build a tableline from this entry
     *
     * @param ZoteroEntry $entry
     * @return string
     */
    private function buildTablerow(ZoteroEntry $entry)
    {
        $problem = "";
        $title = preg_replace("/[\n\r\t ]{2,}/", " ", $entry->getTitle());
        if ($title != $entry->getTitle()) {
            $problem .= $this->config->getLang('problemwhitespace') . " ";
        }
        $citeKey = trim($entry->getCiteKey());
        if ($citeKey === "") {
            $problem .= $this->config->getLang('problemnocitekey') . " ";
        }
        $citeKeyCount = $this->countCiteKeyUsage($entry);
        if ($citeKeyCount > 1) {
            $problem .= sprintf($this->config->getLang('problemmulticitekeys'), hsc($citeKey)) . " ";
        }

        if ($problem != "")
        {
            $problem = sprintf($this->config->getLang('problems'), hsc($problem));
        }

        $entryUrl = $this->config->getUrlForEntry($entry);

        return
            "|" .
            "[[" . $entryUrl . "|" . $entry->getZoteroId() . "]]" . "|" .
            $citeKey . " |" .
            $title . " |" .
            $entry->getAuthor() . " |" .
            $entry->getDate() . " |" .
            $entry->getPages() . " |" .
            $problem . " |\n";
    }

    /**
     * Count number of entries with citekey equal to given entry
     *
     * @param ZoteroEntry $e
     * @return int
     */
    public function countCiteKeyUsage(ZoteroEntry $e)
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
