<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Importer;

use Qafoo\RMF;
use Torii\Struct;
use Torii\Model;

/**
 * Torii 1 configuration importer
 *
 * @version $Revision$
 */
class Torii
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
    public function __construct(Model\User $user, array $modules)
    {
        $this->user    = $user;
        $this->modules = $modules;
    }

    /**
     * Import Torii 1 configuration
     *
     * @param Struct\User $user
     * @param string $config
     * @return void
     */
    public function import(Struct\User $user, $config)
    {
        $doc = new \DOMDocument();
        $doc->load($config);

        $xpath = new \DOMXPath($doc);

        $settings = array();
        foreach ($xpath->query('//section') as $nr => $column) {
            $settings[$nr] = array();
            foreach ($xpath->query('./module', $column) as $module) {
                if ($module = $this->importModule($user, $module)) {
                    $settings[$nr][] = $module;
                }
            }
        }

        $user->settings->modules = $settings;
        $this->user->update($user);
    }

    /**
     * Convert module configuration
     *
     * @param Struct\User $user
     * @param \DOMElement $module
     * @return void
     */
    protected function importModule(Struct\User $user, \DOMElement $module)
    {
        switch ($module->getAttribute('type')) {
            case 'feed':
                return $this->importFeed($user, $module);

            case 'weather':
                return $this->importWeather($user, $module);

            default:
                return false;
        }
    }

    /**
     * Import Torii feed module
     *
     * @param Struct\User $user
     * @param \DOMElement $module
     * @return void
     */
    protected function importFeed(Struct\User $user, \DOMElement $module)
    {
        $config = new Struct\ModuleConfiguration(
            $id = $this->getModuleId($module->getAttribute('name')),
            'Feed',
            $module->getAttribute('name')
        );

        $request = new RMF\Request\HTTP();
        $request->variables = array('path' => '/add');

        $xpath = new \DOMXPath($module->ownerDocument);
        foreach ($xpath->query('.//feed', $module) as $url) {
            $request->body = array(
                'name' => $url->getAttribute('id'),
                'url'  => $url->nodeValue,
            );

            $this->modules['Feed']->handle($request, $user, $config);
        }

        return $config;
    }

    /**
     * Import Torii weather module
     *
     * @param Struct\User $user
     * @param \DOMElement $module
     * @return void
     */
    protected function importWeather(Struct\User $user, \DOMElement $module)
    {
        return new Struct\ModuleConfiguration(
            $this->getModuleId($module->getAttribute('name')),
            'Weather',
            $module->getAttribute('name')
        );
    }

    /**
     * Get a random, unique module ID
     *
     * @param string $title
     * @return string
     */
    protected function getModuleId($title)
    {
        return preg_replace('([^a-z0-9_]+)', '_', strtolower($title)) . '_' . substr(md5(microtime()), 0, 8);
    }
}
