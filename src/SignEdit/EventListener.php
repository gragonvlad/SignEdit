<?php

namespace SignEdit;

use pocketmine\block\BlockIds;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\tile\Sign;

use SignEdit\utils\API;
use SignEdit\lang\Language;

class EventListener implements Listener
{

    /** @var int[] */
	public const BLOCK_SIGN = [
        BlockIds::STANDING_SIGN,// 63
        BlockIds::WALL_SIGN// 68
    ];

	/** @var Main */
	private $owner;
	/** @var API */
	private $api;

	public function __construct(Main $owner)
	{
		$this->owner = $owner;
		$this->api = $owner->getAPI();
	}


	public function onTap(PlayerInteractEvent $event)
	{
		$player = $event->getPlayer();
		$item = $event->getItem();
		$block = $event->getBlock();
		$id_meta = $item->getId().":".$item->getDamage();
		$key_item = $this->getAPI()->getKeyItem();
		if ($block->getId() === 0) return;
		if ($id_meta === $key_item) {
			if (in_array($block->getId(), self::BLOCK_SIGN)) {
				$tile = $player->getLevel()->getTile($block);
				if (!($tile instanceof Sign)) return;
				API::$signedit[$player->getName()]["object"] = $tile;
				if (!isset(API::$signedit[$player->getName()]["copydatas"])) {
					API::$signedit[$player->getName()]["copydatas"] = [];
				}
				$this->getAPI()->requestUI(API::FORM_TYPE_SELECT, $player);
			}
		}
		unset($this->isDealing[$player]);
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
					$this->getAPI()->requestUI(API::FORM_TYPE_EDIT, $player);
					return;
				}

				if ((int)$data == 1) {
					$this->getAPI()->requestUI(API::FORM_TYPE_COPY, $player);
					return;
				}

				if ((int)$data == 2) {
					if (empty(API::$signedit[$player->getName()]["copydatas"])) {
						$player->sendMessage("§c> ".Language::translate("message-paste-error"));
						return;
					}
					$this->getAPI()->requestUI(API::FORM_TYPE_PASTE, $player);
					return;
				}

				if ((int)$data == 3) {
					$this->getAPI()->requestUI(API::FORM_TYPE_INITIAL, $player);
					return;
				}

				if ((int)$data == 4) {
					if (empty(API::$signedit[$player->getName()]["copydatas"])) {
						$player->sendMessage("§c> ".Language::translate("message-paste-error"));
						return;
					}
					$this->getAPI()->requestUI(API::FORM_TYPE_DELPASTE, $player);
					return;
				}
				break;


			case API::FORM_TYPE_EDIT:
				if ($pk->formData == "null\n") {
					$this->getAPI()->requestUI(API::FORM_TYPE_SELECT, $player);
					return;
				}
				if (!is_array($data)) {
					return;
				}
				$sign = API::$signedit[$player->getName()]["object"];
				foreach ($data as $key => $text) {
					$sign->setLine($key, $text);
				}
				$sign->saveNBT();
				$player->sendMessage("§a> ".Language::translate("message-edit-completed"));
				break;


			case API::FORM_TYPE_COPY:
			case API::FORM_TYPE_COPY_ERROR:
				if ($pk->formData == "null\n") {
					$this->getAPI()->requestUI(API::FORM_TYPE_SELECT, $player);
					return;
				}
				if ($data[0] === null) return;
				$sign = API::$signedit[$player->getName()]["object"];
				$title = $data[0];
				if (isset(API::$signedit[$player->getName()]["copydatas"][$title])) {
					$this->getAPI()->requestUI(API::FORM_TYPE_COPY_ERROR, $player);
					return;
				}
				API::$signedit[$player->getName()]["copydatas"][$title] = $sign->getText();
				$player->sendMessage("§a> ".Language::translate("message-copy-completed"));
				break;


			case API::FORM_TYPE_PASTE:
				if ($pk->formData == "null\n") {
					$this->getAPI()->requestUI(API::FORM_TYPE_SELECT, $player);
					return;
				}
				if (!isset(API::$signedit[$player->getName()]["copydatas"])) return;
				$sign = API::$signedit[$player->getName()]["object"];
				$key = array_keys(API::$signedit[$player->getName()]["copydatas"])[$data];
				$texts = API::$signedit[$player->getName()]["copydatas"][$key];
				$sign->setText($texts[0], $texts[1], $texts[2], $texts[3]);
				$sign->saveNBT();
				$player->sendMessage("§a> ".Language::translate("message-paste-completed"));
				break;


			case API::FORM_TYPE_INITIAL:
				if ($pk->formData == "null\n") {
					$this->getAPI()->requestUI(API::FORM_TYPE_SELECT, $player);
					return;
				}
				if ($data) {
					$sign = API::$signedit[$player->getName()]["object"];
					$sign->setText("", "", "", "");
					$sign->saveNBT();
					$player->sendMessage("§a> ".Language::translate("message-clear-completed"));
				} else {
					$player->sendMessage("§b> ".Language::translate("message-clear-avoided"));
				}
				break;


			case API::FORM_TYPE_DELPASTE:
				if ($pk->formData == "null\n") {
					$this->getAPI()->requestUI(API::FORM_TYPE_SELECT, $player);
					return;
				}

				$key = array_keys(API::$signedit[$player->getName()]["copydatas"])[$data];
				unset(API::$signedit[$player->getName()]["copydatas"][$key]);
				$player->sendMessage("§a> ".Language::translate("message-copy-remove"));
				break;
		}
	}


	public function getOwner()
	{
		return $this->owner;
	}


	public function getAPI()
	{
		return $this->api;
	}
}