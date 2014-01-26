<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Module\Calendar;

/**
 * Calendar model
 *
 * @version $Revision$
 */
class Model
{
    /**
     * Doctrine DB Abstraction layer
     *
     * @var \Doctrine\DBAL\Connection
     */
    protected $dbal;

    /**
     * Storage directory
     *
     * @var string
     */
    protected $storageDir;

    /**
     * Construct from user gateway
     *
     * @param \Doctrine\DBAL\Connection $dbal
     * @return void
     */
    public function __construct(\Doctrine\DBAL\Connection $dbal, $storageDir)
    {
        $this->dbal = $dbal;
        $this->storageDir = $storageDir;
    }

    /**
     * Get URLs for module
     *
     * @param string $module
     * @return Struct\Url[]
     */
    public function getUrlList($module)
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $queryBuilder
            ->select('u.calendar_u_id', 'u.calendar_u_url', 'u.calendar_u_update', 'u.calendar_u_status', 'u.calendar_u_name')
            ->from('calendar_url', 'u')
            ->where(
                $queryBuilder->expr()->eq('u.calendar_m_id', ':module')
            )
            ->setParameter(':module', $module);

        $statement = $queryBuilder->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (!$result) {
            return array();
        }

        return array_map(
            function ($urlData) {
                return new Struct\Url(
                    $urlData['calendar_u_id'],
                    $urlData['calendar_u_url'],
                    $urlData['calendar_u_name'],
                    (int) $urlData['calendar_u_status'],
                    (int) $urlData['calendar_u_update']
                );
            },
            $result
        );
    }

    /**
     * Add URL to module
     *
     * @param string $module
     * @param string $url
     * @return void
     */
    public function addUrl($module, $name, $url)
    {
        $this->dbal->insert('calendar_url', array(
            'calendar_m_id'   => $module,
            'calendar_u_url'  => $url,
            'calendar_u_name' => $name,
        ));
    }

    /**
     * Remove URL from module
     *
     * @param string $module
     * @param string $urlId
     * @return void
     */
    public function removeUrl($module, $urlId)
    {
        $this->dbal->delete('calendar_url', array(
            'calendar_m_id' => $module,
            'calendar_u_id' => $urlId
        ));
    }

    /**
     * Update URL status
     *
     * @param mixed $urlId
     * @param int $status
     * @param int $update
     * @return void
     */
    public function updateUrl($urlId, $status, $update)
    {
        $this->dbal->update(
            'calendar_url',
            array(
                'calendar_u_status' => $status,
                'calendar_u_update' => $update,
            ),
            array(
                'calendar_u_id' => $urlId
            )
        );
    }

    /**
     * Get all URLS for all modules
     *
     * @return void
     */
    public function getUrlsPerModule()
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $queryBuilder
            ->select('u.calendar_u_id', 'u.calendar_m_id', 'u.calendar_u_url', 'u.calendar_u_update', 'u.calendar_u_status', 'u.calendar_u_name')
            ->from('calendar_url', 'u');

        $statement = $queryBuilder->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (!$result) {
            return array();
        }

        $urls = array();
        foreach ($result as $row) {
            $urls[$row['calendar_m_id']][] = new Struct\Url(
                $row['calendar_u_id'],
                $row['calendar_u_url'],
                $row['calendar_u_name'],
                (int) $row['calendar_u_status'],
                (int) $row['calendar_u_update']
            );
        }

        return $urls;
    }

    /**
     * Store calendar data
     *
     * @param string $module
     * @param array $entries
     * @return void
     */
    public function storeCalendar($module, array $entries)
    {
        file_put_contents(
            $this->getStorageFileName($module),
            serialize($entries)
        );
    }

    /**
     * Store calendar data
     *
     * @param string $module
     * @param array $entries
     * @return void
     */
    public function getCalendar($module)
    {
        $entries = unserialize(
            file_get_contents(
                $this->getStorageFileName($module)
            )
        );

        // Explode entries per day
        foreach ($entries as $entry) {
            while ($entry->start->format('d.m.Y') !== $entry->end->format('d.m.Y')) {
                $firstDay = clone $entry;
                $firstDay->end = new \DateTime($entry->start->format('d.m.Y') . ' 24:00');
                $entries[] = $firstDay;

                $entry->start->modify("+1 day 0:00");
            }
        }

        return array_filter(
            $this->sortEvents($entries),
            function (Struct\Event $event) {
                return ($event->start >= new \DateTime('today 0:00')) &&
                    ($event->start < new \DateTime('today +7 days')) &&
                    ($event->end > $event->start);
            }
        );
    }

    /**
     * Sort events
     *
     * @param array $entries
     * @return void
     */
    public function sortEvents(array $entries)
    {
        usort(
            $entries,
            function ($a, $b) {
                return $a->start->getTimestamp() - $b->start->getTimestamp();
            }
        );

        return $entries;
    }

    /**
     * Get calendar storage file name
     *
     * @param string $module
     * @return string
     */
    protected function getStorageFileName($module)
    {
        if (!is_dir($this->storageDir)) {
            mkdir($this->storageDir, 0777, true);
        }

        return sprintf(
            "%s/%s.php",
            $this->storageDir,
            $module
        );
    }
}
