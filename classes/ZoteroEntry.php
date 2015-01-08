<?php

/**
 * Entity with properties of an entry from Zotero
 */
class ZoteroEntry
{
	const AUTHOR_PLACEHOLDER = "AUTHOR";
	const TITLE_PLACEHOLDER = "TITLE";
	const DATE_PLACEHOLDER = "DATE";
		
	private $zoteroId;
	private $citeKey;
	private $title;
	private $author;
	private $date;
    private $pages = '';

    /**
     * @param $zoteroId
     */
    public function __construct($zoteroId)
	{
		$this->zoteroId = $zoteroId;
	}
	
	public function getZoteroId()
	{
		return $this->zoteroId;
	}

    /**
     * @param $value
     */
    public function setCiteKey($value)
	{
		$this->citeKey = $value;
	}

	public function getCiteKey()
	{
		return $this->citeKey;
	}

    /**
     * @param $value
     */
    public function setTitle($value)
	{
		$this->title = $value;
	}

	public function getTitle()
	{
		return $this->title;
	}

    /**
     * @param $value
     */
    public function setAuthor($value)
	{
		$this->author = $value;
	}

	public function getAuthor()
	{
		return $this->author;
	}

    /**
     * @param $value
     */
    public function setDate($value)
	{
		$this->date = $value;
	}

	public function getDate()
	{
		return $this->date;
	}

    /**
     * @param $value
     */
    public function setPages($value)
    {
        $this->pages = $value;
    }

    public function getPages()
    {
        return $this->pages;
    }

    /**
     * Returns year
     *
     * @return int
     */
    public function getYear()
	{
		$year = 0;
		$matches = array();
		if (preg_match("/([0-9]{4})/", $this->getDate(), $matches))
		{
			return $matches[1];
		}
		return $year;
	}

    /**
     * Parsed short info string
     *
     * @param string $format string with eventually some placeholders
     * @return string
     */
    public function getShortInfo($format = "")
	{
		$date = $this->getYear();
		if ($date === 0)
		{
			$date = $this->getDate();
		}
		
		if ($format == "")
		{
			return $this->getAuthor() . ": " . $this->getTitle() . " (" . $date . ")";
		}
		else
		{
			$title = str_replace(self::AUTHOR_PLACEHOLDER, $this->getAuthor(), $format);
			$title = str_replace(self::TITLE_PLACEHOLDER, $this->getTitle(), $title);
			$title = str_replace(self::DATE_PLACEHOLDER, $date, $title);
			return $title;
		}
	}

    /**
     * @return string
     */
    public function __toString()
	{
		return $this->getShortInfo();
	}

    /**
     * Is given object equal to this object
     *
     * @param ZoteroEntry $other
     * @return bool
     */
    public function equals(ZoteroEntry $other)
	{
		return $this->getZoteroId() === $other->getZoteroId()
			&& $this->getCiteKey() === $other->getCiteKey()
			&& $this->getAuthor() === $other->getAuthor()
			&& $this->getDate() === $other->getDate()
			&& $this->getTitle() === $other->getTitle();
	}

}
