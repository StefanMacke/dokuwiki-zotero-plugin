<?php
/**
 * Zotero Plugin: Repository helper which handles requests to text repo or online repo
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Stefan Macke <me@stefan-macke.de>
 * @author     Gerrit Uitslag <klapinklapin@gmail.com>
 */

// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once(DOKU_PLUGIN . "zotero/classes/ZoteroEntry.php");
require_once(DOKU_PLUGIN . "zotero/classes/FeedZoteroRepository.php");
require_once(DOKU_PLUGIN . "zotero/classes/TextZoteroRepository.php");
require_once(DOKU_PLUGIN . "zotero/classes/IniZoteroConfig.php");
require_once(DOKU_PLUGIN . "zotero/classes/WebZoteroFeedReader.php");

/**
 * Handles all repository actions
 */
class helper_plugin_zotero_repository extends DokuWiki_Plugin {
    /**
     * @var ZoteroRepository
     */
    protected $textrepository = null;

    /**
     * @var ZoteroConfig
     */
    private $config = null;

    /**
     * Initiate local repo
     */
    public function __construct() {
        $this->config = plugin_load('helper', 'zotero_config');

        $this->textrepository = new TextZoteroRepository($this->config);
    }

    /**
     * Return entry requested by citekey
     *
     * @param $citeKey
     * @return mixed
     * @throws Exception
     * @throws ZoteroEntryNotFoundException
     */
    public function getEntryByCiteKey($citeKey) {
        try {
            return $this->textrepository->getEntryByCiteKey($citeKey);

        } catch(ZoteroEntryNotFoundException $e) {
            if($this->config->getConfig('ZoteroAccess', 'autoupdate') == 1) {
                $feedReader = new WebZoteroFeedReader();
                $webRepo = new FeedZoteroRepository($this->config, $feedReader);
                $entries = $webRepo->getAllEntries();

                $this->textrepository->updateAndSaveEntries($entries);
                return $this->textrepository->getEntryByCiteKey($citeKey);
            } else {
                throw $e;
            }
        }
    }
}