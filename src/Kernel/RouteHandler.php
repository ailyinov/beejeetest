<?php


namespace BeeJee\Kernel;


use BeeJee\Kernel\Exception\ForbiddenException;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment as Twig;

class RouteHandler
{
    /**
     * @var string
     */
    private $class;

    /**
     * @var string
     */
    private $method;

    /**
     * @var string
     */
    private $methodPermission;

    /**
     * @var Twig
     */
    private $twig;

    /**
     * RouteHandler constructor.
     * @param Twig $twig
     * @param string $handler
     */
    public function __construct(Twig $twig, string $handler)
    {
        [$class, $method] = explode('::', $handler);
        $this->method = $this->parseRoutePermissions($method);
        $this->class =$class;
        $this->twig = $twig;
    }

    /**
     * @param Request $request
     * @return string
     * @throws ForbiddenException
     */
    public function handle(Request $request): string
    {
        $this->checkPermissions($request);
        $handlerInstance = new $this->class($this->twig, $request);
        $response = call_user_func([$handlerInstance, $this->method]);

        return $response;
    }

    private function parseRoutePermissions(string $method): string
    {
        $methodData = explode('|', $method);
        if (count($methodData) == 2) {
            [$method, $permissions] = $methodData;
            [, $permission] = explode(':', $permissions);
            $this->methodPermission = $permission;

            return $method;
        }

        return $method;
    }

    /**
     * @param Request $request
     * @throws ForbiddenException
     */
    private function checkPermissions(Request $request): void
    {
        if ($this->methodPermission && !$request->get('current_user')->hasPermission($this->methodPermission)) {
            throw new ForbiddenException();
        }
    }
}