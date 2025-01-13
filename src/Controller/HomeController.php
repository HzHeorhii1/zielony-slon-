<?php

namespace App\Controller;

use Symfony\Component\HttpFoundation\Response;

class HomeController
{
    public function index(): Response
    {
        $filePath = __DIR__ . "/../../public/index.html";
        if (!file_exists($filePath)) {
            return new Response("index.html not found", 404);
        }
        $content = file_get_contents($filePath);

        return new Response($content, 200, ["Content-Type" => "text/html"]);
    }
}
