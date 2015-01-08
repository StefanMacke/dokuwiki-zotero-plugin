<?php
require_once('ZoteroRepositoryTest.php');
require_once('..' . DIRECTORY_SEPARATOR . 'classes/FeedZoteroRepository.php');
require_once('StubZoteroFeedReader.php');
require_once('StubZoteroConfig.php');

class FeedZoteroRepositoryTest extends ZoteroRepositoryTest
{
	public function setUp()
	{
		$feedReader = new StubZoteroFeedReader();
		$config = new StubZoteroConfig();
		$config->setConfig("SourceEntries", "authorFormat", "FIRSTNAME LASTNAME");
		$this->r = new FeedZoteroRepository($feedReader, $config);
	}
}
?>