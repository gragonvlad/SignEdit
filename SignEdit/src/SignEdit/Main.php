<?php

namespace SignEdit;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;

use SignEdit\utils\API;
use SignEdit\lang\Language;
use SignEdit\EventListener;

class Main extends PluginBase
{

    public function onEnable()
    {
        $this->loadConfig();
        $this->loadLanguage();
        $this->api = new API($this);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getLogger()->info("§l§a────────────── §eSignEdit §a───────────────────");
        $this->getLogger()->info("  §2author§r: OtorisanVardo");
        $this->getLogger()->info("  §2contact§r: §bhttps://twitter.com/10ripon_obs ");
        $this->getLogger()->info("  §2language§r: ".Language::translate("language-name"));
        $this->getLogger()->info("§l§a───────────────────────────────────────────");
    }


    public function loadConfig()
    {
        $this->saveDefaultConfig();
        $this->reloadConfig();
        if(!file_exists($this->getDataFolder())) @mkdir($this->getDataFolder(), 0744, true);
        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML);
    }


    public function loadLanguage()
    {
        $languageCode = $this->config->get("language");
        $resources = $this->getResources();
        foreach ($resources as $resource) {
            if ($resource->getFilename() === "eng.json") {
                $default = json_decode(file_get_contents($resource->getPathname(), true), true);
            }
            if ($resource->getFilename() === $languageCode.".json") {
                $setting = json_decode(file_get_contents($resource->getPathname(), true), true);
            }
        }

        if (isset($setting)) {
            $langJson = $setting;
        } else {
            $langJson = $default;
        }
        new Language($this, $langJson);
    }


    public function getAPI()
    {
        return $this->api;
    }
}
