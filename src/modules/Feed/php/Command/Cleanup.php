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
 * Command to clean up database
 *
 * @version $Revision$
 */
class Cleanup extends Periodic\Command
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
        $logger->log( "Cleaning up unused feeds." );
        $this->model->cleanUnusedFeeds();
        $logger->log( "Cleaning up old feed entries." );
        $this->model->cleanOldData();
        $logger->log( "Cleaning up unused read markers." );
        $this->model->cleanUnusedReadMarkers();

        return Periodic\Executor::SUCCESS;
    }
}
