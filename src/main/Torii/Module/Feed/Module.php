<?php
/**
 * This file is part of Torii
 *
 * @version $Revision: 1469 $
 */

namespace Torii\Module\Feed;

use Qafoo\RMF;
use Torii\Struct;

/**
 * Feed module definition
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
            'Feed',
            'Provides access to a number of RSS / Atom feeds'
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
        throw new \RuntimeException( '@TODO: Implement' );
    }
}

// Important for registration
return new Module();

