<?php

namespace LemoniqPvP\PocKit\utils;

use Closure;
use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\CustomFormElement;
use dktapps\pmforms\element\Input;
use dktapps\pmforms\MenuForm;
use dktapps\pmforms\MenuOption;
use dktapps\pmforms\ModalForm;
use LemoniqPvP\PocKit\Main;
use LemoniqPvP\PocKit\tasks\CooldownTask;
use pocketmine\inventory\Inventory;
use pocketmine\permission\Permission;
use pocketmine\permission\PermissionManager;
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
        $options[]=new MenuOption("Manage aliases");
        $options[]=new MenuOption("Cooldown: " . TextFormat::BLUE . (string)$kit["cooldown"]);
        $options[]=new MenuOption(TextFormat::RED . TextFormat::BOLD . "Delete");

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
                    if ($kit["private"]) {
                        PermissionManager::getInstance()->addPermission(new Permission("pockit.kit." . strtolower(str_replace(" ", "_",$kitId)), "Allows access to kit " . $kitId));
                    }
                    ConfigUtils::updateKit($kitId, $kit);
                    self::kitEditSelection($player, $kitId);
                }

                if ($selectedOption === 2) {
                    $kit["clear"] = !$kit["clear"];
                    ConfigUtils::updateKit($kitId, $kit);
                    self::kitEditSelection($player, $kitId);
                }

                if ($selectedOption === 3) {
                    KitInventoryEditor::openEditor($player, $kitId);
                }

                if ($selectedOption === 4) {
                    self::listEditor($player, "alias", $kit["aliases"], $kitId);
                }

                if ($selectedOption === 5) {
                    self::prompt($player, "Edit cooldown", "Enter the new cooldown for the kit " . $kitId . " (seconds)...", function(Player $player, string $newCooldown) use ($kitId, $kit) {
                        if (is_numeric($newCooldown) && !str_contains($newCooldown, ".")) {
                            $kit["cooldown"] = (int)$newCooldown;
                            ConfigUtils::updateKit($kitId, $kit);
                            unset(CooldownTask::$cooldowns[$kitId]);
                        } else {
                            $player->sendMessage("Please enter a valid number");
                        }
                        
                    });
                }

                if ($selectedOption === 6) {
                    $form = new ModalForm("Kit " . $kitId, "Are you sure you want to delete the kit " . $kitId . "?" , function(Player $player, bool $choice) use ($kitId, $kit): void {
                        if ($choice) {
                            ConfigUtils::deleteKit($kitId);
                        }
                    });
                    $player ->sendForm($form);
                }
            }
            
        );

        $player->sendForm($form);
    }

    public static function listEditor(Player $player, string $name, array $array, string $kitId) {
        $listItems = implode(", ", $array);

        $options = [new MenuOption("Add"), new MenuOption("Remove"), new MenuOption("Edit")];

        $form = new MenuForm(
            "Edit ".$name."es",
            "There are currently " . count($array) . " ". $name ."es: \n" . $listItems,
            $options,
            function(Player $player, int $selectedOption) use ($name, &$array, $kitId): void  {
                if ($selectedOption === 0) {
                    self::prompt($player, "Create $name", "Enter the new $name", function(Player $player, string $newAlias) use (&$array, $name, $kitId): void {
                        $array[]= $newAlias;
                        $kits = Main::$instance->kits->get("kits");
                        $kit = $kits[$kitId];

                        $kit[$name . "es"] = $array;

                        ConfigUtils::updateKit($kitId, $kit);
                        self::kitEditSelection($player, $kitId);
                    });
                }
                if ($selectedOption === 1) {
                    
                    $options = [];
                    
                    foreach ($array as $item) {
                        $options[]= new MenuOption($item);
                    }

                    $form = new MenuForm("Remove $name", "Choose a $name to remove", $options, function(Player $player, int $selectedOption) use ($kitId, &$array, $name): void {
                        
                        $array = array_filter($array, function($item) use ($selectedOption, $array) {
                            if ($item === $array[$selectedOption]) {
                                return false;
                            } else {
                                return true;
                            }
                        });
                    
                        $kits = Main::$instance->kits->get("kits");
                        $kit = $kits[$kitId];

                        $kit[$name . "es"] = $array;

                        ConfigUtils::updateKit($kitId, $kit);
                        self::kitEditSelection($player, $kitId);
                    });

                    $player->sendForm($form);
                }
                if ($selectedOption === 2) {
                    
                    $options = [];
                    
                    foreach ($array as $item) {
                        $options[]= new MenuOption($item);
                    }

                    $form = new MenuForm("Edit $name", "Choose a $name to edit", $options, function(Player $player, int $selectedOption) use ($kitId,$array, $name): void  {
                        Main::$instance->getLogger()->info(json_encode($array));
                        foreach ($array as $index => $value) {
                            if ($index === $selectedOption) {
                                self::prompt($player, "Edit " . $array[$selectedOption], "Enter the new alias", function(Player $player, string $newValue) use ($kitId,&$array, $selectedOption, $name): void {
                                    $array = array_filter($array, function($item) use ($selectedOption, $array) {
                                        if ($item === $array[$selectedOption]) {
                                            return false;
                                        } else {
                                            return true;
                                        }
                                    });

                                    $array[] = $newValue;

                                    $kits = Main::$instance->kits->get("kits");
                                    $kit = $kits[$kitId];

                                    $kit[$name . "es"] = $array;

                                    ConfigUtils::updateKit($kitId, $kit);
                                    self::kitEditSelection($player, $kitId);
                                });
                            }
                        }
                    });

                    $player->sendForm($form);
                }
            }
        );

        $player->sendForm($form);
    }

}