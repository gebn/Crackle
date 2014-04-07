<?php

/**
 * Crackle is a powerful yet easy to use object-oriented HTTP client.
 * @author George Brighton
 * @link https://github.com/gebn/Crackle
 */
namespace Crackle {

	use \Exception;

	/*
	 * This file is self-running - it just needs to be `include`d on the page before Crackle is used.
	 */
	Crackle::load();

	/**
	 * Sets up the environment required by Crackle.
	 * @author George Brighton
	 */
	final class Crackle {

		/**
		 * The version of Crackle in use.
		 * @var string
		 */
		const VERSION = '1.3';

		/**
		 * This class should not be instantiated.
		 */
		private function __construct() {}

		/**
		 * Readies Crackle for use.
		 */
		public static function load() {
			static $loaded = false;

			// we only want to initialise once
			if(!$loaded) {
				self::initialise();
				$loaded = true;
			}
		}

		/**
		 * Carries out everything needed to get Crackle up and running.
		 */
		private static function initialise() {
			self::checkRequirements();
			self::registerAutoloader();
		}

		/**
		 * Ensures this installation of PHP meets Crackle's minimum requirements.
		 * @throws \Exception If any requirement is not met.
		 */
		private static function checkRequirements() {
			if(version_compare(PHP_VERSION, '5.3.0', '<')) {
				throw new Exception('Crackle requires PHP 5.3.0 or later.');
			}

			if(!extension_loaded('curl')) {
				throw new Exception('The cURL module must be available for Crackle to operate.');
			}
		}

		/**
		 * Adds the autoloader function to the queue.
		 */
		private static function registerAutoloader() {
			spl_autoload_register(function($qualified) {
				if(substr($qualified, 0, 8) == 'Crackle\\') {
					require str_replace('\\', DIRECTORY_SEPARATOR, substr($qualified, 8)) . '.php';
				}
			}, true);
		}
	}
}