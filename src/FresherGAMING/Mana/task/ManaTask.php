<?php

namespace FresherGAMING\Mana\task;

use FresherGAMING\Mana\Mana;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\scheduler\Task;

class ManaTask extends Task {

    public function __construct(private Mana $main){}

    public function onRun() : void {
        foreach($this->main->getServer()->getOnlinePlayers() as $players){
            if($this->main->getMaxMana($players) !== null && $this->main->getMana($players) !== null){
                if($this->main->getConfig()->get("mana-stats-display") !== ""){
                    $statsdisplay = $this->main->getConfig()->get("mana-stats-display");
                    $players->sendActionBarMessage(str_replace(["{player_mana}", "{player_max_mana}", "{player_mana_regen"], [$this->main->getMana($players), $this->main->getMaxMana($players), $this->main->getManaRegen($players)], $statsdisplay));
                }
                if($this->main->getMana($players) < 1){
                    $players->setSprinting(false);
                    $players->getEffects()->add(new EffectInstance(VanillaEffects::BLINDNESS(), 40, 1, false));
                }
            }
        }
    }
}