<?php
require_once('ZoteroEntry.php');

abstract class ZoteroRepository
{
	protected $entries = array();

	/**
	 * @return	ZoteroEntry
	 */		
	public function getEntryByID($zoteroId)
	{
		$e = @$this->entries[$zoteroId];
		if ($e != null)
		{
			return $e;
		}
		else
		{
			throw new ZoteroEntryNotFoundException("Zotero entry with ID " . $zoteroId . " not found.");
		}
	}

	/**
	 * @return	ZoteroEntry
	 */		
	public function getEntryByCiteKey($citeKey)
	{
		foreach ($this->entries as $e)
		{
			if ($e->getCiteKey() === $citeKey)
			{
				return $e;
			}
		}
		throw new ZoteroEntryNotFoundException("Zotero entry with cite key " . $citeKey . " not found.");
	}

	public function getAllEntries()
	{
		return $this->entries;
	}
	
	public function updateAndSaveEntries(array $entries)
	{}

	public function saveEntries(array $entries)
    {
        $this->entries = array();
        $this->updateAndSaveEntries($entries);
    }
}
?>
