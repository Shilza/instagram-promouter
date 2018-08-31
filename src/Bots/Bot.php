<?php

namespace Bot;

use Entity\BotProcessStatistics;
use InstagramAPI\Exception\FeedbackRequiredException;
use InstagramAPI\Exception\NetworkException;
use InstagramAPI\Exception\RequestException;
use InstagramAPI\Instagram;
use InstagramAPI\Response\Model\User;
use Util\Logger;

abstract class Bot
{
    const MAX_ACCOUNTS_COUNT = 20;
    const MAX_FAILS_COUNT = 15;
    const REQUEST_DELAY = 240; //240
    const DEFAULT_COMMENTS = ['Like it!', 'Nice pic', 'Awesome ☺',
        'Nice image!!!', 'Cute ♥', "👍👍👍", "🔝🔝🔝", "🔥🔥🔥"];

    protected $instagram;
    private $comments;

    protected $likesSelected = false;
    protected $commentsSelected = false;
    protected $followingSelected = false;

    private $newCommentTime = 0;
    private $newLikeTime = 0;
    private $newFollowTime = 0;

    private $failsCount = 0;

    private $botProcessStatistics;
    private $delay;

    /**
     * Bot constructor.
     * @param Instagram $instagram
     * @param array $settings
     * @param bool $delay
     * @param BotProcessStatistics|null $botProcessStatistics
     * @throws \Exception
     */
    protected function __construct(Instagram $instagram, array $settings, bool $delay,
                                   BotProcessStatistics &$botProcessStatistics = null)
    {
        $this->instagram = $instagram;
        $this->botProcessStatistics = $botProcessStatistics;
        $this->delay = $delay;

        if (isset($settings)) {
            if (array_key_exists('likes', $settings))
                $this->likesSelected = $settings['likes'];
            if (array_key_exists('comments', $settings))
                $this->commentsSelected = $settings['comments'];
            if (array_key_exists('followings', $settings))
                $this->followingSelected = $settings['followings'];

            if ($settings['comments']) {
                if (isset($settings['custom_comments'])) {
                    if ($settings['default_comments']) {
                        $this->comments = array_merge($settings['custom_comments'],
                            static::DEFAULT_COMMENTS);
                    } else
                        $this->comments = $settings['custom_comments'];
                } else if ($settings['default_comments'])
                    $this->comments = static::DEFAULT_COMMENTS;
                else throw new \Exception("No comments selected");
            }
        }
    }

    /**
     * @throws \Exception
     * @throws RequestException
     */
    public
    function run()
    {
        try {
            if ($this->followingSelected || $this->likesSelected || $this->commentsSelected) {
                Logger::info("Run " . get_class($this));
                $this->start();
            }
        } catch (FeedbackRequiredException $e) {
            if ($e->hasResponse())
                Logger::debug("Bot crush: " . $e->getResponse()->getMessage());
        } catch (NetworkException $e) {
            Logger::debug("Bot crush: " . $e->getMessage());
        } catch (RequestException $e) {
            if ($this->failsCount++ < static::MAX_FAILS_COUNT) {
                if (stristr($e->getMessage(), "Please wait a few minutes before you try again.") !== false) {
                    Logger::debug("Bot crush: " . $e->getMessage());

                    sleep(static::REQUEST_DELAY);;

                    $this->run();
                } else if (stristr($e->getMessage(), "Not authorized to view user.") === false) {
                    throw $e;
                }
            } else
                throw new \Exception("Request failed");
        } finally {
            $this->failsCount = 0;
        }
    }


    abstract protected function start();

    /**
     * @param $accountsID
     */
    protected function processing($accountsID)
    {
        foreach ($accountsID as $accountID) {

            if ($accountID != $this->instagram->account_id) {
                if ($this->followingSelected && mt_rand(0, 1) == 1) {
                    if (($time = time()) < $this->newFollowTime)
                        sleep($this->newFollowTime - $time);
                    $this->follow($accountID);

                    if($this->delay)
                        $this->newFollowTime = time() + mt_rand(28, 38); //DELAY AFTER REQUEST
                }

                if ($this->likesSelected && mt_rand(0, 1) == 1) {
                    if (($time = time()) < $this->newLikeTime)
                        sleep($this->newLikeTime - $time);
                    $this->likeAccountsMedia($accountID);

                    if($this->delay)
                        $this->newLikeTime = time() + mt_rand(28, 36); //DELAY AFTER REQUEST
                }

                if ($this->commentsSelected && mt_rand(0, 3) == 1) {
                    if (time() < $this->newCommentTime)
                        continue;
                    $this->commentAccountsMedia($accountID);

                    if($this->delay)
                        $this->newCommentTime = time() + mt_rand(200, 250); //DELAY AFTER REQUEST
                }
            }
        }
    }

    /**
     * @param int|string $userID
     */
    protected function likeAccountsMedia($userID)
    {
        $medias = $this->instagram->timeline->getUserFeed($userID)->getItems();
        $count = mt_rand(3, 5);

        if (count($medias) > 0) {

            if ($count > count($medias))
                foreach ($medias as $media) {
                    if (isset($this->botProcessStatistics))
                        $this->botProcessStatistics->likesCount++;
                    $this->instagram->media->like($media->getPk());
                    Logger::trace("Like " . $media->getUser()->getUsername());
                }
            else
                while ($count > 0) {
                    $index = mt_rand(0, count($medias) - 1);
                    $media = $medias[$index];

                    if (!$media->getHasLiked()) {
                        if (isset($this->botProcessStatistics))
                            $this->botProcessStatistics->likesCount++;
                        $this->instagram->media->like($media->getPk());
                        Logger::trace("Like " . $media->getUser()->getUsername());
                    }

                    array_splice($medias, $index, 1);
                    $count--;
                }
        }
    }

    /**
     * @param int|string $userID
     */
    protected function commentAccountsMedia($userID)
    {
        $medias = $this->instagram->timeline->getUserFeed($userID)->getItems();

        $commentableMediasID = [];
        foreach ($medias as $media)
            if (is_null($media->getCommentsDisabled())) //NULL is enabled
                array_push($commentableMediasID, $media->getPk());

        if (count($commentableMediasID) > 0) {
            if (isset($this->botProcessStatistics))
                $this->botProcessStatistics->commentsCount++;
            $comment = $this->instagram->media->comment(
                $commentableMediasID[mt_rand(0, count($commentableMediasID) - 1)],
                $this->comments[mt_rand(0, count($this->comments) - 1)]
            )->getComment();

            Logger::trace("Comment on "
                . $this->instagram->media->getInfo(
                    $comment->getMediaId())->getItems()[0]->getUser()->getUsername()
                . " Text: " . $comment->getText());
        }
    }

    /**
     * @param int|string $userID
     */
    private function follow($userID)
    {
        Logger::trace("Follow on "
            . $this->instagram->people->getInfoById($userID)->getUser()->getUsername()
        );
        if (isset($this->botProcessStatistics))
            $this->botProcessStatistics->followsCount++;
        $this->instagram->people->follow($userID);
    }

    /**
     * @param User[] $accounts
     * @return User[]
     */
    protected function getPublicAccounts(array $accounts)
    {
        $publicAccounts = [];
        $maxCount = static::MAX_ACCOUNTS_COUNT;

        foreach ($accounts as $account) {
            if ($maxCount <= 0)
                break;
            if (!$account->getIsPrivate() &&
                count($this->instagram->timeline->getUserFeed($account->getPk())->getItems()) > 0
            ) {
                array_push($publicAccounts, $account);
                $maxCount--;
            }
        }

        return $publicAccounts;
    }
}