<?php

/**
 * Bibliography helper is responsible for getting the bibliography info from Zotero portal.
 * Builded for the Latexit Plugin
 *
 * @license GPL 2 http://www.gnu.org/licenses/gpl-2.0.html
 * @author  Adam Kučera <adam.kucera@wrent.cz>
 */

/**
 * Singleton helper instance for handling generation of bibliography
 */
class helper_plugin_zotero_bibliography extends DokuWiki_Plugin {

    /**
     * Zotero $type id
     * @var int
     */
    protected $id;
    /**
     * Group or User
     * @var string
     */
    protected $type;
    /**
     * Zotero access key
     * @var string
     */
    protected $key;
    /**
     * Zotero local repository location
     * @var string
     */
    protected $repository;
    /**
     * Bibliography entries itself.
     * @var array
     */
    protected $bib_entries = array();

    /**
     * @var ZoteroConfig
     */
    protected $config = null;

    /**
     * construktor
     *
     * @global array $conf Global DokuWiki configuration
     */
    public function __construct() {
        //get the zotero configuration
        $this->config = plugin_load('helper', 'zotero_config');

        //try to find user or group
        foreach(array('user', 'group') as $type) {
            $id = $this->config->getConfig('ZoteroAccess', $type . 'id');
            if(!empty($id)) {
                $this->type = $type . 's';
                $this->id = $id;
                break;
            }
        }

        $this->key = $this->config->getConfig('ZoteroAccess', 'key');
    }

    /**
     * Load an entry from the external repository using REST api and the
     * information from local repository and insert it to bibliography items.
     *
     * @param ZoteroEntry $entry of an cited bibliography.
     */
    public function insert($entry) {
        $id = $entry->getZoteroId();

        //load the bibtex file using REST api
        $url = "https://api.zotero.org/"
            . $this->type . "/" . $this->id
            . "/items/" . $id
            . "?key=" . $this->key
            . "&format=atom&content=bibtex";
        $http = new HTTPClient();
        $response = $http->get($url);

        $item = simplexml_load_string($response);
        $bib_item = (string) $item->content;

        //make the short title as the title of the entry
        preg_match('#^[@].*\{(.*),$#m', $bib_item, $match);
        $bib_item = str_replace($match[1], $entry->getCiteKey(), $bib_item);

        $this->bib_entries[$entry->getCiteKey()] = $bib_item;
    }

    /**
     * Get the bibtex file from all the entries.
     * @return string
     */
    public function getBibliography() {
        $bibtex = '';
        foreach ($this->bib_entries as $bib) {
            $bibtex .= $bib . "\n\n";
        }
        return $bibtex;
    }

    /**
     * Returns true, if there is no bibliography entries.
     * @return boolean
     */
    public function isEmpty() {
        if(empty($this->bib_entries)) {
            return true;
        } else {
            return false;
        }
    }
}
