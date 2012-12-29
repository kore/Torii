<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Struct;
use Torii\Struct;

/**
 * Base user struct
 *
 * @version $Revision$
 */
class User extends Struct
{
    /**
     * User ID
     *
     * @var string
     */
    public $id;

    /**
     * User email address
     *
     * @var string
     */
    public $email;

    /**
     * User settings
     *
     * @var UserSettings
     */
    public $settings;

    /**
     * User verification key
     *
     * @var string
     */
    public $verified;

    /**
     * Construct
     *
     * @param string $id
     * @param string $email
     * @param mixed $verified
     * @return void
     */
    public function __construct($id, $email, UserSettings $settings, $verified = null)
    {
        $this->id       = $id;
        $this->email    = $email;
        $this->settings = $settings;
        $this->verified = $verified;
    }
}
