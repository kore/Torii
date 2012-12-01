<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Module\Account\Struct;

use Torii\Struct;

/**
 * Account struct
 *
 * @version $Revision$
 */
class Account extends Struct
{
    /**
     * ID
     *
     * @var string
     */
    public $id;

    /**
     * Name
     *
     * @var string
     */
    public $name;

    /**
     * Bankleitzahl
     *
     * @var string
     */
    public $blz;

    /**
     * Kontonummer
     *
     * @var string
     */
    public $knr;

    /**
     * PIN
     *
     * @var string
     */
    public $pin;

    /**
     * Construct
     *
     * @param string $id
     * @param string $name
     * @param string $blz
     * @param string $knr
     * @param string $pin
     * @return void
     */
    public function __construct( $id = null, $name = null, $blz = null, $knr = null, $pin = null )
    {
        $this->id   = $id;
        $this->name = $name;
        $this->blz  = $blz;
        $this->knr  = $knr;
        $this->pin  = $pin;
    }
}

