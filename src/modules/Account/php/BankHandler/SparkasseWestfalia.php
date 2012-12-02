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
    public function fetchTransactions( Struct\Account $account, $accountFile )
    {
        file_put_contents(
            $pinFile = $accountFile . '.pin',
            "PIN_{$account->blz}_{$account->knr} = {$account->pin}\n"
        );

        // Ensure user is created, action seems idempotent
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

        // Actually fetch data
        shell_exec(
            'aqbanking-cli -n -P ' . escapeshellarg( $pinFile ) . ' request ' .
            ' -b ' . escapeshellarg( $account->blz ) .
            ' -a ' . escapeshellarg( $account->knr ) .
            ' --transactions > ' . escapeshellarg( $accountFile . '.ctx' )
        );

        unlink( $pinFile );

        $parser = new \CTXParser\Parser();
        $transactions = $parser->parse( $accountFile . '.ctx' );

        $visitor = new \CTXParser\Visitor\Simplified();
        return $visitor->visit( $transactions );
    }
}
