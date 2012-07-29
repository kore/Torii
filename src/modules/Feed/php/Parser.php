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
 * Neither feed parser nor HTTP client are injectable, because we could not be
 * bothered to define interface for them. If you want to exchange something,
 * create a new parser ;p
 *
 * @version $Revision$
 */
class Parser
{
    /**
     * Parse Feed behind provided URL
     *
     * Returns a Feed instance
     *
     * @param Struct\Url $url
     * @return Struct\Feed
     */
    public function parse( Struct\Url $url )
    {
        $client = new \Buzz\Browser();
        $client->getClient()->setTimeout( 5 );

        $feed = new Struct\Feed( $url );
        try
        {
            $response = $client->get( $url->url );
            $feed->status = $response->getStatusCode();

            if ( !$response->isOk() )
            {
                return $feed;
            }

            $reader = new \SimplePie();
            $reader->set_raw_data( $response->getContent() );
            $reader->init();

            foreach ( $reader->get_items() as $entry )
            {
                $feed->entries[] = $this->parseEntry( $entry );
            }
        }
        catch ( \Zend\Feed\Reader\Exception\RuntimeException $e )
        {
            $feed->status = 406;
            return $feed;
        }
        catch ( \RuntimeException $e )
        {
            $feed->status = 503;
            return $feed;
        }

        return $feed;
    }

    /**
     * Converts a single feed entry into something sensible
     *
     * @param \SimplePie_Item $entry
     * @return Struct\FeedEntry
     */
    protected function parseEntry( \SimplePie_Item $data )
    {
        $entry = new Struct\FeedEntry(
            null,
            $data->get_link(),
            null,
            $data->get_title()
        );

        $entry->date        = $data->get_date( 'U' ) ?: time();
        $entry->description = $data->get_description();
        $entry->content     = $data->get_content();

        return $entry;
    }
}

