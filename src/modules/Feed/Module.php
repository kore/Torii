<?php
/**
 * This file is part of Torii
 *
 * @version $Revision: 1469 $
 */

namespace Torii\Module\Feed;

use Qafoo\RMF;
use Torii\Struct;
use Torii\DIC;
use Torii\Assets;

/**
 * Feed module definition
 *
 * @version $Revision$
 */
class Module extends \Torii\Module
{
    /**
     * Mapping of routes to actions
     *
     * @var array
     */
    protected $mapping = array(
        '(^/add)'     => 'addUrl',
        '(^/getList)' => 'getUrlList',
        '(^/update)'  => 'getFeedData',
    );

    /**
     * Get module summary
     *
     * @return Struct\Module
     */
    public function getSummary()
    {
        return new Struct\Module(
            'Feed',
            'Provides access to a number of RSS / Atom feeds'
        );
    }

    /**
     * Execute action
     *
     * Should, most probably dispatch to own controller implementation.
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @param Struct\ModuleConfiguration $module
     * @return mixed
     */
    public function handle( RMF\Request $request, Struct\User $user, Struct\ModuleConfiguration $module )
    {
        require __DIR__ . '/php/Controller.php';
        require __DIR__ . '/php/Model.php';

        $controller = new Controller(
            new Model(
                $this->dic->dbal
            )
        );

        foreach ( $this->mapping as $regexp => $action )
        {
            if ( preg_match( $regexp, $request->variables['path'] ) )
            {
                return $controller->$action( $request, $user );
            }
        }

        throw new \RuntimeException( "No route found for ". $request->variables['path'] );
    }

    /**
     * Inject DIC
     *
     * This is the interface between modules and the base system. Module
     * definitions are not supposed to be re-usable in other systems.
     *
     * The DIC in this case provides the means to configure your own module
     * environment. The DIC schould NOT be passed to any other objects.
     *
     * @param DIC $dic
     * @return void
     */
    public function initialize( DIC $dic )
    {
        parent::initialize( $dic );

        // Register path for custom templates
        $dic->twig->getLoader()->addPath( __DIR__ . '/templates' );

        // Register assets
        $dic->css->addFileSet( new Assets\FileSet( __DIR__ . '/css', '*.css' ) );
        $dic->javaScript->addFileSet( new Assets\FileSet( __DIR__ . '/js', '*.js' ) );
        $dic->images->addFileSet( new Assets\FileSet( __DIR__ . '/images', '*.png' ) );
    }
}

// Important for registration
return new Module();

