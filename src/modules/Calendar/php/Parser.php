<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Module\Calendar;

use Torii\Module\Calendar;
use Sabre\VObject;

/**
 * Calendar parser
 *
 * Neither calendar parser nor HTTP client are injectable, because we could not be
 * bothered to define interface for them. If you want to exchange something,
 * create a new parser ;p
 *
 * @version $Revision$
 */
class Parser
{
    /**
     * Parse Calendar behind provided URL
     *
     * Returns a Calendar instance
     *
     * @param Struct\Url $url
     * @return Struct\Calendar
     */
    public function parse(Struct\Url $url)
    {
        $client = new \Buzz\Browser(
            new \Buzz\Client\Curl()
        );
        $client->getClient()->setTimeout(5);

        $calendar = new Struct\Calendar($url);
        try {
            $url->requested = time();
            $response = $client->get($url->url);
            $url->status = $response->getStatusCode();

            if (!$response->isOk()) {
                return $calendar;
            }

            $reader = VObject\Reader::read(
                $response->getContent()
            );

            $start = new \DateTime("today 0:00");
            $end = new \DateTime("today +7 days");
            $reader->expand($start, $end);
            foreach ($reader->VEVENT as $entry) {
                $calendar->entries[] = $this->parseEntry($url, $entry);
            }
        } catch (\Exception $e) {
            $url->status = 503;
        }

        return $calendar;
    }

    /**
     * Converts a single zend calendar entry into something sensible
     *
     * @param mixed $data
     * @return Struct\CalendarEntry
     */
    protected function parseEntry(Struct\Url $url, \Sabre\VObject\Component\VEvent $data)
    {
        $event = new Struct\Event();

        $event->summary  = (string) $data->SUMMARY;
        $event->start    = $data->DTSTART->getDateTime();
        $event->end      = $data->DTEND->getDateTime();
        $event->location = (string) $data->LOCATION;
        $event->calendar = $url->name;

        return $event;
    }
}
