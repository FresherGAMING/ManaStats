<?php

namespace FresherGAMING\Mana\commands;

use FresherGAMING\Mana\ManaStats;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;

class ManageManaRegenCmd extends Command implements PluginOwned {

    use PluginOwnedTrait;

    public function __construct(ManaStats $main){
        parent::__construct("managemanaregen");
        $this->setDescription("Manage player's mana regen");
        $this->setPermission("managemanaregen.cmd");
        $this->setUsage("§cUsage:\n /managemanaregen [string:player]\n /managemanaregen [string:player] [add|reduce|set] [float:amount]");
        $this->owningPlugin = $main;
    }

    public function execute(CommandSender $sender, string $label, array $args){
        if(!$this->testPermission($sender)){
            return;
        }
        if($sender instanceof Player){
            if(count($args) < 1){
                $this->manaManage($sender->getName(), $sender, null, null);
            } elseif(count($args) === 1) {
                $this->manaManage($args[0], $sender, null, null);
            } elseif(count($args) > 1 && count($args) < 3){
                $sender->sendMessage($this->getUsage());
            } elseif(count($args) === 3){
                $this->manaManage($args[0], $sender, $args[1], $args[2]);
            }
        } else {
            if(count($args) < 3){
                $sender->sendMessage("§cUsage: /managemanaregen [string:player] [add|reduce|set] [float:amount]");
                return;
            } else {
                $this->manaManage($args[0], $sender, $args[1], (float)$args[2]);
            }
        }
    }

    private function manaManage(string $user, $setter, ?string $action, ?float $amount){
        $main = $this->getOwningPlugin();
        $main->retrieveDataFromDatabase($user, function($result)use($user, $setter, $action, $amount, $main){            
            if(count($result) < 1){
                $setter->sendMessage("§cPlayer not found!");
                return;
            }
            $player = "";
            $manaregen = 0;
            foreach($result as $rows){
                $player = $rows["playerid"];
                $manaregen = $rows["manaregen"];
            }
            if($action === null && $amount === null){
                if(!$setter instanceof Player){
                    $setter->sendMessage("§cUsage: /managemanaregen [string:player] [add|reduce|set] [float:amount]");
                    return;
                }
                $this->manaManageForm($player, $setter, $manaregen);
                return;
            } elseif($action !== null && $amount !== null){
                switch($action){
                    case "add":
                        $main->addManaRegen($player, $amount);
                        $setter->sendMessage("§aSuccessfully add " . $player . "'s mana regen from " . $manaregen . "/s to " . $manaregen + $amount . "/s");
                        return;
                    case "reduce":
                        $main->reduceManaRegen($player, $amount);
                        $setter->sendMessage("§aSuccessfully reduced " . $player . "'s mana regen from " . $manaregen . "/s to " . $manaregen - $amount . "/s");
                        return;
                    case "set":
                        $main->setManaRegen($player, $amount);
                        $setter->sendMessage("§aSuccessfully set " . $player . "'s mana regen from " . $manaregen . "/s to " . $amount . "/s");
                        return;
                }
                $setter->sendMessage("§cUsage: /managemanaregen [string:player] [add|reduce|set] [float:amount]");
                return;
            }
        });
    }

    private function manaManageForm(string $player, Player $setter, float $manaregen){
        $main = $this->getOwningPlugin();
        $form = new CustomForm(function($setterplayer, $data)use($main, $setter, $manaregen, $player){
            if($data === null){
                return;
            }
            $result = $data["result"];
            switch(substr($result, 0, 1)){
                case "+":
                    $amount = (float)substr($result, 1);
                    $main->addManaRegen($player, $amount);
                    $setter->sendMessage("§aSuccessfully add " . $player . "'s mana regen from " . $manaregen . "/s to " . $manaregen + $amount . "/s");
                    return;
                case "-":
                    $amount = (float)substr($result, 1);
                    $main->reduceManaRegen($player, $amount);
                    $setter->sendMessage("§aSuccessfully reduced " . $player . "'s mana regen from " . $manaregen . "/s to " . $manaregen - $amount . "/s");
                    return;
                case "=":
                    $amount = (float)substr($result, 1);
                    $main->setManaRegen($player, $amount);
                    $setter->sendMessage("§aSuccessfully set " . $player . "'s mana regen from " . $manaregen . "/s to " . $amount . "/s");
                    return;
            }
            $setter->sendMessage("§cPlease put the symbol!");
            return;
        });

        $form->setTitle("Manage Player's Mana Regen");
        $content = [
            "§aName: §b{player_name}\n",
            "§aMana Regen: §b{player_mana}"
        ];
        $placeholder = ["{player_name}", "{player_mana}"];
        $realstats = [$player, $manaregen];
        $content = str_replace($placeholder, $realstats, $content);
        $form->addLabel(implode("\n", $content));
        $form->addLabel("Put + then amount if you want to add the player's mana regen, Example: +10");
        $form->addLabel("Put - then amount if you want to reduce the player's mana regen, Example: -10");
        $form->addLabel("Put = then amount if you want to set the player's mana regen, Example: =10");
        $form->addInput("", "", null, "result");
        $setter->sendForm($form);
    }
}