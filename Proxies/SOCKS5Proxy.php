<?php

namespace Crackle\Proxies {

	/**
	 * Represents a SOCKS v5 proxy.
	 * @author George Brighton
	 */
	class SOCKS5Proxy extends Proxy {

		/**
		 * Configure a cURL session to use the proxy defined by this object.
		 * @param resource $handle		The cURL handle to modify.
		 * @see \Crackle\Proxies\Proxy::addTo()
		 */
		public function addTo($handle) {
			curl_setopt($handle, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
		}
	}
}