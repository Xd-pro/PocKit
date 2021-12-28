<?php

namespace LemoniqPvP\PocKit\commands;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use LemoniqPvP\PocKit\Main;
use LemoniqPvP\PocKit\utils\PresetForms;

class KitEditor implements CommandExecutor {

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($sender instanceof Player) {
            
            $config = Main::$instance->kits;

            $options = [];
            $optionNames = [];

            foreach ($config->get("kits") as $kitId => $kit) {
                $options[]= new MenuOption($kitId);
                $optionNames[]= $kitId;
            }

            $form = new MenuForm(
                "Kit Editor",
                "Select a kit to edit",
                $options,
                function(Player $player, int $selectedOption) use ($options, $optionNames, $config): void {
                    $kits = $config->get("kits");
                    
                    $kitId = $optionNames[$selectedOption];
                    $kit = $kits[$kitId];

                    PresetForms::kitEditSelection($player, $kitId);

                }
            );

            $sender->sendForm($form);

        } else {
            $sender->sendMessage("This command can't be used from console.");
            return false;
        }
        return true;
    }

}