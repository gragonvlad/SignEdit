<?php

namespace SignEdit;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\item\Item;
use pocketmine\block\SignPost;
use pocketmine\block\WallSign;
use pocketmine\tile\Sign;
use pocketmine\item\Feather;

use SignEdit\utils\API;
use SignEdit\lang\Language;

class EventListener implements Listener
{

	public function __construct($owner)
	{
		$this->owner = $owner;
		$this->api = $this->owner->getAPI();
		$this->lang = $this->owner->getLanguage();
	}


	public function onTap(PlayerInteractEvent $event)
	{
		$player = $event->getPlayer();
		$item = $event->getItem();
		$block = $event->getBlock();
		if ($item instanceof Feather) {
			if ($block instanceof SignPost || $block instanceof WallSign) {
				$tile = $player->getLevel()->getTile($block);
				if (!($tile instanceof Sign)) return;
				$player->signedit["object"] = $tile;
				if (!isset($player->signedit["copydatas"])) {
					$player->signedit["copydatas"] = [];
				}
				$this->getApi()->requestUI(API::FORM_TYPE_SELECT, $player);
			}
		}
	}


	public function onReceive(DataPacketReceiveEvent $event)
	{
		$pk = $event->getPacket();
		if (!($pk instanceof ModalFormResponsePacket)) return;
		$player = $event->getPlayer();
		$id = $pk->formId;
		$data = json_decode($pk->formData);
		switch ($id) {

			case API::FORM_TYPE_SELECT:

				if ($pk->formData == "null\n") return;

				if ((int)$data == 0) {
					$this->getApi()->requestUI(API::FORM_TYPE_EDIT, $player);
					return;
				}

				if ((int)$data == 1) {
					$this->getApi()->requestUI(API::FORM_TYPE_COPY, $player);
					return;
				}

				if ((int)$data == 2) {
					if (empty($player->signedit["copydatas"])) {
						$player->sendMessage("§c> ".Language::get("message.paste.error", $this->lang));
						return;
					}
					$this->getApi()->requestUI(API::FORM_TYPE_PASTE, $player);
					return;
				}

				if ((int)$data == 3) {
					$this->getApi()->requestUI(API::FORM_TYPE_INITIAL, $player);
					return;
				}

				if ((int)$data == 4) {
					if (empty($player->signedit["copydatas"])) {
						$player->sendMessage("§c> ".Language::get("message.paste.error", $this->lang));
						return;
					}
					$this->getApi()->requestUI(API::FORM_TYPE_DELPASTE, $player);
					return;
				}
				break;


			case API::FORM_TYPE_EDIT:
				if ($pk->formData == "null\n") {
					$this->getApi()->requestUI(API::FORM_TYPE_SELECT, $player);
					return;
				}
				if (!is_array($data)) {
					return;
				}
				$sign = $player->signedit["object"];
				foreach ($data as $key => $text) {
					$sign->setLine($key, $text);
				}
				$sign->saveNBT();
				$player->sendMessage("§a> ".Language::get("message.edit.completed", $this->lang));
				break;


			case API::FORM_TYPE_COPY:
			case API::FORM_TYPE_COPY_ERROR:
				if ($pk->formData == "null\n") {
					$this->getApi()->requestUI(API::FORM_TYPE_SELECT, $player);
					return;
				}
				if ($data[0] === null) return;
				$sign = $player->signedit["object"];
				$title = $data[0];
				if (isset($player->signedit["copydatas"][$title])) {
					$this->getApi()->requestUI(API::FORM_TYPE_COPY_ERROR, $player);
					return;
				}
				$player->signedit["copydatas"][$title] = $sign->getText();
				$player->sendMessage("§a> ".Language::get("message.copy.completed", $this->lang));
				break;


			case API::FORM_TYPE_PASTE:
				if ($pk->formData == "null\n") {
					$this->getApi()->requestUI(API::FORM_TYPE_SELECT, $player);
					return;
				}
				if (!isset($player->signedit["copydatas"])) return;
				$sign = $player->signedit["object"];
				//$texts = array_slice($player->signedit["copydatas"], $data, 1);
				$key = array_keys($player->signedit["copydatas"])[$data];
				$texts = $player->signedit["copydatas"][$key];
				$sign->setText($texts[0], $texts[1], $texts[2], $texts[3]);
				$sign->saveNBT();
				$player->sendMessage("§a> ".Language::get("message.paste.completed", $this->lang));
				break;


			case API::FORM_TYPE_INITIAL:
				if ($pk->formData == "null\n") {
					$this->getApi()->requestUI(API::FORM_TYPE_SELECT, $player);
					return;
				}
				if ($data) {
					$sign = $player->signedit["object"];
					$sign->setText("", "", "", "");
					$sign->saveNBT();
					$player->sendMessage("§a> ".Language::get("message.clear.completed", $this->lang));
				} else {
					$player->sendMessage("§b> ".Language::get("message.clear.avoided", $this->lang));
				}
				break;


			case API::FORM_TYPE_DELPASTE:
				if ($pk->formData == "null\n") {
					$this->getApi()->requestUI(API::FORM_TYPE_SELECT, $player);
					return;
				}

				$key = array_keys($player->signedit["copydatas"])[$data];
				unset($player->signedit["copydatas"][$key]);
				$player->sendMessage("§a> ".Language::get("message.copy.remove", $this->lang));
				break;
		}
	}


	public function getServer()
	{
		return $this->owner->getServer();
	}


	public function getOwner()
	{
		return $this->owner;
	}


	public function getApi()
	{
		return $this->api;
	}
}