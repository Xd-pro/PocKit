<?php

namespace LemoniqPvP\PocKit\utils;

use LemoniqPvP\PocKit\Main;

class ConfigUtils {

    public static function updateKit(string $name, array $new) {
        $config = Main::$instance->kits;
        $kits = Main::$instance->kits->get("kits");

        $kits[$name] = $new;

        $config->set("kits", $kits);
        $config->save();
        $config->reload();
    }

    public static function renameKit(string $old, string $new) {
        $config = Main::$instance->kits;
        $kits = Main::$instance->kits->get("kits");

        $kit = $kits[$old];

        unset($kits[$old]);

        $kits[$new] = $kit;

        $config->set("kits", $kits);
        $config->save();
        $config->reload();
    }

}