<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Struct;
use Torii\Struct;

/**
 * Base module configuration struct
 *
 * @version $Revision$
 */
class ModuleConfiguration extends Struct
{
    /**
     * Module ID
     *
     * @var string
     */
    public $id;

    /**
     * Module type
     *
     * @var string
     */
    public $type;

    /**
     * Module title
     *
     * @var string
     */
    public $title;

    /**
     * Construct
     *
     * @param string $id
     * @param string $type
     * @param string $title
     * @return void
     */
    public function __construct( $id, $type, $title )
    {
        $this->id    = $id;
        $this->type  = $type;
        $this->title = $title;
    }
}

