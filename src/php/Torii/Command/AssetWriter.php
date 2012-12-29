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
     * Is development mode
     *
     * @var bool
     */
    protected $development;

    /**
     * Construct
     *
     * @param string $target
     * @param array $assets
     * @param bool $development
     * @return void
     */
    public function __construct($target, array $assets, $development = false)
    {
        $this->target      = $target;
        $this->assets      = $assets;
        $this->development = $development;
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
    public function run(XML\Node $configuration, Periodic\Logger $logger)
    {
        if ($this->development) {
            $logger->log("Skip asset writing in development mode.");
            return Periodic\Executor::SUCCESS;
        }

        $writer = new Assets\Writer();
        foreach ($this->assets as $path => $collection) {
            $logger->log("Writing assets in $path.");
            $writer->write($collection, $this->target . $path);
        }

        return Periodic\Executor::SUCCESS;
    }
}
