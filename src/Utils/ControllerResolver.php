<?php

namespace App\Utils;
class ControllerResolver
{
    public function getController(Request $request): callable
    {
        $controller = $request->attributes['_controller'] ?? null;
        if (is_callable($controller)) {
            return $controller;
        }
        throw new \Exception("Controller is not callable");
    }
}