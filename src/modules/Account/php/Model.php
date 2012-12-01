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
     * Construct from user gateway
     *
     * @param \Doctrine\DBAL\Connection $dbal
     * @return void
     */
    public function __construct( \Doctrine\DBAL\Connection $dbal, $storageDir )
    {
        $this->dbal = $dbal;
        $this->storageDir = $storageDir;
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
            $account->transactions = include $this->getAccountFileName( $account ) . '.php';
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
        file_put_contents(
            $pinFile = $accountFile . '.pin',
            "PIN_{$account->blz}_{$account->knr} = {$account->pin}\n"
        );

        // Obviously only works for Sparkassen in Westfalia.
        // @TODO: Come up with a generic approach.
        shell_exec(
            'aqhbci-tool4 -n adduser -s https://hbci-pintan-wf.s-hbci.de/PinTanServlet' .
            ' -N ' . escapeshellarg( $account->blz . '_' . $account->knr ) .
            ' -b ' . escapeshellarg( $account->blz ) .
            ' -u ' . escapeshellarg( $account->knr ) .
            ' -t pintan'
        );
        shell_exec(
            'aqhbci-tool4 -n -P ' . escapeshellarg( $pinFile ) .
            ' adduserflags -f forceSsl3' .
            ' -c ' . escapeshellarg( $account->knr )
        );
        shell_exec(
            'aqhbci-tool4 -n -P ' . escapeshellarg( $pinFile ) .
            ' getsysid' .
            ' -c ' . escapeshellarg( $account->knr )
        );
        shell_exec(
            'aqbanking-cli -n -P ' . escapeshellarg( $pinFile ) . ' request ' .
            ' -b ' . escapeshellarg( $account->blz ) .
            ' -a ' . escapeshellarg( $account->knr ) .
            ' --transactions > ' . escapeshellarg( $accountFile )
        );

        unlink( $pinFile );

        $parser = new \CTXParser\Parser();
        $transactions = $parser->parse( $accountFile );

        $visitor = new \CTXParser\Visitor\Simplified();
        $transactions = $visitor->visit( $transactions );

        file_put_contents(
            $accountFile . '.php',
            "<?php\n\nreturn " . var_export( $transactions->accounts[0], true ) . ";\n"
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
            "%s/%s_%s.ctx",
            $this->storageDir,
            $account->blz,
            $account->knr
        );
    }
}

