<?php
/**
 * Zotero Plugin: Links quotes to Zotero sources
 *
 * Syntax: \cite[Page]{ShortName}
 *
 * @license	GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author	Stefan Macke <me@stefan-macke.de>
 */

if (!defined('DOKU_INC'))
{
	define('DOKU_INC', realpath(dirname(__FILE__) . '/../../') . DIRECTORY_SEPARATOR);
}
if (!defined('DOKU_PLUGIN'))
{
	define('DOKU_PLUGIN', DOKU_INC . 'lib/plugins/');
}
require_once(DOKU_PLUGIN . 'syntax.php');

require_once("ZoteroEntry.php");
require_once("FeedZoteroRepository.php");
require_once("TextZoteroRepository.php");
require_once("IniZoteroConfig.php");
require_once("WebZoteroFeedReader.php");

class syntax_plugin_zotero extends DokuWiki_Syntax_Plugin
{
	/**
	 * @var ZoteroRepository
	 */
	private $repo = null;
	
	/**
	 * @var ZoteroConfig
	 */
	private $config = null;
	
	function getInfo()
	{
		return array(
			'author' => 'Stefan Macke',
			'email'  => 'me@stefan-macke.de',
			'date'   => '2013-03-03',
			'name'   => 'Zotero Plugin',
			'desc'   => 'Links quotes to Zotero sources',
			'url'    => 'http://blog.macke.it',
		);
	}

	function getType()
	{
		return 'substition';
	}

	function getSort()
	{
		return 50;
	}

	function connectTo($mode)
	{
		$this->Lexer->addSpecialPattern('\\\cite.*?\}', $mode, 'plugin_zotero');
	}

	function handle($match, $state, $pos, &$handler)
	{
		$citeKey = "";
		$pageRef = "";
		$matches = array();
		if (preg_match("/\\\cite(\[([a-zA-Z0-9 \.,\-:]*)\])?\{([a-zA-Z0-9\-:]*?)\}/", $match, $matches))
		{
			$pageRef = $matches[2];
			$citeKey = $matches[3];
		}
		else
		{
			return $this->outputError("invalid citation: " . $match);
		}
		
		$output = "";
		try
		{	
			$this->config = new IniConfig();
			$cachePage = $this->config->getCachePage();

			$this->repo = new TextZoteroRepository($cachePage, $this->config);
			$output = $this->createSourceOutput($citeKey, $pageRef);

			return $this->output($output);
		}
		catch (Exception $e)
		{
			return $this->outputError($e->getMessage()); 
		}
	}

	private function output($text)
	{
		return '<span class="ZoteroSource">' . $text. '</span>';
	}

	private function outputError($message)
	{
		return $this->output('<span class="error">ERROR: ' . $message . "</span>");
	}

	private function createSourceOutput($citeKey, $pageRef)
	{
		$parentheses = explode(",", $this->config->getConfig("WikiOutput", "parentheses"));
		if (count($parentheses) != 2)
		{
			throw new ZoteroConfigException("configuration not set correctly: WikiOutput.parentheses");
		}
		
		$entry = $this->getZoteroEntry($citeKey);
	
		$output = $parentheses[0] . $this->getZoteroLink($entry);
		$output = $this->addPageRefToOutput($output, $pageRef);
		$output .= $parentheses[1];
		
		return $output;
	}
	
	private function addPageRefToOutput($output, $pageRef)
	{
		if ($pageRef != "")
		{
			if (preg_match("/^[0-9\-f\.]+$/", $pageRef))
			{
				if (isset($this->config['WikiOutput']['pagePrefix']))
				{
					$pageRef = $this->config['WikiOutput']['pagePrefix'] . $pageRef;
				}
			}
			$output .= ", " . $pageRef;
		}
		return $output;
	}
	
	private function getZoteroEntry($citeKey)
	{
		try
		{
			return $this->repo->getEntryByCiteKey($citeKey);
		}
		catch (ZoteroEntryNotFoundException $e)
		{
			if ($this->config->getConfig('ZoteroAccess', 'autoupdate') == 1)
			{
				$cachePage = $this->config->getCachePage();
				$feedReader = new WebZoteroFeedReader($this->config);
				$webRepo = new FeedZoteroRepository($feedReader, $this->config);
				$entries = $webRepo->getAllEntries();
				$this->repo->updateAndSaveEntries($entries);
				return $this->repo->getEntryByCiteKey($citeKey);
			}
			else
			{
				throw $e;
			}
		}
	}

	private function getZoteroLink(ZoteroEntry $entry)
	{
		return '<a href="' . $this->config->getUrlForEntry($entry) . '" title="' . htmlentities($entry->getShortInfo($format)) . '">' . htmlentities($entry->getCiteKey()) . "</a>";
	}
	
	function render($mode, &$renderer, $data) 
	{
		if($mode == 'xhtml')
		{
			$renderer->doc .= $data;
			return true;
		}
		return false;
	}
}
?>
