<?php

namespace FresherGAMING\Mana\task;

use FresherGAMING\Mana\Mana;
use pocketmine\entity\Attribute;
use pocketmine\entity\effect\EffectInstance;
use pocketmine\entity\effect\VanillaEffects;
use pocketmine\scheduler\Task;

class ManaTask extends Task {

    public function __construct(private Mana $main){}

    public function onRun() : void {
        foreach($this->main->getServer()->getOnlinePlayers() as $players){
            if($this->main->getMaxMana($players) !== false && $this->main->getMana($players) !== false){
                $players->sendActionBarMessage("§bMana: " . $this->main->getMana($players) . "/" . $this->main->getMaxMana($players));
                if($this->main->getMana($players) < 1){
                    $players->setSprinting(false);
                    $players->getEffects()->add(new EffectInstance(VanillaEffects::BLINDNESS(), 30, 1, false));
                }
            }
        }
    }
}