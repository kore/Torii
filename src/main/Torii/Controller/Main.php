<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Controller;

use Qafoo\RMF;
use Torii\Struct;
use Torii\Model;

/**
 * Main controller
 *
 * @version $Revision$
 */
class Main
{
    /**
     * User model
     *
     * @var Model\User
     */
    protected $user;

    /**
     * Available modules
     *
     * @var Struct\Module[]
     */
    protected $modules;

    /**
     * Construct from aggregated controller, which performs authorized actions
     *
     * @param Model\User $user
     * @param Struct\Module[] $modules
     * @return void
     */
    public function __construct( Model\User $user, array $modules )
    {
        $this->user    = $user;
        $this->modules = $modules;
    }

    /**
     * Main view action
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @return Struct\Response
     */
    public function view( RMF\Request $request, Struct\User $user )
    {
        return new Struct\Response(
            'main.twig',
            array(
                'settings' => $user->settings,
                'modules'  => $this->modules,
            )
        );
    }

    /**
     * Show settings page
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @return Struct\Response
     */
    public function showSettings( RMF\Request $request, Struct\User $user )
    {
        return new Struct\Response(
            'settings.twig',
            array(
                'settings' => $user->settings,
            )
        );
    }

    /**
     * Show settings page
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @return Struct\Response
     */
    public function updateSettings( RMF\Request $request, Struct\User $user )
    {
        if ( isset( $request->body['submit'] ) )
        {
            $user->settings->columns = (int) $request->body['columns'];
            $user->settings->name    = $request->body['name'];

            $this->user->update( $user );
        }

        return $this->showSettings( $request, $user );
    }

    /**
     * Add module
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @return Struct\Response
     */
    public function addModule( RMF\Request $request, Struct\User $user )
    {
        if ( isset( $request->body['submit'] ) )
        {
            $column = (int) $request->body['column'];
            $module = $request->body['module'];

            if ( ( $column > $user->settings->columns ) &&
                 ( !isset( $this->modules[$module] ) ) )
            {
                throw new \RuntimeException( "Invalid parameters" );
            }

            if ( !isset( $user->settings->modules[$column] ) )
            {
                $user->settings->modules[$column] = array();
            }

            $user->settings->modules[$column][] = new Struct\ModuleConfiguration(
                $this->getModuleId( $request->body['title'] ),
                $module,
                $request->body['title']
            );
            $this->user->update( $user );
        }

        return $this->view( $request, $user );
    }

    /**
     * Get a random, unique module ID
     *
     * @param string $title
     * @return string
     */
    protected function getModuleId( $title )
    {
        return preg_replace( '([^a-z0-9_]+)', '_', strtolower( $title ) ) . '_' . substr( md5( microtime() ), 0, 8 );
    }
}

