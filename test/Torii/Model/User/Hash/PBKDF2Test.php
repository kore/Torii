<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Model\User\Hash;

/**
 * @version $Revision$
 * @covers \Torii\Model\User\Hash\PBKDF2
 * @group unittest
 */
class PBKDF2Test extends \PHPUnit_Framework_TestCase
{
    /**
     * Common PBKDF2 test vectors, as specified here:
     *
     * https://www.ietf.org/rfc/rfc6070.txt
     *
     * @return void
     */
    public function getCommonTestVectors()
    {
        return array(
            array(
                "password",
                "salt",
                1,
                20,
                '0c60c80f961f0e71f3a9b524af6012062fe037a6',
            ),
            array(
                "password",
                "salt",
                2,
                20,
                'ea6c014dc72d6f8ccd1ed92ace1d41f0d8de8957',
            ),
            array(
                "password",
                "salt",
                4096,
                20,
                '4b007901b765489abead49d926f721d065a429c1',
            ),
            /* To slow…
            array(
                "password",
                "salt",
                16777216,
                20,
                'eefe3d61cd4da4e4e9945b3d6ba2158c2634e984',
            ), // */
            array(
                "passwordPASSWORDpassword",
                "saltSALTsaltSALTsaltSALTsaltSALTsalt",
                4096,
                25,
                '3d2eec4fe41c849b80c8d83662c0e44a8b291a964cf2f07038',
            ),
            array(
                "pass\0word",
                "sa\0lt",
                4096,
                16,
                '56fa6aa75548099dcc37d7f03425e0c3',
            ),
        );
    }

    /**
     * @dataProvider getCommonTestVectors
     */
    public function testHash( $password, $salt, $iterations, $length, $expected )
    {
        $hash = new PBKDF2( 'sha1', $iterations, $length );

        $this->assertSame(
            "sha1:$iterations:$salt:$expected",
            $hash->hashPassword( $password, $salt )
        );
    }

    public function testRandomSalt()
    {
        $hash = new PBKDF2();

        $this->assertNotSame(
            $hash->hashPassword( "password" ),
            $hash->hashPassword( "password" )
        );
    }

    public function testVerifyPassword()
    {
        $hash = new PBKDF2();

        $this->assertTrue(
            $hash->verifyPassword( "password", $hash->hashPassword( "password" ) )
        );
    }

    public function testRejectPassword()
    {
        $hash = new PBKDF2();

        $this->assertFalse(
            $hash->verifyPassword( "somethingelse", $hash->hashPassword( "password" ) )
        );
    }
}

