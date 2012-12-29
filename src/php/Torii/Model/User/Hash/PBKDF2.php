<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Model\User\Hash;

use Torii\Model\User\Hash;

/**
 * PBKDF2 hash implementation
 *
 * @version $Revision$
 */
class PBKDF2 extends Hash
{
    /**
     * Used hashing algorithm
     *
     * @var string
     */
    protected $algorithm;

    /**
     * Number of iterations used
     *
     * @var int
     */
    protected $iterations;

    /**
     * Hash length
     *
     * @var int
     */
    protected $hashLength = 24;

    /**
     * Salt length
     *
     * @var int
     */
    const SALT_LENGTH = 24;

    /**
     * Construct from hashing parameters
     *
     * @param string $algorithm
     * @param int $iterations
     * @param int $hashLength
     * @return void
     */
    public function __construct( $algorithm = "sha256", $iterations = 1024, $hashLength = 24 )
    {
        $this->algorithm  = $algorithm;
        $this->iterations = $iterations;
        $this->hashLength = $hashLength;
    }

    /**
     * Hash given password
     *
     * @param string $password
     * @return string
     */
    public function hashPassword( $password, $salt = null )
    {
        $salt = $salt ?: $this->getRandomString();
        return implode( ':', array(
            $this->algorithm,
            $this->iterations,
            $salt,
            $this->pbkdf2(
                $this->algorithm,
                $password,
                $salt,
                $this->iterations,
                $this->hashLength
            )
        ) );
    }

    /**
     * Get sensible random string
     *
     * Should not be guessable from the outside
     *
     * @return void
     */
    protected function getRandomString()
    {
        return substr(
            md5(
                microtime() .
                uniqid( mt_rand(), true ) .
                implode( '', fstat( fopen( __FILE__, 'r' ) ) )
            ),
            0,
            self::SALT_LENGTH
        );
    }

    /**
     * PBKDF2 key derivation function
     *
     * As defined by RSA's PKCS #5: https://www.ietf.org/rfc/rfc2898.txt
     *
     * Test vectors can be found here: https://www.ietf.org/rfc/rfc6070.txt
     *
     * This implementation of PBKDF2 was originally created by defuse.ca
     *
     * @param string $algorithm - The hash algorithm to use. Recommended: SHA256
     * @param string $password - The password.
     * @param string $salt - A salt that is unique to the password.
     * @param string $count - Iteration count. Higher is better, but slower. Recommended: At least 1024.
     * @param int $keyLength - The length of the derived key in bytes.
     * @param bool $rawOutput - If true, the key is returned in raw binary format. Hex encoded otherwise.
     * @return A $keyLength-byte key derived from the password and salt.
     */
    protected function pbkdf2( $algorithm, $password, $salt, $count, $keyLength, $rawOutput = false )
    {
        $algorithm = strtolower( $algorithm );
        if ( !in_array( $algorithm, hash_algos(), true ) ) {
            throw new \RuntimeException( 'PBKDF2 ERROR: Invalid hash algorithm.' );
        }

        if ( $count <= 0 || $keyLength <= 0 ) {
            throw new \RuntimeException( 'PBKDF2 ERROR: Invalid parameters.' );
        }

        $hashLength = strlen( hash( $algorithm, "", true ) );
        $blockCount = ceil( $keyLength / $hashLength );

        $output = "";
        for( $i = 1; $i <= $blockCount; ++$i ) {
            $last = $salt . pack( "N", $i );
            $last = $xorsum = hash_hmac( $algorithm, $last, $password, true );
            for ( $j = 1; $j < $count; ++$j ) {
                $xorsum ^= $last = hash_hmac( $algorithm, $last, $password, true );
            }
            $output .= $xorsum;
        }

        $output = substr( $output, 0, $keyLength );
        return $rawOutput ? $output : bin2hex( $output );
    }

    /**
     * Verify password against provided hash
     *
     * @param string $password
     * @param string $hash
     * @return bool
     */
    public function verifyPassword( $password, $hash )
    {
        list( $algorithm, $iterations, $salt, $hash ) = explode( ':', $hash );
        return $hash === $this->pbkdf2( $algorithm, $password, $salt, $iterations, strlen( hex2bin( $hash ) ) );
    }
}
