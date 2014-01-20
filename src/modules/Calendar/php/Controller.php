<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Module\Calendar;

use Qafoo\RMF;
use Arbit\Periodic;
use Torii\Struct;

/**
 * Calendar module controller
 *
 * @version $Revision$
 */
class Controller
{
    /**
     * Model
     *
     * @var Model
     */
    protected $model;

    /**
     * Parser
     *
     * @var Parser
     */
    protected $parser;

    /**
     * Construct from model
     *
     * @param Model $model
     * @param Parser $parser
     * @return void
     */
    public function __construct(Model $model, Parser $parser)
    {
        $this->model  = $model;
        $this->parser = $parser;
    }

    /**
     * Add URL
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @param Struct\ModuleConfiguration $module
     * @return Struct\Response
     */
    public function addUrl(RMF\Request $request, Struct\User $user, Struct\ModuleConfiguration $module)
    {
        $this->model->addUrl($module->id, $request->body['name'], $request->body['url']);
    }

    /**
     * Remove URL
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @param Struct\ModuleConfiguration $module
     * @return Struct\Response
     */
    public function removeUrl(RMF\Request $request, Struct\User $user, Struct\ModuleConfiguration $module)
    {
        $this->model->removeUrl($module->id, $request->body['url']);
    }

    /**
     * Get URL list
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @param Struct\ModuleConfiguration $module
     * @return Struct\Response
     */
    public function getUrlList(RMF\Request $request, Struct\User $user, Struct\ModuleConfiguration $module)
    {
        return $this->model->getUrlList($module->id);
    }

    /**
     * Get the current calendar data
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @param Struct\ModuleConfiguration $module
     * @return Struct\Response
     */
    public function getCalendarData(RMF\Request $request, Struct\User $user, Struct\ModuleConfiguration $module)
    {
        $entries = $this->model->getCalendar($module->id);
        $timeZone = isset($request->variables['timezone']) ? $request->variables['timezone'] : 'UTC';

        $perDay = array();
        foreach ($entries as $entry) {
            $entry->start->setTimezone(new \DateTimeZone($timeZone));
            $perDay[$entry->start->format('l, jS F')][] = $entry;
            $entry->start = $entry->start->format('H:i');
            $entry->end = $entry->end->format('H:i');
        }

        return $perDay;
    }

    /**
     * Get the current calendar data
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @param Struct\ModuleConfiguration $module
     * @return Struct\Response
     */
    public function refresh(Periodic\Logger $logger)
    {
        $urls = $this->model->getUrlsPerModule();
        foreach ($urls as $module => $moduleUrls) {
            $entries = array();
            foreach ($moduleUrls as $url) {
                $logger->log("Fetch calendar from URL: " . $url->url);
                $entries = array_merge(
                    $entries,
                    $this->parser->parse($url)->entries
                );
                $this->model->updateUrl($url->id, $url->status, $url->requested);
                $logger->log("Status: " . $url->status);
            }

            $entries = $this->model->sortEvents($entries);

            $this->model->storeCalendar($module, $entries);
        }

        return array();
    }
}
