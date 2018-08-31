<?php

namespace Bot;

use Entity\BotProcessStatistics;
use InstagramAPI\Instagram;

abstract class TagBot extends Bot{

    /**
     * TagBot constructor.
     * @param Instagram $instagram
     * @param array $settings
     * @param bool $delay
     * @param BotProcessStatistics|null $botProcessStatistics
     * @throws \Exception
     */
    protected function __construct(Instagram $instagram, array $settings, bool $delay,
                                   BotProcessStatistics &$botProcessStatistics = null){
        parent::__construct($instagram, $settings, $delay, $botProcessStatistics);
    }

    /**
     * @param array $medias
     */
    protected function mediaProcessing(array $medias)
    {
        $accountsID = [];
        foreach ($medias as $media)
            if (!in_array($media->getUser()->getPk(), $accountsID))
                array_push($accountsID, $media->getUser()->getPk());

        $accountsID = array_slice($accountsID, 0, mt_rand(15, 25));
        foreach (
            $this->getPublicAccounts(array_slice(
                    $this->instagram->media->getLikers($medias[0]->getPk())->getUsers(),
                    0, mt_rand(15, 25)
                )
            ) as $acc)
            array_push($accountsID, $acc->getPk());

        $this->processing($accountsID);
    }
}