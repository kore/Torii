<?php
/**
 * This file is part of Torii
 *
 * @version $Revision: 1469 $
 */

namespace Torii;

use Arbit\Periodic;

/**
 * Feed module definition
 *
 * @version $Revision$
 */
interface Cronable
{
    /**
     * Run something in the module. Usually refresh some data.
     *
     * @param Periodic\Logger $logger
     * @return void
     */
    public function refresh(Periodic\Logger $logger);
}
