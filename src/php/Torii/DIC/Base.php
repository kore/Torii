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
        'srcDir'          => true,
        'environment'     => true,
        'configuration'   => true,
        'commandRegistry' => true,
        'debug'           => true,
        'mimeTypeGuesser' => true,
        'javaScript'      => true,
        'templates'       => true,
        'css'             => true,
        'images'          => true,
        'view'            => true,
        'twig'            => true,
        'dbal'            => true,
        'userModel'       => true,
        'mailMessenger'   => true,
        'modules'         => true,
        'authController'  => true,
        'mainController'  => true,
    );

    /**
     * Initialize DIC values
     *
     * @return void
     */
    public function initialize()
    {
        $this->srcDir = function ( $dic ) {
            return substr( __DIR__, 0, strpos( __DIR__, '/src/' ) + 4 );
        };

        $this->environment = function ( $dic ) {
            if ( !is_file( $file = $dic->srcDir . '/../build.properties.local' ) ) {
                return 'production';
            }

            $config = @parse_ini_file( $file );
            if ( !isset( $config['commons.env'] ) ) {
                return 'production';
            }

            return $config['commons.env'];
        };

        $this->debug = function ( $dic ) {
            return (
                $dic->environment === 'development' ||
                $dic->environment === 'testing'
            );
        };

        $this->configuration = function ( $dic ) {
            return new Torii\Configuration(
                $dic->srcDir . '/config/config.ini',
                $dic->environment
            );
        };

        $this->commandRegistry = function ( $dic ) {
            $commandRegistry = new \Arbit\Periodic\CommandRegistry();
            $commandRegistry->registerCommand(
                'torii.module',
                new Torii\Command\Module( $dic->modules )
            );
            $commandRegistry->registerCommand(
                'torii.assets',
                new Torii\Command\AssetWriter(
                    $dic->srcDir . '/htdocs/',
                    array(
                        '/scripts/'   => $dic->javaScript,
                        '/styles/'    => $dic->css,
                        '/images/'    => $dic->images,
                        '/templates/' => $dic->templates,
                    ),
                    $dic->debug
                )
            );
            $commandRegistry->registerCommand(
                'torii.feed.favicons',
                $dic->modules['Feed']->getFaviconCommand()
            );
            $commandRegistry->registerCommand(
                'torii.feed.cleanup',
                $dic->modules['Feed']->getCleanupCommand()
            );

            return $commandRegistry;
        };

        $this->mimeTypeGuesser = function( $dic ) {
            return new Torii\Assets\MimeTypeGuesser\Extension();
        };

        $this->javaScript = function( $dic ) {
            return new Torii\Assets\Collection\Simple( array(
                new Torii\Assets\FileSet( $dic->srcDir . '/js', 'vendor/jquery/*.js', 'vendor/*/*.min.js' ),
                new Torii\Assets\FileSet( $dic->srcDir . '/js', 'vendor/bootstrap/*.js', 'vendor/*/*.min.js' ),
                new Torii\Assets\FileSet( $dic->srcDir . '/js', 'vendor/mustache/*.js', 'vendor/*/*.min.js' ),
                new Torii\Assets\FileSet( $dic->srcDir . '/js', 'vendor/underscore/*.js', 'vendor/*/*.min.js' ),
                new Torii\Assets\FileSet( $dic->srcDir . '/js', '*.js' ),
            ) );
        };

        $this->templates = function( $dic ) {
            return new Torii\Assets\Collection\Simple( array(
            ) );
        };

        $this->css = function( $dic ) {
            return new Torii\Assets\Collection\Simple( array(
                new Torii\Assets\FileSet( $dic->srcDir . '/css', 'bootstrap.min.css' ),
                new Torii\Assets\FileSet( $dic->srcDir . '/css', 'bootstrap-responsive.min.css' ),
                new Torii\Assets\FileSet( $dic->srcDir . '/css', 'app.css' ),
            ) );
        };

        $this->images = function( $dic ) {
            return new Torii\Assets\Collection\Simple( array(
                new Torii\Assets\FileSet( $dic->srcDir . '/images', '*.png' ),
            ) );
        };

        $this->twigExtension = function( $dic ) {
            return new Torii\View\Twig\Extension( $dic );
        };

        $this->twig = function( $dic ) {
            $twig = new \Twig_Environment(
                new \Twig_Loader_Filesystem( $dic->srcDir . '/twig' ),
                array(
//                    'cache' => $dic->srcDir . '/cache'
                )
            );

            $twig->addExtension( $dic->twigExtension );

            return $twig;
        };

        $this->view = function( $dic ) {
            return new RMF\View\AcceptHeaderViewDispatcher( array(
                '(json)' => new RMF\View\Json(),
                '(html)' => new Torii\View\Twig( $dic->twig ),
            ) );
        };

        $this->dbal = function( $dic ) {
            $connection = \Doctrine\DBAL\DriverManager::getConnection(
                $dic->configuration->database,
                new \Doctrine\DBAL\Configuration()
            );
            $connection->getConfiguration()->setSQLLogger(
                new Torii\Debug\SQLLogger(
                    $dic->srcDir . '/var/slow.log',
                    1.
                )
            );

            return $connection;
        };

        $this->userModel = function( $dic ) {
            return new Torii\Model\User(
                $dic->dbal,
                new Torii\Model\User\Hash\PBKDF2()
            );
        };

        $this->mailMessenger = function( $dic ) {
            return new Torii\MailMessenger(
                $dic->twig,
                $dic->configuration->mailSender
            );
        };

        $this->authController = function( $dic ) {
            return new Torii\Controller\Auth(
                $dic->userModel,
                $dic->mailMessenger
            );
        };

        $this->modules = function( $dic ) {
            $modules = array();
            foreach ( glob( $dic->srcDir . '/modules/*/Module.php' ) as $moduleFile ) {
                $module = include $moduleFile;
                if ( !$module instanceof Torii\Module ) {
                    throw new \RuntimeException( "Invalid module definition in $moduleFile. Must return an instance of \\Torii\\Module." );
                }

                $module->initialize( $dic );
                $modules[basename( dirname( $moduleFile ) )] = $module;
            }

            return $modules;
        };

        $this->mainController = function( $dic ) {
            return new Torii\Controller\Auth\Filter(
                new Torii\Controller\Main(
                    $dic->userModel,
                    $dic->modules
                )
            );
        };

        $this->assetController = function( $dic ) {
            return new Torii\Controller\Assets( array(
                '(/scripts/(?P<path>.*)$)'   => new Torii\Assets\Collection\Filter\MimeType(
                    $dic->javaScript,
                    $dic->mimeTypeGuesser
                ),
                '(/styles/(?P<path>.*)$)'    => new Torii\Assets\Collection\Filter\MimeType(
                    $dic->css,
                    $dic->mimeTypeGuesser
                ),
                '(/images/(?P<path>.*)$)'    => new Torii\Assets\Collection\Filter\MimeType(
                    $dic->images,
                    $dic->mimeTypeGuesser
                ),
                '(/templates/(?P<path>.*)$)' => new Torii\Assets\Collection\Filter\MimeType(
                    $dic->templates,
                    $dic->mimeTypeGuesser
                ),
            ) );
        };
    }
}
