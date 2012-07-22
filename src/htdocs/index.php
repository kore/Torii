<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii;
use Qafoo\RMF;

require __DIR__ . '/../php/Torii/bootstrap.php';
$dic = new DIC\Base();
$dic->environment = 'development';

$dispatcher = new RMF\Dispatcher\Simple(
    new RMF\Router\Regexp( array(
        // Torii main actions
        '(^/portal$)' => array(
            'GET'  => array( $dic->mainController, 'view' ),
        ),
        '(^/portal/settings$)' => array(
            'GET'  => array( $dic->mainController, 'showSettings' ),
            'POST' => array( $dic->mainController, 'updateSettings' ),
        ),
        '(^/portal/addModule$)' => array(
            'POST' => array( $dic->mainController, 'addModule' ),
        ),
        '(^/portal/resort$)' => array(
            'POST' => array( $dic->mainController, 'resort' ),
        ),

        // Torii module dispatching
        '(^/module/(?P<module>[A-Za-z0-9_-]+)(?P<path>/.*)?$)' => array(
            'PUT'    => array( $dic->mainController, 'dispatch' ),
            'DELETE' => array( $dic->mainController, 'dispatch' ),
            'POST'   => array( $dic->mainController, 'dispatch' ),
            'GET'    => array( $dic->mainController, 'dispatch' ),
        ),

        // Auth related actions
        '(^/$)' => array(
            'GET'  => array( $dic->authController, 'login' ),
        ),
        '(^/auth/login$)' => array(
            'POST'  => array( $dic->authController, 'login' ),
        ),
        '(^/auth/register$)' => array(
            'POST'  => array( $dic->authController, 'register' ),
        ),
        '(^/auth/confirm/(?P<user>[a-f0-9]+)/(?P<hash>[a-f0-9]+)$)' => array(
            'GET'  => array( $dic->authController, 'confirm' ),
        ),
        '(^/auth/logout$)' => array(
            'GET'  => array( $dic->authController, 'logout' ),
        ),
        '(^/auth/forgot$)' => array(
            'GET'  => array( $dic->authController, 'showForgot' ),
            'POST'  => array( $dic->authController, 'forgot' ),
        ),

        // Fallback handling of assets
        '(^/(?:styles|images|scripts)/)' => array(
            'GET'  => array( $dic->assetController, 'deliver' ),
        ),
    ) ),
    $dic->view
);

$request = new RMF\Request\HTTP();
$request->addHandler( 'body', new RMF\Request\PropertyHandler\PostBody() );
$request->addHandler( 'session', new RMF\Request\PropertyHandler\Session() );

$dispatcher->dispatch( $request );

