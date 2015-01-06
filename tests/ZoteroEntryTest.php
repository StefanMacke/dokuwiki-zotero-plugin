<?php
require_once('..' . DIRECTORY_SEPARATOR . 'ZoteroEntry.php');

class ZoteroEntryTest extends PHPUnit_Framework_TestCase
{
	const ID = "ABC";
	const CITE_KEY = "Macke2012";
	const TITLE = "Zotero Plugin Documentation";
	const AUTHOR = "Stefan Macke";
	const DATE = "2012";
	
	/**
	 * @var ZoteroEntry
	 */
	private $e;
	
	public function setUp()
	{
		$this->e = new ZoteroEntry(self::ID);
		$this->e->setAuthor(self::AUTHOR);
		$this->e->setCiteKey(self::CITE_KEY);
		$this->e->setDate(self::DATE);
		$this->e->setTitle(self::TITLE);
	}

	public function testAttributes()
	{
		$this->assertEquals(self::ID, $this->e->getZoteroId());
		$this->assertEquals(self::AUTHOR, $this->e->getAuthor());
		$this->assertEquals(self::CITE_KEY, $this->e->getCiteKey());
		$this->assertEquals(self::DATE, $this->e->getDate());
		$this->assertEquals(self::TITLE, $this->e->getTitle());
	}	
	
	public function testShortInfo()
	{
		$this->assertEquals(self::AUTHOR . ": " . self::TITLE . " (" . self::DATE . ")", $this->e->getShortInfo());
		$this->assertEquals("pre " . self::AUTHOR . " - " . self::TITLE . " [" . self::DATE . "]", $this->e->getShortInfo("pre " . ZoteroEntry::AUTHOR_PLACEHOLDER . " - " . ZoteroEntry::TITLE_PLACEHOLDER . " [" . ZoteroEntry::DATE_PLACEHOLDER . "]"));
	}
}
?>
