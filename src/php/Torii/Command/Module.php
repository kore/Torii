<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Command;

use Torii\Cronable;
use Arbit\Periodic;
use Arbit\Xml;

/**
 * Base DIC
 *
 * @version $Revision$
 */
class Module extends Periodic\Command
{
    /**
     * Available modules
     *
     * @var array
     */
    protected $modules;

    /**
     * Construct from available modules
     *
     * @param array $modules
     * @return void
     */
    public function __construct(array $modules)
    {
        $this->modules = $modules;
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
        $name = (string) $configuration;
        if (!isset($this->modules[$name])) {
            $logger->log("Module $name not found.", Periodic\Logger::WARNING);
            return Periodic\Executor::ERROR;
        }

        $module = $this->modules[$name];
        if (!$module instanceof Cronable) {
            $logger->log("Module $name has no support for cron jobs.", Periodic\Logger::WARNING);
            return Periodic\Executor::ERROR;
        }

        $logger->log("Refresh data in module $name.");
        $module->refresh($logger);
        return Periodic\Executor::SUCCESS;
    }
}
