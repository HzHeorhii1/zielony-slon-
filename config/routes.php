<?php

use App\Controller;
use App\Utils\Route;
use App\Utils\RouteCollection;

$routes = new RouteCollection();

$routes->add('home', new Route('/', [Controller\HomeController::class, 'index']));
$routes->add('get_user_schedule', new Route('/api/user/schedule', [Controller\UserScheduleController::class, 'getUserSchedule']));
$routes->add('scrape_data', new Route('/api/scrape', [Controller\ScraperController::class, 'scrapeData']));
$routes->add('get_suggestions', new Route('/api/suggestions/{kind}', [Controller\ApiController::class, 'getSuggestions']));
$routes->add('run_scrape', new Route('/api/run-scrape', [Controller\ScraperController::class, 'runScraper']));
$routes->add('create_entity', new Route('/api/entity/{entity}', [Controller\EntityController::class, 'createEntity'], ['POST']));
$routes->add('get_entity', new Route('/api/entity/{entity}/{id}', [Controller\EntityController::class, 'getEntity'], ['GET']));
$routes->add('update_entity', new Route('/api/entity/{entity}/{id}', [Controller\EntityController::class, 'updateEntity'], ['PUT']));
$routes->add('delete_entity', new Route('/api/entity/{entity}/{id}', [Controller\EntityController::class, 'deleteEntity'], ['DELETE']));

return $routes;