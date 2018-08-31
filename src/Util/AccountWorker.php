<?php

namespace Util;

use InstagramAPI\Exception\NetworkException;
use InstagramAPI\Exception\NotFoundException;
use InstagramAPI\Exception\RequestException;
use Instagram\Instagram;

class AccountWorker
{
    const REQUEST_DELAY = 240;
    const MAX_FAILS_COUNT = 5;

    private $maxPointsCount;
    private $failsCount = 0;
    private $instagram;
    private $delay;

    /**
     * AccountWorker constructor.
     * @param Instagram $instagram
     * @param bool $delay
     */
    public function __construct(Instagram $instagram, bool $delay)
    {
        $this->instagram = $instagram;
        $this->delay = $delay;
        $this->maxPointsCount = mt_rand(1800, 2000);
    }

    public function unfollowFromAll()
    {
        $this->runFunction('unfollowingFromAll');
    }

    public function unfollowFromUnfollowers()
    {
        $this->runFunction('unfollowingFromUnfollowers');
    }

    /**
     * @param $function
     * @throws \Exception
     */
    private function runFunction($function)
    {
        try {
            Logger::trace("Account worker started");
            $this->$function();
        } catch (\InstagramAPI\Exception\FeedbackRequiredException $e) {
            if ($e->hasResponse())
                Logger::debug("Bot crush: " . $e->getResponse()->getMessage());
        } catch (NetworkException $e) {
            //SKIP SLL ERRORS
        } catch (RequestException $e) {
            if ($this->failsCount++ < static::MAX_FAILS_COUNT) {
                if (stristr($e->getMessage(), "Please wait a few minutes before you try again.") !== false) {
                    Logger::debug("AccountWorker crush: " . $e->getMessage());

                    sleep(static::REQUEST_DELAY);

                    Logger::debug("Sleep end");

                    $this->runFunction($function);
                } else if (stristr($e->getMessage(), "Not authorized to view user.") === false) {
                    throw $e;
                }
            } else
                throw new \Exception("Request failed");
        } finally {
            $this->failsCount = 0;
        }
    }

    private function unfollowingFromAll()
    {
        $followings = [];

        foreach (array_slice($this->instagram->getSelfFollowing(), 0, $this->maxPointsCount)
                 as $following) {
            array_push($followings, $following->getPk());
        }

        $this->unfollow($followings);
    }

    /**
     * @param array $followedUsers
     */
    private function unfollow(array $followedUsers)
    {

        for ($i = 0; $i < count($followedUsers); $i++) {

            Logger::trace("Unfollow from " . $followedUsers[$i]);
            try {
                $this->instagram->unfollow($followedUsers[$i]);
            } catch (NotFoundException $e) {
                //SKIP DELETED ACCOUNT
            } catch (NetworkException $e) {
                $i--;
            }
            $this->failsCount = 0;

            if($this->delay)
                sleep(mt_rand(12, 22)); //DELAY AFTER REQUEST
        }
    }

    /**
     * @throws \Exception
     */
    private function unfollowingFromUnfollowers()
    {
        $allFollowedUsers = [];

        foreach (
            array_slice($this->instagram->getSelfFollowing(), 0, $this->maxPointsCount)
            as $following) {
            array_push($allFollowedUsers, $following->getPk());
        }

        for ($count = 0; $count * 200 < count($allFollowedUsers); $count++) {
            $followedUsers = array_slice($allFollowedUsers, $count * 200, 200);
            $unfollowers = [];

            for ($i = 0; $i < count($followedUsers); $i++)
                try {
                    if (!$this->instagram->getFriendship(
                        $followedUsers[$i])->isFollowedBy())
                        array_push($unfollowers, $followedUsers[$i]);
                } catch (NotFoundException $e) {
                    //SKIP
                } catch (NetworkException $e) {
                    $i--;
                }

            $this->unfollow($unfollowers);
            if ($this->maxPointsCount <= 0)
                return;
        }
    }
}