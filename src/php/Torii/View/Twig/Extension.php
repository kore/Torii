<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\View\Twig;

use Torii\DIC;

/**
 * Custom twig extension
 *
 * @version $Revision$
 */
class Extension extends \Twig_Extension
{
    /**
     * Torii global Dependency Injection Container
     *
     * @var DIC
     */
    protected $dic;

    /**
     * Construct from DIC
     *
     * @param DIC $dic
     * @return void
     */
    public function __construct( DIC $dic )
    {
        $this->dic = $dic;
    }

    /**
     * get extension name
     *
     * @return string
     */
    public function getName()
    {
        return 'Torii';
    }

    /**
     * Get global variables to register in Twig templates
     *
     * @return array
     */
    public function getGlobals()
    {
        return array(
            'javaScript' => $this->dic->javaScript->getFiles(),
            'css'        => $this->dic->css->getFiles(),
            'debug'      => $this->dic->debug,
        );
    }
}
