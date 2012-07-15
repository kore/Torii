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
     * Index action
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function index( RMF\Request $request )
    {
        return new Struct\Response(
            'login.twig'
        );
    }
}

