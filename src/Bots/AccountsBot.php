<?php

namespace Bot;

use Entity\BotProcessStatistics;
use InstagramAPI\Instagram;
use InstagramAPI\Response\Model\User;
use InstagramAPI\Signatures;
use Util\Logger;

class AccountsBot extends Bot
{
    private $cyclesCount = 0;
    private $genesisAccounts;

    /**
     * AccountsBot constructor.
     * @param Instagram $instagram
     * @param array $settings
     * @param bool $delay
     * @param BotProcessStatistics|null $botProcessStatistics
     * @throws \Exception
     */
    public function __construct(Instagram $instagram, array $settings, bool $delay,
                                BotProcessStatistics &$botProcessStatistics = null)
    {
        parent::__construct($instagram, $settings, $delay,$botProcessStatistics);
        $this->genesisAccounts = $settings['genesis_accounts'];
    }

    protected function start()
    {
        $genesisAccount = $this->getRandomGenesisAccount();
        $id = $this->instagram->people->getUserIdForName($genesisAccount);
        Logger::trace("Genesis account: " . $genesisAccount);
        $this->cyclesCount = 0;
        $account = $this->instagram->people->getInfoById($id)->getUser();
        if (!$account->getIsPrivate()) {
            $this->accountProcessing($account);
        }
    }

    private function getRandomGenesisAccount()
    {
        return $this->genesisAccounts[mt_rand(0, count($this->genesisAccounts)-1)];
    }

    /**
     * @param User $account
     * @return bool
     */
    private function accountProcessing(User $account)
    {
        sleep(mt_rand(0, 3));
        if ($this->isStageFinished())
            return true;

        if (!$account->getFollowerCount())
            return false;

        $items = $this->instagram->timeline->getUserFeed(
            $account->getPk())->getItems();
        if (count($items) > 0)
            $accounts = array_merge(
                array_slice(
                    $this->instagram->people->getFollowers($account->getPk(),
                        Signatures::generateUUID())->getUsers(), 0, mt_rand(15, 25)
                ),
                array_slice(
                    $this->instagram->media->getLikers(
                        $items[0]->getPk())->getUsers(),
                    0, mt_rand(15, 25)
                )
            );
        else
            $accounts = array_slice(
                $this->instagram->people->getFollowers($account->getPk(),
                    Signatures::generateUUID())->getUsers(), 0, mt_rand(15, 25)
            );

        $publicAccounts = $this->getPublicAccounts($accounts);

        $accountsID = [];
        foreach ($publicAccounts as $acc)
            array_push($accountsID, $acc->getPk());

        $this->processing($accountsID);

        if (count($publicAccounts) == 0)
            return false;
        else {
            if (!$this->accountProcessing($publicAccounts[rand(0, count($publicAccounts) - 1)])) {
                foreach ($publicAccounts as $publicAccount)
                    if ($this->accountProcessing($publicAccount))
                        return true;
                return false;
            } else
                return true;
        }
    }

    /**
     * @return bool
     */
    private function isStageFinished()
    {
        if ($this->cyclesCount++ > 1)
            return true;
        else
            return false;
    }

}