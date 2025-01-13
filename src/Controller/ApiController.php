<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\HttpKernel\KernelInterface;

class ApiController
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client();
    }


    public function getSuggestions(Request $request, string $kind): JsonResponse
    {
        $query = $request->query->get('query');
        if (!$query) {
            return new JsonResponse([]);
        }
        try {
            $url =
                "https://plan.zut.edu.pl/schedule.php?kind={$kind}&query=" .
                urlencode($query);

            $response = $this->client->get($url);
            $data = json_decode($response->getBody(), true);

            if ($data === null && json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception("Invalid JSON response from plan.zut.edu.pl");
            }
            return new JsonResponse($data);
        } catch (GuzzleException $e) {
            return new JsonResponse(
                [
                    "error" =>
                        "Failed to fetch suggestions from plan.zut.edu.pl: " .
                        $e->getMessage(),
                ],
                500
            );
        } catch (\Exception $e) {
            return new JsonResponse(["error" => $e->getMessage()], 500);
        }
    }
}