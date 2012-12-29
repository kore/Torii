<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Module\Feed\Struct;

use Torii\Struct;

/**
 * URl struct
 *
 * @version $Revision$
 */
class Url extends Struct
{
    /**
     * URL ID
     *
     * @var mixed
     */
    public $id;

    /**
     * URL
     *
     * @var string
     */
    public $url;

    /**
     * Name
     *
     * @var string
     */
    public $name;

    /**
     * Last request status
     *
     * @var int
     */
    public $status;

    /**
     * Last request time
     *
     * @var int
     */
    public $requested;

    /**
     * Construct
     *
     * @param mixed $id
     * @param string $url
     * @param string $name
     * @param int $status
     * @param int $requested
     * @return void
     */
    public function __construct( $id, $url, $name = null, $status = null, $requested = null )
    {
        $this->id        = $id;
        $this->url       = $url;
        $this->name      = $name;
        $this->status    = $status;
        $this->requested = $requested;
    }
}
