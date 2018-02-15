<?php

namespace SignEdit\utils;

use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use SignEdit\lang\Language;

class API
{

	const FORM_TYPE_SELECT = 42347;
	const FORM_TYPE_EDIT = 42348;
	const FORM_TYPE_COPY = 42349;
	const FORM_TYPE_PASTE = 42350;
	const FORM_TYPE_INITIAL = 42351;
	const FORM_TYPE_COPY_ERROR = 42352;
	const FORM_TYPE_DELPASTE = 42353;

	const FORM_IMAGE_EDIT = "https://i.imgur.com/QmA6UZR.png";
	const FORM_IMAGE_PASTE = "https://i.imgur.com/hA4v71w.png";
	const FORM_IMAGE_COPY = "https://i.imgur.com/vGXIZhS.png";
	const FORM_IMAGE_INITIAL = "https://i.imgur.com/4hBz3Ij.png";
	const FORM_IMAGE_DELPASTE = "https://i.imgur.com/n8W4leS.png";


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
					$player->sendMessage("§c> ".Language::get("message.paste.error", "jpn"));
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
		$data["title"] = "§l".Language::get("form.select.title", "jpn");
		$data["content"] = Language::get("form.select.content", "jpn");

		$replaceset["text"] = Language::get("form.select.button.edit", "jpn");
		$replaceset["image"]["type"] = "url";
		$replaceset["image"]["data"] = API::FORM_IMAGE_EDIT;
		$data["buttons"][] = $replaceset;

		$copy["text"] = Language::get("form.select.button.copy", "jpn");
		$copy["image"]["type"] = "url";
		$copy["image"]["data"] = API::FORM_IMAGE_COPY;
		$data["buttons"][] = $copy;

		$paste["text"] = Language::get("form.select.button.paste", "jpn");
		$paste["image"]["type"] = "url";
		$paste["image"]["data"] = API::FORM_IMAGE_PASTE;
		$data["buttons"][] = $paste;

		$clear["text"] = Language::get("form.select.button.clear", "jpn");
		$clear["image"]["type"] = "url";
		$clear["image"]["data"] = API::FORM_IMAGE_INITIAL;
		$data["buttons"][] = $clear;

		$rmPaste["text"] = Language::get("form.select.button.remove", "jpn");
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
		$data["title"] = "§l".Language::get("form.edit.title", "jpn");
		for ($i=0; $i<4; $i++) {
			$content[$i]["type"] = "input";
			$content[$i]["text"] = Language::get("form.edit.line".$i, "jpn")." ";
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
		$data["title"] = "§l".Language::get("form.copy.title", "jpn");
		$content["type"] = "input";
		$content["text"] = Language::get("form.copy.input.text", "jpn");
		$content["placeholder"] = Language::get("form.copy.input.placeholder", "jpn");
		$data["content"][] = $content;
		$json = $this->getEncodedJson($data);
		return $json;
	}


	public function getCopyErrorFormJson()
	{
		$data = [];
		$data["type"] = "custom_form";
		$data["title"] = "§l".Language::get("form.copy.title", "jpn");
		$content["type"] = "input";
		$content["text"] = Language::get("form.copy.input.text", "jpn");;
		$content["placeholder"] = Language::get("form.copy.input.placeholder", "jpn");
		$data["content"][] = $content;
		$content["type"] = "label";
		$content["text"] = "§c".Language::get("form.copy.label.text", "jpn");
		$data["content"][] = $content;

		$json = $this->getEncodedJson($data);
		return $json;
	}


	public function getPasteFormJson($player)
	{
		if (empty($player->signedit["copydatas"])) return null;
		$data = [];
		$data["type"] = "form";
		$data["title"] = "§l".Language::get("form.paste.title", "jpn");
		$data["content"] = Language::get("form.paste.content", "jpn");

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
		$data["title"] = "§l".Language::get("form.remove.title", "jpn");
		$data["content"] = Language::get("form.remove.content", "jpn");

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
		$data["title"] = "§l".Language::get("form.clear.title", "jpn");
		$data["content"] = Language::get("form.clear.content", "jpn");
		$data["button1"] = Language::get("form.clear.button1", "jpn");
		$data["button2"] = Language::get("form.clear.button2", "jpn");

		$json = $this->getEncodedJson($data);
		return $json;
	}


	public function getEncodedJson($data)
	{
		return json_encode($data, JSON_PRETTY_PRINT | JSON_BIGINT_AS_STRING | JSON_UNESCAPED_UNICODE);
	}
}
