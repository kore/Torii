<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Command;

use Torii\Assets,
    Arbit\Periodic,
    Arbit\Xml;

/**
 * Asset writer periodic command
 *
 * @version $Revision$
 */
class AssetWriter extends Periodic\Command
{
    /**
     * Target directory
     *
     * @var string
     */
    protected $target;

    /**
     * Assets
     *
     * @var array
     */
    protected $assets;

    /**
     * Construct
     *
     * @param array $target
     * @param array $assets
     * @return void
     */
    public function __construct( $target, array $assets )
    {
        $this->target = $target;
        $this->assets = $assets;
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
        $writer = new Assets\Writer();
        foreach ( $this->assets as $path => $collection )
        {
            $logger->log( "Writing assets in $path." );
            $writer->write( $collection, $this->target . $path );
        }

        return Periodic\Executor::SUCCESS;
    }
}

