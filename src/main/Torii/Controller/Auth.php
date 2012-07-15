<?php
/**
 * This file is part of Torii
 *
 * @version $Revision: 1469 $
 */

namespace Torii\Controller;

use Qafoo\RMF;
use Torii\Struct;
use Torii\Model;

/**
 * Auth controller
 *
 * @version $Revision$
 */
class Auth
{
    /**
     * Aggregated controller
     *
     * @var Controller
     */
    protected $controller;

    /**
     * User model
     *
     * @var Model\User
     */
    protected $user;

    /**
     * Requests, which may pass without authorization
     *
     * Array of regular expressions
     *
     * @var array
     */
    protected $unauthorized = array();

    /**
     * Construct from aggregated controller, which performs authorized actions
     *
     * @param Model\User $user
     * @return void
     */
    public function __construct( Model\User $user )
    {
        $this->user = $user;
    }

    /**
     * Register
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function register( RMF\Request $request )
    {
        $errors  = array();
        $success = array();

        try
        {
            if ( !filter_var( $request->body['login'], \FILTER_VALIDATE_EMAIL ) )
            {
                throw new \Exception( 'Login must be a valid E-Mail address.' );
            }

            if ( $request->body['password'] !== $request->body['repeat'] )
            {
                throw new \Exception( 'Passwords do not match.' );
            }

            if ( !$request->body['password'] )
            {
                throw new \Exception( 'Password may not be empty.' );
            }

            $user = $this->user->create( $request->body['login'], $request->body['password'] );
            $success[] = "We sent you an email to complete registration. Please confirm by clicking on the link in the mail.";
        }
        catch ( \Exception $e )
        {
            $errors[] = $e->getMessage();
        }

        return new Struct\Response(
            'login.twig',
            array(
                'errors'  => $errors,
                'success' => $success,
            )
        );
    }

    /**
     * Login user
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function login( RMF\Request $request )
    {
        $errors = array();
        if ( isset( $request->body['submit'] ) )
        {
            try
            {
                $user = $this->user->findByLogin( $request->body['login'] );
                if ( !$user->verifyPassword( $request->body['password'] ) )
                {
                    throw new \Exception( 'Invalid password provided.' );
                }
                $request->session['user'] = $user->id;

                // @TODO: This ia na ugly hack:
                header( 'Location: /' );
                exit( 0 );
            }
            catch ( \Exception $e )
            {
                $errors[] = "Could not login with the provided data.";
            }
        }

        return new Struct\Response(
            'login.twig',
            array(
                'errors' => $errors,
            )
        );
    }

    /**
     * Logout user
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function logout( RMF\Request $request )
    {
        unset( $request->session['user'] );
        return $this->login( $request );
    }
}

