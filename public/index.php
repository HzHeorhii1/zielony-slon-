<?php

require_once __DIR__ . "/../vendor/autoload.php";

use App\Utils\Container;
use App\Utils\EntityManagerCreator;
use Dotenv\Dotenv;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RequestContext;
use App\Service\ScheduleCleanupService;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . "/../");
$dotenv->load();

// Create Request object
$request = Request::createFromGlobals();

// Load routes
$routes = include __DIR__ . "/../config/routes.php";

// Context
$context = new RequestContext();
$context->fromRequest($request);

// Url Matcher
$matcher = new UrlMatcher($routes, $context);

// Event Dispatcher
$dispatcher = new EventDispatcher();

// Entity Manager
$entityManager = EntityManagerCreator::createEntityManager();

// Create Container
$container = new Container($entityManager);
// Create cleanup service
$scheduleCleanupService = $container->get(ScheduleCleanupService::class); // Отримання сервісу

// Controller Resolver
$controllerResolver = new ControllerResolver();


// HttpKernel
$kernel = new HttpKernel($dispatcher, $controllerResolver);


// Schedule cleanup logic
$currentDate = new \DateTime();
$summerSemesterEndDate = new \DateTime('2024-07-20');
$winterSemesterEndDate = new \DateTime('2024-02-15');
if ($currentDate->format('m-d') === $summerSemesterEndDate->format('m-d') || $currentDate->format('m-d') === $winterSemesterEndDate->format('m-d')) {
    $scheduleCleanupService->cleanupUserSchedules();
}

try {
    $parameters = $matcher->match($request->getPathInfo());
    $request->attributes->add($parameters);
    $controller = $parameters["_controller"];
    if (is_string($controller)) {
        $controllerParts = explode("::", $controller);
        $controllerName = $controllerParts[0];
        $methodName = $controllerParts[1] ?? "__invoke";
        $controllerInstance = $container->get($controllerName);
        $controller = [$controllerInstance, $methodName];
    }
    $request->attributes->set("_controller", $controller);
    $response = $kernel->handle($request);
} catch (\Symfony\Component\Routing\Exception\ResourceNotFoundException $e) {
    $response = new \Symfony\Component\HttpFoundation\JsonResponse(
        ["error" => "Not Found"],
        404
    );
} catch (\Exception $e) {
    $response = new \Symfony\Component\HttpFoundation\JsonResponse(
        ["error" => "Internal Server Error: " . $e->getMessage()],
        500
    );
}
$response->send();