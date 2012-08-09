<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Module\Feed\Command;

use Torii\Module\Feed,
    Arbit\Periodic,
    Arbit\Xml;

/**
 * Favicon fetcher periodic command
 *
 * @version $Revision$
 */
class FaviconFetcher extends Periodic\Command
{
    /**
     * Feed model
     *
     * @var Model
     */
    protected $model;

    /**
     * Construct
     *
     * @param Model $model
     * @return void
     */
    public function __construct( Feed\Model $model )
    {
        $this->model = $model;
    }

    /**
     * Run command
     *
     * Execute the actual bits.
     *
     * Should return one of the status constant values, defined as class
     * constants in Executor.
     *
     * @param XML\Node $configuration
     * @param Periodic\Logger $logger
     * @return int
     */
    public function run( XML\Node $configuration, Periodic\Logger $logger )
    {
        $urls = $this->model->getUrlsWithoutFavicon();
        foreach ( $urls as $url )
        {
            $logger->log( "Fetching favicon for {$url->url}." );
            if ( $favicon = $this->fetchFavicon( $url->url, $logger ) )
            {
                $logger->log( "Found favicon $favicon for {$url->url}." );
                $this->model->updateFavicon( $url->id, $favicon );
            }
        }

        return Periodic\Executor::SUCCESS;
    }

    /**
     * Try to fetch favicon for the given URL
     *
     * @param string $url
     * @param Periodic\Logger $logger
     * @return string
     */
    protected function fetchFavicon( $url, Periodic\Logger $logger )
    {
        $urlComponents = parse_url( $url );
        $baseUrl = $urlComponents['scheme'] . '://' .
            ( isset( $urlComponents['user'] ) ?
                $urlComponents['user'] .
                    ( isset( $urlComponents['pass'] ) ? ':' . $urlComponents['pass'] : '' ) .
                '@' :
                '' ) .
            $urlComponents['host'] .
            ( isset( $urlComponents['port'] ) ? ':' . $urlComponents['port'] : '' ) . '/';

        $target = __DIR__ . '/../../images/favicons/';
        $name   = $urlComponents['host'];

        $client = new \Buzz\Browser();
        $client->getClient()->setTimeout( 5 );

        try
        {
            // First try to fetch common favicon.ico
            $response = $client->get( $baseUrl. '/favicon.ico' );
            if ( $response->isOk() )
            {
                $name = $name . '.ico';
                file_put_contents( $target . $name, $response->getContent() );
                return $name;
            }

            // Load root and try to locate favicon
            $response = $client->get( $baseUrl );
            if ( !$response->isOk() )
            {
                $logger->log( "Could not fetch root: $baseUrl", Periodic\Logger::WARNING );
                return false;
            }

            // Try to find favicon in resulting (probably) HTML
            if ( !preg_match( '(<link[^>]+rel=([\'"]?)[^>]*icon[^>]*\1[^>]*>)', $response->getContent(), $match ) )
            {
                $logger->log( "Did not find icon referenced in source on $baseUrl.", Periodic\Logger::WARNING );
                return false;
            }

            $link = $match[0];
            if ( !preg_match( '((?J)href=([\'"])(?P<favicon>[^>]*?)\1|href=(?P<favicon>\\S+))', $link, $match ) )
            {
                $logger->log( "Could not extract href property in link element $link.", Periodic\Logger::WARNING );
                return false;
            }

            $favicon = $match['favicon'];
            switch ( true )
            {
                case ( strpos( $favicon, '//' ) === 0 ):
                    $favicon = $urlComponents['scheme'] . ':' . $favicon;
                    break;

                case ( strpos( $favicon, 'http' ) === 0 ):
                    break;

                default:
                    $favicon = $baseUrl . $favicon;
                    break;
            }

            $extension = pathinfo( parse_url( $favicon, \PHP_URL_PATH ), \PATHINFO_EXTENSION );
            $response  = $client->get( $favicon );
            if ( $response->isOk() )
            {
                $name = $name . '.' . $extension;
                file_put_contents( $target . $name, $response->getContent() );
                return $name;
            }
        }
        catch ( \Exception $e )
        {
            $logger->log( "An error occured while fetching: " . $e->getMessage(), Periodic\Logger::ERROR );
            return false;
        }

        $logger->log( "No favicon fetching strategy worked.", Periodic\Logger::WARNING );
        return false;
    }
}

