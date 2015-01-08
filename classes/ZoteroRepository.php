<?php
require_once('ZoteroEntry.php');

/**
 * Stores entries
 */
abstract class ZoteroRepository
{
    /** @var ZoteroEntry[]  */
	protected $entries = array();

    /**
     * @var ZoteroConfig
     */
    protected $config = null;

    /**
     * Get config
     *
     * @param ZoteroConfig $config
     */
    public function __construct(ZoteroConfig $config) {
        $this->config = $config;
    }

    /**
     * @param string $zoteroId
     * @throws ZoteroEntryNotFoundException
     * @return    ZoteroEntry
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
            $msg = sprintf($this->config->getLang('reponoid', hsc($zoteroId)));
			throw new ZoteroEntryNotFoundException($msg);
		}
	}

    /**
     * @param $citeKey
     * @throws ZoteroEntryNotFoundException
     * @return    ZoteroEntry
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

        $msg = sprintf($this->config->getLang('reponocitekey'), hsc($citeKey));
        throw new ZoteroEntryNotFoundException($msg);
	}

    /**
     * @return ZoteroEntry[]
     */
    public function getAllEntries()
	{
		return $this->entries;
	}

    /**
     * @param array $entries
     */
    public function updateAndSaveEntries(array $entries)
	{}

    /**
     * @param array $entries
     */
    public function saveEntries(array $entries)
    {
        $this->entries = array();
        $this->updateAndSaveEntries($entries);
    }
}
