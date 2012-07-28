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
     * Mixed settings, stored per module
     *
     * @var array
     */
    public $settings;

    /**
     * Construct
     *
     * @param string $id
     * @param string $type
     * @param string $title
     * @param array $settings
     * @return void
     */
    public function __construct( $id, $type, $title, array $settings = array() )
    {
        $this->id       = $id;
        $this->type     = $type;
        $this->title    = $title;
        $this->settings = $settings;
    }

    /**
     * Create from data array
     *
     * @param array $data
     * @return ModuleConfiguration
     */
    public static function create( $data )
    {
        return new static(
            $data['id'],
            $data['type'],
            $data['title'],
            isset( $data['settings'] ) ? $data['settings'] : array()
        );
    }
}

