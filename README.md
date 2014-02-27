# Crackle

Crackle is a powerful yet easy to use object-oriented HTTP client for PHP.

## Features

 - GET, POST, HEAD, PUT, DELETE
 - Request authentication (basic, digest, NTLM)
 - Proxy support (optional basic and NTLM authentication)
 - Easy header management for both requests and responses
 - Send parameters with duplicate names and files in a single request
 - Simultaneous processing of multiple requests
 - Request callbacks
 - Many more...

## Dependencies

### Required

 - PHP >= 5.3.0
 - cURL extension

### Optional

 - Fileinfo extension

## Getting Started

For additional examples, see the contents of `/Examples`.

### A simple GET request

````php
require_once 'Crackle.php';
use \Crackle\Requests\GETRequest;

$request = new GETRequest('http://icanhazip.com');
$request->fire();
if($request->succeeded()) {
	echo $request->getResponse()->getContent();
}
````

### A POST request with callback

Crackle allows you to attach a callback to each request, which is executed immediately after that request completes:

````php
require_once 'Crackle.php';
use \Crackle\Requests\POSTRequest;
use \Crackle\Requests\Parts\Files\POSTFile;

$request = new POSTRequest('https://example.com');
$request->getHeaders()->set('custom-header', 'value');
$request->addField('name[]', 'value1');
$request->addField('name[]', 'value2'); // duplicate field names are supported
$request->addFile('upload', POSTFile::factory('photo.jpg'));
$request->addFile('another', POSTFile::factory('document.docx'));
$request->setCallback(function(POSTRequest $request) { // callback is passed the original request object
	if($request->succeeded()) {
		$request->getResponse()->writeTo('/home/gebn'); // write the response to a file
	}
});

$request->fire();
````

### A PUT request with NTLM authentication and proxy

All types of request can be authenticated and proxied:

````php
require_once 'Crackle.php';
use \Crackle\Requests\PUTRequest;
use \Crackle\Requests\Parts\Files\PUTFile;
use \Crackle\Proxies\SOCKS5Proxy;
use \Crackle\Authentication\Methods\BasicCredentials;
use \Crackle\Authentication\Methods\NTLMCredentials;

$request = new PUTRequest('https://example.com/file.txt');

// Basic, Digest and NTLM are supported
$request->setCredentials(new BasicCredentials('username', 'password'));

// can also use PUTFile::factory($path) to create a PUTFile object from a real file
$file = new PUTFile();
$file->setContent('virtual file content');
$file->setMimeType('text/plain'); // optional
$request->setFile($file);

$proxy = new SOCKS5Proxy('10.11.12.13'); // HTTP proxies are also supported
$proxy->setCredentials(new NTLMCredentials('username', 'password')); // Basic and NTLM supported
$request->setProxy($proxy);

$request->fire();
````

### Parallel requests

This sample will fire off requests to BBC News and Twitter, and announce when each request has finished:

````php
require_once 'Crackle.php';
use \Crackle\Requests\GETRequest;
use \Crackle\Requester;

$req1 = new GETRequest('http://www.bbc.co.uk/news/');
$req1->setCallback(function($request) {
	echo '$req1 finished', "\n";
});

$req2 = new GETRequest('https://twitter.com/#!/');
$req2->setCallback(function($request) {
	echo '$req2 finished', "\n";
});

$requester = new Requester();
$requester->queue($req1);
$requester->queue($req2);
$requester->fireAll();
````

N.B. All types of request can be done simultaneously - you can mix `GETRequest`, `POSTRequest` etc.
	
### Setting custom options using the handle

Crackle allows you to directly manipulate requests through their underlying cURL session. This handle can be retrieved by calling `getHandle()` on a `\Crackle\Requests\Request` object:

````php
$request = new GETRequest('https://example.com');
curl_setopt($request->getHandle(), CURLOPT_SSL_VERIFYPEER, false);
````

N.B. The following options are set by Crackle immediately before execution, so setting them manually will have no effect:

#### All requests

 - `CURLOPT_HEADER` (set headers on the `\Crackle\Headers` object returned by `getHeaders()`)
 - `CURLOPT_URL` (use `setUrl()`)
 - `CURLOPT_RETURNTRANSFER`

#### HEAD requests

 - `CURLOPT_NOBODY`

#### POST requests

 - `CURLOPT_POST`
 - `CURLOPT_POSTFIELDS` (this is constructed automatically from added fields and files)

#### PUT requests

 - `CURLOPT_PUT`
 - `CURLOPT_INFILE` (use `setFile()`)
 - `CURLOPT_INFILESIZE` (set automatically)

#### DELETE requests

 - `CURLOPT_CUSTOMREQUEST`

#### Authenticated requests

 - `CURLOPT_USERPWD`
 - `CURLOPT_HTTPAUTH`

## Development

[Click here](https://trello.com/b/91q94waP/crackle) to go to this project's public Trello board.

## Licence

Crackle is released under the MIT Licence. For more information about how this allows you to use the library, see the [Wikipedia article](http://en.wikipedia.org/wiki/MIT_License).