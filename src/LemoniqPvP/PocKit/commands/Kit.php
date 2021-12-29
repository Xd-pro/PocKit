<?php

namespace LemoniqPvP\PocKit\commands;

use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use LemoniqPvP\PocKit\Main;
use LemoniqPvP\PocKit\utils\KitGiver;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class Kit implements CommandExecutor {

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($sender instanceof Player) {
            if (isset($args[0])) {
                KitGiver::giveKit($sender, $args[0]);
                return true;
            } else {

                $kits = Main::$instance->kits->get("kits");

                $options = [];
                $optionIds = [];

                foreach ($kits as $name => $kit) {

                    $canUseKit = true;

                    if ($kit["private"]) {
                        if (!$sender->hasPermission("pockit.kit." . strtolower(str_replace(" ", "_",$name)))) {
                            $canUseKit = false;
                        }
                    }

                    if ($canUseKit) {
                        $options[]=new MenuOption($name);
                        $optionIds[]=$name;
                    }
                }

                $form = new MenuForm("Kits", "Choose a kit", $options, function(Player $player, int $selectedOption) use ($kits, $optionIds): void {
                    $kitId = $optionIds[$selectedOption];

                    KitGiver::giveKit($player, $kitId);
                });

                $sender->sendForm($form);
            }
        } else {
            $sender->sendMessage("You can't use this command from console.");
        }
        return false;
    }

}