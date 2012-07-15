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
     * Log user in
     *
     * Returns false if credentials are wrong or user is disabled. Returns User 
     * struct, if successfull.
     *
     * @param string $login
     * @param string $password
     * @return User
     */
    public function login( $login, $password )
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $queryBuilder
            ->select( 'u_id', 'u_login', 'u_password', 'u_settings' )
            ->from( 'user', 'u' )
            ->where(
                $queryBuilder->expr()->andx(
                    $queryBuilder->expr()->eq( 'u_login', ':login' ),
                    $queryBuilder->expr()->eq( 'u_verified', '1' )
                )
            )
            ->setParameter( ':login', $login );

        $statement = $queryBuilder->execute();
        $result = $statement->fetch( \PDO::FETCH_ASSOC );

        if ( !$result )
        {
            // User not found or not verified
            return false;
        }

        if ( !$this->hash->verifyPassword( $password, $result['u_password'] ) )
        {
            // invalid password provided
            return false;
        }

        return new Struct\User(
            $result['u_id'],
            $result['u_login'],
            Struct\UserSettings::create( json_decode( $result['u_settings'] ) )
        );
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
            new Struct\UserSettings(),
            $key
        );
    }
}

