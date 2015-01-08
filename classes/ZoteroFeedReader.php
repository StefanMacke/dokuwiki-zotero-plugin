<?php

/**
 * Interface ZoteroFeedReader
 */
interface ZoteroFeedReader
{
    /**
     * Returns XML content as string
     *
     * @return string of the XML
     */
    function getFeed();
}
