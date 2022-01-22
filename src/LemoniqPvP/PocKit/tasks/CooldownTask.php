<?php

namespace LemoniqPvP\PocKit\tasks;

use LemoniqPvP\PocKit\Main;
use pocketmine\scheduler\Task;
use Ramsey\Uuid\Uuid;

class CooldownTask extends Task {

    public static array $cooldowns = [];

    public function onRun(): void
    {
        if (self::$cooldowns === []) {
            self::$cooldowns = Main::$instance->cooldowns->getAll();
        }
        foreach (self::$cooldowns as $kitId => $players) {
            foreach ($players as $id => $value) { 
                if ($value <= 0) {
                    unset(self::$cooldowns[$kitId][$id]);
                    continue;
                } else {
                    self::$cooldowns[$kitId][$id] = self::$cooldowns[$kitId][$id] - 1;
                }
            }
        }
        Main::$instance->cooldowns->setAll(self::$cooldowns);
        Main::$instance->cooldowns->save();
    }

}
