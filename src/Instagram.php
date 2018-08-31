<?php

namespace Instagram;

use Bot\AccountsBot;
use Bot\GeotagBot;
use Bot\HashtagBot;
use Entity\BotProcessStatistics;
use InstagramAPI\Signatures;
use Util\AccountWorker;
use Util\Logger;

class Instagram
{
    private $instagram;

    private $accountBot;
    private $hashTagBot;
    private $geoTagBot;

    private $statistics;
    private $settings;

    private $delay;

    /**
     * Instagram constructor.
     * @param array $settings
     * @param bool $delay
     * @param BotProcessStatistics|null $statistics
     */
    public function __construct(array $settings, bool $delay,
                                BotProcessStatistics &$statistics = null){

        $this->settings = $settings;
        $this->statistics = $statistics;
        $this->delay = $delay;
    }

    /**
     * @param string $username
     * @param string $password
     */
    public function login(string $username, string $password){
        $this->instagram = new \InstagramAPI\Instagram(false, false);
        $this->instagram->login($username, $password);
    }

    /**
     * The AccountBot takes one of the accounts and begins
     * to search for the accounts that are subscribed to it,
     * by finding the accounts, it subscribes, make likes or comments (optional)
     */
    public function runAccountsBot(){
        $this->createIfNull(AccountsBot::class, $this->accountBot)->run();
    }

    /**
     * The HashtagBot searches for publications by hashtags,
     * takes accounts and processes them
     */
    public function runHashTagBot(){
        $this->createIfNull(HashtagBot::class, $this->hashTagBot)->run();
    }

    /**
     * The HashtagBot searches for publications by geotags,
     * takes accounts and processes them
     */
    public function runGeoTagBot(){
        $this->createIfNull(GeotagBot::class, $this->geoTagBot)->run();
    }

    /**
     * @param $filePath
     * @param bool $logToConsole
     */
    public function startLogging($filePath, $logToConsole = false){
        Logger::setFilePath($filePath);
        Logger::setLogToConsoleFlag($logToConsole);
    }

    public function getSelfFollowing(){
        return $this->instagram->people->getSelfFollowing(
            Signatures::generateUUID())->getUsers();
    }

    /**
     * @param $userId
     */
    public function unfollow($userId){
        $this->instagram->people->unfollow($userId);
    }

    public function unfollowFromAll(){
        (new AccountWorker($this, $this->delay))->unfollowFromAll();
    }

    public function unfollowFromUnfollowers(){
        (new AccountWorker($this, $this->delay))->unfollowFromUnfollowers();
    }

    /**
     * @param $userId
     * @return \InstagramAPI\Response\FriendshipsShowResponse
     */
    public function getFriendship($userId){
        return $this->instagram->people->getFriendship($userId);
    }

    /**
     * @param $className
     * @param $bot
     * @return mixed
     */
    private function createIfNull($className, $bot){
        return (is_null($bot)
            ? new $className($this->instagram, $this->settings, $this->delay, $this->statistics)
            : $bot);
    }
}