<?php
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
	
	public function __construct($zoteroId)
	{
		$this->zoteroId = $zoteroId;
	}
	
	public function getZoteroId()
	{
		return $this->zoteroId;
	}
	
	public function setCiteKey($value)
	{
		$this->citeKey = $value;
	}

	public function getCiteKey()
	{
		return $this->citeKey;
	}

	public function setTitle($value)
	{
		$this->title = $value;
	}

	public function getTitle()
	{
		return $this->title;
	}

	public function setAuthor($value)
	{
		$this->author = $value;
	}

	public function getAuthor()
	{
		return $this->author;
	}

	public function setDate($value)
	{
		$this->date = $value;
	}

	public function getDate()
	{
		return $this->date;
	}
  
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
	
	public function __toString()
	{
		return $this->getShortInfo();
	}
	
	public function equals(ZoteroEntry $other)
	{
		return $this->getZoteroId() === $other->getZoteroId()
			&& $this->getCiteKey() === $other->getCiteKey()
			&& $this->getAuthor() === $other->getAuthor()
			&& $this->getDate() === $other->getDate()
			&& $this->getTitle() === $other->getTitle();
	}
}
?>