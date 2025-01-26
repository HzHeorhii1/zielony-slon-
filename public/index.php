<?php
require_once __DIR__ . "/../bootstrap.php";

use App\Utils\Container;
use App\Utils\EntityManagerCreator;
use App\Utils\Request;
use App\Utils\ControllerResolver;
use App\Utils\HttpKernel;
use App\Utils\UrlMatcher;
use App\Utils\RequestContext;
use App\Service\ScheduleCleanupService;
use App\Utils\JsonResponse;

// Load environment variables
$dotenvPath = __DIR__ . DIRECTORY_SEPARATOR . '../.env';
if (file_exists($dotenvPath)) {
    $env = parse_ini_file($dotenvPath);
    foreach ($env as $key => $value) {
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}

// Create Request object
$request = Request::createFromGlobals();

// Load routes
$routes = include __DIR__ . "/../config/routes.php";

// Context
$context = new RequestContext();
$context->fromRequest($request);
// Url Matcher
$matcher = new UrlMatcher($routes, $context);
// Entity Manager
$entityManager = EntityManagerCreator::createEntityManager();
// Create Container
$container = new Container($entityManager);
// Create cleanup service
$scheduleCleanupService = $container->get(ScheduleCleanupService::class);
// Controller Resolver
$controllerResolver = new ControllerResolver();
// HttpKernel
$kernel = new HttpKernel($controllerResolver);

// schedule cleanup logic
$currentDate = new \DateTime();
$summerSemesterEndDate = new \DateTime('2024-07-20');
$winterSemesterEndDate = new \DateTime('2024-02-15');
if ($currentDate->format('m-d') === $summerSemesterEndDate->format('m-d') || $currentDate->format('m-d') === $winterSemesterEndDate->format('m-d')) {
    $scheduleCleanupService->cleanupUserSchedules();
}

try {
    $parameters = $matcher->match($request->getPathInfo());
    $request->add($parameters);
    $controller = $parameters["_controller"];
    if (is_array($controller)) {
        $controllerName = $controller[0];
        $methodName = $controller[1] ?? "__invoke";
        $controllerInstance = $container->get($controllerName);
        $controller = [$controllerInstance, $methodName];
    }

    $request->attributes["_controller"] = $controller;
    $response = $kernel->handle($request);

} catch (\App\Utils\ResourceNotFoundException $e) {
    $response = new JsonResponse(
        ["error" => "Not Found"],
        404
    );
} catch (\Exception $e) {
    $response = new JsonResponse(
        ["error" => "Internal Server Error: " . $e->getMessage()],
        500
    );
}
$response->send();