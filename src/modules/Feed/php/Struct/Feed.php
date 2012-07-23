<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Module\Feed\Struct;

use Torii\Struct;

/**
 * Feed struct
 *
 * @version $Revision$
 */
class Feed extends Struct
{
    /**
     * Url
     *
     * @var Url
     */
    public $url;

    /**
     * Status
     *
     * @var int
     */
    public $status;

    /**
     * Feed entries
     *
     * @var FeedEntry[]
     */
    public $entries = array();

    /**
     * Construct
     *
     * @param Url $url
     * @param int $status
     * @param array $entries
     * @return void
     */
    public function __construct( Url $url, $status = null, array $entries = array() )
    {
        $this->url     = $url;
        $this->status  = $status;
        $this->entries = $entries;
    }
}

