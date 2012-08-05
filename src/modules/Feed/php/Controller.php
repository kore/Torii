<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Module\Feed;

use Qafoo\RMF;
use Arbit\Periodic;
use Torii\Struct;

/**
 * Feed module controller
 *
 * @version $Revision$
 */
class Controller
{
    /**
     * Model
     *
     * @var Model
     */
    protected $model;

    /**
     * Parser
     *
     * @var Parser
     */
    protected $parser;

    /**
     * Construct from model
     *
     * @param Model $model
     * @param Parser $parser
     * @return void
     */
    public function __construct( Model $model, Parser $parser )
    {
        $this->model  = $model;
        $this->parser = $parser;
    }

    /**
     * Add URL
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @param Struct\ModuleConfiguration $module
     * @return Struct\Response
     */
    public function addUrl( RMF\Request $request, Struct\User $user, Struct\ModuleConfiguration $module )
    {
        $this->model->addUrl( $module->id, $request->body['name'], $request->body['url'] );
    }

    /**
     * Remove URL
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @param Struct\ModuleConfiguration $module
     * @return Struct\Response
     */
    public function removeUrl( RMF\Request $request, Struct\User $user, Struct\ModuleConfiguration $module )
    {
        $this->model->removeUrl( $module->id, $request->body['url'] );
    }

    /**
     * Get URL list
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @param Struct\ModuleConfiguration $module
     * @return Struct\Response
     */
    public function getUrlList( RMF\Request $request, Struct\User $user, Struct\ModuleConfiguration $module )
    {
        return $this->model->getUrlList( $module->id );
    }

    /**
     * Get the current feed data
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @param Struct\ModuleConfiguration $module
     * @return Struct\Response
     */
    public function getFeedData( RMF\Request $request, Struct\User $user, Struct\ModuleConfiguration $module )
    {
        return $this->model->getUnread( $module->id );
    }

    /**
     * Redirect to feed entry
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @param Struct\ModuleConfiguration $module
     * @return Struct\Response
     */
    public function redirect( RMF\Request $request, Struct\User $user, Struct\ModuleConfiguration $module )
    {
        if ( !preg_match( '(^/redirect/(\\d+)/(.+)$)', $request->variables['path'], $match ) )
        {
            throw new \RuntimeException( "Invalid URL passed" );
        }

        $this->model->markRead( $request->variables['module'], $match[1] );
        header( 'Location: ' . urldecode( $match[2] ) );
        exit( 0 );
    }

    /**
     * Clear all URLs of one feed
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @param Struct\ModuleConfiguration $module
     * @return Struct\Response
     */
    public function clear( RMF\Request $request, Struct\User $user, Struct\ModuleConfiguration $module )
    {
        if ( !preg_match( '(^/clear/(.+)$)', $request->variables['path'], $match ) )
        {
            throw new \RuntimeException( "Invalid URL passed" );
        }

        $this->model->clear( $request->variables['module'], $match[1] );
        return array( 'ok' => true );
    }

    /**
     * Update feeds
     *
     * @param Periodic\Logger $logger
     * @return void
     */
    public function refresh( Periodic\Logger $logger )
    {
        foreach ( $this->model->getPending( 300 ) as $url )
        {
            $logger->log( "Update {$url->url}" );
            $feed = $this->parser->parse( $url );

            foreach ( $feed->entries as $entry )
            {
                $this->model->addEntry(
                    $url->id,
                    $entry->link,
                    $entry->date,
                    $entry->title,
                    $entry->description,
                    $entry->content
                );
            }

            $logger->log( "Done -- status: {$feed->status}" );
            $this->model->updateUrl(
                $url->id,
                $feed->status,
                time()
            );
        }
    }
}

