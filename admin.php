<?php
/**
 * Zotero admin plugin for manually download the library
 *
 * @license    GPL 2 (http://www.gnu.org/licenses/gpl.html)
 * @author     Gerrit Uitslag <klapinklapin@gmail.com>
 */
// must be run within Dokuwiki
if(!defined('DOKU_INC')) die();

require_once('classes/IniZoteroConfig.php');
require_once('classes/ImportZoteroFeedReader.php');
require_once('classes/FeedZoteroRepository.php');
require_once('classes/TextZoteroRepository.php');

/**
 * Manually download library
 */
class admin_plugin_zotero extends DokuWiki_Admin_Plugin {

    /**
     * @return int
     */
    function getMenuSort() {
        return 100;
    }

    function handle() {
//        // downloading and parsing all Zotero entries could take quite some time
//        set_time_limit(600);
//
//        checkSecurityToken()
//            isSubmitted()
//            hasValidInput()
//        if() {
//            $config = new ManualConfig();
//
//            $feedReader = new ImportZoteroFeedReader($config);
//            $webRepo = new FeedZoteroRepository($config, $feedReader);
//            $entries = $webRepo->getAllEntries();
//
//            $textRepo = new TextZoteroRepository($config);
//            $textRepo->saveEntries($entries);
//
//            $count = count($textRepo->getAllEntries());
//            echo sprintf($config->getLang('importall'), $count, $config->getCachePage()) . "\n";
//        }
//


    }

    function html() {
        print $this->locale_xhtml('downloadallentries');

        ptln('<div id="config__manager">');


        ptln('</div>');

    }

}

