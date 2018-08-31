<?php

namespace Bot;

use Entity\BotProcessStatistics;
use InstagramAPI\Instagram;
use InstagramAPI\Signatures;

class HashtagBot extends TagBot{
    const DEFAULT_HASHTAGS = ['follow4like', "follow4likes", "follow",
        "follow4", "follow4folow", "followers",
        "following", "liker", "likers",
        "likelike", "liked", "likeme", "like4follow", "instalike", "likeit"];

    private $hashtags;

    /**
     * HashtagBot constructor.
     * @param Instagram $instagram
     * @param array $settings
     * @param bool $delay
     * @param BotProcessStatistics|null $botProcessStatistics
     * @throws \Exception
     */
    public function __construct(Instagram $instagram, array $settings, bool $delay,
                                BotProcessStatistics &$botProcessStatistics = null){
        parent::__construct($instagram, $settings, $delay, $botProcessStatistics);

        $hashtags = $settings['custom_hashtags'];
        if(isset($hashtags) && is_array($hashtags) && count($hashtags) > 0) {
            if($settings['default_hashtags'])
                $this->hashtags = array_merge($hashtags, static::DEFAULT_HASHTAGS);
            else
                $this->hashtags = $hashtags;
        } else if($settings['default_hashtags'])
            $this->hashtags = static::DEFAULT_HASHTAGS;
        else throw new \Exception("No hashtags selected");
    }

    protected function start()
    {
        $this->mediaProcessing($this->instagram->hashtag->getFeed(
            $this->hashtags[mt_rand(0, count($this->hashtags) - 1)],
            Signatures::generateUUID()
            )->getItems()
        );
    }
}