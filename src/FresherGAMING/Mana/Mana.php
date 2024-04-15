<?php

namespace FresherGAMING\Mana;

use FresherGAMING\Mana\task\ManaRegenTask;
use FresherGAMING\Mana\task\ManaTask;
use pocketmine\entity\Attribute;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerToggleSprintEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

class Mana extends PluginBase implements Listener {

    private array $mana = [];
    private array $maxmana = [];
    private array $manastopregen = [];
    private array $manareduce = [];
    private array $manaregencd = [];
    private DataConnector $db;

    public function onEnable() : void{
        $this->saveResource("config.yml");
        $this->getScheduler()->scheduleRepeatingTask(new ManaTask($this), 1);
        $this->getScheduler()->scheduleRepeatingTask(new ManaRegenTask($this), 20);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->db = libasynql::create($this, $this->getConfig()->get("database"), [
            "sqlite" => "sqlite.sql",
            "mysql"  => "mysql.sql"
        ]);
        $this->db->executeGeneric("mana.setup");
    }

    public function onLogin(PlayerLoginEvent $event){
        $player = $event->getPlayer();
        $this->setData($player);
    }

    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        $this->clearData($player);
    }

    public function onMove(PlayerMoveEvent $event){
        $player = $event->getPlayer();
        $from = $event->getFrom();
        $to = $event->getTo();
        if($from->getX() === $to->getX() && $from->getY() === $to->getY() && $from->getZ() === $to->getZ()){
            return;
        }
        $this->manastopregen[$player->getName()] = time() + 2;
        if($this->getMana($player) < 1){
            return;
        }
        if($player->isSprinting()){
            if(time() > $this->manareduce[$player->getName()]){
                $this->manareduce[$player->getName()] = time() + 1;
                $this->reduceMana($player, 1);
            }
        }
    }

    public function onJump(PlayerJumpEvent $event){
        $player = $event->getPlayer();
        $this->manastopregen[$player->getName()] = time() + 1.5;
        if($this->getMana($player) < 1){
            return;
        }
        $this->reduceMana($player, 1);
    }

    private function setData(Player $player){
        $this->db->executeSelect("mana.getdata", ["playerid" => $player->getName()], function(array $rows)use($player){
            if(count($rows) < 1){
                $this->db->executeInsert("mana.newdata", ["playerid" => $player->getName(), "maxmana" => $this->getConfig()->get("default-mana")]);
                $this->maxmana[$player->getName()] = $this->getConfig()->get("default-mana");
            } else {
                foreach($rows as $data){
                    $this->maxmana[$player->getName()] = $data["maxmana"];
                }
            }
        });
        $this->mana[$player->getName()] = 0;
        $this->manastopregen[$player->getName()] = 0;
        $this->manareduce[$player->getName()] = 0;
        $this->manaregencd[$player->getName()] = 0;
    }

    private function clearData(Player $player){
        unset($this->mana[$player->getName()]);
        unset($this->maxmana[$player->getName()]);
        unset($this->manastopregen[$player->getName()]);
        unset($this->manareduce[$player->getName()]);
        unset($this->manaregencd[$player->getName()]);
    }

    public function getMana(Player $player){
        return $this->mana[$player->getName()];
    }

    public function setMana(Player $player, int $mana){
        $this->mana[$player->getName()] = $mana;
    }

    public function addMana(Player $player, int $mana){
        $this->setMana($player, $this->getMana($player) + $mana);
    }

    public function reduceMana(Player $player, int $mana){
        $this->setMana($player, $this->getMana($player) - $mana);
    }

    public function getMaxMana(Player $player){
        return $this->maxmana[$player->getName()];
    }

    public function setMaxMana(Player $player, int $maxmana){
        $this->db->executeChange("mana.setmaxmana", ["playerid" => $player->getName(), "maxmana" => $maxmana]);
        $this->maxmana[$player->getName()] = $maxmana;
    }

    public function addMaxMana(Player $player, int $mana){
        $this->setMaxMana($player, $this->getMaxMana($player) + $mana);
    }

    public function reduceMaxMana(Player $player, int $mana){
        $this->setMaxMana($player, $this->getMaxMana($player) - $mana);
    }

    public function isRegenCooldown(Player $player){
        if(time() < $this->manaregencd[$player->getName()]){
            return true;
        } else {
            return false;
        }
    }

    public function isManaStopRegen(Player $player){
        if(time() < $this->manastopregen[$player->getName()]){
            return true;
        } else {
            return false;
        }
    }
}