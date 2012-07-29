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
            $this->decode( $data->get_title() )
        );

        $entry->date        = $data->get_date( 'U' ) ?: time();
        $entry->description = $this->decode( $data->get_description() );
        $entry->content     = $this->decode( $data->get_content() );

        return $entry;
    }

    /**
     * Decode all HTML and XML entities in the string
     *
     * @param string $string
     * @return string
     */
    protected function decode( $string )
    {
        $string = html_entity_decode( $string );

        $string = preg_replace_callback(
            '(&#(\\d+);)',
            function ( $matches )
            {
                return Parser::codePointToUtf8( $matches[1] );
            },
            $string
        );

        $string = preg_replace_callback(
            '(&#x([a-fA-F0-9]+);)',
            function ( $matches )
            {
                return Parser::codePointToUtf8( hexdec( $matches[1] ) );
            },
            $string
        );

        return $string;
    }

    /**
     * Convert unicode code point to UTF-8 character
     *
     * @param int $decimal
     * @return string
     */
    public static function codePointToUtf8( $decimal )
    {
        switch ( true )
        {
            case $decimal <= 0x007f:
                return chr( $decimal );

            case $decimal <= 0x07ff:
                return
                    chr( 0xc0 | ( $decimal >> 6 ) ) .
                    chr( 0x80 | ( $decimal & 0x003f ) );

            case $decimal === 0xFEFF:
                return '';

            case $decimal >= 0xD800 && $decimal <= 0xDFFF:
                throw new \RuntimeException( "Surrogates are not handled." );

            case $decimal <= 0xffff:
                return
                    chr( 0xe0 | ( $decimal >> 12 ) ) .
                    chr( 0x80 | ( ( $decimal >> 6 ) & 0x003f ) ) .
                    chr( 0x80 | ( $decimal & 0x003f ) );

            case $decimal <= 0x10ffff:
                return
                    chr( 0xf0 | ( $decimal >> 18 ) ) .
                    chr( 0x80 | ( ( $decimal >> 12 ) & 0x3f ) ) .
                    chr( 0x80 | ( ( $decimal >> 6 ) & 0x3f ) ) .
                    chr( 0x80 | ( $decimal & 0x3f ) );

            default:
                throw new \RuntimeException( "Invalid or unhandled codepoint." );
        }
    }
}

