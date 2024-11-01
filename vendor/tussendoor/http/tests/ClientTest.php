<?php

namespace Tussendoor\Http\Tests;

use Tussendoor\Http\Client;
use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{
    public function testGetRequest()
    {
        $client = new Client;
        $request = $client->get('https://jsonplaceholder.typicode.com/posts/1');
        $this->assertInstanceOf('Tussendoor\Http\Request', $request);
    }
}
