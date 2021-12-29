<?php

declare(strict_types=1);

namespace LemoniqPvP\PocKit;

use LemoniqPvP\PocKit\commands\CreateKit;
use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use LemoniqPvP\PocKit\commands\KitEditor;
use muqsit\invmenu\InvMenuHandler;
use LemoniqPvP\PocKit\commands\Kit;
use LemoniqPvP\PocKit\tasks\CooldownTask;

class Main extends PluginBase{

    public Config $kits;

    public Config $cooldowns;

    public static self $instance;

    public function onEnable(): void {

        self::$instance = $this;
        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }
        $this->getCommand("kiteditor")->{"setExecutor"}(new KitEditor());
        $this->getCommand("kit")->{"setExecutor"}(new Kit());
        $this->getCommand("createkit")->{"setExecutor"}(new CreateKit());
        $this->cooldowns = new Config($this->getDataFolder() . "cooldowns.json", Config::JSON, []);
        $this->getScheduler()->scheduleRepeatingTask(new CooldownTask(), 20);

        if (!file_exists($this->getDataFolder() . "kits.json")) {
            $this->saveResource("kits.json");
        }

        $this->kits = new Config($this->getDataFolder() . "kits.json", Config::JSON);

        foreach($this->kits->get("kits") as $kitId => $kit) {
            if ($kit["private"]) {
                PermissionManager::getInstance()->addPermission(new Permission("pockit.kit." . strtolower(str_replace(" ", "_",$kitId)), "Allows access to kit " . $kitId));
            }
        }
    }

}
