<?php

/**
 * @author  SAS OpenXtrem <dev@openxtrem.com>
 * @license https://www.gnu.org/licenses/gpl.html GNU General Public License
 * @license https://www.openxtrem.com/licenses/oxol.html OXOL OpenXtrem Open License
 */

use Ox\Status\StatusAuthListener;
use Ox\Status\StatusErrorHandler;
use Ox\Status\StatusExceptionListener;
use Ox\Status\StatusRequest;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Controller\ControllerResolver;
use Symfony\Component\HttpKernel\EventListener\RouterListener;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\Routing\Loader\YamlFileLoader;
use Symfony\Component\Routing\Router;

/**
 * STATUS Front Controller
 */

// Autoload
require __DIR__ . '/../vendor/autoload.php';

// Error handler
StatusErrorHandler::setHandlers();

// Request
$request       = StatusRequest::create();
$request_stack = new RequestStack();

// Router
$loader = new YamlFileLoader(new FileLocator(__DIR__));
$router = new Router($loader, 'routes.yml');

// Event dispatcher
$dispatcher = new EventDispatcher();
$dispatcher->addSubscriber(new StatusAuthListener());
$dispatcher->addSubscriber(new RouterListener($router, $request_stack));
$dispatcher->addSubscriber(new StatusExceptionListener());

// Kernel
$resolver = new ControllerResolver();
$kernel   = new HttpKernel($dispatcher, $resolver);
$response = $kernel->handle($request);

// Output
$response->send();
