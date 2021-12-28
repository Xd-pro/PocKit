<?php

namespace LemoniqPvP\PocKit\utils;

use Closure;
use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\CustomFormElement;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use LemoniqPvP\PocKit\Main;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class PresetForms {

    public static function prompt(Player $player, string $title, string $content, Closure $after) {

        $form = new CustomForm(
            $title,
            [new Input("prompt", $content)],
            function(Player $player, CustomFormResponse $data) use ($after): void {
                $after($player, $data->getString("prompt"));
            }
        );

        $player->sendForm($form);

    }

    public static function kitEditSelection(Player $player, string $kitId) {

        $kit = Main::$instance->kits->get("kits")[$kitId];
        
        $options = [new MenuOption("Rename") ];

        if ($kit["private"] === true) {
            $ifPrivatePermDisplay = "This kit can only be used by players with the permission pockit.kit." . strtolower(str_replace(" ", "_",$kitId));
            $options[]=new MenuOption("Private: ". TextFormat::GREEN . "true");
        } else {
            $ifPrivatePermDisplay = "";
            $options[]=new MenuOption("Private: ". TextFormat::RED . "false");
        }

        if ($kit["clear"] === true) {
            $options[]=new MenuOption("Clear inventory: ". TextFormat::GREEN . "true");
        } else {
            $options[]=new MenuOption("Clear inventory: ". TextFormat::RED . "false");
        }

        $options[]=new MenuOption("Change items");

        $form = new MenuForm(
            "Editing " . $kitId,
            $ifPrivatePermDisplay,
            $options,
            function(Player $player, int $selectedOption) use ($kit, $kitId): void {
                
                if ($selectedOption === 0) {
                    self::prompt($player, "Rename kit", "Enter the new name for the kit " . $kitId . "...", function(Player $player, string $newName) use ($kitId) {
                        ConfigUtils::renameKit($kitId, $newName);
                        self::kitEditSelection($player, $newName);
                    });
                }

                if ($selectedOption === 1) {
                    $kit["private"] = !$kit["private"];
                    ConfigUtils::updateKit($kitId, $kit);
                    self::kitEditSelection($player, $kitId);
                }

                if ($selectedOption === 2) {
                    $kit["clear"] = !$kit["clear"];
                    ConfigUtils::updateKit($kitId, $kit);
                    self::kitEditSelection($player, $kitId);
                }

                
            }
            
        );

        $player->sendForm($form);
    }

}