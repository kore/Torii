<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Module\Account;

/**
 * Account model
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
     * Get Accounts for module
     *
     * @param string $module
     * @return Struct\Account[]
     */
    public function getAccountList( $module )
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $queryBuilder
            ->select( 'a.account_a_id', 'a.account_a_name', 'a.account_a_blz', 'a.account_a_knr', 'a.account_a_pin' )
            ->from( 'account_account', 'a' )
            ->where(
                $queryBuilder->expr()->eq( 'a.account_m_id', ':module' )
            )
            ->setParameter( ':module', $module );

        $statement = $queryBuilder->execute();
        $result = $statement->fetchAll( \PDO::FETCH_ASSOC );

        if ( !$result )
        {
            return array();
        }

        return array_map(
            function ( $accountData )
            {
                return new Struct\Account(
                    $accountData['account_a_id'],
                    $accountData['account_a_name'],
                    $accountData['account_a_blz'],
                    $accountData['account_a_knr'],
                    $accountData['account_a_pin']
                );
            },
            $result
        );
    }

    /**
     * Add account to module
     *
     * @param string $module
     * @param string $name
     * @param string $blz
     * @param string $knr
     * @param string $pin
     * @return void
     */
    public function addAccount( $module, $name, $blz, $knr, $pin )
    {
        $this->dbal->insert( 'account_account', array(
            'account_m_id'   => $module,
            'account_a_name' => $name,
            'account_a_blz' => $blz,
            'account_a_knr' => $knr,
            'account_a_pin' => $pin,
        ) );
    }

    /**
     * Remove Account from module
     *
     * @param string $module
     * @param string $accountId
     * @return void
     */
    public function removeAccount( $module, $accountId )
    {
        $this->dbal->delete( 'account_account', array(
            'account_m_id' => $module,
            'account_a_id' => $accountId
        ) );
    }
}

