<?php

namespace SignEdit;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use SignEdit\utils\API;
use SignEdit\lang\Language;
use SignEdit\EventListener;

class Main extends PluginBase
{

    public function onEnable()
    {
        $this->api = new API($this);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
        $lang = new Language();
        $lang->initData();
        $this->getLogger()->info("§l§a────────────── §eSignEdit §a───────────────────");
        $this->getLogger()->info("  §2製作§r: OtorisanVardo");
        $this->getLogger()->info("   §2連絡§r: §bhttps://twitter.com/10ripon_obs ");
        $this->getLogger()->info("§l§a──────────────────────────────────────────────");
        $this->getLogger()->info("  §c二次配布は禁止とします");
        $this->getLogger()->info("  §c同梱のライセンスに従ってください");
        $this->getLogger()->info("  §6何かあればツイッターで連絡お願いします");
        $this->getLogger()->info("§l§a──────────────────────────────────────────────");
    }


    public function getAPI()
    {
        return $this->api;
    }
}
