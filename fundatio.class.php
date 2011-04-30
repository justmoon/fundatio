<?php
/**
 * Fundatio - Simple PHP website base system.
 *
 * This is a simple utility class for creating plain PHP websites. It provides
 * configuration management and a number of useful functions. Unlike many frameworks,
 * it stays out of your way. You can include it and not even know it's there.
 *
 * I wrote this mostly for myself for use in minor projects when I want to whip up
 * a site quickly. But in true Torvaldsian manner I've backed it up by making it
 * open source.
 */
class Fnd extends ArrayObject
{
	/**
	 * Initialize Fundatio.
	 *
	 * This function is called when Fundatio is included, it includes the minimum
	 * setup necessary.
	 */
	public static function init()
	{
		global $fundatio;

		if (defined('FUNDATIO')) return;

		// Create the singleton (stores configuration)
		$fundatio = new self;

		// This can be used to prevent templates etc. from being opened directly
		// e.g.: defined('FUNDATIO') or die('Access denied.');
		define('FUNDATIO', true);

		// Load configuration defaults and configuration file (if present)
		$fundatio->initConfig();

		// Add base directory to the include path
		$include_path = realpath(dirname(__FILE__).'/../') . '/';
		set_include_path($fundatio['base_dir'] . PATH_SEPARATOR .
		                 get_include_path());
	}

	/**
	 * Initializes configuration.
	 *
	 * This function will try to guess/calculate some of the configuration that
	 * we may need and load the configuration file if there is one.
	 */
	public function initConfig($filename = null)
	{
		// Normally debug mode should be turned off
		$this['debug'] = false;

		// The config file is one level above Fundatio by convention.
		$this['config_dir'] = realpath(dirname(__FILE__).'/../').'/';

		// The root directory for the project is two levels above Fundatio
		// by convention.
		$this['base_dir'] = realpath(dirname(__FILE__).'/../../').'/';

		// This is the basic root url of the site
		$this['domain'] = $this->detectDomain(); // domain/ip of the server
		$this['local_part'] = $this->detectLocalPart(); // local part of the url
		$this['base_url'] = 'http://'.$this['domain'].$this['local_part'];

		// Find/load configuration file if it exists
		@include_once($_ENV['config_dir'].'config.inc.php');
	}

	/**
	 * Detects the hostname the site is currently being accessed on.
	 */
	public function detectDomain()
	{
		$domain = @$_SERVER['SERVER_NAME'];

		if ($domain == 'localhost') {
			$domain = '127.0.0.1';
		}

		return $domain;
	}

	/**
	 * Detects the local part of the URL using the document root setting.
	 */
	public function detectLocalPart()
	{
		$lp = '/'.substr($this['base_dir'], strlen(realpath($_SERVER['DOCUMENT_ROOT']))+1);

		return ($lp == '//') ? '/' : $lp;
	}

	/**
	 * Utility function for creating relative URLs.
	 *
	 * A common problem is that we want to use safe URLs that work independently of
	 * the location of the script, the installation path of the website etc. This
	 * function generates such URLs based on Fundatio's configuration.
	 */
	public static function url($relPart = '')
	{
		global $fundatio;

		return $fundatio['base_url'].$relPart;
	}

	/**
	 * Includes a template from the include path.
	 */
	public static function template($file, $props = array())
	{
		global $fundatio;

		extract($props);
		include($file);
	}

	/**
	 * Starts capturing content.
	 */
	public static function startCapture()
	{
		ob_start();
	}

	public static function endCapture()
	{
		$buffer = ob_get_contents();
		ob_end_clean();
		return $buffer;
	}
}

Fnd::init();