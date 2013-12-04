<?php
/**
 * @author Jacob Morrison <jomorrison@gmail.com>
 * @copyright Copyright (c) 2013 Jacob Morrison
 * @package SubnationalDivisions
 * @version 1.0
 */
namespace jom;

defined('SUBNATIONAL_DIVISION_DATA_PATH') || define('SUBNATIONAL_DIVISION_DATA_PATH', dirname(dirname(__DIR__)) . DIRECTORY_SEPARATOR . 'data');

class SubnationalDivisions
{
	protected static $_dataPath;
	protected static $_config;

	public static $defaultCountryConfig = [
		'language' => 'en'
	];

	public static function getCountries()
	{
		$config = self::loadConfig();
		if ($config && isset($config['countries'])) {
			return $config['countries'];
		}
		return false;
	}

	public static function getCountry($country)
	{
		$countries = self::getCountries();
		if (isset($countries[$country])) {
			return array_merge(self::$defaultCountryConfig, $countries[$country]);
		}
		return false;
	}

    public static function get($country, $shortName = false, $flat = false)
    {
    	if (!self::getCountry($country)) {
    		return false;
    	}
    	$countryConfig = self::getCountry($country);
    	$countryTemplate = $countryConfig['template'];

    	if (is_array($countryTemplate)) {
    		$package = [];
    		foreach ($countryTemplate as $partLabel => $part) {
    			$partPieces = self::prepare(self::loadData($countryConfig, $part), $shortName);
    			if ($partPieces) {
    				if ($flat) {
    					$package = array_merge($package, $partPieces);
    				} else {
    					asort($partPieces);
    					$package[$partLabel] = $partPieces;
    				}
    			}
    		}
    		if ($flat) {
    			asort($package);
    		}
    	} else {
    		$package = self::prepare(self::loadData($countryConfig, $countryTemplate), $shortName);
    	}
        return $package;
    }

    public static function getAll($shortName = false, $flat = false)
    {
    	$package = [];
    	$countries = self::getCountries();
    	if (!$countries) { return $package; }

    	foreach ($countries as $id => $list) {
    		$package[$id] = self::get($id, $shortName, $flat);
    	}
    	return $package;
    }

    protected static function prepare($list, $shortName)
    {
    	if (!is_array($list)) {
    		return false;
    	}
    	if ($shortName) {
    		$short = [];
    		foreach ($list as $shortName => $longName) {
    			return $short[$shortName] = $shortName;
    		}
    	}
    	return $list;
    }

	public static function setDataPath($path)
	{
		self::$_dataPath = $path;
	}

	public static function getDataPath()
	{
		if (!isset(self::$_dataPath)) {
			return SUBNATIONAL_DIVISION_DATA_PATH;
		}
		return self::$_dataPath;
	}

	protected static function loadData($country, $part)
	{
		$dataPath = self::getDataPath() . DIRECTORY_SEPARATOR . $country['language'] . DIRECTORY_SEPARATOR . $part .'.php';
		if (!file_exists($dataPath)) {
			return false;
		}
		return include($dataPath);
	}

	protected static function loadConfig()
	{
		if (!isset(self::$_config)) {
			$configPath = self::getDataPath() . DIRECTORY_SEPARATOR . 'config.php';
			if (!file_exists($configPath)) {
				var_dump($configPath);exit;
				return false;
			}
			self::$_config = include($configPath);
		}
		return self::$_config;
	}
}
