<?php

namespace SignEdit\utils;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use SignEdit\lang\Language;

class API
{

	const FORM_TYPE_SELECT = 1947;
	const FORM_TYPE_EDIT = 1948;
	const FORM_TYPE_COPY = 1949;
	const FORM_TYPE_PASTE = 1950;
	const FORM_TYPE_INITIAL = 1951;
	const FORM_TYPE_COPY_ERROR = 1952;
	const FORM_TYPE_DELPASTE = 1953;

	const FORM_IMAGE_EDIT = "https://i.imgur.com/QmA6UZR.png";
	const FORM_IMAGE_PASTE = "https://i.imgur.com/hA4v71w.png";
	const FORM_IMAGE_COPY = "https://i.imgur.com/vGXIZhS.png";
	const FORM_IMAGE_INITIAL = "https://i.imgur.com/4hBz3Ij.png";
	const FORM_IMAGE_DELPASTE = "https://i.imgur.com/n8W4leS.png";


	public function __construct($owner)
	{
		$this->owner = $owner;
	}


	public function requestUI($formId, $player, $sign=null)
	{
		switch ($formId) {

			case API::FORM_TYPE_SELECT:
				$json = $this->getSelectFormJson();
				break;

			case API::FORM_TYPE_EDIT:
				$json = $this->getEditFormJson($player);
				break;

			case API::FORM_TYPE_COPY:
				$json = $this->getCopyFormJson();
				break;

			case API::FORM_TYPE_COPY_ERROR:
				$json = $this->getCopyErrorFormJson();
				break;

			case API::FORM_TYPE_PASTE:
				$json = $this->getPasteFormJson($player);
				if ($json == null) {
					$player->sendMessage("§c> ".Language::translate("message-paste-error"));
					return;
				}
				break;

			case API::FORM_TYPE_DELPASTE:
				$json = $this->getDelPasteFormJson($player);
				break;

			case API::FORM_TYPE_INITIAL:
				$json = $this->getInitialFormJson();
				break;
		}

		$pk = new ModalFormRequestPacket();
        $pk->formId = $formId;
        $pk->formData = $json;
        $player->dataPacket($pk);
	}


	public function getSelectFormJson()
	{
		$data = [];
		$data["type"] = "form";
		$data["title"] = "§l".Language::translate("form-select-title");
		$data["content"] = Language::translate("form-select-content");

		$replaceset["text"] = Language::translate("form-select-button-edit");
		$replaceset["image"]["type"] = "url";
		$replaceset["image"]["data"] = API::FORM_IMAGE_EDIT;
		$data["buttons"][] = $replaceset;

		$copy["text"] = Language::translate("form-select-button-copy");
		$copy["image"]["type"] = "url";
		$copy["image"]["data"] = API::FORM_IMAGE_COPY;
		$data["buttons"][] = $copy;

		$paste["text"] = Language::translate("form-select-button-paste");
		$paste["image"]["type"] = "url";
		$paste["image"]["data"] = API::FORM_IMAGE_PASTE;
		$data["buttons"][] = $paste;

		$clear["text"] = Language::translate("form-select-button-clear");
		$clear["image"]["type"] = "url";
		$clear["image"]["data"] = API::FORM_IMAGE_INITIAL;
		$data["buttons"][] = $clear;

		$rmPaste["text"] = Language::translate("form-select-button-remove");
		$rmPaste["image"]["type"] = "url";
		$rmPaste["image"]["data"] = API::FORM_IMAGE_DELPASTE;
		$data["buttons"][] = $rmPaste;

		$json = $this->getEncodedJson($data);
		return $json;
	}


	public function getEditFormJson($player)
	{
		$sign = $player->signedit["object"];
		$data = [];
		$data["type"] = "custom_form";
		$data["title"] = "§l".Language::translate("form-edit-title");
		for ($i=0; $i<4; $i++) {
			$content[$i]["type"] = "input";
			$content[$i]["text"] = Language::translate("form-edit-line".$i)." ";
			$content[$i]["default"] = $sign->getLine($i);
		}
		$data["content"] = $content;

		$json = $this->getEncodedJson($data);
		return $json;
	}


	public function getCopyFormJson()
	{
		$data = [];
		$data["type"] = "custom_form";
		$data["title"] = "§l".Language::translate("form-copy-title");
		$content["type"] = "input";
		$content["text"] = Language::translate("form-copy-input-text");
		$content["placeholder"] = Language::translate("form-copy-input-placeholder");
		$data["content"][] = $content;
		$json = $this->getEncodedJson($data);
		return $json;
	}


	public function getCopyErrorFormJson()
	{
		$data = [];
		$data["type"] = "custom_form";
		$data["title"] = "§l".Language::translate("form-copy-title");
		$content["type"] = "input";
		$content["text"] = Language::translate("form-copy-input-text");;
		$content["placeholder"] = Language::translate("form-copy-input-placeholder");
		$data["content"][] = $content;
		$content["type"] = "label";
		$content["text"] = "§c".Language::translate("form-copy-label-text");
		$data["content"][] = $content;

		$json = $this->getEncodedJson($data);
		return $json;
	}


	public function getPasteFormJson($player)
	{
		if (empty($player->signedit["copydatas"])) return null;
		$data = [];
		$data["type"] = "form";
		$data["title"] = "§l".Language::translate("form-paste-title");
		$data["content"] = Language::translate("form-paste-content");

		foreach ($player->signedit["copydatas"] as $keyword => $copyed) {
			$panels["text"] = $keyword;
			$panels["image"]["type"] = "url";
			$panels["image"]["data"] = "";
			$data["buttons"][] = $panels;
		}

		$json = $this->getEncodedJson($data);
		return $json;
	}


	public function getDelPasteFormJson($player)
	{
		if (!isset($player->signedit["copydatas"])) return null;
		$data = [];
		$data["type"] = "form";
		$data["title"] = "§l".Language::translate("form-remove-title");
		$data["content"] = Language::translate("form-remove-content");

		foreach ($player->signedit["copydatas"] as $keyword => $copyed) {
			$panels["text"] = $keyword;
			$panels["image"]["type"] = "url";
			$panels["image"]["data"] = "";
			$data["buttons"][] = $panels;
		}

		$json = $this->getEncodedJson($data);
		return $json;
	}


	public function getInitialFormJson()
	{
		$data = [];
		$data["type"] = "modal";
		$data["title"] = "§l".Language::translate("form-clear-title");
		$data["content"] = Language::translate("form-clear-content");
		$data["button1"] = Language::translate("form-clear-button1");
		$data["button2"] = Language::translate("form-clear-button2");

		$json = $this->getEncodedJson($data);
		return $json;
	}


	public function getEncodedJson($data)
	{
		return json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
	}
}
