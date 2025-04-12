<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;

class GiphyService
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function searchGif(string $query): ?string
    {
        $client = HttpClient::create();
        $url = sprintf('https://api.giphy.com/v1/gifs/search?api_key=%s&q=%s&limit=1', $this->apiKey, urlencode($query));

        try {
            $response = $client->request('GET', $url);
            $data = $response->toArray();
            return $data['data'][0]['images']['original']['url'] ?? null;
        } catch (\Exception $e) {
            return null;
        }
    }

    public function getDefaultGif(): string
    {
        return 'https://media.giphy.com/media/xT9DPFwYqUqK02v7hC/giphy.gif';
    }
}