<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\DIC;
use Torii\DIC;
use Torii;

/**
 * Base DIC
 *
 * @version $Revision$
 */
class Base extends DIC
{
    /**
     * Array with names of objects, which are always shared inside of this DIC
     * instance.
     *
     * @var array(string)
     */
    protected $alwaysShared = array(
        'srcDir'         => true,
        'configuration'  => true,
        'authController' => true,
        'view'           => true,
        'twig'           => true,
    );

    /**
     * Initialize DIC values
     *
     * @return void
     */
    public function initialize()
    {
        $this->srcDir = function ( $dic )
        {
            return substr( __DIR__, 0, strpos( __DIR__, '/src/' ) + 4 );
        };

        $this->configuration = function ( $dic )
        {
            return new Torii\Configuration(
                $dic->srcDir . '/config/config.ini',
                $dic->environment
            );
        };

        $this->twig = function ( $dic )
        {
            return new \Twig_Environment(
                new \Twig_Loader_Filesystem( $dic->srcDir . '/templates' ),
                array(
//                    'cache' => $dic->srcDir . '/cache'
                )
            );
        };

        $this->view = function( $dic )
        {
            return new Torii\View\Twig( $dic->twig );
        };

        $this->dbal = function( $dic )
        {
            return \Doctrine\DBAL\DriverManager::getConnection(
                $dic->configuration->database,
                new \Doctrine\DBAL\Configuration()
            );
        };

        $this->userModel = function( $dic )
        {
            return new Torii\Model\User(
                $dic->dbal,
                new Torii\Model\User\Hash\PBKDF2()
            );
        };

        $this->authController = function( $dic )
        {
            return new Torii\Controller\Auth( $dic->userModel );
        };
    }
}

