<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Module\Feed;

/**
 * Feed model
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
     * Construct from user gateway
     *
     * @param \Doctrine\DBAL\Connection $dbal
     * @return void
     */
    public function __construct(\Doctrine\DBAL\Connection $dbal)
    {
        $this->dbal = $dbal;
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
            ->select('u.feed_u_id', 'u.feed_u_url', 'u.feed_u_update', 'u.feed_u_status', 'rel.feed_m_u_name')
            ->from('feed_m_u_rel', 'rel')
            ->join(
                'rel',
                'feed_url', 'u',
                $queryBuilder->expr()->eq('rel.feed_u_id', 'u.feed_u_id')
            )
            ->where(
                $queryBuilder->expr()->eq('rel.feed_m_id', ':module')
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
                    $urlData['feed_u_id'],
                    $urlData['feed_u_url'],
                    $urlData['feed_m_u_name'],
                    (int) $urlData['feed_u_status'],
                    (int) $urlData['feed_u_update']
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
        $this->checkModule($module);
        $urlId = $this->getUrlId($url);

        $this->dbal->insert('feed_m_u_rel', array(
            'feed_m_id'     => $module,
            'feed_u_id'     => $urlId,
            'feed_m_u_name' => $name,
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
        $this->dbal->delete('feed_m_u_rel', array(
            'feed_m_id' => $module,
            'feed_u_id' => $urlId
        ));
    }

    /**
     * Get pending feeds
     *
     * @param int $age
     * @return void
     */
    public function getPending($age)
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $queryBuilder
            ->select('feed_u_id', 'feed_u_url')
            ->from('feed_url', 'u')
            ->where(
                $queryBuilder->expr()->lt('feed_u_update', ':update')
            )
            ->setParameter(':update', time() - $age);

        $statement = $queryBuilder->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return array_map(
            function ($row) {
                return new Struct\Url(
                    $row['feed_u_id'],
                    $row['feed_u_url']
                );
            },
            $result
        );
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
            'feed_url',
            array(
                'feed_u_status' => (int) $status,
                'feed_u_update' => $update,
            ),
            array(
                'feed_u_id' => $urlId
            )
        );
    }

    /**
     * Get IDs of items already read for the given feed
     *
     * This is not executed as a subquery, because this extraction into an
     * extra query speeds up the query execution A LOT. Stupidity, but works
     * better,
     *
     * @param string $module
     * @return int[]
     */
    protected function getReadDataIds($module)
    {
        $subSelect = $this->dbal->createQueryBuilder();
        $subSelect
            ->select('feed_d_id')
            ->from('feed_m_d_rel', 'rel')
            ->where(
                $subSelect->expr()->eq('feed_m_id', ':module')
            )
            ->setParameter(':module', $module);
        $statement = $subSelect->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        return array_map(
            function ($row) {
                return $row['feed_d_id'];
            },
            $result
        ) ?: array(0);
    }

    /**
     * Get unread feed entries for module
     *
     * @param string $module
     * @return Struct\Entry[]
     */
    public function getUnread($module, $count = 10)
    {
        $read = $this->getReadDataIds($module);

        $queryBuilder = $this->dbal->createQueryBuilder();
        $queryBuilder
            ->select('d.feed_d_id', 'd.feed_d_data', 'mrel.feed_m_u_name', 'u.feed_u_favicon')
            ->from('feed_m_u_rel', 'mrel')
            ->join(
                'mrel',
                'feed_data', 'd',
                $queryBuilder->expr()->andx(
                    $queryBuilder->expr()->eq('mrel.feed_u_id', 'd.feed_u_id'),
                    // @HACK: Doctrine DBAL is buggy currently regarding
                    // building IN() statements :/
                    $this->dbal->quoteIdentifier('d.feed_d_id') . ' NOT IN(' . implode(', ', $read) . ')'
                )
            )
            ->join(
                'mrel',
                'feed_url', 'u',
                $queryBuilder->expr()->eq('mrel.feed_u_id', 'u.feed_u_id')
            )
            ->where(
                $queryBuilder->expr()->eq('mrel.feed_m_id', ':module')
            )
            ->orderBy('d.feed_d_time', 'DESC')
            ->setMaxResults($count)
            ->setParameter(':module', $module);

        $statement = $queryBuilder->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);


        return array_map(
            function ($row) {
                $data = json_decode($row['feed_d_data'], true);
                if ($data === null) {
                    throw new \RuntimeException(
                        "JSON parse error for ${row['feed_m_u_name']}: ${row['feed_d_data']}."
                    );
                }

                return Struct\FeedEntry::create(
                    $row['feed_d_id'],
                    $row['feed_m_u_name'],
                    $row['feed_u_favicon'],
                    $data
                );
            },
            $result
        );
    }

    /**
     * Update URL status
     *
     * @param mixed $urlId
     * @param string $link
     * @param int $date
     * @param string $title
     * @param string $description
     * @param string $content
     * @return void
     */
    public function addEntry($urlId, $link, $date, $title, $description = null, $content = null)
    {
        $hash = hash("sha256", $link);
        $queryBuilder = $this->dbal->createQueryBuilder();
        $queryBuilder
            ->select('feed_d_id')
            ->from('feed_data', 'd')
            ->where(
                $queryBuilder->expr()->andx(
                    $queryBuilder->expr()->eq('feed_d_url', ':hash'),
                    $queryBuilder->expr()->eq('feed_u_id', ':url')
                )
            )
            ->setParameter(':hash', $hash)
            ->setParameter(':url', $urlId);

        $statement = $queryBuilder->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (!count($result)) {
            $this->dbal->insert(
                'feed_data',
                array(
                    'feed_d_url'  => $hash,
                    'feed_u_id'   => $urlId,
                    'feed_d_time' => $date,
                    'feed_d_data' => json_encode(array(
                        'link'        => $link,
                        'title'       => $title,
                        'description' => $description,
                        'content'     => $content,
                    )),
                )
            );
        }
    }

    /**
     * Mark URL read
     *
     * @param mixed $urlId
     * @return void
     */
    public function markRead($module, $entry)
    {
        $this->dbal->insert(
            'feed_m_d_rel',
            array(
                'feed_m_id'     => $module,
                'feed_d_id'     => $entry,
            )
        );
    }

    /**
     * Clear feed
     *
     * @param mixed $urlId
     * @return void
     */
    public function clear($module, $feed)
    {
        $read = $this->getReadDataIds($module);

        $queryBuilder = $this->dbal->createQueryBuilder();
        $queryBuilder
            ->select('d.feed_d_id')
            ->from('feed_m_u_rel', 'mrel')
            ->join(
                'mrel',
                'feed_data', 'd',
                $queryBuilder->expr()->andx(
                    $queryBuilder->expr()->eq('mrel.feed_u_id', 'd.feed_u_id'),
                    // @HACK: Doctrine DBAL is buggy currently regarding
                    // building IN() statements :/
                    $this->dbal->quoteIdentifier('d.feed_d_id') . ' NOT IN(' . implode(', ', $read) . ')'
                )
            )
            ->where(
                $queryBuilder->expr()->andx(
                    $queryBuilder->expr()->eq('mrel.feed_m_id', ':module'),
                    $queryBuilder->expr()->eq('mrel.feed_m_u_name', ':feed')
                )
            )
            ->setParameter(':feed', $feed)
            ->setParameter(':module', $module);

        $statement = $queryBuilder->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($result as $row) {
            $this->dbal->insert(
                'feed_m_d_rel',
                array(
                    'feed_m_id' => $module,
                    'feed_d_id' => $row['feed_d_id'],
                )
            );
        }
    }

    /**
     * Ensures module exists in database
     *
     * @param string $module
     * @return void
     */
    protected function checkModule($module)
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $queryBuilder
            ->select('feed_m_id')
            ->from('feed_module', 'm')
            ->where(
                $queryBuilder->expr()->eq('feed_m_id', ':module')
            )
            ->setParameter(':module', $module);

        $statement = $queryBuilder->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (!count($result)) {
            $this->dbal->insert('feed_module', array(
                'feed_m_id'       => $module,
                'feed_m_settings' => '{}',
            ));
        }
    }

    /**
     * Get URL ID from table.
     *
     * If URL does not exist yet, it will be added.
     *
     * @param string $url
     * @return void
     */
    protected function getUrlId($url)
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $queryBuilder
            ->select('feed_u_id')
            ->from('feed_url', 'u')
            ->where(
                $queryBuilder->expr()->eq('feed_u_url', ':url')
            )
            ->setParameter(':url', $url);

        $statement = $queryBuilder->execute();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        if ($result) {
            return $result['feed_u_id'];
        }

        $this->dbal->insert('feed_url', array(
            'feed_u_url'    => $url,
            'feed_u_update' => 0,
            'feed_u_status' => 0,
        ));

        return $this->dbal->lastInsertId();
    }

    /**
     * Get URL from table.
     *
     * @param int $urlId
     * @return string
     */
    public function getFeedData($urlId)
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $queryBuilder
            ->select('feed_d_data')
            ->from('feed_data', 'd')
            ->where(
                $queryBuilder->expr()->eq('feed_d_id', ':id')
            )
            ->setParameter(':id', $urlId);

        $statement = $queryBuilder->execute();
        $result = $statement->fetch(\PDO::FETCH_ASSOC);

        if (!$result) {
            throw new \OutOfBoundsException("No URL found for ID $urlId");
        }

        return json_decode($result['feed_d_data']);
    }

    /**
     * Get URLs which do not yet have a favicon assigned
     *
     * @return Struct\Url[]
     */
    public function getUrlsWithoutFavicon()
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $queryBuilder
            ->select('feed_u_id', 'feed_u_url')
            ->from('feed_url', 'u')
            ->where(
                $queryBuilder->expr()->isNull('feed_u_favicon')
            );

        $statement = $queryBuilder->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);

        if (!$result) {
            return array();
        }

        return array_map(
            function ($urlData) {
                return new Struct\Url(
                    $urlData['feed_u_id'],
                    $urlData['feed_u_url']
                );
            },
            $result
        );
    }

    /**
     * Update favicon for URL
     *
     * @param mixed $urlId
     * @param string $favicon
     * @return void
     */
    public function updateFavicon($urlId, $favicon)
    {
        $this->dbal->update(
            'feed_url',
            array(
                'feed_u_favicon' => $favicon,
            ),
            array(
                'feed_u_id' => $urlId
            )
        );
    }

    /**
     * Clean unused feed URLs
     *
     * @return void
     */
    public function cleanUnusedFeeds()
    {
        $subSelect = $this->dbal->createQueryBuilder();
        $subSelect
            ->select('feed_u_id')
            ->from('feed_m_u_rel', 'rel');

        $queryBuilder = $this->dbal->createQueryBuilder();
        $queryBuilder
            ->delete('feed_url')
            ->where(
                $this->dbal->quoteIdentifier('feed_u_id') . ' NOT IN(' . $subSelect . ')'
            );

        $queryBuilder->execute();
    }

    /**
     * Clean old feed data
     *
     * @return void
     */
    public function cleanOldData()
    {
        $subSelect = $this->dbal->createQueryBuilder();
        $subSelect
            ->select('feed_u_id')
            ->from('feed_url', 'url');

        $statement = $subSelect->execute();
        $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
        $urls = array_map(
            function ($row) {
                return $row['feed_u_id'];
            },
            $result
        ) ?: array(0);

        // Remove feed data for feeds no longer existing
        $queryBuilder = $this->dbal->createQueryBuilder();
        $queryBuilder
            ->delete('feed_data')
            ->where(
                $this->dbal->quoteIdentifier('feed_u_id') . ' NOT IN(' . implode(', ', $urls) . ')'
            );
        $queryBuilder->execute();

        // Only keep the 50 most recent feed data rows per URL
        foreach ($urls as $urlId) {
            $queryBuilder = $this->dbal->createQueryBuilder();
            $queryBuilder
                ->select('feed_d_id')
                ->from('feed_data', 'data')
                ->where(
                    $queryBuilder->expr()->eq('data.feed_u_id', ':url')
                )
                ->setParameter(':url', $urlId)
                ->orderBy('data.feed_d_time', 'DESC')
                ->setMaxResults(32768)
                ->setFirstResult(50);
            $queryBuilder->execute();

            $statement = $queryBuilder->execute();
            $result = $statement->fetchAll(\PDO::FETCH_ASSOC);
            $dataIds = array_map(
                function ($row) {
                    return $row['feed_d_id'];
                },
                $result
            );

            if (count($dataIds)) {
                $queryBuilder = $this->dbal->createQueryBuilder();
                $queryBuilder
                    ->delete('feed_data')
                    ->where(
                        $this->dbal->quoteIdentifier('feed_d_id') . ' IN(' . implode(', ', $dataIds) . ')'
                    );
                $queryBuilder->execute();
            }
        }
    }

    /**
     * Clean unused read markers
     *
     * @return void
     */
    public function cleanUnusedReadMarkers()
    {
        $subSelect = $this->dbal->createQueryBuilder();
        $subSelect
            ->select('feed_d_id')
            ->from('feed_data', 'data');

        $queryBuilder = $this->dbal->createQueryBuilder();
        $queryBuilder
            ->delete('feed_m_d_rel')
            ->where(
                $this->dbal->quoteIdentifier('feed_d_id') . ' NOT IN(' . $subSelect . ')'
            );

        $queryBuilder->execute();
    }
}
