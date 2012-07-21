<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Struct;
use Torii\Struct;

/**
 * Base user settings struct
 *
 * @version $Revision$
 */
class UserSettings extends Struct
{
    /**
     * User name
     *
     * @var string
     */
    public $name;

    /**
     * Number of columns
     *
     * @var int
     */
    public $columns = 3;

    /**
     * Installed modules
     *
     * @var array
     */
    public $modules = array();

    /**
     * Create from data array
     *
     * @param array $data
     * @return UserSettings
     */
    public static function create( $data )
    {
        var_dump( $data );
        $settings = new static();
        if ( is_array( $data ) )
        {
            foreach ( $data as $key => $value )
            {
                $settings->$key = $value;
            }

            foreach ( $settings->modules as $cnr => $column )
            {
                foreach ( $column as $mnr => $module )
                {
                    $settings->modules[$cnr][$mnr] = ModuleConfiguration::create( $module );
                }
            }
        }

        return $settings;
    }
}

