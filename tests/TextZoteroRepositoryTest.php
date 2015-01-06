<?php
require_once('ZoteroRepositoryTest.php');
require_once('StubZoteroConfig.php');
require_once('..' . DIRECTORY_SEPARATOR . 'TextZoteroRepository.php');

class TextZoteroRepositoryTest extends ZoteroRepositoryTest
{
	public function setUp()
	{
		$this->r = new TextZoteroRepository(dirname(__FILE__) . DIRECTORY_SEPARATOR . "TestEntries.txt", new StubZoteroConfig());
	}
}
?>