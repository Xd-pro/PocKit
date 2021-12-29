<?php

namespace LemoniqPvP\PocKit\utils;

use LemoniqPvP\PocKit\Main;
use LemoniqPvP\PocKit\tasks\CooldownTask;
use pocketmine\player\Player;
use muqsit\invmenu\InvMenu;
use pocketmine\item\Item;
use pocketmine\utils\TextFormat;

class KitGiver {

    public static function giveKit(Player $player, string $kitId) {

        $kits = Main::$instance->kits->get("kits");
        
        if (!isset($kits[$kitId])) {
            foreach ($kits as $id => $kit) {
                if (in_array($kitId, $kit["aliases"]) || strtolower($kitId) === strtolower($id)) {
                    $kitId = $id;
                }
            }
        }

        if (!isset($kits[$kitId])) {
            $player->sendMessage("Kit not found.");
            return false;
        }

        $kit = $kits[$kitId];

        if (!$player->hasPermission("pockit.kit." . strtolower(str_replace(" ", "_",$kitId))) && $kit["private"]) {
            $player->sendMessage("You do not have permission to use this kit!");
            return false;
        }

        if (isset(CooldownTask::$cooldowns[$kitId][$player->getUniqueId()->toString()])) {
            $player->sendMessage("That kit is on cooldown! You'll be able to use it again in " . TextFormat::GREEN . self::secondsToTime(CooldownTask::$cooldowns[$kitId][$player->getUniqueId()->toString()]));
            return false;
        }

        if ($kit["clear"]) {
            $player->getInventory()->clearAll();
            $player->getArmorInventory()->clearAll();

            $items = [];
            foreach ($kit["items"] as $item) {
                $items[]= Item::jsonDeserialize($item);
            }

            $player->getInventory()->setContents($items);

        } else {
            $menu = InvMenu::create(InvMenu::TYPE_CHEST);

            $items = [];
            foreach ($kit["items"] as $item) {
                $items[]= Item::jsonDeserialize($item);
            }

            $menu->getInventory()->setContents($items);
            $menu->setName("Kit " . $kitId);

            $menu->send($player);
        }
        if (isset($kit["cooldown"])) {
            if (!isset(CooldownTask::$cooldowns[$kitId])) {
                CooldownTask::$cooldowns[$kitId] = [];
            }
            CooldownTask::$cooldowns[$kitId][$player->getUniqueId()->toString()] = $kit["cooldown"];
        }

    }

    private static function secondsToTime($seconds) {
        $dtF = new \DateTime('@0');
        $dtT = new \DateTime("@$seconds");
        return $dtF->diff($dtT)->format('%a days, %h hours, %i minutes and %s seconds');
    }

}