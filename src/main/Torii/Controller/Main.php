<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Controller;

use Qafoo\RMF;
use Torii\Struct;

/**
 * Main controller
 *
 * @version $Revision$
 */
class Main
{
    /**
     * Main view action
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @return Struct\Response
     */
    public function view( RMF\Request $request, Struct\User $user )
    {
        return new Struct\Response(
            'main.twig'
        );
    }
}

