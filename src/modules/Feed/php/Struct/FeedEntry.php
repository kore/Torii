<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Module\Feed\Struct;

use Torii\Struct;

/**
 * Feed entry struct
 *
 * @version $Revision$
 */
class FeedEntry extends Struct
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
    public $link;

    /**
     * Feed name
     *
     * @var string
     */
    public $feed;

    /**
     * Title
     *
     * @var string
     */
    public $title;

    /**
     * Entry publish / change date
     *
     * @var int
     */
    public $date;

    /**
     * Description
     *
     * @var string
     */
    public $description;

    /**
     * Content
     *
     * @var string
     */
    public $content;

    /**
     * Construct
     *
     * @param mixed $id
     * @param string $link
     * @param string $feed
     * @param string $title
     * @return void
     */
    public function __construct( $id, $link, $feed, $title )
    {
        $this->id    = $id;
        $this->link  = $link;
        $this->feed  = $feed;
        $this->title = $title;
    }

    /**
     * Create from ID and data array
     *
     * @param mixed $id
     * @param array $data
     * @return FeedEntry
     */
    public static function create( $id, $feed, array $data )
    {
        $entry = new static( $id, $data['link'], $feed, $data['title'] );

        $entry->description = $data['description'];
        $entry->content     = $data['content'];

        return $entry;
    }
}

