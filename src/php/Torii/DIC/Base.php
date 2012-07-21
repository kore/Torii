<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\DIC;

use Qafoo\RMF;
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
        'debug'          => true,
        'javaScript'     => true,
        'css'            => true,
        'images'         => true,
        'view'           => true,
        'twig'           => true,
        'dbal'           => true,
        'userModel'      => true,
        'mailMessenger'  => true,
        'modules'        => true,
        'authController' => true,
        'mainController' => true,
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

        $this->debug = function ( $dic )
        {
            return (
                $this->environment === 'development' ||
                $this->environment === 'testing'
            );
        };

        $this->configuration = function ( $dic )
        {
            return new Torii\Configuration(
                $dic->srcDir . '/config/config.ini',
                $dic->environment
            );
        };

        $this->javaScript = function( $dic )
        {
            return new Torii\Assets\Collection\Simple( array(
                new Torii\Assets\FileSet( $this->srcDir . '/js', 'vendor/jquery/*.js', 'vendor/*/*.min.js' ),
                new Torii\Assets\FileSet( $this->srcDir . '/js', 'vendor/bootstrap/*.js', 'vendor/*/*.min.js' ),
                new Torii\Assets\FileSet( $this->srcDir . '/js', '*.js' ),
            ) );
        };

        $this->css = function( $dic )
        {
            return new Torii\Assets\Collection\Simple( array(
                new Torii\Assets\FileSet( $this->srcDir . '/css', '*.min.css' ),
                new Torii\Assets\FileSet( $this->srcDir . '/css', 'app.css' ),
            ) );
        };

        $this->images = function( $dic )
        {
            return new Torii\Assets\Collection\Simple( array(
                new Torii\Assets\FileSet( $this->srcDir . '/images', '*.png' ),
            ) );
        };

        $this->twigExtension = function( $dic )
        {
            return new Torii\View\Twig\Extension( $dic );
        };

        $this->twig = function( $dic )
        {
            $twig = new \Twig_Environment(
                new \Twig_Loader_Filesystem( $dic->srcDir . '/templates' ),
                array(
//                    'cache' => $dic->srcDir . '/cache'
                )
            );

            $twig->addExtension( $dic->twigExtension );

            return $twig;
        };

        $this->view = function( $dic )
        {
            return new RMF\View\AcceptHeaderViewDispatcher( array(
                '(json)' => new RMF\View\Json(),
                '(html)' => new Torii\View\Twig( $dic->twig ),
            ) );
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

        $this->mailMessenger = function( $dic )
        {
            return new Torii\MailMessenger(
                $dic->twig,
                $dic->configuration->mailSender
            );
        };

        $this->authController = function( $dic )
        {
            return new Torii\Controller\Auth(
                $dic->userModel,
                $dic->mailMessenger
            );
        };

        $this->modules = function( $dic )
        {
            $modules = array();
            foreach ( glob( __DIR__ . '/../Module/*/Module.php' ) as $moduleFile )
            {
                $module = include $moduleFile;
                if ( !$module instanceof Torii\Module )
                {
                    throw new \RuntimeException( "Invalid module definition in $moduleFile. Must return an instance of \\Torii\\Module." );
                }

                $module->initialize( $dic );
                $modules[basename( dirname( $moduleFile ) )] = $module;
            }

            return $modules;
        };

        $this->mainController = function( $dic )
        {
            return new Torii\Controller\Auth\Filter(
                new Torii\Controller\Main(
                    $dic->userModel,
                    $dic->modules
                )
            );
        };

        $this->assetController = function( $dic )
        {
            return new Torii\Controller\Assets( array(
                '(/scripts/(?P<path>.*)$)' => $dic->javaScript,
                '(/styles/(?P<path>.*)$)'  => $dic->css,
                '(/images/(?P<path>.*)$)'  => $dic->images,
            ) );
        };
    }
}

