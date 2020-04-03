<?php


namespace BeeJee\Controller;


use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Twig\Environment as Twig;

abstract class AbstractController
{
    /**
     * @var Twig
     */
    private $twig;

    /**
     * @var string
     */
    private $currentPath;

    /**
     * @var Request
     */
    protected $request;

    /**
     * AbstractController constructor.
     * @param Twig $twig
     * @param Request $request
     * @param string $currentPath
     */
    public function __construct(Twig $twig, Request $request, string $currentPath)
    {
        $this->twig = $twig;
        $this->currentPath = $currentPath;
        $this->request = $request;
    }

    protected function render(string $template, array $context)
    {
        $context['current_user'] = $this->request->get('current_user');

        return $this->twig->render($template, $context);
    }

    protected function getCurrentPath(): string
    {
        return $this->currentPath;
    }

    protected function redirect(string $path): void
    {
        $redirectResponse = new RedirectResponse($path);
        $redirectResponse->send();
    }
}