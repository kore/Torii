<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Model;

use Torii\Struct;

/**
 * User model
 *
 * @version $Revision$
 */
class User
{
    /**
     * Construct from user gateway
     *
     * @param \Doctrine\DBAL\Connection $dbal
     * @param User\Hash $hash
     * @param mixed $id
     * @return void
     */
    public function __construct( \Doctrine\DBAL\Connection $dbal, User\Hash $hash, $id = null )
    {
        $this->dbal = $dbal;
        $this->hash = $hash;
        $this->id   = $id;
    }

    /**
     * Find user by login
     *
     * Find a user in the database by its login name and return the
     * User, or throw an exception.
     *
     * @param string $login
     * @return User
     */
    public function findByLogin( $login )
    {
        throw new \Exception( "@TODO: Implement" );
    }

    /**
     * Load user
     *
     * Return user model
     *
     * @param string $id
     * @return User
     */
    public function load( $id )
    {
        throw new \Exception( "@TODO: Implement" );
    }

    /**
     * Verifiy
     *
     * Verify user by the given hash
     *
     * @param string $id
     * @param string $hash
     * @return bool
     */
    public function verify( $id, $hash )
    {
        return (bool) $this->dbal->update(
            'user',
            array(
                'u_verified' => '1',
            ),
            array(
                'u_id'       => $id,
                'u_verified' => $hash,
            )
        );
    }

    /**
     * Create new user
     *
     * Returns user model
     *
     * @param string $id
     * @return User
     */
    public function create( $email, $password )
    {
        $this->dbal->insert( 'user', array(
            'u_login'    => $email,
            'u_password' => $this->hash->hashPassword( $password ),
            'u_verified' => $key = md5( microtime() ),
        ) );

        return new Struct\User(
            $this->dbal->lastInsertId(),
            $email,
            $key
        );
    }
}

