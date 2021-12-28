<?php

namespace LemoniqPvP\PocKit\utils;

use dktapps\pmforms\ModalForm;
use LemoniqPvP\PocKit\Main;
use muqsit\invmenu\InvMenu;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;

class KitInventoryEditor {

    public static function openEditor(Player $player, string $kitId) {
        $config = Main::$instance->kits;
        $kits = $config->get("kits", null);
        $menu = InvMenu::create(InvMenu::TYPE_DOUBLE_CHEST);
        $kit = $kits[$kitId];

        $items = [];
        foreach ($kit["items"] as $item) {
            $items[]= Item::jsonDeserialize($item);
        }

        $menu->getInventory()->setContents($items);
        $menu->setName("Kit " . $kitId);

        $menu->setInventoryCloseListener(function(Player $player, Inventory $inventory) use ($kitId, $kit) {
            $form = new ModalForm("Kit " . $kitId, "Do you want to confirm your changes?", function(Player $player, bool $choice) use ($kitId, $kit, $inventory): void {
                if ($choice) {
                    $kit["items"] = array_map(function(Item $item) {
                        return $item->jsonSerialize();
                    }, $inventory->getContents());
                    ConfigUtils::updateKit($kitId, $kit);
                }
                PresetForms::kitEditSelection($player, $kitId);
            });
            $player->sendForm($form);
        });

        $menu->send($player);
    }

}