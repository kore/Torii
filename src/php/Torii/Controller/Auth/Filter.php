<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Controller\Auth;

/**
 * Proxy for controller actions, which require authentification
 *
 * @version $Revision$
 */
class Filter
{
    /**
     * Aggregated proxy target controller
     *
     * @var mixed
     */
    protected $controller;

    /**
     * Construct from aggregated controller
     *
     * @param mixed $controller
     * @return void
     */
    public function __construct( $controller )
    {
        $this->controller = $controller;
    }

    /**
     * Proxy calls, which require authentification
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call( $method, array $arguments )
    {
        $request = reset( $arguments );

        if ( !isset( $request->session['user'] ) ) {
            // @TODO: This ia an ugly hack:
            header( 'Location: /' );
            exit( 0 );
        }

        if ( !is_callable( array( $this->controller, $method ) ) ) {
            throw new \BadMethodCallException( "Call not available in aggregated controller." );
        }

        return $this->controller->$method( $request, $request->session['user'] );
    }
}
