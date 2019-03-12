<?php

namespace SignEdit;

use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;

use SignEdit\utils\API;
use SignEdit\lang\Language;

class Main extends PluginBase
{

	/** @var API */
	private $api;
	/** @var Config */
	private $config;

	public function onEnable()
	{
		$this->loadConfig();
		$this->loadLanguage();
		$this->api = new API($this);
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
		$desc = $this->getDescription();
		$this->getLogger()->info("author : {$desc->getAuthors()[0]}");
		$this->getLogger()->info("contact : {$desc->getWebsite()}");
		$this->getLogger()->info("version : {$desc->getVersion()}");
		$this->getLogger()->info("language : ".Language::translate("language-name"));
	}

	public function loadConfig()
	{
		$this->saveDefaultConfig();
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

	public function getConfigData($key)
	{
		if ($key == null) return false;
		return $this->config->get($key);
	}

	public function getAPI()
	{
		return $this->api;
	}
}
