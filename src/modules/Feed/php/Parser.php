<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Module\Feed;

/**
 * Feed parser
 *
 * @version $Revision$
 */
class Parser
{
    public function parse( $url )
    {
        $data = \Zend\Feed\Reader\Reader::importString(
            file_get_contents( $url->url )
        );

        $feed = new Struct\Feed( $url, 200 );
        foreach ( $data as $entry )
        {
            $feed->entries[] = $this->parseEntry( $entry );
        }

        return $feed;
    }

    protected function parseEntry( $data )
    {
        $entry = new Struct\FeedEntry(
            null,
            $data->getLink(),
            $data->getTitle()
        );

        $entry->date        = $data->getDateModified() ?
            $data->getDateModified()->format( 'U' ) :
            time();

        $entry->description = $data->getDescription();
        $entry->content     = $data->getContent();

        return $entry;
    }
}

