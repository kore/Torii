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
     * Storage directory
     *
     * @var string
     */
    protected $storageDir;

    /**
     * Handler for different banks to fetch tarnsaction data
     *
     * @var BankHandlerDispatcher
     */
    protected $bankHandler;

    /**
     * Construct from user gateway
     *
     * @param \Doctrine\DBAL\Connection $dbal
     * @return void
     */
    public function __construct( \Doctrine\DBAL\Connection $dbal, $storageDir )
    {
        $this->dbal = $dbal;
        $this->storageDir = $storageDir;
        $this->bankHandler = new BankHandlerDispatcher();
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
            ->select( 'a.account_a_id', 'a.account_a_name', 'a.account_a_blz', 'a.account_a_knr' )
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
                    null
                );
            },
            $result
        );
    }

    /**
     * Get Accounts data for module
     *
     * @param string $module
     * @return Struct\Account[]
     */
    public function getAccountData( $module )
    {
        $accounts = $this->getAccountList( $module );
        foreach ( $accounts as $account )
        {
            $account->transactions = include $this->getAccountFileName( $account );
        }

        return $accounts;
    }

    /**
     * Get all accounts
     *
     * @return Struct\Account[]
     */
    public function getAllAccounts()
    {
        $queryBuilder = $this->dbal->createQueryBuilder();
        $queryBuilder
            ->select( 'a.account_a_id', 'a.account_a_name', 'a.account_a_blz', 'a.account_a_knr', 'a.account_a_pin' )
            ->from( 'account_account', 'a' );

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

    /**
     * Update transactions for given account
     *
     * @param Struct\Account $account
     * @return void
     */
    public function updateTransactions(Struct\Account $account)
    {
        $accountFile = $this->getAccountFileName( $account );

        $transactions = $this->bankHandler->fetchTransactions( $account, $accountFile );

        file_put_contents(
            $accountFile,
            "<?php\n\nreturn " . var_export( $transactions, true ) . ";\n"
        );
    }

    /**
     * Get account storage file name
     *
     * @param Struct\Account $account
     * @return string
     */
    protected function getAccountFileName( Struct\Account $account )
    {
        if ( !is_dir( $this->storageDir ) )
        {
            mkdir( $this->storageDir, 0777, true );
        }

        return sprintf(
            "%s/%s_%s.php",
            $this->storageDir,
            $account->blz,
            $account->knr
        );
    }
}

