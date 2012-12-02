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
abstract class BankHandler
{
    /**
     * Fetch transaction data for account
     *
     * @param Struct\Account $account
     * @param string $accountFile
     * @return \CTXParser\Visitor\Simplified\AccountList
     */
    abstract public function fetchTransactions( Struct\Account $account, $accountFile );
}
