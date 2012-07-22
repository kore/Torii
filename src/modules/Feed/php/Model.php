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
            ->where(
                $queryBuilder->expr()->eq( 'rel.feed_m_id', ':module' )
            )
            ->leftJoin(
                'rel',
                'feed_url', 'u',
                $queryBuilder->expr()->eq( 'rel.feed_u_id', 'u.feed_u_id' )
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
     * Get unread feed entries for module
     *
     * @param string $module
     * @return Struct\Entry[]
     */
    public function getUnread( $module )
    {
        return array();
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

