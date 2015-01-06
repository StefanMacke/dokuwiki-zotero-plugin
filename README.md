dokuwiki-zotero-plugin
======================

Plugin for DokuWiki to display bibliography entries from Zotero.

Installation
============

1. Extract archive to `[DokuWikiRoot]/lib/plugins/zotero` or use the plugin
   manager and point to http://f.macke.it/ZoteroPlugin for an automatic 
   installation.
2. Enter your Zotero username, key, and user ID into `config.ini`.
3. Create a new wiki page for the cached Zotero entries and enter its name 
   into `config.ini` (e.g. `zotero:sources`).
4. Enter your Zotero entries into the cache file (the newly created wiki page
   or `ZoteroEntries.txt` if you don't want to use the wiki page). This can be 
   automated by calling `importAllEntries.php` from the command line.
5. If you cite a source that is not already in the cache, the plugin will
   automatically download your latest Zotero entries and add them to the 
   cache. For this to work you have to set `autoupdating` in `config.ini` to `1`.
