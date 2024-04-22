<?php

namespace FresherGAMING\Mana;

use FresherGAMING\Mana\commands\ManaCmd;
use FresherGAMING\Mana\commands\ManageManaCmd;
use FresherGAMING\Mana\commands\ManageManaRegenCmd;
use FresherGAMING\Mana\commands\ManageMaxManaCmd;
use FresherGAMING\Mana\task\ManaRegenTask;
use FresherGAMING\Mana\task\ManaTask;
use pocketmine\event\player\PlayerJumpEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\Listener;
use pocketmine\player\Player;
use poggit\libasynql\DataConnector;
use poggit\libasynql\libasynql;

class Mana extends PluginBase implements Listener {

    private array $mana = [];
    private array $maxmana = [];
    private array $manaregen = [];
    private array $manastopregen = [];
    private array $manareduce = [];
    private array $manaregencd = [];
    private DataConnector $db;

    public function onEnable() : void{
        $this->saveResource("config.yml");
        $this->getScheduler()->scheduleRepeatingTask(new ManaTask($this), 5);
        $this->getScheduler()->scheduleRepeatingTask(new ManaRegenTask($this), 20);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->db = libasynql::create($this, $this->getConfig()->get("database"), [
            "sqlite" => "mana.sql",
            "mysql"  => "mana.sql"
        ]);
        $this->db->executeGeneric("mana.setup");
        $this->getServer()->getCommandMap()->register("Mana", new ManaCmd($this));
        $this->getServer()->getCommandMap()->register("Mana", new ManageManaCmd($this));
        $this->getServer()->getCommandMap()->register("Mana", new ManageManaRegenCmd($this));
        $this->getServer()->getCommandMap()->register("Mana", new ManageMaxManaCmd($this));
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
        $this->manastopregen[$player->getName()] = time() + 1.5;
        if($this->getMana($player) < 1){
            return;
        }
        if($player->isSprinting()){
            if(time() > $this->manareduce[$player->getName()]){
                $manareduce = $this->getConfig()->get("sprint-mana");
                $mana = $this->getMana($player);
                $this->manareduce[$player->getName()] = time() + 1;
                if($mana - $manareduce < 0){
                    $this->reduceMana($player, $mana);
                    return;
                }
                $this->reduceMana($player, $manareduce);
            }
        }
    }

    public function onJump(PlayerJumpEvent $event){
        $player = $event->getPlayer();
        $this->manastopregen[$player->getName()] = time() + 1.5;
        if($this->getMana($player) < 1){
            return;
        }
        $manareduce = $this->getConfig()->get("jump-mana");
        $mana = $this->getMana($player);
        if($mana - $manareduce < 0){
            $this->reduceMana($player, $mana);
            return;
        }
        $this->reduceMana($player, $manareduce);
    }

    private function setData(Player $player){
        $this->db->executeSelect("mana.getdata", ["playerid" => $player->getName()], function(array $rows)use($player){
            if(count($rows) < 1){
                $this->db->executeInsert("mana.newdata", ["playerid" => $player->getName(), "maxmana" => $this->getConfig()->get("default-mana"), "manaregen" => $this->getConfig()->get("default-mana-regen")]);
                $this->maxmana[$player->getName()] = $this->getConfig()->get("default-mana");
                $this->manaregen[$player->getName()] = $this->getConfig()->get("default-mana-regen");
            } else {
                foreach($rows as $data){
                    $this->maxmana[$player->getName()] = $data["maxmana"];
                    $this->manaregen[$player->getName()] = $data["manaregen"];
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
        unset($this->manaregen[$player->getName()]);
        unset($this->manastopregen[$player->getName()]);
        unset($this->manareduce[$player->getName()]);
        unset($this->manaregencd[$player->getName()]);
    }

    public function getMana(string|Player $player){
        $playername = $player;
        if($player instanceof Player){
            $playername = $player->getName();
        }
        if(isset($this->mana[$playername])){
            return $this->mana[$playername];
        } else {
            return null;
        }
    }

    public function setMana(string|Player $player, float $mana){
        if($player instanceof Player){
            $this->mana[$player->getName()] = $mana;
        } else {
            $this->mana[$player] = $mana;
        }
    }

    public function addMana(string|Player $player, float $mana){
        $this->setMana($player, $this->getMana($player) + $mana);
    }

    public function reduceMana(string|Player $player, float $mana){
        $this->setMana($player, $this->getMana($player) - $mana);
    }

    public function getMaxMana(string|Player $player){
        $playername = $player;
        if($player instanceof Player){
            $playername = $player->getName();
        }
        if(isset($this->maxmana[$playername])){
            return $this->maxmana[$playername];
        } else {
            return null;
        }
    }

    public function setMaxMana(string|Player $player, float $maxmana){
        if($player instanceof Player){
            $this->db->executeChange("mana.setmaxmana", ["playerid" => $player->getName(), "maxmana" => $maxmana]);
            $this->maxmana[$player->getName()] = $maxmana;
        } else {
            $this->db->executeChange("mana.setmaxmana", ["playerid" => $player, "maxmana" => $maxmana]);
            $this->maxmana[$player] = $maxmana;
        }
    }

    public function addMaxMana(string|Player $player, float $mana){
        $this->setMaxMana($player, $this->getMaxMana($player) + $mana);
    }

    public function reduceMaxMana(string|Player $player, float $mana){
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

    public function getManaRegen(string|Player $player){
        $playername = $player;
        if($player instanceof Player){
            $playername = $player->getName();
        }
        if(isset($this->manaregen[$playername])){
            return $this->manaregen[$playername];
        } else {
            return null;
        }
    }

    public function setManaRegen(string|Player $player, float $manaregen){
        if($player instanceof Player){
            $this->db->executeChange("mana.setmanaregen", ["playerid" => $player->getName(), "manaregen" => $manaregen]);
            $this->manaregen[$player->getName()] = $manaregen;
        } else {
            $this->db->executeChange("mana.setmanaregen", ["playerid" => $player, "manaregen" => $manaregen]);
            $this->manaregen[$player] = $manaregen;
        }
    }

    public function addManaRegen(string|Player $player, float $manaregen){
        $this->setManaRegen($player, $this->getManaRegen($player) + $manaregen);
    }

    public function reduceManaRegen(string|Player $player, float $manaregen){
        $this->setManaRegen($player, $this->getManaRegen($player) - $manaregen);
    }

    public function retrieveDataFromDatabase(string|Player $player, \Closure $closure){
        if($player instanceof Player){
            $this->db->executeSelect("mana.getdata", ["playerid" => $player->getName()], function($result)use($closure){
                $closure($result);
            });
        } else {
            $this->db->executeSelect("mana.getdata", ["playerid" => $player], function($result)use($closure){
                $closure($result);
            });
        }
    }
}