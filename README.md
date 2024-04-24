# About This Plugin
Mana is a Pocketmine MP Plugin that will add mana features on your server.

Every time you're sprint or jump, The mana will decrease.

When the mana reach 0, You will get blindness effect.

If you want mana to be regenerated, You have to stay at a place and didn't moving an inch.

# Commands
| Command                          | Description                  | Usage                                                                  |
| -------------------------------- | ---------------------------- | ---------------------------------------------------------------------- |
| /mana                            | View player's mana stats     | /mana [string:player]                                                  |
| /managemana [FORM TYPE]          | Manage player's mana stats   | /managemana [string:player]                                            |
| /managemana [MANUAL TYPE]        | Manage player's mana stats   | /managemana [string:player] [add/reduce/set] [float:amount]            |
| /managemanaregen [FORM TYPE]     | Manage player's mana regen   | /managemanaregen [string:player]                                       |
| /managemanaregen [MANUAL TYPE]   | Manage player's mana regen   | /managemanaregen [string:player] [add|reduce|set] [float:amount]       |
| /managemaxmana [FORM TYPE]       | Manage player's max mana     | /managemaxmana [string:player]                                         |
| /managemaxmana [MANUAL TYPE]     | Manage player's max mana     | /managemaxmana [string:player] [add|reduce|set] [float:amount]         |

# Permissions
| Command            | Permissions                                  | Default | Description                |
| ------------------ | -------------------------------------------- | ------- | -------------------------- |
| /mana              | mana.cmd                                     | True    | View Your Own Stats        |
| /mana              | mana.cmd.others                              | OP      | View Other's Stats         |
| /managemana        | managemana.cmd                               | OP      | Manage Player's Mana       |
| /managemanaregen   | managemanaregen.cmd                          | OP      | Manage Player's Mana Regen |
| /managemaxmana     | managemaxmana.cnmd                           | OP      | Manage Player's Max Mana   |
|         -          | mana.bypass                                  | False   | Bypass Mana System         |

# For Developers
Importing The ManaStats File
```php
use FresherGAMING\Mana\ManaStats;
```

### Player's Mana Stats
If you want to get the player's mana, Only For Online Players
```php
$player = //Could be string name or player type
ManaStats::getInstance()->getMana($player);
```

If you want to add the player's mana, Only For Online Players
```php
$player = //Could be string name or player type
$amount = //Could be float type
ManaStats::getInstance()->addMana($player, $amount);
```

If you want to reduce the player's mana, Only For Online Players
```php
$player = //Could be string name or player type
$amount = //Could be float type
ManaStats::getInstance()->reduceMana($player, $amount);
```

If you want to set the player's mana, Only For Online Players
```php
$player = //Could be string name or player type
$amount = //Could be float type
ManaStats::getInstance()->setMana($player, $amount);
```

### Player's Mana Regen Stats, Only for Online Players
If you want to get the player's mana regen, Only For Online Players
```php
$player = //Could be string name or player type
ManaStats::getInstance()->getManaRegen($player);
```

If you want to add the player's mana regen, Could be Offline or Online Players
```php
$player = //Could be string name or player type
$amount = //Could be float type
ManaStats::getInstance()->addManaRegen($player, $amount);
```

If you want to reduce the player's mana regen, Could be Offline or Online Players
```php
$player = //Could be string name or player type
$amount = //Could be float type
ManaStats::getInstance()->reduceManaRegen($player, $amount);
```

If you want to set the player's mana regen, Could be Offline or Online Players
```php
$player = //Could be string name or player type
$amount = //Could be float type
ManaStats::getInstance()->setManaRegen($player, $amount);
```

### Player's Max Mana Stats, Only for Online Players
If you want to get the player's max mana, Only For Online Players
```php
$player = //Could be string name or player type
ManaStats::getInstance()->getMaxMana($player);
```

If you want to add the player's max mana, Could be Offline or Online Players
```php
$player = //Could be string name or player type
$amount = //Could be float type
ManaStats::getInstance()->addMaxMana($player, $amount);
```

If you want to reduce the player's max mana, Could be Offline or Online Players
```php
$player = //Could be string name or player type
$amount = //Could be float type
ManaStats::getInstance()->reduceMaxMana($player, $amount);
```

If you want to set the player's max mana, Could be Offline or Online Players
```php
$player = //Could be string name or player type
$amount = //Could be float type
ManaStats::getInstance()->setMaxMana($player, $amount);
```

### Getting The Stats Directly From Database, Could be Offline or Online Players
```php
$player = //Could be string name or player type
ManaStats::getInstance()->retrieveDataFromDatabase($player, function($result){
   if(count($result) < 1){
      //Player not Found
   }
   foreach($result as $stats){
      //$stats["maxmana"] to get the player's max mana
      //$stats["manaregen"] to get the player's mana regen
   }
});
```
