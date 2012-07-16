<?php
/**
 * This file is part of Torii
 *
 * @version $Revision: 1469 $
 */

namespace Torii\Module\GoogleSearch;

use Qafoo\RMF;
use Torii\Struct;
use Torii\DIC;

/**
 * GoogleSearch module definition
 *
 * @version $Revision$
 */
class Module extends \Torii\Module
{
    /**
     * Get module summary
     *
     * @return Struct\Module
     */
    public function getSummary()
    {
        return new Struct\Module(
            'GoogleSearch',
            'Search using google'
        );
    }

    /**
     * Execute action
     *
     * Should, most probably dispatch to own controller implementation.
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @return Struct\Response
     */
    public function handle( RMF\Request $request, Struct\User $user )
    {
        // Nothing to do in here…
    }

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
        parent::initialize( $dic );

        // Register path for custom templates
        $dic->twig->getLoader()->addPath( __DIR__ . '/templates' );
    }
}

// Important for registration
return new Module();

