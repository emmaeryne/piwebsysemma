<?php

namespace App\Service;

use Symfony\Component\HttpClient\HttpClient;

class WeatherService
{
    private string $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function getWeatherData(string $city): ?array
    {
        $client = HttpClient::create();
        $url = sprintf('http://api.openweathermap.org/data/2.5/weather?q=%s&appid=%s&units=metric', $city, $this->apiKey);

        try {
            $response = $client->request('GET', $url);
            return $response->toArray();
        } catch (\Exception $e) {
            return null;
        }
    }
}