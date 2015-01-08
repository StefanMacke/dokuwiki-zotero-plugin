#!/usr/bin/env php
<?php
// downloading and parsing all Zotero entries could take quite some time
set_time_limit(600);

require_once('classes/IniZoteroConfig.php');
require_once('classes/ImportZoteroFeedReader.php');
require_once('classes/FeedZoteroRepository.php');
require_once('classes/TextZoteroRepository.php');


$config = new IniConfig();

$cachePage = $config->getCachePage();
$textRepo = new TextZoteroRepository($cachePage, $config);

$feedReader = new ImportZoteroFeedReader($config);
$webRepo = new FeedZoteroRepository($feedReader, $config);
$entries = $webRepo->getAllEntries();

$textRepo->saveEntries($entries);

$count = count($textRepo->getAllEntries());
echo sprintf($config->getLang('importall'), $count, $cachePage). "\n";
