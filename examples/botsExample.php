<?php

use Entity\BotProcessStatistics;
use Instagram\InstagramBuilder;

$statistics = new BotProcessStatistics();

$instagram = (new InstagramBuilder('login', 'password'))
    ->likes()
    ->follows()
    ->comments()
    ->addCustomComments(['Pretty', 'Wow!'])
    ->defaultHashtags()
    ->defaultGeotags()
    ->addCustomHashtags(['hashtag', 'sunrise'])
    ->addCustomGeotags(['Ottawa', 'Berlin'])
    ->withoutDelay()
    ->setBotsStatistics($statistics)
    ->build();
$instagram->startLogging('logfile', true);

$instagram->runAccountsBot();
echo PHP_EOL . $statistics->getPointsCount();
$instagram->runHashTagBot();
echo PHP_EOL . $statistics->getPointsCount();
$instagram->runGeoTagBot();

echo PHP_EOL . "Points count: " . $statistics->getPointsCount() . PHP_EOL;



