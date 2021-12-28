<?php

declare(strict_types=1);

namespace LemoniqPvP\PocKit;

use pocketmine\item\Item;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\NetworkSession;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use LemoniqPvP\PocKit\commands\KitEditor;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\InvMenuEventHandler;
use muqsit\invmenu\InvMenuHandler;
use muqsit\invmenu\session\PlayerManager;

class Main extends PluginBase{

    public Config $kits;

    public static self $instance;

    public function onEnable(): void {

        self::$instance = $this;
        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }
        PermissionManager::getInstance()->addPermission(new Permission("kits.kit."));
        $this->getCommand("kiteditor")->{"setExecutor"}(new KitEditor());

        if (!file_exists($this->getDataFolder() . "kits.json")) {
            $this->saveResource("kits.json");
        }

        $this->kits = new Config($this->getDataFolder() . "kits.json", Config::JSON);
    }

}
