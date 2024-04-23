# About This Plugin
Mana is a Pocketmine MP Plugin that will add like stamina features on your server.

When you're sprint or jump, the mana will decrease.

If you want the mana to regen, you have to stay at a place and didn't moving an inch.

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

