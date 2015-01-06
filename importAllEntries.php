#!/usr/bin/env php
<?php
// downloading and parsing all Zotero entries could take quite some time
set_time_limit(600);

require_once('IniZoteroConfig.php');
require_once('ImportZoteroFeedReader.php');
require_once('FeedZoteroRepository.php');
require_once('TextZoteroRepository.php');


$config = new IniConfig();

$cachePage = $config->getCachePage();
$textRepo = new TextZoteroRepository($cachePage, $config);

$feedReader = new ImportZoteroFeedReader($config);
$webRepo = new FeedZoteroRepository($feedReader, $config);
$entries = $webRepo->getAllEntries();

$textRepo->saveEntries($entries);

echo count($textRepo->getAllEntries()) . " entries imported into file " . $cachePage . ".\n";
?>
