# Crackle

Crackle is an easy to use, object-oriented HTTP client.

## Features

 - GET, POST
 - Authentication (basic, digest, NTLM)
 - File uploading
 - Request callbacks
 - Easy header management for both requests and responses.
 - Send parameters with duplicate names and files in a single request.
 - Simultaneous processing of multiple requests
 - Many more...

## Requirements

 - PHP >= 5.3.0
 - cURL extension

## Getting Started

For additional examples, see the contents of `/Examples`.

### A simple GET request

````php
use \Crackle\Requests\GETRequest;

$request = new GETRequest('http://icanhazip.com');
$request->fire();
if(!$request->isError()) {
	echo $request->getResponse()->getContent();
}
````

### A POST request with callback

Crackle allows you to attach a callback to each request, which is executed immediately after that request completes:

````php
use \Crackle\Requests\POSTRequest;

$request = new POSTRequest('https://example.com');
$request->getHeaders()->set('custom-header', 'value');
$request->addField('name[]', 'value1');
$request->addField('name[]', 'value2'); // duplicate field names are supported
$request->addFile('upload', 'photo.jpg');
$request->addFile('another', 'document.docx');
$request->setCallback(function(POSTRequest $request) { // callback is passed the original request object
	if(!$request->isError()) {
		$request->getResponse()->writeTo('/home/gebn'); // write the response to a file
	}
});

$request->fire();
````

### Parallel requests

This sample will fire off requests to BBC News and Twitter, and announce when each request has finished:

````php
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

N.B. All types of requests can be done simultaneously - you can mix verbs.
	
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

#### POST requests

 - `CURLOPT_POST`
 - `CURLOPT_POSTFIELDS` (this is constructed automatically from added fields and files)

#### Authenticated requests

 - `CURLOPT_USERPWD`
 - `CURLOPT_HTTPAUTH`

## Development

[Click here](https://trello.com/b/91q94waP/crackle) to go to this project's public Trello board.

## Licence

Crackle is released under the MIT Licence. For more information, see the [Wikipedia article](http://en.wikipedia.org/wiki/MIT_License).