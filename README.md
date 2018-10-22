# instagram-promouter
Smart library for the promotion of accounts in the Instagram  

# Installation
Using Composer  

```composer require shilza/instagram-promouter dev-master```

**I don't have Composer**  
You can download it [here](https://getcomposer.org/download/)


# Description
You just run the bot and it does all the routine work of mass following and mass liking for you  

### The Instagram promouter has three kinds of bots:
#### 1. Account bot  
The Account bot takes an account from the list of default accounts(you can pass to bot your custom accounts list), from the selected account it looks at the list of followers, selects some of them, then follow/like/comment them.
#### 2. **Hashtag bot**
The Hashtag bot searches for publications on the specified hashtags, then it selects some publications and follow/like/comment on their owner.
#### 3. **Geotag bot**
The actions of the Geotag bot are similar to those of the Hashtag bot, but to search for publications, it uses specified geotags.

### How to use
```php
use Instagram\InstagramBuilder;

$instagram = (new InstagramBuilder('login', 'password'))->build();

$instagram->runAccountsBot();
$instagram->runHashTagBot();
$instagram->runGeoTagBot();
```  

#### With the help of a `InstagramBuilder` class you can configure the work of bots:
1. You can activate likes, follows, comments.
2. You can activate default comments, hashtags and geotags also you can add your custom comments, hashtags and geotags.
3. To mimic the actions of the browser, the bots use the delay between requests to remove it, you need to call the method `withoutDelay()` of the InstagramBuilder class.
4. To configure the Account bot operations, you can specify the list of accounts that it will use with the `setGenesisAccounts()` method of the InstagramBuilder class. If you do not specify them, then default accounts will be used.

If you want to log the actions of bots, you can use the built-in logger.  
```php
$instagram->startLogging('logfile' , true);
```  
The first parameter is the path to the log file.
If you need to output logs to the console, you can pass the flag `true` to the second parameter.
  
  
If you want to keep statistics of the actions of bots, you can use `BotProcessStatistics` class.  

```php
use Entity\BotProcessStatistics;
use Instagram\InstagramBuilder;

$statistics = new BotProcessStatistics();

$instagram = (new InstagramBuilder('login', 'password'))
    ->setBotsStatistics($statistics)
    ->build();
``` 

The `BotProcessStatistics` object will retain the count of likes, follows and comments which were made by the bot.

# Examples
All examples can be found [here](https://github.com/Shilza/instagram-promouter/tree/master/examples)

# Legal
This code is in no way affiliated with, authorized, maintained, sponsored or endorsed by Instagram or any of its affiliates or subsidiaries. This is an independent and unofficial service.
