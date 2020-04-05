<?php


namespace BeeJee\Kernel;

use BeeJee\Kernel\Exception\ForbiddenException;
use BeeJee\Service\AuthService;
use FastRoute\Dispatcher as RouteDispatcher;
use FastRoute\Dispatcher\GroupCountBased;
use Illuminate\Database\Capsule\Manager as CapsuleManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\Handler\NativeFileSessionHandler;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Twig\Environment as Twig;

class Kernel
{
    /**
     * @var GroupCountBased
     */
    private $routeDispatcher;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Twig
     */
    private $twig;

    /**
     * Kernel constructor.
     * @param GroupCountBased $routeDispatcher
     * @param Config $config
     * @param Twig $twig
     */
    public function __construct(GroupCountBased $routeDispatcher, Config $config, Twig $twig)
    {
        $this->routeDispatcher = $routeDispatcher;
        $this->config = $config;
        $this->twig = $twig;
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Exception
     */
    public function handle(Request $request): Response
    {
        $httpMethod = $request->getMethod();
        $pathInfo = $request->getPathInfo();

        $routeInfo = $this->routeDispatcher->dispatch($httpMethod, $pathInfo);
        switch ($routeInfo[0]) {
            case RouteDispatcher::NOT_FOUND:
                return new Response('Not found', 404);

            case RouteDispatcher::METHOD_NOT_ALLOWED:
                $allowedMethods = $routeInfo[1];
                return new Response('Method Not Allowed. Allowed methods are: ' . implode(', ', $allowedMethods), 405);

            case RouteDispatcher::FOUND:
                $this->initEloquent();
                $this->initSession($request);
                $this->initUser($request);
                [, $handler, $vars] = $routeInfo;
                try {
                    $responseData = $this->handleRoute($request, $handler, $vars);
                } catch (ForbiddenException $ex) {
                    return new Response($ex->getMessage(), 403);
                }

                return new Response($responseData);
        }

        throw new \Exception('Can\'t handle uri ' . $request->getRequestUri());
    }

    private function initEloquent()
    {
        $capsule = new CapsuleManager();

        $capsule->addConnection([
            "driver" => $this->config->get('DB_DRIVER'),
            "host" => $this->config->get('DB_HOST'),
            "database" => $this->config->get('DB_NAME'),
            "username" => $this->config->get('DB_USER'),
            "password" => $this->config->get('DB_PASSWORD')

        ]);

        $capsule->getDatabaseManager();
        $capsule->bootEloquent();
    }

    private function initUser(Request $request): void
    {
        $session = $request->getSession();
        $user = AuthService::createUserFromSession($session);
        $request->attributes->set('current_user', $user);
    }

    /**
     * @param Request $request
     * @param string $handler
     * @param array $vars
     * @return string
     * @throws ForbiddenException
     */
    private function handleRoute(Request $request, string $handler, array $vars): string
    {
        $routeHandler = new RouteHandler($this->twig, $handler);
        $request->attributes->add($vars);

        return $routeHandler->handle($request);
    }

    private function initSession(Request $request)
    {
        $sessionOptions = array('name' => $this->config->get('SESSION_ID'), 'cookie_path' => '/');
        $savePath = realpath(__DIR__ . '/../../' . $this->config->get('SESSION_FILE_STORAGE'));
        $storage = new NativeSessionStorage($sessionOptions, new NativeFileSessionHandler($savePath));

        $session = new Session($storage);
        $session->start();
        $request->setSession($session);
    }
}