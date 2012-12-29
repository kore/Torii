<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Module\Account\BankHandler;

use Torii\Module\Account\BankHandler;
use Torii\Module\Account\Struct;

/**
 * Account model
 *
 * @version $Revision$
 */
class SparkasseWestfalia extends BankHandler
{
    /**
     * Fetch transaction data for account
     *
     * @param Struct\Account $account
     * @param string $accountFile
     * @return \CTXParser\Visitor\Simplified\AccountList
     */
    public function fetchTransactions(Struct\Account $account, $accountFile)
    {
        file_put_contents(
            $pinFile = $accountFile . '.pin',
            "PIN_{$account->blz}_{$account->knr} = {$account->pin}\n"
        );

        // Ensure user is created, action seems idempotent
        $this->exec(
            'aqhbci-tool4 --noninteractive --acceptvalidcerts' .
            ' adduser -s https://hbci-pintan-wf.s-hbci.de/PinTanServlet' .
            ' -N ' . escapeshellarg($account->blz . '_' . $account->knr) .
            ' -b ' . escapeshellarg($account->blz) .
            ' -u ' . escapeshellarg($account->knr) .
            ' -t pintan'
        );
        $this->exec(
            'aqhbci-tool4 --noninteractive --acceptvalidcerts' .
            ' -P ' . escapeshellarg($pinFile) .
            ' adduserflags -f forceSsl3' .
            ' -c ' . escapeshellarg($account->knr)
        );
        $this->exec(
            'aqhbci-tool4 --noninteractive --acceptvalidcerts' .
            ' -P ' . escapeshellarg($pinFile) .
            ' getsysid' .
            ' -c ' . escapeshellarg($account->knr)
        );

        // Actually fetch data
        $this->exec(
            'aqbanking-cli --noninteractive --acceptvalidcerts' .
            ' -P ' . escapeshellarg($pinFile) .
            ' request ' .
            ' -b ' . escapeshellarg($account->blz) .
            ' --transactions > ' . escapeshellarg($accountFile . '.ctx')
        );

        unlink($pinFile);

        $parser = new \CTXParser\Parser();
        $transactions = $parser->parse($accountFile . '.ctx');

        $visitor = new \CTXParser\Visitor\Simplified();
        $transactions = $visitor->visit($transactions);

        // Only return accounts which actually contain data
        return array_values(array_filter(
            $transactions->accounts,
            function ($account) {
                return $account->status !== null;
            }
        ));
    }
}
