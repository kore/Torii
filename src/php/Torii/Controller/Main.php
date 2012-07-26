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

        header( "Location: /portal" );
        exit( 0 );
    }

    /**
     * Resort modules
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @return Struct\Response
     */
    public function resort( RMF\Request $request, Struct\User $user )
    {
        $modules = array();
        foreach ( $user->settings->modules as $column )
        {
            foreach ( $column as $module )
            {
                $modules[$module->id] = $module;
            }
        }

        $user->settings->modules = array();
        foreach ( $request->body['modules'] as $cnr => $column )
        {
            $user->settings->modules[$cnr] = array();
            foreach ( $column as $mnr => $moduleId )
            {
                $user->settings->modules[$cnr][$mnr] = $modules[$moduleId];
            }
        }

        $this->user->update( $user );

        return array(
            "ok" => true,
        );
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
            $column = (int) $request->body['column'] - 1;
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

    /**
     * Dispatch request to module
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @return Struct\Response
     */
    public function dispatch( RMF\Request $request, Struct\User $user )
    {
        $module = $this->getModuleConfig( $user, $request->variables['module'] );

        if ( !isset( $this->modules[$module->type] ) )
        {
            throw new \RuntimeException( "Invalid module: " . $module->type );
        }

        $moduleHandler = $this->modules[$module->type];
        return $moduleHandler->handle( $request, $user, $module );
    }

    /**
     * Get Module configuration from ID and user
     *
     * @param Struct\User $user
     * @param string $moduleId
     * @return Struct\Module
     */
    protected function getModuleConfig( Struct\User $user, $moduleId )
    {
        foreach ( $user->settings->modules as $column )
        {
            foreach ( $column as $module )
            {
                if ( $module->id === $moduleId )
                {
                    return $module;
                }
            }
        }

        throw new \RuntimeException( "Invalid module $moduleId" );
    }
}

