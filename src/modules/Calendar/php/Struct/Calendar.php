<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Module\Calendar\Struct;

use Torii\Struct;

/**
 * Feed struct
 *
 * @version $Revision$
 */
class Calendar extends Struct
{
    /**
     * Url
     *
     * @var Url
     */
    public $url;

    /**
     * Events
     *
     * @var Event[]
     */
    public $entries = array();

    /**
     * Construct
     *
     * @param Url $url
     * @param array $entries
     * @return void
     */
    public function __construct( Url $url, array $entries = array() )
    {
        $this->url     = $url;
        $this->entries = $entries;
    }
}
