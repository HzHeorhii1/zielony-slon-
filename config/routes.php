<?php
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\Route;
use App\Controller;

$routes = new RouteCollection();

$routes->add('home', new Route('/', ['_controller' => Controller\HomeController::class . '::index']));
$routes->add('get_user_schedule', new Route('/api/user/schedule', ['_controller' => Controller\UserScheduleController::class . '::getUserSchedule']));
$routes->add('scrape_data', new Route('/api/scrape', ['_controller' => Controller\ScraperController::class . '::scrapeData']));
$routes->add('get_suggestions', new Route('/api/suggestions/{kind}', ['_controller' => Controller\ApiController::class . '::getSuggestions']));
// new router
$routes->add('run_scrape', new Route('/api/run-scrape', ['_controller' => Controller\ScraperController::class . '::runScraper']));
// New routes for CRUD operations on entities
$routes->add('create_entity', new Route('/api/entity/{entity}', ['_controller' => Controller\EntityController::class . '::createEntity'], methods: ['POST']));
$routes->add('get_entity', new Route('/api/entity/{entity}/{id}', ['_controller' => Controller\EntityController::class . '::getEntity'], methods: ['GET']));
$routes->add('update_entity', new Route('/api/entity/{entity}/{id}', ['_controller' => Controller\EntityController::class . '::updateEntity'], methods: ['PUT']));
$routes->add('delete_entity', new Route('/api/entity/{entity}/{id}', ['_controller' => Controller\EntityController::class . '::deleteEntity'], methods: ['DELETE']));

return $routes;