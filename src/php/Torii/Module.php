<?php
/**
 * This file is part of Torii
 *
 * @version $Revision: 1469 $
 */

namespace Torii;

use Qafoo\RMF;
use Torii\Struct;
use Torii\DIC;

/**
 * Module base class
 *
 * @version $Revision$
 */
abstract class Module
{
    /**
     * Dependency Injection Container from Torii
     *
     * @var DIC
     */
    protected $dic;

    /**
     * Inject DIC
     *
     * This is the interface between modules and the base system. Module
     * definitions are not supposed to be re-usable in other systems.
     *
     * The DIC in this case provides the means to configure your own module
     * environment. The DIC schould NOT be passed to any other objects.
     *
     * @param DIC $dic
     * @return void
     */
    public function initialize( DIC $dic )
    {
        $this->dic = $dic;
    }

    /**
     * Get module summary
     *
     * @return Struct\Module
     */
    abstract public function getSummary();

    /**
     * Execute action
     *
     * Should, most probably dispatch to own controller implementation.
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @param Struct\ModuleConfiguration $module
     * @return mixed
     */
    abstract public function handle( RMF\Request $request, Struct\User $user, Struct\ModuleConfiguration $module );
}

