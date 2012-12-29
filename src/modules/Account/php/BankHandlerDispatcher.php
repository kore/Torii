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
class BankHandlerDispatcher extends BankHandler
{
    /**
     * Registered handler per bank
     *
     * @var array
     */
    protected $handler = array();

    public function __construct()
    {
        $this->handler = array(
            '42050001' => new BankHandler\SparkasseWestfalia(),
        );
    }

    /**
     * Fetch transaction data for account
     *
     * @param Struct\Account $account
     * @param string $accountFile
     * @return \CTXParser\Visitor\Simplified\AccountList
     */
    public function fetchTransactions(Struct\Account $account, $accountFile)
    {
        if (!isset($this->handler[ $account->blz])) {
            throw new \OutOfBoundsException(
                "No handler available for bank " . $account->blz
            );
        }

        return $this->handler[$account->blz]->fetchTransactions($account, $accountFile);
    }
}
