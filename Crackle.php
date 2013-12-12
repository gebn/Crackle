<?php

namespace Crackle {

	/*
	 * This file is self-running - it just needs to be `include`d on the page before Crackle is used.
	 */
	new Crackle();

	/**
	 * Sets up the environment required by Crackle.
	 * @author George Brighton
	 */
	class Crackle {

		/**
		 * The version of Crackle in use.
		 * @var string
		 */
		const VERSION = '1.0';

		/**
		 * Carries out everything needed to get Crackle up and running.
		 */
		public function __construct() {
			$this->registerAutoloader();
		}

		/**
		 * Adds the autoloader function to the queue.
		 */
		private function registerAutoloader() {
			spl_autoload_register(function($qualified) {
				require substr($qualified, 8) . '.php';
			}, true);
		}
	}
}