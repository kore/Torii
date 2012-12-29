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
     * User model
     *
     * @var Model\User
     */
    protected $user;

    /**
     * Mail messenger
     *
     * @var MailMessenger
     */
    protected $mailMessenger;

    /**
     * Construct from aggregated controller, which performs authorized actions
     *
     * @param Model\User $user
     * @param \Torii\MailMessenger $mailMessenger
     * @return void
     */
    public function __construct(Model\User $user, \Torii\MailMessenger $mailMessenger)
    {
        $this->user          = $user;
        $this->mailMessenger = $mailMessenger;
    }

    /**
     * Register
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function register(RMF\Request $request)
    {
        $errors  = array();
        $success = array();

        try {
            if (!filter_var($request->body['login'], \FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Login must be a valid E-Mail address.');
            }

            if ($request->body['password'] !== $request->body['repeat']) {
                throw new \Exception('Passwords do not match.');
            }

            if (!$request->body['password']) {
                throw new \Exception('Password may not be empty.');
            }

            $user = $this->user->create($request->body['login'], $request->body['password']);

            $this->mailMessenger->send(
                $user->email,
                new Struct\Response(
                    'registered.twig',
                    array(
                        'request' => $request,
                        'user'    => $user,
                    )
                )
            );

            $success[] = "We sent you an email to complete registration." .
                "Please confirm by clicking on the link in the mail.";
        } catch (\Exception $e) {
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
     * Register
     *
     * @param RMF\Request $request
     * @return Struct\Response
     */
    public function confirm(RMF\Request $request)
    {
        $errors  = array();
        $success = array();

        if ($this->user->verify($request->variables['user'], $request->variables['hash'])) {
            $success[] = "You are now verified and may log in.";
        } else {
            $errors[] = "Verification failed.";
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
    public function login(RMF\Request $request)
    {
        $errors = array();
        if (isset($request->body['submit'])) {
            try {
                if (!$user = $this->user->login($request->body['login'], $request->body['password'])) {
                    throw new \Exception('Invalid login data provided.');
                }
                $request->session['user'] = $user;

                // @TODO: This ia an ugly hack:
                header('Location: /portal');
                exit(0);
            } catch (\Exception $e) {
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
    public function logout(RMF\Request $request)
    {
        unset($request->session['user']);
        return $this->login($request);
    }
}
