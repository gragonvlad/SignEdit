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
        $lang = new Language();
        $lang->initData();
        $this->loadConfig();
        $this->api = new API($this);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $this->getLogger()->info("§l§a────────────── §eSignEdit §a───────────────────");
        $this->getLogger()->info("  §2製作§r: OtorisanVardo");
        $this->getLogger()->info("   §2連絡§r: §bhttps://twitter.com/10ripon_obs ");
        $this->getLogger()->info("§l§a──────────────────────────────────────────────");
        $this->getLogger()->info("  §c二次配布は禁止とします");
        $this->getLogger()->info("  §c同梱のライセンスに従ってください");
        $this->getLogger()->info("  §6何かあればツイッターで連絡お願いします");
        $this->getLogger()->info("§l§a──────────────────────────────────────────────");
    }


    public function loadConfig()
    {
        $this->saveDefaultConfig();
        $this->reloadConfig();
        if(!file_exists($this->getDataFolder())) @mkdir($this->getDataFolder(), 0744, true);
        $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML);
        $this->lang = $this->config->get("language");
    }


    public function getAPI()
    {
        return $this->api;
    }


    public function getLanguage()
    {
        return $this->lang;
    }
}
