<?php

namespace SignEdit\lang;

class Language
{

	public static $instance;

	function __construct(){}

	function initData()
	{
		$this->jpn = parse_ini_file("jpn.ini");
		$this->eng = parse_ini_file("eng.ini");
		self::$instance = $this;
	}

	static function get($key, $lang)
	{
		$txt = self::$instance->$lang[$key];
		if (strpos($txt, "%n") != false) {
			$text = str_replace("%n", "\n", $txt);
		} else {
			$text = $txt;
		}
		return $text;
	}
}