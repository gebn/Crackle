# Crackle

Crackle is an easy to use, object-oriented HTTP client.

## Features

 - GET, POST
 - Authentication (basic, digest, NTLM)
 - File uploading
 - Easy header management for both requests and responses.
 - Send parameters with duplicate names and files in a single request.
 - Simultaneous processing of multiple requests
 - Many more...

## Requirements

 - PHP >= 5.3.0
 - cURL PHP extension

## Getting Started

For additional examples, see the contents of `/Examples`.

### A simple GET request

	use \Crackle\Requests\GETRequest;
	use \Crackle\Requester;
	
    $request = new GETRequest('http://icanhazip.com');
	Requester::fire($request);
	if(!$request->isError()) {
		echo $request->getResponse()->getContent();
	}

### A POST request with callback

A callback can be set on each request that is run immediately after the request finishes. This function is given the original request object executed:

	use \Crackle\Requests\POSTRequest;
	use \Crackle\Requester;
	
	$request = new POSTRequest('https://example.com');
	$request->getHeaders()->set('custom-header', 'value');
	$request->addField('name[]', 'value1');
	$request->addField('name[]', 'value2');
	$request->addFile('upload', 'photo.jpg');
	$request->addFile('another', 'document.docx');
	$request->setCallback(function($request) {
		if(!$request->isError()) {
			$request->getResponse()->writeTo('/home/gebn');
		}
	});
	
	Requester::fire($request);

### Parallel requests

	use \Crackle\Requests\GETRequest;
	use \Crackle\Requester;
	
	$req1 = new GETRequest('http://www.bbc.co.uk/news/');
	$req1->setCallback(function($request) {
		echo '$req1 finished';
	});
	
	$req2 = new GETRequest('https://twitter.com/#!/');
	$req2->setCallback(function($request) {
		echo '$req2 finished';
	});

	$requester = new Requester();
	$requester->queue($req1);
	$requester->queue($req2);
	$requester->fireAll();

N.B. All types of requests can be done simultaneously - you can mix verbs.
	
### Setting custom options using the handle

Crackle allows you to directly manipulate requests through their underlying cURL session. This handle can be retrieved by calling `getHandle()` on a `\Crackle\Requests\Request` object:

	$request = new GETRequest('https://example.com');
	curl_setopt($request->getHandle(), CURLOPT_SSL_VERIFYPEER, false);

The following options are set by Crackle immediately before execution, so setting them manually will have no effect:

#### All requests

 - `CURLOPT_HEADER` (set headers on the `\Crackle\Headers` object returned by `getHeaders()`)
 - `CURLOPT_URL` (use `setUrl()`)
 - `CURLOPT_RETURNTRANSFER`

#### POST requests

 - `CURLOPT_POST`
 - `CURLOPT_POSTFIELDS`

#### Authenticated requests

 - `CURLOPT_USERPWD`
 - `CURLOPT_HTTPAUTH`

## Development

[Click here](https://trello.com/b/91q94waP/crackle) to go to this project's public Trello board.

## Licence

Crackle is released under the MIT Licence. For more information, see the [Wikipedia article](http://en.wikipedia.org/wiki/MIT_License).