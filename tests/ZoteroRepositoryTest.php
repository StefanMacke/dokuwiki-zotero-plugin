<?php
require_once('..' . DIRECTORY_SEPARATOR . 'classes/ZoteroRepository.php');

abstract class ZoteroRepositoryTest extends PHPUnit_Framework_TestCase
{
	private $existingEntries = array();
	private $nonExistingEntry;
	
	/**
	 * @var ZoteroRepository
	 */
	protected $r;

	public function __construct()
	{
		$e = new ZoteroEntry("ABC123");
		$e->setAuthor("Stefan Macke");
		$e->setCiteKey("Macke2011");
		$e->setDate("2011");
		$e->setTitle("A nice book");
		$this->existingEntries[0] = $e;

		$e = new ZoteroEntry("BCD234");
		$e->setAuthor("Stefan Werner");
		$e->setCiteKey("Werner2012");
		$e->setDate("2012");
		$e->setTitle("A nicer book");
		$this->existingEntries[1] = $e;

		$e = new ZoteroEntry("CDE345");
		$e->setCiteKey("Merkel2010");
		$this->nonExistingEntry = $e;
	}
	
	public function testExistingEntriesById()
	{
		foreach ($this->existingEntries as $expected)
		{
			$actual = $this->r->getEntryByID($expected->getZoteroId());
			$this->assertTrue($expected->equals($actual));
		}
	}

	/**
	 * @expectedException ZoteroEntryNotFoundException
	 */
	public function testNonExistingEntriesById()
	{
		$e = $this->r->getEntryByID($this->nonExistingEntry->getZoteroId());
	}

	public function testExistingEntriesByKey()
	{
		foreach ($this->existingEntries as $expected)
		{
			$actual = $this->r->getEntryByCiteKey($expected->getCiteKey());
			$this->assertTrue($expected->equals($actual));
		}
	}

	/**
	 * @expectedException ZoteroEntryNotFoundException
	 */
	public function testNonExistingEntriesByKey()
	{
		$e = $this->r->getEntryByCiteKey($this->nonExistingEntry->getCiteKey());
	}
}
?>