<?php

namespace Instagram;

class InstagramBuilder
{
    private $userName;
    private $password;
    private $settings = [];
    private $botStatistics;
    private $delay = true;

    const SETTINGS = [
        'followings' => false,
        'likes' => false,
        'comments' => false,
        'default_hashtags' => false,
        'default_geotags' => false,
        'default_comments' => false,
        'genesis_accounts' => [
            'arianagrande',
            'oprah',
            'selenagomez',
            'iamcardib',
            'ozuna'
        ]
    ];

    /**
     * InstagramBuilder constructor.
     * @param $userName
     * @param $password
     */
    public function __construct($userName, $password)
    {
        $this->userName = $userName;
        $this->password = $password;
    }

    /**
     * @return Instagram
     */
    public function build(){
        $instagram = new Instagram($this->buildSettings(), $this->delay,
            $this->botStatistics);
        $instagram->login($this->userName,  $this->password);

        return $instagram;
    }

    private function buildSettings(){
        return array_merge(static::SETTINGS, $this->settings);
    }

    /**
     * Allows the bot to follow
     *
     * @return $this
     */
    public function follows(){
        $this->settings = array_merge($this->settings, ['followings' => true]);
        return $this;
    }

    /**
     * Allows the bot to make likes
     *
     * @return $this
     */
    public function likes(){
        $this->settings = array_merge($this->settings, ['likes' => true]);
        return $this;
    }

    /**
     * Allows the bot to commenting
     *
     * @return $this
     */
    public function comments(){
        $this->settings = array_merge($this->settings, ['comments' => true]);
        return $this;
    }

    /**
     * Add custom comments
     *
     * @param array $customComments
     * @return $this
     */
    public function addCustomComments(array $customComments){
        $this->settings = array_merge($this->settings, ['custom_comments' => $customComments]);
        return $this;
    }

    /**
     * Allows you to use default hashtags
     *
     * @return $this
     */
    public function defaultHashtags(){
        $this->settings = array_merge($this->settings, ['default_hashtags' => true]);
        return $this;
    }


    /**
     * Allows you to use default geotags
     * @return $this
     */
    public function defaultGeotags(){
        $this->settings = array_merge($this->settings, ['default_geotags' => true]);
        return $this;
    }

    /**
     * Add custom hashtags
     *
     * @param array $customHashtags
     * @return $this
     */
    public function addCustomHashtags(array $customHashtags){
        $this->settings = array_merge($this->settings, ['custom_hashtags' => $customHashtags]);
        return $this;
    }

    /**
     * Add custom geotags
     *
     * @param array $customGeotags
     * @return $this
     */
    public function addCustomGeotags(array $customGeotags){
        $this->settings = array_merge($this->settings, ['custom_geotags' => $customGeotags]);
        return $this;
    }

    /**
     * Sets up accounts that the AccountBot will use
     *
     * @param array $genesisAccounts
     * @return $this
     */
    public function setGenesisAccounts(array $genesisAccounts){
        $this->settings = array_merge($this->settings, ['genesis_accounts' => $genesisAccounts]);
        return $this;
    }

    /**
     * We must use the delay before the requests to mimic the browser
     * If you want a faster promotion, you can set this flag to false
     * Warning: if you remove the delay, your account can be blocked by Instagram
     * @return $this
     */
    public function withoutDelay(){
        $this->delay = false;
        return $this;
    }

    /**
     * If you want to know the number of likes, comments and subscribers that the bot has done,
     * you can add a statistic object and later get the data you need
     *
     * @param $botProcessStatistics
     * @return $this
     */
    public function setBotsStatistics(&$botProcessStatistics){
        $this->botStatistics = $botProcessStatistics;
        return $this;
    }
}