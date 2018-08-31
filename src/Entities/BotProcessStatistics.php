<?php
namespace Entity;

class BotProcessStatistics{
    public $id;
    public $likesCount = 0;
    public $commentsCount = 0;
    public $followsCount = 0;

    /**
     * BotProcessStatistics constructor.
     * @param int $id
     * @param int $likesCount
     * @param int $commentsCount
     * @param int $followsCount
     */
    public function __construct($id = 0, $likesCount = 0, $commentsCount = 0, $followsCount = 0)
    {
        $this->id = $id;
        $this->likesCount = $likesCount;
        $this->commentsCount = $commentsCount;
        $this->followsCount = $followsCount;
    }

    /**
     * @return int
     */
    public function getPointsCount(){
        return $this->likesCount + $this->commentsCount + $this->followsCount;
    }
}