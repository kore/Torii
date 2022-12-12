<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii;
use Qafoo\RMF;

// Do nothing but redirect to Portalific (Torii 2)
header('Location: https://portalific.com/');
exit();

$requested = $_SERVER['REQUEST_URI'];
if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']) {
    $requested = str_replace('?' . $_SERVER['QUERY_STRING'], '', $requested);
}

if ( file_exists( __DIR__ . $requested ) &&
     is_file( __DIR__ . $requested ) )
{
    // Do not try to server static files – this is only important, if used
    // together with PHPs internal webserver.
    return false;
}

require __DIR__ . '/../php/Torii/bootstrap.php';
$dic = new DIC\Base();

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
        '(^/portal/import$)' => array(
            'POST' => array( $dic->mainController, 'import' ),
        ),
        '(^/portal/resort$)' => array(
            'POST' => array( $dic->mainController, 'resort' ),
        ),
        '(^/portal/config/(?P<module>[A-Za-z0-9_-]+)$)' => array(
            'GET'  => array( $dic->mainController, 'getConfig' ),
            'POST' => array( $dic->mainController, 'configure' ),
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
        '(^/(?:styles|images|scripts|templates)/)' => array(
            'GET'  => array( $dic->assetController, 'deliver' ),
        ),
    ) ),
    $dic->view
);

$request = new RMF\Request\HTTP();
$request->addHandler( 'body', new RMF\Request\PropertyHandler\PostBody() );
$request->addHandler( 'session', new RMF\Request\PropertyHandler\Session() );

$dispatcher->dispatch( $request );

