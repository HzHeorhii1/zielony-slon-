<?php

namespace App\Utils;

class HttpKernel
{
    private ControllerResolver $controllerResolver;

    public function __construct(ControllerResolver $controllerResolver)
    {
        $this->controllerResolver = $controllerResolver;
    }
    public function handle(Request $request): Response
    {
        $controller = $this->controllerResolver->getController($request);
        $kind = $request->get('kind');
        // p4ss $kind if it exists
        return call_user_func_array($controller, $kind ? [$request, $kind] : [$request]);
    }
}