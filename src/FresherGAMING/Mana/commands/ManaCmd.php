<?php

namespace FresherGAMING\Mana\commands;

use FresherGAMING\Mana\Mana;
use jojoe77777\FormAPI\SimpleForm;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\console\ConsoleCommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;

class ManaCmd extends Command implements PluginOwned {

    use PluginOwnedTrait;

    public function __construct(Mana $main){
        parent::__construct("mana");
        $this->setDescription("View players mana stats");
        $this->setPermission("mana.cmd");
        $this->setUsage("§aUsage: /mana [string:player]");
        $this->owningPlugin = $main;
    }

    public function execute(CommandSender $sender, string $label, array $args){
        if($sender instanceof Player){
            if(count($args) < 1){
                $this->manaForm($sender->getName(), $sender);
            } else {
                $this->manaForm($args[0], $sender);
            }
        } else {
            if(count($args) < 1){
                $sender->sendMessage($this->getUsage());
            } else {
                $this->viewMana($args[0], $sender);
            }
        }
    }

    private function manaForm(string $user, Player $viewer){
        $main = $this->getOwningPlugin();
        $form = new SimpleForm(function($player, $data){});
        if(strtolower($user) === strtolower($viewer->getName())){
            self:
            $form->setTitle($main->getConfig()->get("mana-self-form-title"));
            $content = $main->getConfig()->get("mana-self-form-content");
            $placeholder = ["{player_name}", "{player_mana}", "{player_max_mana}", "{player_mana_regen}"];
            $realstats = [$viewer->getName(), $main->getMana($viewer), $main->getMaxMana($viewer), $main->getManaRegen($viewer)];
            $content = str_replace($placeholder, $realstats, $content);
            $form->setContent($content);
            $viewer->sendForm($form);
            return;
        } else {
            if($main->getConfig()->get("allow-players-view-others") !== true && (!$viewer->hasPermission("mana.cmd.others"))){
                goto self;
            }
            if($main->getServer()->getPlayerExact($user) instanceof Player){
                $user = $main->getServer()->getPlayerExact($user)->getName();
                $form->setTitle($main->getConfig()->get("mana-other-form-title"));
                $content = $main->getConfig()->get("mana-other-form-content");
                $placeholder = ["{player_name}", "{player_mana}", "{player_max_mana}", "{player_mana_regen}"];
                $realstats = [$user, $main->getMana($user), $main->getMaxMana($user), $main->getManaRegen($user)];
                $content = str_replace($placeholder, $realstats, $content);
                $form->setContent($content);
                $viewer->sendForm($form);
                return;
            }
            $main->retrieveDataFromDatabase($user, function($result) use($form, $main, $viewer){
                if(count($result) < 1){
                    $viewer->sendMessage($main->getConfig()->get("mana-other-cmd-invalid-player"));
                    return;
                }
                foreach($result as $rows){
                    $form->setTitle($main->getConfig()->get("mana-offline-form-title"));
                    $content = $main->getConfig()->get("mana-offline-form-content");
                    $placeholder = ["{player_name}", "{player_mana}", "{player_max_mana}", "{player_mana_regen}"];
                    $realstats = [$rows["playerid"], "(Player is Offline)", $rows["maxmana"], $rows["manaregen"]];
                    $content = str_replace($placeholder, $realstats, $content);
                    $form->setContent($content);
                    $viewer->sendForm($form);
                    return;
                }
            });
        }
    }

    private function viewMana(string $user, $viewer){
        $main = $this->getOwningPlugin();
        if($main->getServer()->getPlayerExact($user) instanceof Player){
            $user = $main->getServer()->getPlayerExact($user)->getName();
            $viewer->sendMessage($main->getConfig()->get("mana-other-form-title"));
            $content = $main->getConfig()->get("mana-other-form-content");
            $placeholder = ["{player_name}", "{player_mana}", "{player_max_mana}", "{player_mana_regen}"];
            $realstats = [$user, $main->getMana($user), $main->getMaxMana($user), $main->getManaRegen($user)];
            $content = str_replace($placeholder, $realstats, $content);
            $viewer->sendMessage($content);
            return;
        }
        $main->retrieveDataFromDatabase($user, function($result) use($main, $viewer){
            if(count($result) < 1){
                $viewer->sendMessage($main->getConfig()->get("mana-other-cmd-invalid-player"));
                return;
            }
            foreach($result as $rows){
                $viewer->sendMessage($main->getConfig()->get("mana-offline-form-title"));
                $content = $main->getConfig()->get("mana-offline-form-content");
                $placeholder = ["{player_name}", "{player_mana}", "{player_max_mana}", "{player_mana_regen}"];
                $realstats = [$rows["playerid"], "Offline", $rows["maxmana"], $rows["manaregen"]];
                $content = str_replace($placeholder, $realstats, $content);
                $viewer->sendMessage($content);
                return;
            }
        });
    }
}