<?php

namespace Crackle\Proxies {

	/**
	 * Represents an HTTP proxy server.
	 * @author George Brighton
	 */
	class HTTPProxy extends Proxy {

		/**
		 * Whether to tunnel through this proxy.
		 * Defaults to false.
		 * @var boolean
		 */
		private $isTunnel;

		/**
		 * Initialise a new HTTPProxy object.
		 */
		public function __construct() {
			$this->setTunnel(false);
		}

		/**
		 * Get whether to tunnel through this proxy.
		 * @return boolean		Whether to tunnel through this proxy.
		 */
		private final function isTunnel() {
			return $this->isTunnel;
		}

		/**
		 * Set whether to tunnel through this proxy. Defaults to false.
		 * @param boolean $isTunnel		Whether to tunnel through this proxy.
		 */
		public final function setTunnel($isTunnel) {
			$this->isTunnel = (bool)$isTunnel;
		}

		/**
		 * Configure a cURL session to use the proxy defined by this object.
		 * @param resource $handle		The cURL handle to modify.
		 * @see \Crackle\Proxies\Proxy::addTo()
		 */
		public function addTo($handle) {
			parent::addTo($handle);
			curl_setopt($handle, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
			curl_setopt($handle, CURLOPT_HTTPPROXYTUNNEL, $this->isTunnel());
		}
	}
}