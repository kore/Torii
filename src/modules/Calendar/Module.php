<?php
/**
 * This file is part of Torii
 *
 * @version $Revision: 1469 $
 */

namespace Torii\Module\Calendar;

use Qafoo\RMF;
use Arbit\Periodic;
use Torii\Struct;
use Torii\DIC;
use Torii\Assets;

/**
 * Calendar module definition
 *
 * @version $Revision$
 */
class Module extends \Torii\Module implements \Torii\Cronable
{
    /**
     * Mapping of routes to actions
     *
     * @var array
     */
    protected $mapping = array(
        '(^/add)'      => 'addUrl',
        '(^/remove)'   => 'removeUrl',
        '(^/getList)'  => 'getUrlList',
        '(^/update)'   => 'getCalendarData',
    );

    /**
     * Get module summary
     *
     * @return Struct\Module
     */
    public function getSummary()
    {
        return new Struct\Module(
            'Calendar',
            'List pending events from multiple calendars.'
        );
    }

    /**
     * Get module internal controller
     *
     * @return Controller
     */
    protected function getController()
    {
        return new Controller(
            new Model(
                $this->dic->dbal,
                $this->dic->srcDir . '/var/calendar/'
            ),
            new Parser()
        );
    }

    /**
     * Execute action
     *
     * Should, most probably dispatch to own controller implementation.
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @param Struct\ModuleConfiguration $module
     * @return mixed
     */
    public function handle(RMF\Request $request, Struct\User $user, Struct\ModuleConfiguration $module)
    {
        foreach ($this->mapping as $regexp => $action) {
            if (preg_match($regexp, $request->variables['path'])) {
                return $this->getController()->$action($request, $user, $module);
            }
        }

        throw new \RuntimeException("No route found for ". $request->variables['path']);
    }

    /**
     * Inject DIC
     *
     * This is the interface between modules and the base system. Module
     * definitions are not supposed to be re-usable in other systems.
     *
     * The DIC in this case provides the means to configure your own module
     * environment. The DIC schould NOT be passed to any other objects.
     *
     * @param DIC $dic
     * @return void
     */
    public function initialize(DIC $dic)
    {
        parent::initialize($dic);

        // Register path for custom templates
        $dic->twig->getLoader()->addPath(__DIR__ . '/twig');

        // Register assets
        $dic->css->addFileSet(new Assets\FileSet(__DIR__ . '/css', '*.css'));
        $dic->javaScript->addFileSet(new Assets\FileSet(__DIR__ . '/js', '*.js'));
        $dic->templates->addFileSet(new Assets\FileSet(__DIR__ . '/mustache', 'calendar/*.mustache'));
        $dic->images->addFileSet(new Assets\FileSet(__DIR__ . '/images', '*.png'));
    }

    /**
     * Run something in the module. Usually refresh some data.
     *
     * @param Periodic\Logger $logger
     * @return void
     */
    public function refresh(Periodic\Logger $logger)
    {
        $this->getController()->refresh($logger);
    }
}

// Important for registration
return new Module();
