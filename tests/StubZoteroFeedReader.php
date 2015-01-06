<?php
require_once('..' . DIRECTORY_SEPARATOR . 'ZoteroFeedReader.php');

class StubZoteroFeedReader implements ZoteroFeedReader
{
	private $fileName;
	
	public function __construct($fileName = "TestEntries.xml")
	{
		$this->fileName = $fileName;
	}
	
	function getFeed()
	{
		return file_get_contents(dirname(__FILE__) . DIRECTORY_SEPARATOR . $this->fileName);
	}
}
?>