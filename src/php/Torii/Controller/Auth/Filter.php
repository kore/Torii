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
    protected $aggregate;

    /**
     * Auth controller
     *
     * @var Controller\Auth
     */
    protected $authController;

    /**
     * Construct from aggregated controller
     *
     * @param mixed $controller
     * @return void
     */
    public function __construct($authController, $aggregate)
    {
        $this->aggregate = $aggregate;
        $this->authController = $authController;
    }

    /**
     * Proxy calls, which require authentification
     *
     * @param string $method
     * @param array $arguments
     * @return mixed
     */
    public function __call($method, array $arguments)
    {
        $request = reset($arguments);

        if (!isset($request->session['user'])) {
            // Hack:
            header('HTTP/1.0 403 Forbidden');
            return $this->authController->login($request);
        }

        if (!is_callable(array($this->aggregate, $method))) {
            throw new \BadMethodCallException("Call not available in aggregated controller.");
        }

        return $this->aggregate->$method($request, $request->session['user']);
    }
}
