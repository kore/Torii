<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Module\Calendar;

use Qafoo\RMF;
use Arbit\Periodic;
use Torii\Struct;

/**
 * Calendar module controller
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
     * Get the current calendar data
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @param Struct\ModuleConfiguration $module
     * @return Struct\Response
     */
    public function getCalendarData( RMF\Request $request, Struct\User $user, Struct\ModuleConfiguration $module )
    {
        $entries = $this->model->getCalendar( $module->id );

        $perDay = array();
        foreach ( $entries as $entry )
        {
            $perDay[$entry->date->format( 'l, jS F' )][] = $entry;
            $entry->date = $entry->date->format( 'H:i' );
        }

        return $perDay;
    }

    /**
     * Get the current calendar data
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @param Struct\ModuleConfiguration $module
     * @return Struct\Response
     */
    public function refresh( Periodic\Logger $logger )
    {
        $urls = $this->model->getUrlsPerModule();
        foreach ( $urls as $module => $moduleUrls )
        {
            $entries = array();
            foreach ( $moduleUrls as $url )
            {
                $entries = array_merge(
                    $entries,
                    $this->parser->parse( $url )->entries
                );
                $this->model->updateUrl( $url->id, $url->status, $url->requested );
            }

            usort(
                $entries,
                function ( $a, $b )
                {
                    return $a->date->getTimestamp() - $b->date->getTimestamp();
                }
            );

            $this->model->storeCalendar( $module, $entries );
        }

        return array();
    }
}

