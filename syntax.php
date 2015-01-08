<?php
/**
 * Zotero Plugin: Links quotes to Zotero sources
 *
 * Syntax: \cite[Page]{ShortName}
 *
 * @license	GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author	Stefan Macke <me@stefan-macke.de>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once("classes/IniZoteroConfig.php");
require_once("classes/ZoteroEntry.php");

/**
 * Handles cite reference in wiki text
 */
class syntax_plugin_zotero extends DokuWiki_Syntax_Plugin
{
    /**
	 * @var helper_plugin_zotero_repository
	 */
    protected $repository = null;

	/**
	 * @var ZoteroConfig
	 */
	protected $config = null;

    /**
     * @var helper_plugin_zotero_bibliography
     */
    protected $bibliography;

    /**
     * Obtain some instances
     */
    public function __construct() {
        $this->config = plugin_load('helper', 'zotero_config');
        $this->bibliography = plugin_load('helper', 'zotero_bibliography');
        $this->repository = plugin_load('helper', 'zotero_repository');

    }

    /**
     * Syntax Type
     *
     * Needs to return one of the mode types defined in $PARSER_MODES in parser.php
     *
     * @return string
     */
    public function getType()
	{
		return 'substition';
	}

    /**
     * Sort for applying this mode
     *
     * @return int
     */
    public function getSort()
	{
		return 50;
	}

    /**
     * @param string $mode
     */
    public function connectTo($mode)
	{
		$this->Lexer->addSpecialPattern('\\\cite.*?\}', $mode, 'plugin_zotero');
	}

    /**
     * Handler to prepare matched data for the rendering process
     *
     * @param   string       $match   The text matched by the patterns
     * @param   int          $state   The lexer state for the match
     * @param   int          $pos     The character position of the matched text
     * @param   Doku_Handler $handler The Doku_Handler object
     * @return  array Return an array with all data you want to use in render
     */
    public function handle($match, $state, $pos, Doku_Handler $handler)
	{
		$matches = array();
        $return = array(
            'entry' => null,
            'pageref' => '',
            'error' => ''
        );
        try
        {
            if (preg_match("/\\\cite(\[([a-zA-Z0-9 \.,\-:]*)\])?\{([a-zA-Z0-9\-:]*?)\}/", $match, $matches))
            {

                if($matches[1] == '') {
                    // no page argement given
                    $return['pageref'] = null;
                } else {
                    $return['pageref'] = trim($matches[2]);
                }
                $citeKey           = $matches[3];

                $return['entry'] = $this->repository->getEntryByCiteKey($citeKey);
            }
            else
            {
                throw new ZoteroParserException(sprintf($this->getLang('invalidcitation'), hsc($match)));
            }
        }
        catch(Exception $e)
        {
            $return['error'] = $e->getMessage();
        }
        return $return;
	}

    /**
     * Handles the actual output creation.
     *
     * @param   $mode     string        output format being rendered
     * @param   $renderer Doku_Renderer the current renderer object
     * @param   $data     array         data created by handler()
     * @return  boolean                 rendered correctly?
     */
    public function render($mode, Doku_Renderer $renderer, $data)
	{
        require_once("classes/ZoteroEntry.php");

        /** @var ZoteroEntry $entry */
        /** @var string      $pageRef */
        $entry   = $data['entry'];
        $pageRef = $data['pageref'];

		if($mode == 'xhtml')
		{
            if($data['error']) {
                //error message
                $output = $data['error'];

            } else {
                try
                {
                    $output = $this->createSourceOutput($entry, $pageRef);
                }
                catch (Exception $e)
                {
                    $output = $this->outputError($e->getMessage());
                }
            }

			$renderer->doc .= $this->output($output);
			return true;
		}
        if($mode == 'latex')
        {
            $renderer->doc .= '\\cite{'.$entry->getCiteKey().'}';

            $this->addBibliographyEntry($entry);

        }
		return false;
	}

    /**
     * @param $text
     * @return string
     */
    private function output($text)
    {
        return '<span class="ZoteroSource">' . $text. '</span>';
    }

    /**
     * Returns html of source reference
     *
     * @param ZoteroEntry $entry
     * @param string      $pageRef
     * @return string
     * @throws ZoteroConfigException
     */
    private function createSourceOutput($entry, $pageRef)
    {
        $parentheses = explode(",", $this->config->getConfig("WikiOutput", "parentheses"));

        // falback to pages in entry
        if($pageRef === '') {
            $pageRef = $entry->getPages();
        }

        $output = $parentheses[0] . $this->getZoteroLink($entry);
        $output .= $this->buildPageRef($pageRef);
        $output .= $parentheses[1];

        return $output;
    }

    /**
     * Returns html of link to the source
     *
     * @param ZoteroEntry $entry
     * @return string
     */
    private function getZoteroLink(ZoteroEntry $entry)
    {
        $titleformat = $this->config->getConfig('WikiOutput', 'titleFormat');

        return '<a href="' . $this->config->getUrlForEntry($entry)
             . '" title="' . htmlentities($entry->getShortInfo($titleformat)) . '">'
             . htmlentities($entry->getCiteKey())
             . "</a>";
    }

    /**
     * @param string $pageRef range of interest
     * @return string
     */
    private function buildPageRef($pageRef)
    {                   var_dump($pageRef) ;
        //keep empty
        if( $pageRef === null) return '';

        $return = '';
        if ($pageRef != '')
        {
            if (preg_match("/^[0-9\-f\.]+$/", $pageRef))
            {
                $pageprefix = $this->config->getConfig('WikiOutput', 'pagePrefix');
                if ($pageprefix)
                {
                    $pageRef = $pageprefix . $pageRef;
                }
            }
            $return .= ", " . $pageRef;
        }
        var_dump($pageRef) ;
        return $return;
    }

    /**
     * @param $message
     * @return string
     */
    private function outputError($message)
    {
        return $this->output('<span class="error">' . sprintf($this->getLang('error'), $message) . "</span>");
    }

    /**
     * @param $entry
     */
    protected function addBibliographyEntry($entry) {
        if($this->bibliography) {
            $this->bibliography->insert($entry);
        }
    }
}
