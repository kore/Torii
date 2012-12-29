<?php
/**
 * This file is part of Torii
 *
 * @version $Revision$
 */

namespace Torii\Module\Account;

use Qafoo\RMF;
use Arbit\Periodic;
use Torii\Struct;

/**
 * Account module controller
 *
 * @version $Revision$
 */
class Controller
{
    /**
     * Model
     *
     * @var Model
     */
    protected $model;

    /**
     * Construct from model
     *
     * @param Model $model
     * @return void
     */
    public function __construct(Model $model)
    {
        $this->model  = $model;
    }

    /**
     * Add Account
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @param Struct\ModuleConfiguration $module
     * @return Struct\Response
     */
    public function addAccount(RMF\Request $request, Struct\User $user, Struct\ModuleConfiguration $module)
    {
        $this->model->addAccount(
            $module->id,
            trim($request->body['name']),
            trim($request->body['blz']),
            trim($request->body['knr']),
            trim($request->body['pin'])
        );
    }

    /**
     * Remove Account
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @param Struct\ModuleConfiguration $module
     * @return Struct\Response
     */
    public function removeAccount(RMF\Request $request, Struct\User $user, Struct\ModuleConfiguration $module)
    {
        $this->model->removeAccount($module->id, $request->body['account']);
    }

    /**
     * Get Account list
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @param Struct\ModuleConfiguration $module
     * @return Struct\Response
     */
    public function getAccountList(RMF\Request $request, Struct\User $user, Struct\ModuleConfiguration $module)
    {
        return $this->model->getAccountList($module->id);
    }

    /**
     * Get the current account data
     *
     * @param RMF\Request $request
     * @param Struct\User $user
     * @param Struct\ModuleConfiguration $module
     * @return Struct\Response
     */
    public function getAccountData(RMF\Request $request, Struct\User $user, Struct\ModuleConfiguration $module)
    {
        return $this->model->getAccountData($module->id);
    }

    /**
     * Refresh data in background
     *
     * @param Periodic\Logger $logger
     * @return void
     */
    public function refresh(Periodic\Logger $logger)
    {
        $accounts = $this->model->getAllAccounts();

        foreach ($accounts as $account) {
            $logger->log("Updating {$account->knr} for {$account->blz}");
            $this->model->updateTransactions($account);
            $logger->log("Done");
        }

        return array();
    }
}
