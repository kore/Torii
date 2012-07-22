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
        $this->model->getUnread( $module->id );
    }
}

