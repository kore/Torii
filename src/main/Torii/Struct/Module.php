<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Struct;
use Torii\Struct;

/**
 * Base module struct
 *
 * @version $Revision$
 */
class Module extends Struct
{
    /**
     * User ID
     *
     * @var string
     */
    public $name;

    /**
     * User email address
     *
     * @var string
     */
    public $description;

    /**
     * Construct
     *
     * @param string $name
     * @param string $description
     * @return void
     */
    public function __construct( $name, $description )
    {
        $this->name        = $name;
        $this->description = $description;
    }
}

