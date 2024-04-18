<?php

namespace FresherGAMING\Mana\task;

use FresherGAMING\Mana\Mana;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\scheduler\Task;

class ManaRegenTask extends Task {

    public function __construct(private Mana $main){}

    public function onRun() : void {
        foreach($this->main->getServer()->getOnlinePlayers() as $players){
            if($this->main->getMaxMana($players) !== null && $this->main->getMana($players) !== null){
                if($this->main->getMana($players) < $this->main->getMaxMana($players)){
                    if(!$this->main->isManaStopRegen($players)){
                        if(!$this->main->isRegenCooldown($players)){
                            $this->main->addMana($players, $this->main->getManaRegen($players));
                        }
                    }
                }
            }
        }
    }
}