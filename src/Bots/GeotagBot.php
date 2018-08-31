<?php

namespace Bot;

use Entity\BotProcessStatistics;
use InstagramAPI\Instagram;

class GeotagBot extends TagBot{
    const DEFAULT_GEOTAGS = [
        'California', "India", "Kiev"
    ];

    private $geotags;

    /**
     * GeotagBot constructor.
     * @param Instagram $instagram
     * @param array $settings
     * @param bool $delay
     * @param BotProcessStatistics|null $botProcessStatistics
     * @throws \Exception
     */
    public function __construct(Instagram $instagram, array $settings,  bool $delay, BotProcessStatistics &$botProcessStatistics = null){
        parent::__construct($instagram, $settings, $delay, $botProcessStatistics);

        $geotags = $settings['custom_geotags'];
        if(isset($geotags) && is_array($geotags) && count($geotags) > 0) {
            if($settings['default_geotags'])
                $this->geotags = array_merge($geotags, static::DEFAULT_GEOTAGS);
            else
                $this->geotags = $geotags;
        } else if($settings['default_geotags'])
            $this->geotags = static::DEFAULT_GEOTAGS;
        else throw new \Exception("No geotags selected");
    }


    protected function start(){
        if(isset($this->geotags)){
            $result = $this->instagram->location->findPlaces(
                $this->geotags[mt_rand(0, count($this->geotags) - 1)]);

            $this->mediaProcessing($this->instagram->location->getFeed(
                $result->getItems()[0]->getLocation()->getPk(),
                $result->getRankToken()
            )->getItems()
            );
        }
    }

}