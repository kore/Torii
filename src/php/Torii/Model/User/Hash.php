<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Model\User;

/**
 * Base class for hash implementations
 *
 * @version $Revision$
 */
abstract class Hash
{
    /**
     * Hash given password
     *
     * @param string $password
     * @return string
     */
    abstract public function hashPassword( $password );

    /**
     * Verify password against provided hash
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    abstract public function verifyPassword( $password, $hash );
}

