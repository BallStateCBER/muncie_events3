<?php
/**
 * Routes configuration
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Core\Plugin;
use Cake\Routing\RouteBuilder;
use Cake\Routing\Router;
use Cake\Routing\Route\DashedRoute;

/**
 * The default class to use for all routes
 *
 * The following route classes are supplied with CakePHP and are appropriate
 * to set as the default:
 *
 * - Route
 * - InflectedRoute
 * - DashedRoute
 *
 * If no call is made to `Router::defaultRouteClass()`, the class used is
 * `Route` (`Cake\Routing\Route\Route`)
 *
 * Note that `Route` does not do any inflections on URLs which will result in
 * inconsistently cased URLs when used with `:plugin`, `:controller` and
 * `:action` markers.
 *
 */

Router::extensions(['ics']);

Router::defaultRouteClass(DashedRoute::class);

Router::scope('/', function (RouteBuilder $routes) {
    // home
    $routes->connect('/', ['controller' => 'events', 'action' => 'index']);

    // Categories
    Router::connect(
        "/:slug/",
        ['controller' => 'events', 'action' => 'category'],
        ['pass' => ['slug']]
    );

    // viewing events
    Router::connect(
        "event/:id",
        ['controller' => 'events', 'action' => 'view'],
        ['id' => '[0-9]+', 'pass' => ['id']]
    );
    // events actions
    foreach (['approve', 'delete', 'edit', 'edit_series', 'location', 'publish'] as $action) {
        Router::connect(
            "/event/$action/:id",
            ['controller' => 'events', 'action' => $action],
            ['id' => '[0-9]+', 'pass' => ['id']]
        );
    }
    // location index
    $routes->connect('/past_locations', ['controller' => 'events', 'action' => 'past_locations']);

    // viewing locations indexes
    $routes->connect(
        '/location/*',
        ['controller' => 'events', 'action' => 'location']
    );

    // viewing locations indexes
    $routes->connect(
        '/location/:location/:direction*',
        ['controller' => 'events', 'action' => 'location'],
        ['pass' => ['slug', 'direction']]
    );

    // viewing event series
    Router::connect(
        "event_series/:id",
        ['controller' => 'eventSeries', 'action' => 'view'],
        ['id' => '[0-9]+', 'pass' => ['id']]
    );

    // eventseries actions
    Router::connect(
        "/event_series/edit/:id",
        ['controller' => 'eventSeries', 'action' => 'edit'],
        ['id' => '[0-9]+', 'pass' => ['id']]
    );

    // moderation
    $routes->connect('/moderate', ['controller' => 'events', 'action' => 'moderate']);

    // pages
    $pages = ['about', 'contact', 'terms'];
    foreach ($pages as $page) {
        $routes->connect('/' . $page, ['controller' => 'pages', 'action' => $page]);
    }

    // search
    $routes->connect('/search', ['controller' => 'events', 'action' => 'search']);

    // Tag
    Router::connect(
        "/tag/:slug/:direction",
        ['controller' => 'events', 'action' => 'tag'],
        ['pass' => ['slug', 'direction']]
    );
    // Tag
    Router::connect(
        "/tag/:slug",
        ['controller' => 'events', 'action' => 'tag'],
        ['pass' => ['slug']]
    );

    // Tags
    Router::scope('/tags', ['controller' => 'tags'], function (RouteBuilder $routes) {
        $routes->connect('/', ['action' => 'index', 'future']);
        $routes->connect('/past', ['action' => 'index', 'past']);
    });

    // user actions
    $userActions = ['account', 'login', 'logout', 'register'];
    foreach ($userActions as $action) {
        $routes->connect('/' . $action, ['controller' => 'users', 'action' => $action]);
    }

    // viewing users
    Router::connect(
        "user/:id/*",
        ['controller' => 'users', 'action' => 'view'],
        ['id' => '[0-9]+', 'pass' => ['id']]
    );

    // widgets
    $routes->connect('/widgets', ['controller' => 'widgets', 'action' => 'index']);
    Router::scope('/widgets/customize', ['controller' => 'widgets'], function (RouteBuilder $routes) {
        $routes->connect('/feed', ['action' => 'customizeFeed']);
        $routes->connect('/month', ['action' => 'customizeMonth']);
    });

    // downloadable content
    Router::connect(
        "/event/:id.ics",
        ['controller' => 'events',
        'action' => 'ics'],
        ['id' => '[0-9]+', 'pass' => ['id']]
    );

    /**
     * Connect catchall routes for all controllers.
     *
     * Using the argument `DashedRoute`, the `fallbacks` method is a shortcut for
     *    `$routes->connect('/:controller', ['action' => 'index'], ['routeClass' => 'DashedRoute']);`
     *    `$routes->connect('/:controller/:action/*', [], ['routeClass' => 'DashedRoute']);`
     *
     * Any route class can be used with this method, such as:
     * - DashedRoute
     * - InflectedRoute
     * - Route
     * - Or your own route class
     *
     * You can remove these routes once you've connected the
     * routes you want in your application.
     */
    $routes->fallbacks(DashedRoute::class);
});

/**
 * Load all plugin routes.  See the Plugin documentation on
 * how to customize the loading of plugin routes.
 */
Plugin::routes();
