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
            $mana = $this->main->getMana($players);
            $maxmana = $this->main->getMaxMana($players);
            $manaregen = $this->main->getManaRegen($players);
            if($mana !== null && $maxmana !== null){
                if($mana < $maxmana){
                    if(!$this->main->isManaStopRegen($players)){
                        if(!$this->main->isRegenCooldown($players)){
                            if($mana + $manaregen > $maxmana){
                                $this->main->addMana($players, $maxmana - $mana);
                                return;
                            }
                            $this->main->addMana($players, $manaregen);
                        }
                    }
                }
            }
        }
    }
}