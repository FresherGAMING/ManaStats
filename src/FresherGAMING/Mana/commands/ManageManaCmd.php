<?php

namespace FresherGAMING\Mana\commands;

use FresherGAMING\Mana\Mana;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;

class ManageManaCmd extends Command implements PluginOwned {

    use PluginOwnedTrait;

    public function __construct(Mana $main){
        parent::__construct("managemana");
        $this->setDescription("Manage player's mana");
        $this->setPermission("managemana.cmd");
        $this->setUsage("§cUsage:\n /managemana [string:player]\n /managemana [string:player] [add|reduce|set] [float:amount]");
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
                $sender->sendMessage("§cUsage: /managemana [string:player] [add|reduce|set] [float:amount]");
                return;
            } else {
                $this->manaManage($args[0], $sender, $args[1], (float)$args[2]);
            }
        }
    }

    private function manaManage(string $user, $setter, ?string $action, ?float $amount){
        $main = $this->getOwningPlugin();
        $player = $main->getServer()->getPlayerExact($user);
        if($player === null){
            $setter->sendMessage("§c" . $user . " is Offline!");
            return;
        }
        if($action === null && $amount === null){
            if(!$setter instanceof Player){
                $setter->sendMessage("§cUsage: /managemana [string:player] [add|reduce|set] [float:amount]");
                return;
            }
            $this->manaManageForm($player, $setter);
            return;
        } elseif($action !== null && $amount !== null){
            switch($action){
                case "add":
                    $mana = $main->getMana($player);
                    $main->addMana($player, $amount);
                    if($main->getMana($player) > $main->getMaxMana($player)){
                        $main->setMana($player, $main->getMaxMana($player));
                    }
                    $setter->sendMessage("§aSuccessfully add " . $player->getName() . "'s mana from " . $mana . "/" . $main->getMaxMana($player) . " to " . $main->getMana($player) . "/" . $main->getMaxMana($player));
                    return;
                case "reduce":
                    $mana = $main->getMana($player);
                    $main->reduceMana($player, $amount);
                    if($main->getMana($player) < 0){
                        $main->setMana($player, 0);
                    }
                    $setter->sendMessage("§aSuccessfully reduced " . $player->getName() . "'s mana from " . $mana . "/" . $main->getMaxMana($player) . " to " . $main->getMana($player) . "/" . $main->getMaxMana($player));
                    return;
                case "set":
                    $mana = $main->getMana($player);
                    $main->setMana($player, $amount);
                    $setter->sendMessage("§aSuccessfully set " . $player->getName() . "'s mana from " . $mana . "/" . $main->getMaxMana($player) . " to " . $main->getMana($player) . "/" . $main->getMaxMana($player));
                    return;
            }
            $setter->sendMessage("§cUsage: /managemana [string:player] [add|reduce|set] [float:amount]");
            return;
        }
    }

    private function manaManageForm(Player $player, Player $setter){
        $main = $this->getOwningPlugin();
        $form = new CustomForm(function($setterplayer, $data)use($main, $setter, $player){
            if($data === null){
                return;
            }
            $result = $data["result"];
            switch(substr($result, 0, 1)){
                case "+":
                    $amount = (float)substr($result, 1);
                    $mana = $main->getMana($player);
                    $main->addMana($player, $amount);
                    if($main->getMana($player) > $main->getMaxMana($player)){
                        $main->setMana($player, $main->getMaxMana($player));
                    }
                    $setter->sendMessage("§aSuccessfully add " . $player->getName() . "'s mana from " . $mana . "/" . $main->getMaxMana($player) . " to " . $main->getMana($player) . "/" . $main->getMaxMana($player));
                    return;
                case "-":
                    $amount = (float)substr($result, 1);
                    $mana = $main->getMana($player);
                    $main->reduceMana($player, $amount);
                    if($main->getMana($player) < 0){
                        $main->setMana($player, 0);
                    }
                    $setter->sendMessage("§aSuccessfully reduced " . $player->getName() . "'s mana from " . $mana . "/" . $main->getMaxMana($player) . " to " . $main->getMana($player) . "/" . $main->getMaxMana($player));
                    return;
                case "=":
                    $amount = (float)substr($result, 1);
                    $mana = $main->getMana($player);
                    $main->setMana($player, $amount);
                    $setter->sendMessage("§aSuccessfully set " . $player->getName() . "'s mana from " . $mana . "/" . $main->getMaxMana($player) . " to " . $main->getMana($player) . "/" . $main->getMaxMana($player));
                    return;
            }
            $setter->sendMessage("§cPlease put the symbol!");
            return;
        });
        $form->setTitle("Manage Player's Mana");
        $content = [
            "§aName: §b{player_name}\n",
            "§aMana: §b{player_mana}"
        ];
        $placeholder = ["{player_name}", "{player_mana}"];
        $realstats = [$player->getName(), $main->getMana($player)];
        $content = str_replace($placeholder, $realstats, $content);
        $form->addLabel(implode("\n", $content));
        $form->addLabel("Put + then amount if you want to add the player's mana, Example: +10");
        $form->addLabel("Put - then amount if you want to reduce the player's mana, Example: -10");
        $form->addLabel("Put = then amount if you want to set the player's mana, Example: =10");
        $form->addInput("", "", null, "result");
        $setter->sendForm($form);
    }
}