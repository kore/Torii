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
    public function __construct( \Doctrine\DBAL\Connection $dbal )
    {
        $this->dbal = $dbal;
    }

    /**
     * Get URLs for module
     *
     * @param string $module
     * @return Struct\Url[]
     */
    public function getUrlList( $module )
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $queryBuilder
            ->select( 'u.feed_u_id', 'u.feed_u_url', 'u.feed_u_update', 'u.feed_u_status' )
            ->from( 'feed_m_u_rel', 'rel' )
            ->join(
                'rel',
                'feed_url', 'u',
                $queryBuilder->expr()->eq( 'rel.feed_u_id', 'u.feed_u_id' )
            )
            ->where(
                $queryBuilder->expr()->eq( 'rel.feed_m_id', ':module' )
            )
            ->setParameter( ':module', $module );

        $statement = $queryBuilder->execute();
        $result = $statement->fetchAll( \PDO::FETCH_ASSOC );

        if ( !$result )
        {
            return array();
        }

        return array_map(
            function ( $urlData )
            {
                return new Struct\Url(
                    $urlData['feed_u_id'],
                    $urlData['feed_u_url'],
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
    public function addUrl( $module, $url )
    {
        $this->checkModule( $module );
        $urlId = $this->getUrlId( $url );

        $this->dbal->insert( 'feed_m_u_rel', array(
            'feed_m_id' => $module,
            'feed_u_id' => $urlId
        ) );
    }

    /**
     * Remove URL from module
     *
     * @param string $module
     * @param string $urlId
     * @return void
     */
    public function removeUrl( $module, $urlId )
    {
        $this->dbal->delete( 'feed_m_u_rel', array(
            'feed_m_id' => $module,
            'feed_u_id' => $urlId
        ) );
    }

    /**
     * Get pending feeds
     *
     * @param int $age
     * @return void
     */
    public function getPending( $age )
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $queryBuilder
            ->select( 'feed_u_id', 'feed_u_url' )
            ->from( 'feed_url', 'u' )
            ->where(
                $queryBuilder->expr()->lt( 'feed_u_update', ':update' )
            )
            ->setParameter( ':update', time() - $age );

        $statement = $queryBuilder->execute();
        $result = $statement->fetchAll( \PDO::FETCH_ASSOC );

        return array_map(
            function ( $row )
            {
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
    public function updateUrl( $urlId, $status, $update )
    {
        $this->dbal->update(
            'feed_url',
            array(
                'feed_u_status' => $status,
                'feed_u_update' => $update,
            ),
            array(
                'feed_u_id' => $urlId
            )
        );
    }

    /**
     * Get unread feed entries for module
     *
     * @param string $module
     * @return Struct\Entry[]
     */
    public function getUnread( $module, $count = 10 )
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $queryBuilder
            ->select( 'd.feed_d_id', 'd.feed_d_data' )
            ->from( 'feed_m_u_rel', 'mrel' )
            ->join(
                'mrel',
                'feed_data', 'd',
                $queryBuilder->expr()->eq( 'mrel.feed_u_id', 'd.feed_u_id' )
            )
            ->leftJoin(
                'mrel',
                'feed_m_d_rel', 'drel',
                $queryBuilder->expr()->andx(
                    $queryBuilder->expr()->eq( 'drel.feed_d_id', 'd.feed_d_id' ),
                    $queryBuilder->expr()->eq( 'drel.feed_m_id', ':module' )
                )
            )
            ->where(
                $queryBuilder->expr()->eq( 'mrel.feed_m_id', ':module' )
            )
            ->orderBy( 'd.feed_d_time', 'DESC' )
            ->setMaxResults( $count )
            ->setParameter( ':module', $module );

        $statement = $queryBuilder->execute();
        $result = $statement->fetchAll( \PDO::FETCH_ASSOC );

        return array_map(
            function ( $row )
            {
                return Struct\FeedEntry::create(
                    $row['feed_d_id'],
                    json_decode( $row['feed_d_data'], true )
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
    public function addEntry( $urlId, $link, $date, $title, $description = null, $content = null )
    {
        $this->dbal->insert(
            'feed_data',
            array(
                'feed_d_url'  => hash( "sha256", $link, true ),
                'feed_u_id'   => $urlId,
                'feed_d_time' => $date,
                'feed_d_data' => json_encode( array(
                    'link'        => $link,
                    'title'       => $title,
                    'description' => $description,
                    'content'     => $content,
                ) ),
            )
        );
    }

    /**
     * Ensures module exists in database
     *
     * @param string $module
     * @return void
     */
    protected function checkModule( $module )
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $queryBuilder
            ->select( 'feed_m_id' )
            ->from( 'feed_module', 'm' )
            ->where(
                $queryBuilder->expr()->eq( 'feed_m_id', ':module' )
            )
            ->setParameter( ':module', $module );

        $statement = $queryBuilder->execute();
        $result = $statement->fetchAll( \PDO::FETCH_ASSOC );

        if ( !count( $result ) )
        {
            $this->dbal->insert( 'feed_module', array(
                'feed_m_id'       => $module,
                'feed_m_settings' => '{}',
            ) );
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
    protected function getUrlId( $url )
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $queryBuilder
            ->select( 'feed_u_id' )
            ->from( 'feed_url', 'u' )
            ->where(
                $queryBuilder->expr()->eq( 'feed_u_url', ':url' )
            )
            ->setParameter( ':url', $url );

        $statement = $queryBuilder->execute();
        $result = $statement->fetch( \PDO::FETCH_ASSOC );

        if ( $result )
        {
            return $result['feed_u_id'];
        }

        $this->dbal->insert( 'feed_url', array(
            'feed_u_url'    => $url,
            'feed_u_update' => 0,
            'feed_u_status' => 0,
        ) );

        return $this->dbal->lastInsertId();
    }
}

