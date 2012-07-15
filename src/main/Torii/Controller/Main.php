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
}

