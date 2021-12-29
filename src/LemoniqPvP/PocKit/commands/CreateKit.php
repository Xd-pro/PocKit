<?php

namespace LemoniqPvP\PocKit\commands;

use Exception;
use LemoniqPvP\PocKit\utils\ConfigUtils;
use LemoniqPvP\PocKit\utils\PresetForms;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;

class CreateKit implements CommandExecutor {

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($sender instanceof Player) {
            PresetForms::prompt($sender, "Create kit", "Enter the name for this kit", function(Player $player, string $name) {
                try {
                    ConfigUtils::createKit($name);
                } catch (Exception $e) {
                    $player->sendMessage($e->getMessage());
                }
                PresetForms::kitEditSelection($player, $name);
            });
        } else {
            $sender->sendMessage("You can't use this command from console.");
        }
        return false;
    }

}