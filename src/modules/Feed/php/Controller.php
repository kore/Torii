<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Module\Feed;

use Qafoo\RMF;
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
     * Construct from model
     *
     * @param Model $model
     * @return void
     */
    public function __construct( Model $model )
    {
        $this->model = $model;
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
        $this->model->addUrl( $module->id, $request->body['url'] );
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
     * Method triggered by cron job to refresh feed data
     *
     * @return void
     */
    public function refresh()
    {
        foreach ( $this->model->getPending( 300 ) as $url )
        {
            $feed = \Zend\Feed\Reader\Reader::importString(
                file_get_contents( $url->url )
            );

            foreach ( $feed as $entry )
            {
                $this->model->addEntry(
                    $url->id,
                    $entry->getLink(),
                    $entry->getDateModified()->format( 'U' ),
                    $entry->getTitle(),
                    $entry->getDescription(),
                    $entry->getContent()
                );
            }

            $this->model->updateUrl(
                $url->id,
                200,
                time()
            );
        }
    }
}

