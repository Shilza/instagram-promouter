<?php

use Instagram\InstagramBuilder;

$instagram = (new InstagramBuilder('login', 'password'))->build();
$instagram->startLogging('logfile' , true);
$instagram->unfollowFromAll();

