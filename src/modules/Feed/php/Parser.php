<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Module\Feed;

/**
 * Feed parser
 *
 * Neither feed parser nor HTTP client are injectable, because we could not be
 * bothered to define interface for them. If you want to exchange something,
 * create a new parser ;p
 *
 * @version $Revision$
 */
class Parser
{
    /**
     * Parse Feed behind provided URL
     *
     * Returns a Feed instance
     *
     * @param Struct\Url $url
     * @return Struct\Feed
     */
    public function parse(Struct\Url $url)
    {
        $client = new \Buzz\Browser();
        $client->getClient()->setTimeout(5);

        $feed = new Struct\Feed($url);
        try {
            $response = $client->get($url->url);
            $feed->status = $response->getStatusCode();

            if (!$response->isOk()) {
                return $feed;
            }

            $reader = \Zend\Feed\Reader\Reader::importString(
                $response->getContent()
            );

            foreach ($reader as $entry) {
                $feed->entries[] = $this->parseEntry($entry);
            }
        } catch (\Zend\Feed\Reader\Exception\RuntimeException $e) {
            $feed->status = 406;
            return $feed;
        } catch (\RuntimeException $e) {
            $feed->status = 503;
            return $feed;
        }

        return $feed;
    }

    /**
     * Converts a single zend feed entry into something sensible
     *
     * @param mixed $data
     * @return Struct\FeedEntry
     */
    protected function parseEntry($data)
    {
        $entry = new Struct\FeedEntry(
            null,
            $data->getLink(),
            null,
            $data->getTitle()
        );

        $entry->date        = $data->getDateModified() ?
            $data->getDateModified()->format('U') :
            time();

        $entry->content     = $data->getContent() ?: $data->getDescription();
        $entry->description = strip_tags($data->getDescription() ?: $data->getContent());

        return $entry;
    }
}
