<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// API Routes
$routes->group('api', ['namespace' => 'App\Controllers\API'], static function ($routes) {
    // ========================
    // Auth Routes
    // ========================
    $routes->post('auth/register', 'AuthController::register');
    $routes->post('auth/login', 'AuthController::login');
    $routes->post('auth/logout', 'AuthController::logout', ['filter' => 'jwt']);
    $routes->get('auth/me', 'AuthController::me');

    // ========================
    // Forum Routes
    // ========================
    $routes->get('forums', 'ForumController::index');
    $routes->post('forums', 'ForumController::store', ['filter' => 'jwt']);
    $routes->get('forums/(:num)', 'ForumController::show/$1');
    $routes->put('forums/(:num)', 'ForumController::update/$1');
    $routes->delete('forums/(:num)', 'ForumController::delete/$1');

    // ========================
    // Task Routes
    // ========================
    $routes->get('tasks', 'TaskController::index');
    $routes->post('tasks', 'TaskController::store', ['filter' => 'jwt']);
    $routes->get('tasks/(:num)', 'TaskController::show/$1');
    $routes->put('tasks/(:num)', 'TaskController::update/$1');
    $routes->delete('tasks/(:num)', 'TaskController::delete/$1');

    // ========================
    // Note Routes
    // ========================
    $routes->get('notes', 'NoteController::index');
    $routes->post('notes', 'NoteController::create', ['filter' => 'jwt']);
    $routes->get('notes/(:num)', 'NoteController::show/$1');
    $routes->put('notes/(:num)', 'NoteController::update/$1');
    $routes->delete('notes/(:num)', 'NoteController::delete/$1');

    // ========================
    // Discussion Routes
    // ========================
    $routes->get('discussions', 'DiscussionController::index');
    $routes->post('discussions', 'DiscussionController::store', ['filter' => 'jwt']);
    $routes->get('discussions/(:num)', 'DiscussionController::show/$1');
    $routes->put('discussions/(:num)', 'DiscussionController::update/$1');
    $routes->delete('discussions/(:num)', 'DiscussionController::delete/$1');

    // ========================
    // Reminder Routes
    // ========================
    $routes->get('reminders', 'ReminderController::index');
    $routes->post('reminders', 'ReminderController::create', ['filter' => 'jwt']);
    $routes->delete('reminders/(:num)', 'ReminderController::delete/$1');
});
