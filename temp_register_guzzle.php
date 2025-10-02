<?php
require __DIR__.'/vendor/autoload.php';
use GuzzleHttp\Client;
$client = new Client(['base_uri' => 'http://127.0.0.1:8000']);
try {
    $res = $client->request('POST', '/api/register', [
        'multipart' => [
            ['name' => 'firstname', 'contents' => 'GTest'],
            ['name' => 'lastname', 'contents' => 'User'],
            ['name' => 'email', 'contents' => 'testreg_gz_' . rand(1000,9999) . '@example.com'],
            ['name' => 'password', 'contents' => 'Password123'],
            ['name' => 'c_password', 'contents' => 'Password123']
        ],
        'http_errors' => false,
        'verify' => false
    ]);
    echo $res->getStatusCode() . "\n";
    echo (string)$res->getBody();
} catch (\Throwable $e) {
    echo 'error: ' . $e->getMessage();
}
