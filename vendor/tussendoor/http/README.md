
# WordPress HTTP package
A simple wrapper for the default HTTP functions in WordPress.

## Install

Via Composer

``` bash
$ composer require Tussendoor/Http
```

## Usage

### Basic usage
Request an URL and return the response.

```php
<?php

use Tussendoor\Http\Client;

$client = new Client();
$response = $client->get('https://someurl.com/')->send();

// $response instanceof Tussendoor\Http\Response
$response->getBody(); // string
$response->getHeaders(); // array
$response->getHeader('Accept-Encoding'); // application/json
$response->getStatusCode(); // 200
```

### Post data
Post some data to an URL and return the response.
```php
<?php

use Tussendoor\Http\Client;

$client = new Client();
$response = $client->post('https://someurl.com/', [
    'foo' => 'bar', 
    'input' => 1337,
])->send();
```

### Additional headers
Set additional headers, like an API token.
```php
<?php

use Tussendoor\Http\Client;

$client = new Client();
$response = $client->get('https://someurl.com/')
    ->setHeader('api-token', 'xxxxx')
    ->send();
```

### Additional arguments
Since we're using default WordPress functions to execute the request, it's possible to set some additional arguments. Check [the WordPress documentation](https://developer.wordpress.org/reference/classes/WP_Http/request/) for a full list.
```php
<?php

use Tussendoor\Http\Client;

$client = new Client();
$response = $client->get('https://someurl.com/')
    ->setArgument('user-agent', 'my user agent string')
    ->setArgument('cookies', [
        'cookie-name'       => 'value',
        'phpsessionid'      => 'loremipsum'
    ])->send();
```

Some arguments have a shortcut within the Request instance:
```php
<?php

use Tussendoor\Http\Client;

$client = new Client();
$client->get('https://someurl.com')
    ->timeout($timeout) // in seconds
    ->redirects($hops) // The amount of redirects (hops) to follow
    ->agent($agent) // User-Agent string
    ->blocking($blocking) // Wether or not to create a non-blocking request
    ->send();
```

### Relative URLs
When creating a `Client` instance, the constructor accepts a base URL. Every subsequent call to `get()`, `post()` or `request()` will only need a relative URL. This is especially usefull when wrapping an API.

```php
<?php

use Tussendoor\Http\Client;

$client = new Client('https://mybaseurl.com');
$response = $client->get('/some/endpoint/')->send();
```

### Safe URLs
Under the hood we're using WordPress functions to execute the request. In every case we're using the _safe_ version of that function: `wp_safe_remote_get()`, `wp_safe_remote_post()`, `wp_safe_remote_request()`.

This means WordPress does some additional checks to make sure that the URL is valid and safe:
1. The protocol is http, https or ssl
2. There's no username and password combination in the URL
3. It's not a local url (localhost, local network)
4. Port 80, 443 or 8080

Use the filter `http_request_host_is_external` to allow local urls. 

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Testing

``` bash
$ composer test
```

## Security

If you discover any security related issues, please email sander@tussendoor.nl instead of using the issue tracker.

## Credits

- Sander de Kroon