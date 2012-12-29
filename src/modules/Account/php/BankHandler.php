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
    abstract public function fetchTransactions(Struct\Account $account, $accountFile);

    /**
     * Exec command
     *
     * This should be refactored into an aggegated command executor, but is
     * fine for now.
     *
     * @param string $command
     * @return mxied
     */
    protected function exec($command)
    {
        $return = shell_exec($command);
        return $return;
    }
}
